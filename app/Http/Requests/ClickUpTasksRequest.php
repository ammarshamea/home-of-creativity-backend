<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClickUpTasksRequest extends FormRequest
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
            'briefs' => ['required', 'array', 'min:1'],
            'briefs.*.department' => ['required', 'string', 'max:80'],
            'briefs.*.brief' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
