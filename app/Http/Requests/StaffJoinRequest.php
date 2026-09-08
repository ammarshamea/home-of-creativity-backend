<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffJoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('telegram_username') === '') {
            $this->merge(['telegram_username' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'telegram_user_id' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'telegram_username' => ['nullable', 'string', 'max:80'],
        ];
    }
}
