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

        $before = $record->getAttributes();
        $record->update(['status' => 'cancelled']);

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
