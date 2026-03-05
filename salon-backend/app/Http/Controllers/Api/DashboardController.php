<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\InventoryMoveType;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\InventoryMove;
use App\Models\Item;
use App\Models\Sale;
use App\Models\ServiceRecord;
use App\Models\Stylist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now()->toDateString();

        $servicesToday = ServiceRecord::query()
            ->whereDate('performed_at', $today)
            ->where('status', 'completed')
            ->count();

        $salesToday = (float) Sale::query()
            ->whereDate('sold_at', $today)
            ->sum('total_amount');

        $consumptionCostToday = (float) InventoryMove::query()
            ->where('type', InventoryMoveType::ServiceConsumption)
            ->whereDate('moved_at', $today)
            ->selectRaw('COALESCE(SUM(ABS(quantity_base) * unit_cost_base), 0) as total')
            ->value('total');

        $commissionsToday = (float) Commission::query()
            ->whereDate('calculated_at', $today)
            ->sum('amount');

        $alerts = Item::query()
            ->withSum('stockLots as stock_total', 'quantity_remaining')
            ->where('is_active', true)
            ->orderByRaw('COALESCE(stock_total, 0) - stock_minimum asc')
            ->limit(5)
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

        $recentServices = ServiceRecord::query()
            ->with(['service', 'stylist.user', 'client'])
            ->where('status', 'completed')
            ->latest('performed_at')
            ->limit(5)
            ->get()
            ->map(function (ServiceRecord $record) {
                return [
                    'id' => $record->id,
                    'performed_at' => $record->performed_at,
                    'client' => $record->client?->full_name,
                    'service' => $record->service?->name,
                    'stylist' => $record->stylist?->user?->name,
                ];
            });

        $topCommissions = Stylist::query()
            ->with('user')
            ->withSum(['commissions as total_commission' => function ($query) use ($today) {
                $query->whereDate('calculated_at', $today);
            }], 'amount')
            ->orderByDesc('total_commission')
            ->limit(4)
            ->get()
            ->map(function (Stylist $stylist) {
                return [
                    'stylist' => $stylist->user?->name,
                    'total' => (float) ($stylist->total_commission ?? 0),
                ];
            });

        return response()->json([
            'kpis' => [
                'services_today' => $servicesToday,
                'sales_today' => $salesToday,
                'consumption_cost_today' => $consumptionCostToday,
                'commissions_today' => $commissionsToday,
            ],
            'alerts' => $alerts,
            'recent_services' => $recentServices,
            'top_commissions' => $topCommissions,
        ]);
    }
}
