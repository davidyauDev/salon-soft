<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Stylist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Commission::query()->with(['stylist.user', 'record.service']);

        if ($request->filled('from')) {
            $query->whereDate('calculated_at', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('calculated_at', '<=', $request->string('to'));
        }

        $commissions = $query->latest('calculated_at')->paginate(25);

        return response()->json($commissions);
    }

    public function summary(Request $request): JsonResponse
    {
        $from = $request->filled('from') ? $request->string('from') : null;
        $to = $request->filled('to') ? $request->string('to') : null;

        $summary = Stylist::query()
            ->with('user')
            ->withSum(['commissions as total_commission' => function ($sub) use ($from, $to) {
                if ($from) {
                    $sub->whereDate('calculated_at', '>=', $from);
                }
                if ($to) {
                    $sub->whereDate('calculated_at', '<=', $to);
                }
            }], 'amount')
            ->orderByDesc('total_commission')
            ->get()
            ->map(function (Stylist $stylist) {
                return [
                    'stylist' => $stylist->user?->name,
                    'total' => (float) ($stylist->total_commission ?? 0),
                ];
            });

        return response()->json($summary);
    }
}
