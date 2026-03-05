<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'stylist_id' => $this->stylist_id,
            'client_id' => $this->client_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status?->value ?? $this->status,
            'payment_method' => $this->payment_method?->value ?? $this->payment_method,
            'performed_at' => $this->performed_at,
            'notes' => $this->notes,
            'service' => $this->whenLoaded('service'),
            'stylist' => $this->whenLoaded('stylist'),
            'client' => $this->whenLoaded('client'),
            'consumptions' => $this->whenLoaded('consumptions'),
            'commission' => $this->whenLoaded('commission'),
        ];
    }
}
