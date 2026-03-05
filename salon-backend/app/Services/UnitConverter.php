<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UnitConverter
{
    public function toBase(Item $item, float $quantity, string $unit): float
    {
        return $quantity * $this->factorToBase($item, $unit);
    }

    public function factorToBase(Item $item, string $unit): float
    {
        $normalizedUnit = Str::lower(trim($unit));

        if (Str::lower($item->base_unit) === $normalizedUnit) {
            return 1.0;
        }

        $unitRow = ItemUnit::query()
            ->where('item_id', $item->id)
            ->whereRaw('lower(unit) = ?', [$normalizedUnit])
            ->first();

        if (!$unitRow) {
            throw new InvalidArgumentException("Unit {$unit} is not configured for item {$item->id}.");
        }

        return (float) $unitRow->factor_to_base;
    }
}
