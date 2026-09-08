<?php

namespace App\Http\Requests;

use App\Enums\ClickUpTaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClickUpMappingRequest extends FormRequest
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
            'request_number' => ['required', 'string'],
            'event_uuid' => ['required', 'uuid'],
            'request_uuid' => ['required', 'uuid'],
            'task_type' => ['required', Rule::enum(ClickUpTaskType::class)],
            'integration_key' => ['required', 'string', 'max:191'],
            'brief_id' => ['nullable', 'integer'],
            'clickup_task_id' => ['required', 'string'],
            'clickup_list_id' => ['nullable', 'string'],
            'clickup_user_id' => ['nullable', 'string'],
            'clickup_url' => ['nullable', 'url'],
            'status' => ['nullable', 'string'],
        ];
    }
}
