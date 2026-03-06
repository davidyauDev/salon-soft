<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id ?? $this->route('item');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['sometimes', Rule::enum(ItemType::class)],
            'base_unit' => ['sometimes', 'string', 'max:20'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_minimum' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('items', 'sku')->ignore($itemId)],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('items', 'barcode')->ignore($itemId)],
            'reorder_point' => ['nullable', 'numeric', 'min:0'],
            'reorder_qty' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit' => ['required_with:units', 'string', 'max:20'],
            'units.*.factor_to_base' => ['required_with:units', 'numeric', 'min:0'],
            'units.*.is_base' => ['nullable', 'boolean'],
        ];
    }
}
