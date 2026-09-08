<?php

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $serviceRequest = $this->route('service_request');
            $next = RequestStatus::tryFrom((string) $this->input('status'));

            if (! $serviceRequest instanceof ServiceRequest || ! $next instanceof RequestStatus) {
                return;
            }

            if ($next === RequestStatus::PaymentConfirmed) {
                $validator->errors()->add('status', 'Use the confirm payment action instead of manual status change.');
            }

            if (! $serviceRequest->status->canTransitionTo($next) && $serviceRequest->status !== $next) {
                $validator->errors()->add(
                    'status',
                    "Cannot transition from {$serviceRequest->status->value} to {$next->value}.",
                );
            }
        });
    }
}
