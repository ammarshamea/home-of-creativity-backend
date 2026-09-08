<?php

namespace App\Jobs;

use App\Enums\IntegrationEventStatus;
use App\Models\IntegrationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchIntegrationEventJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public int $integrationEventId) {}

    public function handle(): void
    {
        $event = IntegrationEvent::query()->find($this->integrationEventId);
        if (! $event || $event->status === IntegrationEventStatus::Dispatched) {
            return;
        }

        $event->forceFill([
            'status' => IntegrationEventStatus::Processing,
            'attempts' => $event->attempts + 1,
        ])->save();

        $url = config('services.n8n.webhook_url');
        if (! is_string($url) || $url === '') {
            Log::info('n8n webhook skipped; N8N_WEBHOOK_URL is empty.', [
                'event_uuid' => $event->event_uuid,
                'event_type' => $event->event_type->value,
            ]);
            $event->forceFill([
                'status' => IntegrationEventStatus::Dispatched,
                'dispatched_at' => now(),
            ])->save();

            return;
        }

        $body = [
            'event' => $event->event_type->value,
            'event_uuid' => $event->event_uuid,
            'request_number' => $event->request_number,
            'request_uuid' => $event->request_uuid,
            'correlation_id' => $event->correlation_id,
            'aggregate_version' => $event->aggregate_version,
            'payload' => $event->payload ?? [],
        ];

        try {
            $response = Http::timeout((int) config('services.n8n.timeout', 12))
                ->connectTimeout(3)
                ->acceptJson()
                ->withHeaders([
                    'X-N8N-Secret' => (string) config('services.n8n.webhook_secret'),
                ])
                ->post($url, $body);
        } catch (ConnectionException|Throwable $exception) {
            $this->markFailed($event, $exception->getMessage());

            throw $exception;
        }

        if (! $response->successful()) {
            $this->markFailed($event, 'HTTP '.$response->status());

            return;
        }

        $event->forceFill([
            'status' => IntegrationEventStatus::Dispatched,
            'dispatched_at' => now(),
            'last_error' => null,
        ])->save();
    }

    private function markFailed(IntegrationEvent $event, string $error): void
    {
        $delayMinutes = min(60, 2 ** min($event->attempts, 6));

        $event->forceFill([
            'status' => IntegrationEventStatus::Failed,
            'last_error' => $error,
            'next_retry_at' => now()->addMinutes($delayMinutes),
        ])->save();

        Log::warning('Integration event dispatch failed.', [
            'event_uuid' => $event->event_uuid,
            'event_type' => $event->event_type->value,
            'error' => $error,
        ]);
    }
}
