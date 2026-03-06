<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::query()
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(ServiceCategoryResource::collection($categories));
    }

    public function store(StoreServiceCategoryRequest $request): JsonResponse
    {
        $category = ServiceCategory::query()->create($request->validated());

        return response()->json(new ServiceCategoryResource($category->loadCount('services')), 201);
    }

    public function show(ServiceCategory $serviceCategory): JsonResponse
    {
        $serviceCategory->loadCount('services');

        return response()->json(new ServiceCategoryResource($serviceCategory));
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): JsonResponse
    {
        $serviceCategory->update($request->validated());
        $serviceCategory->loadCount('services');

        return response()->json(new ServiceCategoryResource($serviceCategory));
    }

    public function destroy(Request $request, ServiceCategory $serviceCategory): JsonResponse
    {
        if ($serviceCategory->services()->exists()) {
            return response()->json(['message' => 'La categoria tiene servicios y no puede eliminarse.'], 422);
        }

        $serviceCategory->delete();

        return response()->json(status: 204);
    }
}
