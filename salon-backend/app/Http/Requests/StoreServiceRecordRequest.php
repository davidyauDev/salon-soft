<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'stylist_id' => ['required', 'exists:stylists,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
            'performed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'consumptions' => ['nullable', 'array'],
            'consumptions.*.item_id' => ['required_with:consumptions', 'exists:items,id'],
            'consumptions.*.quantity' => ['required_with:consumptions', 'numeric', 'gt:0'],
            'consumptions.*.unit' => ['required_with:consumptions', 'string', 'max:20'],
        ];
    }
}
