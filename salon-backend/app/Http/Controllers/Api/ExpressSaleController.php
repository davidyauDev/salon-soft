<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpressSaleRequest;
use App\Http\Resources\ServiceRecordResource;
use App\Models\ServiceRecord;
use App\Services\ExpressSaleService;
use Illuminate\Http\JsonResponse;

class ExpressSaleController extends Controller
{
    public function index(): JsonResponse
    {
        $records = ServiceRecord::query()
            ->where('source', 'express')
            ->with(['service', 'stylist.user', 'client', 'sale.items.item'])
            ->latest('performed_at')
            ->paginate(20);

        return response()->json(ServiceRecordResource::collection($records));
    }

    public function store(StoreExpressSaleRequest $request, ExpressSaleService $service): JsonResponse
    {
        $record = $service->create($request->validated(), $request->user()?->id);

        return response()->json(new ServiceRecordResource($record), 201);
    }
}
