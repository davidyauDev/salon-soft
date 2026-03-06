<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_min' => ['nullable', 'integer', 'min:5'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'requires_deposit' => ['nullable', 'boolean'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'required_if:requires_deposit,1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'location_ids' => ['sometimes', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'stylist_ids' => ['sometimes', 'array'],
            'stylist_ids.*' => ['integer', 'exists:stylists,id'],
        ];
    }
}
