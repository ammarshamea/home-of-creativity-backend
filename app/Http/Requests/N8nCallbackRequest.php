<?php

namespace App\Http\Requests;

use App\Enums\WorkflowEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class N8nCallbackRequest extends FormRequest
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
            'event' => ['required', 'string', Rule::enum(WorkflowEventType::class)],
            'request_number' => ['required', 'string', 'exists:requests,number'],
            'payload' => ['nullable', 'array'],
            'payload.ai_analysis' => ['nullable', 'array'],
            'payload.odoo_quotation_id' => ['nullable', 'string', 'max:80'],
            'payload.odoo_partner_id' => ['nullable', 'string', 'max:80'],
            'payload.odoo_invoice_id' => ['nullable', 'string', 'max:80'],
            'payload.briefs' => ['nullable', 'array'],
            'payload.briefs.*.department' => ['required_with:payload.briefs', 'string', 'max:80'],
            'payload.briefs.*.brief' => ['nullable', 'string', 'max:5000'],
            'payload.briefs.*.clickup_task_id' => ['nullable', 'string', 'max:80'],
        ];
    }
}
