<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRecordRequest;
use App\Http\Requests\UpdateServiceRecordRequest;
use App\Http\Resources\ServiceRecordResource;
use App\Models\ServiceRecord;
use App\Models\Stylist;
use App\Services\AuditLogger;
use App\Services\ServiceRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\InventoryMoveType;
use App\Models\InventoryMove;

class ServiceRecordController extends Controller
{
    public function index(): JsonResponse
    {
        $records = \App\Models\ServiceRecord::query()
            ->with(['service', 'stylist.user', 'client'])
            ->latest('performed_at')
            ->paginate(20);

        return response()->json(ServiceRecordResource::collection($records));
    }

    public function store(StoreServiceRecordRequest $request, ServiceRecordService $service): JsonResponse
    {
        $record = $service->create($request->validated(), $request->user()?->id);

        return response()->json(new ServiceRecordResource($record), 201);
    }

    public function update(
        UpdateServiceRecordRequest $request,
        ServiceRecord $record,
        AuditLogger $logger
    ): JsonResponse {
        if ($record->status?->value === 'cancelled' || $record->status === 'cancelled') {
            return response()->json(['message' => 'Cannot edit a cancelled service.'], 422);
        }

        $before = $record->getAttributes();
        $record->fill($request->validated());
        $record->save();

        if ($record->commission) {
            /** @var Stylist $stylist */
            $stylist = Stylist::query()->findOrFail($record->stylist_id);
            $rate = (float) $stylist->commission_rate;
            $amount = round(((float) $record->total_amount) * $rate / 100, 2);
            $record->commission->update([
                'stylist_id' => $stylist->id,
                'base_amount' => $record->total_amount,
                'rate' => $rate,
                'amount' => $amount,
                'calculated_at' => now(),
            ]);
        }

        $record->load(['service', 'stylist.user', 'client']);
        $logger->log('update', $record, $request->user()?->id, $before, $record->getAttributes(), $request);

        return response()->json(new ServiceRecordResource($record));
    }

    public function cancel(Request $request, ServiceRecord $record, AuditLogger $logger): JsonResponse
    {
        if ($record->status?->value === 'cancelled' || $record->status === 'cancelled') {
            return response()->json(new ServiceRecordResource($record));
        }

        $record->load('consumptions.stockLot');

        $before = $record->getAttributes();
        $record->update(['status' => 'cancelled']);

        foreach ($record->consumptions as $consumption) {
            $lot = $consumption->stockLot;
            if ($lot) {
                $lot->increment('quantity_remaining', $consumption->quantity_base);
            }

            InventoryMove::query()->create([
                'item_id' => $consumption->item_id,
                'type' => InventoryMoveType::Adjustment,
                'quantity_base' => $consumption->quantity_base,
                'unit_cost_base' => $consumption->unit_cost_base,
                'reference_type' => 'service_cancel',
                'reference_id' => $record->id,
                'user_id' => $request->user()?->id,
                'moved_at' => now(),
            ]);
        }

        if ($record->commission) {
            $record->commission->update([
                'base_amount' => 0,
                'amount' => 0,
                'calculated_at' => now(),
            ]);
        }

        $record->load(['service', 'stylist.user', 'client']);
        $logger->log('cancel', $record, $request->user()?->id, $before, $record->getAttributes(), $request);

        return response()->json(new ServiceRecordResource($record));
    }
}
