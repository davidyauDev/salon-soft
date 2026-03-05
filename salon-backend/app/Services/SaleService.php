<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryMoveType;
use App\Enums\ItemType;
use App\Models\InventoryMove;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly UnitConverter $unitConverter,
        private readonly StockAllocator $stockAllocator,
    ) {
    }

    /**
     * @param array{
     *   client_id?:int|null,
     *   payment_method?:string|null,
     *   sold_at?:string|null,
     *   items: array<int, array{item_id:int, quantity:float, unit:string, unit_price:float}>
     * } $payload
     */
    public function create(array $payload, ?int $userId): Sale
    {
        return DB::transaction(function () use ($payload, $userId) {
            $sale = Sale::query()->create([
                'client_id' => $payload['client_id'] ?? null,
                'total_amount' => 0,
                'status' => 'completed',
                'payment_method' => $payload['payment_method'] ?? null,
                'sold_at' => $payload['sold_at'] ?? now(),
            ]);

            $total = 0.0;

            foreach ($payload['items'] as $line) {
                /** @var Item $item */
                $item = Item::query()->findOrFail($line['item_id']);

                if ($item->type === ItemType::ConsumeOnly) {
                    throw new \InvalidArgumentException('Item not available for sale.');
                }

                $factor = $this->unitConverter->factorToBase($item, $line['unit']);
                $quantityBase = (float) $line['quantity'] * $factor;
                $unitPriceBase = (float) $line['unit_price'] / $factor;

                $allocations = $this->stockAllocator->allocate($item, $quantityBase);

                foreach ($allocations as $allocation) {
                    /** @var \App\Models\StockLot $lot */
                    $lot = $allocation['lot'];
                    $allocQty = $allocation['quantity'];
                    $unitCostBase = $allocation['unit_cost'];

                    $lot->decrement('quantity_remaining', $allocQty);

                    SaleItem::query()->create([
                        'sale_id' => $sale->id,
                        'item_id' => $item->id,
                        'stock_lot_id' => $lot->id,
                        'quantity_base' => $allocQty,
                        'unit_price_base' => $unitPriceBase,
                        'unit_cost_base' => $unitCostBase,
                    ]);

                    InventoryMove::query()->create([
                        'item_id' => $item->id,
                        'type' => InventoryMoveType::Sale,
                        'quantity_base' => -$allocQty,
                        'unit_cost_base' => $unitCostBase,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'user_id' => $userId,
                        'moved_at' => $payload['sold_at'] ?? now(),
                    ]);

                    $total += $unitPriceBase * $allocQty;
                }
            }

            $sale->update(['total_amount' => $total]);

            return $sale->load('items');
        });
    }
}
