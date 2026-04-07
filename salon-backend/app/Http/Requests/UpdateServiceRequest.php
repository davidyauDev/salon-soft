<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:service_categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration_min' => ['sometimes', 'nullable', 'integer', 'min:5'],
            'base_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'requires_deposit' => ['sometimes', 'nullable', 'boolean'],
            'deposit_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'required_if:requires_deposit,1'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'location_ids' => ['sometimes', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'stylist_ids' => ['sometimes', 'array'],
            'stylist_ids.*' => ['integer', 'exists:stylists,id'],
        ];
    }
}
