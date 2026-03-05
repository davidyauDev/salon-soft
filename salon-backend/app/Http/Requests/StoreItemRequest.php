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
            'type' => ['required', Rule::enum(ItemType::class)],
            'base_unit' => ['required', 'string', 'max:20'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_minimum' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit' => ['required_with:units', 'string', 'max:20'],
            'units.*.factor_to_base' => ['required_with:units', 'numeric', 'min:0'],
            'units.*.is_base' => ['nullable', 'boolean'],
        ];
    }
}
