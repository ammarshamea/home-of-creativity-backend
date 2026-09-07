<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramNotifyRequest extends FormRequest
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
            'text' => ['required', 'string', 'max:4000'],
            'chat_id' => ['nullable', 'string', 'max:80'],
        ];
    }
}
