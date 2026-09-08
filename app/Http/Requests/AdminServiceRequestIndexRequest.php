<?php

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use Illuminate\Validation\Rule;

class AdminServiceRequestIndexRequest extends PaginatedIndexRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'status' => ['sometimes', 'nullable', 'string', Rule::enum(RequestStatus::class)],
        ];
    }
}
