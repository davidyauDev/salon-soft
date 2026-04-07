<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id ?? $this->route('brand');

        return [
            'name' => ['sometimes', 'string', 'max:120', Rule::unique('brands', 'name')->ignore($brandId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
