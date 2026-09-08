<?php

namespace App\Actions;

use App\Enums\WorkflowEventType;
use App\Models\IntegrationEvent;
use App\Models\ServiceRequest;

/**
 * @deprecated Use EnqueueIntegrationEvent directly.
 */
class DispatchN8nEvent
{
    public function __construct(private EnqueueIntegrationEvent $enqueueIntegrationEvent) {}

    public function handle(ServiceRequest $request, WorkflowEventType $type, array $payload = []): IntegrationEvent
    {
        return $this->enqueueIntegrationEvent->handle($request, $type, $payload);
    }
}
