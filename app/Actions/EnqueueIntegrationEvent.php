<?php

namespace App\Actions;

use App\Enums\IntegrationEventStatus;
use App\Enums\WorkflowEventType;
use App\Jobs\DispatchIntegrationEventJob;
use App\Models\IntegrationEvent;
use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class EnqueueIntegrationEvent
{
    public function handle(
        ServiceRequest $request,
        WorkflowEventType $type,
        array $payload = [],
        ?string $eventUuid = null,
    ): IntegrationEvent {
        $request->loadMissing('client');

        $event = IntegrationEvent::query()->create([
            'event_uuid' => $eventUuid ?? (string) Str::uuid(),
            'event_type' => $type,
            'request_uuid' => $request->uuid,
            'request_number' => $request->number,
            'correlation_id' => (string) Str::uuid(),
            'aggregate_version' => $request->aggregate_version,
            'payload' => $this->buildPayload($request, $payload),
            'status' => IntegrationEventStatus::Pending,
        ]);

        DispatchIntegrationEventJob::dispatch($event->id)->afterCommit();

        return $event;
    }

    public function retry(IntegrationEvent $event): IntegrationEvent
    {
        $event->forceFill([
            'status' => IntegrationEventStatus::Pending,
            'next_retry_at' => null,
        ])->save();

        DispatchIntegrationEventJob::dispatch($event->id)->afterCommit();

        return $event->fresh() ?? $event;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildPayload(ServiceRequest $request, array $payload): array
    {
        return [
            'id' => $request->id,
            'uuid' => $request->uuid,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status->value,
            'work_type' => $request->work_type?->value,
            'source' => $request->source->value,
            'created_at' => $request->created_at?->toIso8601String(),
            'client' => [
                'name' => $request->client?->name,
                'email' => $request->client?->email,
                'phone' => $request->client?->phone,
                'telegram_user_id' => $request->client?->telegram_user_id,
                'locale' => $request->client?->locale,
            ],
            ...$payload,
        ];
    }
}
