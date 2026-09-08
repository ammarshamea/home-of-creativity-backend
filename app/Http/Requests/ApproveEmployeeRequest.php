<?php

namespace App\Http\Requests;

use App\Enums\EmployeeProfession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('clickup_user_id') === '') {
            $this->merge(['clickup_user_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'profession' => ['required', 'string', Rule::enum(EmployeeProfession::class)],
            'clickup_user_id' => ['nullable', 'string', 'max:80'],
        ];
    }
}
