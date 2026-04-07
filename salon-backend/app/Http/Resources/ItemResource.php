<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type?->value ?? $this->type,
            'base_unit' => $this->base_unit,
            'sale_price' => $this->sale_price,
            'stock_minimum' => $this->stock_minimum,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'reorder_point' => $this->reorder_point,
            'reorder_qty' => $this->reorder_qty,
            'is_active' => $this->is_active,
            'units' => $this->whenLoaded('units'),
            'category' => $this->whenLoaded('category'),
            'brand' => $this->whenLoaded('brand'),
        ];
    }
}
