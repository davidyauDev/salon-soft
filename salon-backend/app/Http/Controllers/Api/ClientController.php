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
    private function resolveFullName(array $payload, ?Client $client = null): string
    {
        $firstName = array_key_exists('first_name', $payload)
            ? (string) ($payload['first_name'] ?? '')
            : (string) ($client?->first_name ?? '');

        $lastName = array_key_exists('last_name', $payload)
            ? (string) ($payload['last_name'] ?? '')
            : (string) ($client?->last_name ?? '');

        $fullName = array_key_exists('full_name', $payload)
            ? (string) ($payload['full_name'] ?? '')
            : (string) ($client?->full_name ?? '');

        $composed = trim(trim($firstName) . ' ' . trim($lastName));

        return $composed !== '' ? $composed : trim($fullName);
    }

    public function index(): JsonResponse
    {
        $clients = Client::query()
            ->withCount('serviceRecords')
            ->withSum('serviceRecords', 'total_amount')
            ->withSum('sales', 'total_amount')
            ->orderBy('full_name')
            ->get();

        return response()->json(ClientResource::collection($clients));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['full_name'] = $this->resolveFullName($payload);

        $client = Client::query()->create($payload);

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
        $payload = $request->validated();

        if (array_key_exists('first_name', $payload) || array_key_exists('last_name', $payload) || array_key_exists('full_name', $payload)) {
            $payload['full_name'] = $this->resolveFullName($payload, $client);
        }

        $client->update($payload);

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
