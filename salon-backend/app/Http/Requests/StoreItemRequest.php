<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(ItemType::class)],
            'base_unit' => ['required', 'string', 'max:20'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock_minimum' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:60', 'unique:items,sku'],
            'barcode' => ['nullable', 'string', 'max:80', 'unique:items,barcode'],
            'reorder_point' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'reorder_qty' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'is_active' => ['nullable', 'boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit' => ['required_with:units', 'string', 'max:20'],
            'units.*.factor_to_base' => ['required_with:units', 'numeric', 'min:0', 'max:999999999.999'],
            'units.*.is_base' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.max' => 'El precio de venta es demasiado grande.',
            'reorder_point.max' => 'El punto de reposicion es demasiado grande.',
            'reorder_qty.max' => 'La cantidad de reposicion es demasiado grande.',
            'units.*.factor_to_base.max' => 'Uno de los factores de unidad es demasiado grande.',
        ];
    }
}
