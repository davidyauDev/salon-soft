<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::query()->with(['units', 'category', 'brand'])->orderBy('name')->get();

        return response()->json(ItemResource::collection($items));
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = Item::query()->create($request->validated());

        $this->syncUnits($item, $request->input('units'));

        return response()->json(new ItemResource($item->load(['units', 'category', 'brand'])), 201);
    }

    public function show(Item $item): JsonResponse
    {
        return response()->json(new ItemResource($item->load(['units', 'category', 'brand'])));
    }

    public function update(UpdateItemRequest $request, Item $item, AuditLogger $logger): JsonResponse
    {
        $before = $item->getAttributes();
        $item->update($request->validated());

        if ($request->filled('units')) {
            $this->syncUnits($item, $request->input('units'));
        }

        $item->refresh();
        $logger->log('update', $item, $request->user()?->id, $before, $item->getAttributes(), $request);

        return response()->json(new ItemResource($item->load(['units', 'category', 'brand'])));
    }

    public function destroy(Request $request, Item $item, AuditLogger $logger): JsonResponse
    {
        if (
            $item->inventoryMoves()->exists()
            || $item->stockLots()->exists()
            || $item->saleItems()->exists()
            || $item->serviceConsumptions()->exists()
        ) {
            return response()->json([
                'message' => 'No se puede eliminar este producto porque tiene historial de inventario o movimientos registrados.',
            ], 422);
        }

        $before = $item->getAttributes();
        $logger->log('delete', $item, $request->user()?->id, $before, null, $request);
        $item->delete();

        return response()->json(status: 204);
    }

    private function syncUnits(Item $item, ?array $units): void
    {
        $normalized = collect($units ?? [])
            ->map(function (array $unit) {
                return [
                    'unit' => $unit['unit'],
                    'factor_to_base' => $unit['factor_to_base'],
                    'is_base' => (bool) ($unit['is_base'] ?? false),
                ];
            });

        $hasBase = $normalized->contains(function (array $unit) use ($item) {
            return strtolower($unit['unit']) === strtolower($item->base_unit);
        });

        if (!$hasBase) {
            $normalized->push([
                'unit' => $item->base_unit,
                'factor_to_base' => 1,
                'is_base' => true,
            ]);
        }

        $item->units()->delete();
        $item->units()->createMany($normalized->values()->all());
    }
}
