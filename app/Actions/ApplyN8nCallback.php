<?php

namespace App\Actions;

use App\Enums\WorkflowEventType;
use App\Models\ServiceRequest;
use Illuminate\Validation\ValidationException;

class ApplyN8nCallback
{
    public function __construct(private ApplyClickUpMapping $applyClickUpMapping) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(ServiceRequest $request, string $event, array $payload = []): ServiceRequest
    {
        $type = WorkflowEventType::tryFrom($event);
        if (! $type) {
            throw ValidationException::withMessages([
                'event' => 'Unknown workflow event.',
            ]);
        }

        return match ($type) {
            WorkflowEventType::RequestSubmitted,
            WorkflowEventType::PaymentConfirmed,
            WorkflowEventType::RevisionRequested,
            WorkflowEventType::ProjectCompleted,
            WorkflowEventType::DeliveryReady => $request->fresh(['client', 'briefs', 'clickupTasks']) ?? $request,
            WorkflowEventType::TasksReady => $this->applyClickUpMapping->handle($request, $payload),
            WorkflowEventType::QuotationReady => throw ValidationException::withMessages([
                'event' => 'Automatic quotations are disabled; send quotation from the dashboard.',
            ]),
        };
    }
}
