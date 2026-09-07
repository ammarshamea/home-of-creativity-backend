<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OdooQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'request_number' => ['required', 'string', 'exists:requests,number'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client' => ['nullable', 'array'],
            'client.name' => ['nullable', 'string', 'max:255'],
            'client.email' => ['nullable', 'email'],
            'client.phone' => ['nullable', 'string', 'max:40'],
        ];
    }
}
