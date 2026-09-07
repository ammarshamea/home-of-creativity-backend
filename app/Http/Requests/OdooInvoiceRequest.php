<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OdooInvoiceRequest extends FormRequest
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
            'odoo_partner_id' => ['nullable', 'string', 'max:80'],
            'odoo_quotation_id' => ['nullable', 'string', 'max:80'],
        ];
    }
}
