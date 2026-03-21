<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpressSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'stylist_id' => ['required', 'exists:stylists,id'],
            'performed_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'service_price' => ['required', 'numeric', 'min:0'],
            'products' => ['nullable', 'array'],
            'products.*.item_id' => ['required_with:products', 'exists:items,id'],
            'products.*.quantity' => ['required_with:products', 'numeric', 'gt:0'],
            'products.*.unit' => ['required_with:products', 'string', 'max:20'],
            'products.*.unit_price' => ['required_with:products', 'numeric', 'min:0'],
        ];
    }
}
