<?php

namespace App\Actions;

use App\Enums\GeminiStatus;
use App\Enums\PaymentMethod;
use App\Enums\RequestStatus;
use App\Jobs\ClassifyWithGeminiJob;
use App\Models\ServiceRequest;
use App\Services\RequestStatusTransitionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmRequestPayment
{
    public function __construct(private RequestStatusTransitionService $transitions) {}

    public function handle(ServiceRequest $request, PaymentMethod $method): ServiceRequest
    {
        if ($request->status !== RequestStatus::AwaitingPayment) {
            throw ValidationException::withMessages([
                'status' => 'Payment can only be confirmed while awaiting payment.',
            ]);
        }

        if ($request->gemini_status === GeminiStatus::Pending || $request->gemini_status === GeminiStatus::Processing) {
            throw ValidationException::withMessages([
                'gemini' => 'Gemini classification is already in progress.',
            ]);
        }

        return DB::transaction(function () use ($request, $method): ServiceRequest {
            $request->forceFill([
                'paid_at' => now(),
                'payment_method' => $method,
                'gemini_status' => GeminiStatus::Pending,
                'gemini_error' => null,
            ])->save();

            ClassifyWithGeminiJob::dispatch($request->id)->afterCommit();

            return $request->fresh(['client', 'files']) ?? $request;
        });
    }
}
