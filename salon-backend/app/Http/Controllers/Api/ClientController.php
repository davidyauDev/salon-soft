<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::query()->orderBy('full_name')->get();

        return response()->json(ClientResource::collection($clients));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::query()->create($request->validated());

        return response()->json(new ClientResource($client), 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json(new ClientResource($client));
    }

    public function history(Client $client): JsonResponse
    {
        $services = $client->serviceRecords()
            ->with(['service', 'stylist.user'])
            ->latest('performed_at')
            ->get();

        $sales = $client->sales()
            ->with(['items.item'])
            ->latest('sold_at')
            ->get();

        return response()->json([
            'client' => new ClientResource($client),
            'services' => $services,
            'sales' => $sales,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client, AuditLogger $logger): JsonResponse
    {
        $before = $client->getAttributes();
        $client->update($request->validated());

        $client->refresh();
        $logger->log('update', $client, $request->user()?->id, $before, $client->getAttributes(), $request);

        return response()->json(new ClientResource($client));
    }

    public function destroy(Request $request, Client $client, AuditLogger $logger): JsonResponse
    {
        $before = $client->getAttributes();
        $logger->log('delete', $client, $request->user()?->id, $before, null, $request);
        $client->delete();

        return response()->json(status: 204);
    }
}
