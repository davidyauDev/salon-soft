<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::query()->orderBy('name')->get();

        return response()->json(SupplierResource::collection($suppliers));
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::query()->create($request->validated());

        return response()->json(new SupplierResource($supplier), 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json(new SupplierResource($supplier));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json(new SupplierResource($supplier));
    }

    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        if ($supplier->stockLots()->exists()) {
            return response()->json(['message' => 'Supplier has purchases and cannot be deleted.'], 422);
        }

        $supplier->delete();

        return response()->json(status: 204);
    }
}
