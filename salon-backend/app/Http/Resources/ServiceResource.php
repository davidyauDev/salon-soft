<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\ServiceCategoryResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\StylistResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_min' => $this->duration_min,
            'base_price' => $this->base_price,
            'requires_deposit' => $this->requires_deposit,
            'deposit_amount' => $this->deposit_amount,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'category' => $this->whenLoaded('category', fn () => new ServiceCategoryResource($this->category)),
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'stylists' => StylistResource::collection($this->whenLoaded('stylists')),
        ];
    }
}
