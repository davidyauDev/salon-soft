<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryMoveType;
use App\Enums\ItemType;
use App\Models\Commission;
use App\Models\InventoryMove;
use App\Models\Item;
use App\Models\ServiceRecord;
use App\Models\Stylist;
use Illuminate\Support\Facades\DB;

class ServiceRecordService
{
    public function __construct(
        private readonly UnitConverter $unitConverter,
        private readonly StockAllocator $stockAllocator,
    ) {
    }

    /**
     * @param array{
     *   service_id:int,
     *   stylist_id:int,
     *   client_id?:int|null,
     *   total_amount:float,
     *   payment_method?:string|null,
     *   performed_at?:string|null,
     *   notes?:string|null,
     *   consumptions?: array<int, array{item_id:int, quantity:float, unit:string}>
     * } $payload
     */
    public function create(array $payload, ?int $userId): ServiceRecord
    {
        return DB::transaction(function () use ($payload, $userId) {
            $record = ServiceRecord::query()->create([
                'service_id' => $payload['service_id'],
                'stylist_id' => $payload['stylist_id'],
                'client_id' => $payload['client_id'] ?? null,
                'total_amount' => $payload['total_amount'],
                'status' => 'completed',
                'payment_method' => $payload['payment_method'] ?? null,
                'performed_at' => $payload['performed_at'] ?? now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            if (!empty($payload['consumptions'])) {
                foreach ($payload['consumptions'] as $line) {
                    /** @var Item $item */
                    $item = Item::query()->findOrFail($line['item_id']);

                    if (!$item->is_active) {
                        throw new \InvalidArgumentException('Item is inactive.');
                    }

                    if ($item->type === ItemType::SellOnly) {
                        throw new \InvalidArgumentException('Item not available for service consumption.');
                    }

                    $factor = $this->unitConverter->factorToBase($item, $line['unit']);
                    $quantityBase = (float) $line['quantity'] * $factor;

                    $allocations = $this->stockAllocator->allocate($item, $quantityBase);

                    foreach ($allocations as $allocation) {
                        $lot = $allocation['lot'];
                        $allocQty = $allocation['quantity'];
                        $unitCostBase = $allocation['unit_cost'];

                        $lot->decrement('quantity_remaining', $allocQty);

                        $record->consumptions()->create([
                            'item_id' => $item->id,
                            'stock_lot_id' => $lot->id,
                            'quantity_base' => $allocQty,
                            'unit_cost_base' => $unitCostBase,
                        ]);

                        InventoryMove::query()->create([
                            'item_id' => $item->id,
                            'type' => InventoryMoveType::ServiceConsumption,
                            'quantity_base' => -$allocQty,
                            'unit_cost_base' => $unitCostBase,
                            'reference_type' => 'service',
                            'reference_id' => $record->id,
                            'user_id' => $userId,
                            'moved_at' => $payload['performed_at'] ?? now(),
                        ]);
                    }
                }
            }

            /** @var Stylist $stylist */
            $stylist = Stylist::query()->findOrFail($payload['stylist_id']);
            $rate = (float) $stylist->commission_rate;
            $amount = round(((float) $payload['total_amount']) * $rate / 100, 2);

            Commission::query()->create([
                'service_record_id' => $record->id,
                'stylist_id' => $stylist->id,
                'base_amount' => $payload['total_amount'],
                'rate' => $rate,
                'amount' => $amount,
                'calculated_at' => now(),
            ]);

            return $record->load(['consumptions', 'commission']);
        });
    }
}
