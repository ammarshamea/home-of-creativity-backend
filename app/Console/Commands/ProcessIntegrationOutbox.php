<?php

namespace App\Console\Commands;

use App\Actions\EnqueueIntegrationEvent;
use App\Enums\IntegrationEventStatus;
use App\Models\IntegrationEvent;
use Illuminate\Console\Command;

class ProcessIntegrationOutbox extends Command
{
    protected $signature = 'integration:process-outbox {--limit=25}';

    protected $description = 'Retry pending or failed integration outbox events';

    public function handle(EnqueueIntegrationEvent $enqueue): int
    {
        $events = IntegrationEvent::query()
            ->whereIn('status', [IntegrationEventStatus::Pending, IntegrationEventStatus::Failed])
            ->where(function ($query): void {
                $query->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        foreach ($events as $event) {
            $enqueue->retry($event);
            $this->line("Queued {$event->event_uuid} ({$event->event_type->value})");
        }

        return self::SUCCESS;
    }
}
