<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\InventoryMoveType;
use App\Http\Controllers\Controller;
use App\Models\InventoryMove;
use App\Models\Item;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function stockLow(): JsonResponse
    {
        $items = Item::query()
            ->withSum('stockLots as stock_total', 'quantity_remaining')
            ->where('is_active', true)
            ->havingRaw('COALESCE(stock_total, 0) <= stock_minimum')
            ->orderByRaw('COALESCE(stock_total, 0) - stock_minimum asc')
            ->get()
            ->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'stock_total' => (float) ($item->stock_total ?? 0),
                    'stock_minimum' => (float) $item->stock_minimum,
                    'base_unit' => $item->base_unit,
                ];
            });

        return response()->json($items);
    }

    public function salesSummary(Request $request): JsonResponse
    {
        $query = Sale::query();

        if ($request->filled('from')) {
            $query->whereDate('sold_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('sold_at', '<=', $request->string('to'));
        }

        $total = (float) $query->sum('total_amount');

        return response()->json([
            'total_sales' => $total,
        ]);
    }

    public function consumptionSummary(Request $request): JsonResponse
    {
        $query = InventoryMove::query()
            ->where('type', InventoryMoveType::ServiceConsumption);

        if ($request->filled('from')) {
            $query->whereDate('moved_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('moved_at', '<=', $request->string('to'));
        }

        $total = (float) $query
            ->selectRaw('COALESCE(SUM(ABS(quantity_base) * unit_cost_base), 0) as total')
            ->value('total');

        return response()->json([
            'total_consumption' => $total,
        ]);
    }
}
