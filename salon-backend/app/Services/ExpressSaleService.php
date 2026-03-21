<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ServiceRecord;
use Illuminate\Support\Facades\DB;

class ExpressSaleService
{
    public function __construct(
        private readonly SaleService $saleService,
        private readonly ServiceRecordService $serviceRecordService,
    ) {
    }

    /**
     * @param array{
     *   client_id?:int|null,
     *   service_id:int,
     *   stylist_id:int,
     *   performed_at:string,
     *   notes?:string|null,
     *   payment_method?:string|null,
     *   service_price:float,
     *   products?: array<int, array{
     *      item_id:int,
     *      quantity:float,
     *      unit:string,
     *      unit_price:float
     *   }>
     * } $payload
     */
    public function create(array $payload, ?int $userId): ServiceRecord
    {
        return DB::transaction(function () use ($payload, $userId) {
            $sale = null;

            if (!empty($payload['products'])) {
                $sale = $this->saleService->create([
                    'client_id' => $payload['client_id'] ?? null,
                    'payment_method' => $payload['payment_method'] ?? null,
                    'sold_at' => $payload['performed_at'],
                    'items' => $payload['products'],
                ], $userId);
            }

            $record = $this->serviceRecordService->create([
                'service_id' => $payload['service_id'],
                'stylist_id' => $payload['stylist_id'],
                'client_id' => $payload['client_id'] ?? null,
                'total_amount' => $payload['service_price'],
                'payment_method' => $payload['payment_method'] ?? null,
                'performed_at' => $payload['performed_at'],
                'notes' => $payload['notes'] ?? null,
                'consumptions' => [],
            ], $userId);

            $record->forceFill([
                'source' => 'express',
                'sale_id' => $sale?->id,
            ])->save();

            return $record->fresh([
                'service',
                'stylist.user',
                'client',
                'sale.items.item',
            ]);
        });
    }
}
