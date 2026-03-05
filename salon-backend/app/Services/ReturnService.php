<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryMoveType;
use App\Models\InventoryMove;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockLot;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnService
{
    public function __construct(private readonly UnitConverter $unitConverter)
    {
    }

    /**
     * @param array{
     *   sale_id:int,
     *   reason?:string|null,
     *   processed_at?:string|null,
     *   items: array<int, array{sale_item_id:int, quantity:float, unit:string, refund_unit:float}>
     * } $payload
     */
    public function create(array $payload, ?int $userId): SaleReturn
    {
        return DB::transaction(function () use ($payload, $userId) {
            $return = SaleReturn::query()->create([
                'sale_id' => $payload['sale_id'],
                'total_refund' => 0,
                'reason' => $payload['reason'] ?? null,
                'processed_at' => $payload['processed_at'] ?? now(),
            ]);

            $totalRefund = 0.0;

            foreach ($payload['items'] as $line) {
                /** @var SaleItem $saleItem */
                $saleItem = SaleItem::query()->with('item')->findOrFail($line['sale_item_id']);

                if ($saleItem->sale_id !== (int) $payload['sale_id']) {
                    throw new InvalidArgumentException('Return item does not belong to the sale.');
                }

                $factor = $this->unitConverter->factorToBase($saleItem->item, $line['unit']);
                $quantityBase = (float) $line['quantity'] * $factor;

                if ($quantityBase > (float) $saleItem->quantity_base) {
                    throw new InvalidArgumentException('Return quantity exceeds sold quantity.');
                }

                $refundUnitBase = (float) $line['refund_unit'] / $factor;

                $restockedLot = StockLot::query()->create([
                    'item_id' => $saleItem->item_id,
                    'quantity_remaining' => $quantityBase,
                    'cost_per_base' => (float) $saleItem->unit_cost_base,
                    'purchased_at' => $payload['processed_at'] ?? now(),
                    'notes' => 'Return from sale #' . $payload['sale_id'],
                ]);

                $return->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'item_id' => $saleItem->item_id,
                    'restocked_lot_id' => $restockedLot->id,
                    'quantity_base' => $quantityBase,
                    'unit_refund_base' => $refundUnitBase,
                ]);

                InventoryMove::query()->create([
                    'item_id' => $saleItem->item_id,
                    'type' => InventoryMoveType::ReturnIn,
                    'quantity_base' => $quantityBase,
                    'unit_cost_base' => $saleItem->unit_cost_base,
                    'reference_type' => 'return',
                    'reference_id' => $return->id,
                    'user_id' => $userId,
                    'moved_at' => $payload['processed_at'] ?? now(),
                ]);

                $totalRefund += $refundUnitBase * $quantityBase;
            }

            $return->update(['total_refund' => $totalRefund]);

            return $return->load('items');
        });
    }
}
