<?php

namespace App\Actions;

use App\Enums\WorkflowEventType;
use App\Models\RequestEvent;
use App\Models\ServiceRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchN8nEvent
{
    public function handle(ServiceRequest $request, WorkflowEventType $type, array $payload = []): RequestEvent
    {
        $request->loadMissing('client');

        $event = $request->events()->create([
            'type' => $type,
            'payload' => $payload,
        ]);

        $url = config('services.n8n.webhook_url');
        if (! is_string($url) || $url === '') {
            Log::info('n8n webhook skipped; N8N_WEBHOOK_URL is empty.', [
                'request' => $request->number,
                'event' => $type->value,
            ]);

            return $event;
        }

        $body = [
            'event' => $type->value,
            'request_number' => $request->number,
            'status' => $request->status->value,
            'payload' => [
                'id' => $request->id,
                'title' => $request->title,
                'description' => $request->description,
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
            ],
        ];

        try {
            $response = Http::timeout((int) config('services.n8n.timeout', 3))
                ->connectTimeout(1)
                ->acceptJson()
                ->withHeaders([
                    'X-N8N-Secret' => (string) config('services.n8n.webhook_secret'),
                ])
                ->post($url, $body);
        } catch (ConnectionException|Throwable $exception) {
            Log::warning('n8n webhook failed.', [
                'request' => $request->number,
                'event' => $type->value,
                'error' => $exception->getMessage(),
            ]);

            return $event;
        }

        if ($response->successful()) {
            $event->forceFill(['dispatched_at' => now()])->save();
        } else {
            Log::warning('n8n webhook failed.', [
                'status' => $response->status(),
                'request' => $request->number,
                'event' => $type->value,
            ]);
        }

        return $event;
    }
}
