<?php

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateServiceRequestStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::enum(RequestStatus::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $serviceRequest = $this->route('service_request');
                $next = RequestStatus::tryFrom((string) $this->input('status'));

                if (! $serviceRequest instanceof ServiceRequest || ! $next instanceof RequestStatus) {
                    return;
                }

                if (! $serviceRequest->status->allowsStaffTransitionTo($next)) {
                    $validator->errors()->add(
                        'status',
                        'Payment cannot be confirmed before a quotation is sent.',
                    );
                }
            },
        ];
    }
}
