<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'stylist_id' => ['required', 'integer', 'exists:stylists,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,yape'],
            'performed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
