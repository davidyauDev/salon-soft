<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()->orderBy('name')->get();

        return response()->json(BrandResource::collection($brands));
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::query()->create($request->validated());

        return response()->json(new BrandResource($brand), 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json(new BrandResource($brand));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());

        return response()->json(new BrandResource($brand));
    }

    public function destroy(Request $request, Brand $brand): JsonResponse
    {
        if ($brand->items()->exists()) {
            return response()->json(['message' => 'Brand has items and cannot be deleted.'], 422);
        }

        $brand->delete();

        return response()->json(status: 204);
    }
}
