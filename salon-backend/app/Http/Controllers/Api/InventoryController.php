<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::query()
            ->with(['units', 'stockLots'])
            ->withSum('stockLots as stock_total', 'quantity_remaining')
            ->orderBy('name')
            ->get()
            ->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type?->value ?? $item->type,
                    'base_unit' => $item->base_unit,
                    'sale_price' => $item->sale_price,
                    'stock_minimum' => $item->stock_minimum,
                    'stock_total' => (float) ($item->stock_total ?? 0),
                    'units' => $item->units,
                    'lots' => $item->stockLots,
                ];
            });

        return response()->json($items);
    }
}
