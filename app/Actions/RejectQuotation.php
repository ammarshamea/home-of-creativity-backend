<?php

namespace App\Actions;

use App\Enums\EmployeeProfession;
use App\Enums\QuotationDecisionType;
use App\Enums\RequestStatus;
use App\Models\Quotation;
use App\Models\QuotationDecision;
use App\Models\ServiceRequest;
use App\Services\RequestStatusTransitionService;
use App\Support\ResolveServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectQuotation
{
    public function __construct(
        private RequestStatusTransitionService $transitions,
        private NotifyEmployees $notifyEmployees,
    ) {}

    public function handle(ServiceRequest $request, string $reason, ?Quotation $quotation = null): ServiceRequest
    {
        if ($request->status !== RequestStatus::QuotationSent) {
            return $request;
        }

        $quotation ??= $request->quotations()->latest('version')->first();
        if (! $quotation) {
            throw ValidationException::withMessages(['quotation' => 'No quotation found.']);
        }

        return DB::transaction(function () use ($request, $quotation, $reason): ServiceRequest {
            QuotationDecision::query()->firstOrCreate(
                [
                    'request_id' => $request->id,
                    'quotation_id' => $quotation->id,
                    'decision' => QuotationDecisionType::Rejected,
                ],
                ['reason' => $reason],
            );

            $updated = $this->transitions->transition($request, RequestStatus::QuotationRejected, 'client', $reason);

            $fresh = $updated->fresh('client') ?? $updated;
            $displayNumber = ResolveServiceRequest::displayNumber($fresh);
            $this->notifyEmployees->handle(
                $fresh,
                EmployeeProfession::Sales,
                "❌ رفض الزبون عرض السعر\n#{$displayNumber} — {$fresh->title}\n{$fresh->client?->name}\n\nالسبب: {$reason}",
            );

            return $updated;
        });
    }
}
