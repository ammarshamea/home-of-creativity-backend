<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TelegramSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'telegram_user_id' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'attachments.*.file_name' => ['required', 'string', 'max:255'],
            'attachments.*.file_base64' => ['required', 'string'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $description = trim((string) $this->input('description', ''));
            $attachments = $this->input('attachments', []);

            if ($description === '' && (! is_array($attachments) || $attachments === [])) {
                $validator->errors()->add('description', 'Description or at least one attachment is required.');
            }
        });
    }
}
