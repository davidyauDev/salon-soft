<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Models\StockLot;
use Illuminate\Support\Collection;

class StockAllocator
{
    /**
     * @return Collection<int, array{lot: StockLot, quantity: float, unit_cost: float}>
     */
    public function allocate(Item $item, float $quantityBase): Collection
    {
        $remaining = $quantityBase;
        $allocations = collect();

        $lots = StockLot::query()
            ->where('item_id', $item->id)
            ->available()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->orderBy('purchased_at')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, (float) $lot->quantity_remaining);

            if ($take <= 0) {
                continue;
            }

            $allocations->push([
                'lot' => $lot,
                'quantity' => $take,
                'unit_cost' => (float) $lot->cost_per_base,
            ]);

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new InsufficientStockException("Insufficient stock for item {$item->id}.");
        }

        return $allocations;
    }
}
