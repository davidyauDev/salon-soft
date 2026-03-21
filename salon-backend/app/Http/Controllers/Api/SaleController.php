<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function index(): JsonResponse
    {
        $sales = \App\Models\Sale::query()
            ->whereDoesntHave('serviceRecords', fn ($query) => $query->where('source', 'express'))
            ->with(['items.item', 'client'])
            ->latest('sold_at')
            ->paginate(20);

        return response()->json(SaleResource::collection($sales));
    }

    public function store(StoreSaleRequest $request, SaleService $service): JsonResponse
    {
        $sale = $service->create($request->validated(), $request->user()?->id);

        return response()->json(new SaleResource($sale->load('items')), 201);
    }
}
