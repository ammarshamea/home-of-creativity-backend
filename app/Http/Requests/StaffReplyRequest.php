<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffReplyRequest extends FormRequest
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
            'telegram_user_id' => ['required', 'string', 'max:80'],
            'request_number' => ['required', 'string', 'exists:requests,number'],
            'text' => ['required', 'string', 'max:4000'],
        ];
    }
}
