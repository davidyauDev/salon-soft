<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InventoryMoveType;
use App\Models\InventoryMove;
use App\Models\Item;
use App\Models\StockLot;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private readonly UnitConverter $unitConverter)
    {
    }

    /**
     * @param array{item_id:int, quantity:float, unit:string, cost_per_unit:float, purchased_at?:string|null, expires_at?:string|null, notes?:string|null} $payload
     */
    public function create(array $payload, ?int $userId): StockLot
    {
        return DB::transaction(function () use ($payload, $userId) {
            /** @var Item $item */
            $item = Item::query()->findOrFail($payload['item_id']);

            $factor = $this->unitConverter->factorToBase($item, $payload['unit']);
            $quantityBase = (float) $payload['quantity'] * $factor;
            $unitCostBase = (float) $payload['cost_per_unit'] / $factor;

            $lot = StockLot::query()->create([
                'item_id' => $item->id,
                'quantity_remaining' => $quantityBase,
                'cost_per_base' => $unitCostBase,
                'purchased_at' => $payload['purchased_at'] ?? now(),
                'expires_at' => $payload['expires_at'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            InventoryMove::query()->create([
                'item_id' => $item->id,
                'type' => InventoryMoveType::Purchase,
                'quantity_base' => $quantityBase,
                'unit_cost_base' => $unitCostBase,
                'reference_type' => 'purchase',
                'reference_id' => $lot->id,
                'user_id' => $userId,
                'moved_at' => $payload['purchased_at'] ?? now(),
            ]);

            return $lot;
        });
    }
}
