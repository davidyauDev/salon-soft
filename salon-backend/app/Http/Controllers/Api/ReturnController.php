<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReturnRequest;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;

class ReturnController extends Controller
{
    public function store(StoreReturnRequest $request, ReturnService $service): JsonResponse
    {
        $return = $service->create($request->validated(), $request->user()?->id);

        return response()->json([
            'id' => $return->id,
            'sale_id' => $return->sale_id,
            'total_refund' => $return->total_refund,
            'items' => $return->items,
        ], 201);
    }
}
