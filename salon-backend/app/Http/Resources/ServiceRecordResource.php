<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $productsTotal = $this->relationLoaded('sale')
            ? (float) ($this->sale?->total_amount ?? 0)
            : 0.0;

        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'stylist_id' => $this->stylist_id,
            'client_id' => $this->client_id,
            'sale_id' => $this->sale_id,
            'total_amount' => $this->total_amount,
            'products_total' => $productsTotal,
            'grand_total' => (float) $this->total_amount + $productsTotal,
            'status' => $this->status?->value ?? $this->status,
            'source' => $this->source,
            'payment_method' => $this->payment_method?->value ?? $this->payment_method,
            'performed_at' => $this->performed_at,
            'notes' => $this->notes,
            'service' => $this->whenLoaded('service'),
            'stylist' => $this->whenLoaded('stylist'),
            'client' => $this->whenLoaded('client'),
            'sale' => $this->whenLoaded('sale', fn () => new SaleResource($this->sale)),
            'consumptions' => $this->whenLoaded('consumptions'),
            'commission' => $this->whenLoaded('commission'),
        ];
    }
}
