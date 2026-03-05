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
    public function index(): JsonResponse
    {
        $services = Service::query()->orderBy('name')->get();

        return response()->json(ServiceResource::collection($services));
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::query()->create($request->validated());

        return response()->json(new ServiceResource($service), 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json(new ServiceResource($service));
    }

    public function update(UpdateServiceRequest $request, Service $service, AuditLogger $logger): JsonResponse
    {
        $before = $service->getAttributes();
        $service->update($request->validated());

        $service->refresh();
        $logger->log('update', $service, $request->user()?->id, $before, $service->getAttributes(), $request);

        return response()->json(new ServiceResource($service));
    }

    public function destroy(Request $request, Service $service, AuditLogger $logger): JsonResponse
    {
        $before = $service->getAttributes();
        $logger->log('delete', $service, $request->user()?->id, $before, null, $request);
        $service->delete();

        return response()->json(status: 204);
    }
}
