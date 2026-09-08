<?php

namespace App\Actions;

use App\Enums\RequestStatus;
use App\Enums\WorkflowEventType;
use App\Models\ServiceRequest;

class DispatchStatusWorkflow
{
    public function __construct(private EnqueueIntegrationEvent $enqueueIntegrationEvent) {}

    public function handle(ServiceRequest $request, RequestStatus $status): void
    {
        $type = match ($status) {
            RequestStatus::ReadyForReview => WorkflowEventType::DeliveryReady,
            RequestStatus::RevisionRequested => WorkflowEventType::RevisionRequested,
            RequestStatus::Completed => WorkflowEventType::ProjectCompleted,
            default => null,
        };

        if (! $type instanceof WorkflowEventType) {
            return;
        }

        $this->enqueueIntegrationEvent->handle($request->fresh(['client']) ?? $request, $type);
    }
}
