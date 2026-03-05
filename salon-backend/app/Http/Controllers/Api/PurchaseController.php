<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\StockLot;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function store(StorePurchaseRequest $request, PurchaseService $service): JsonResponse
    {
        $lot = $service->create($request->validated(), $request->user()?->id);

        return response()->json($this->presentLot($lot), 201);
    }

    private function presentLot(StockLot $lot): array
    {
        return [
            'id' => $lot->id,
            'item_id' => $lot->item_id,
            'quantity_remaining' => $lot->quantity_remaining,
            'cost_per_base' => $lot->cost_per_base,
            'purchased_at' => $lot->purchased_at,
            'expires_at' => $lot->expires_at,
        ];
    }
}
