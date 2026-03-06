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
            ->with(['units', 'stockLots', 'category', 'brand'])
            ->withSum('stockLots as stock_total', 'quantity_remaining')
            ->orderBy('name')
            ->get()
            ->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type?->value ?? $item->type,
                    'base_unit' => $item->base_unit,
                    'sale_price' => $item->sale_price,
                    'stock_minimum' => $item->stock_minimum,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'reorder_point' => $item->reorder_point,
                    'reorder_qty' => $item->reorder_qty,
                    'stock_total' => (float) ($item->stock_total ?? 0),
                    'category' => $item->category ? [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                    ] : null,
                    'brand' => $item->brand ? [
                        'id' => $item->brand->id,
                        'name' => $item->brand->name,
                    ] : null,
                    'units' => $item->units,
                    'lots' => $item->stockLots,
                ];
            });

        return response()->json($items);
    }
}
