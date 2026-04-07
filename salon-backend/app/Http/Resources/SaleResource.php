<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client'),
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method?->value ?? $this->payment_method,
            'sold_at' => $this->sold_at,
            'items' => $this->whenLoaded('items'),
        ];
    }
}
