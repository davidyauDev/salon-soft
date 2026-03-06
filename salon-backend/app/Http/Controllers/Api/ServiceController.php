<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()
            ->with(['category', 'locations', 'stylists.user']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q')->toString() . '%';
            $query->where('name', 'like', $term);
        }

        $services = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json(ServiceResource::collection($services));
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $locationIds = $payload['location_ids'] ?? null;
        $stylistIds = $payload['stylist_ids'] ?? null;

        unset($payload['location_ids'], $payload['stylist_ids']);

        $service = Service::query()->create($payload);

        if ($locationIds !== null) {
            $service->locations()->sync($locationIds);
        }

        if ($stylistIds !== null) {
            $service->stylists()->sync($stylistIds);
        }

        $service->load(['category', 'locations', 'stylists.user']);

        return response()->json(new ServiceResource($service), 201);
    }

    public function show(Service $service): JsonResponse
    {
        $service->load(['category', 'locations', 'stylists.user']);

        return response()->json(new ServiceResource($service));
    }

    public function update(UpdateServiceRequest $request, Service $service, AuditLogger $logger): JsonResponse
    {
        $before = $service->getAttributes();

        $payload = $request->validated();
        $locationIds = $payload['location_ids'] ?? null;
        $stylistIds = $payload['stylist_ids'] ?? null;

        unset($payload['location_ids'], $payload['stylist_ids']);

        $service->update($payload);

        if ($locationIds !== null) {
            $service->locations()->sync($locationIds);
        }

        if ($stylistIds !== null) {
            $service->stylists()->sync($stylistIds);
        }

        $service->refresh();
        $service->load(['category', 'locations', 'stylists.user']);
        $logger->log('update', $service, $request->user()?->id, $before, $service->getAttributes(), $request);

        return response()->json(new ServiceResource($service));
    }

    public function destroy(Request $request, Service $service, AuditLogger $logger): JsonResponse
    {
        if ($service->records()->exists()) {
            return response()->json(['message' => 'El servicio tiene atenciones registradas y no puede eliminarse.'], 422);
        }

        $before = $service->getAttributes();
        $logger->log('delete', $service, $request->user()?->id, $before, null, $request);
        $service->delete();

        return response()->json(status: 204);
    }
}
