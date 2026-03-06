<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $servicesTotal = $this->service_records_sum_total_amount ?? null;
        $salesTotal = $this->sales_sum_total_amount ?? null;

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'dni' => $this->dni,
            'address' => $this->address,
            'birth_date' => optional($this->birth_date)->toDateString(),
            'gender' => $this->gender,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toDateString(),
            'service_count' => $this->service_records_count ?? 0,
            'services_total' => $servicesTotal ? (float) $servicesTotal : 0.0,
            'sales_total' => $salesTotal ? (float) $salesTotal : 0.0,
            'total_sales' => (float) ($servicesTotal ?? 0) + (float) ($salesTotal ?? 0),
        ];
    }
}
