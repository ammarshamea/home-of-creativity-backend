<?php

namespace App\Actions;

use App\Enums\EmployeeProfession;
use App\Enums\RequestSource;
use App\Enums\RequestStatus;
use App\Enums\WorkflowEventType;
use App\Models\Client;
use App\Models\ServiceRequest;

class SubmitServiceRequest
{
    public function __construct(
        private GenerateRequestNumber $generateRequestNumber,
        private DispatchN8nEvent $dispatchN8nEvent,
        private NotifyEmployees $notifyEmployees,
    ) {}

    /**
     * @param  array{title: string, description: string, source?: RequestSource}  $data
     */
    public function handle(Client $client, array $data): ServiceRequest
    {
        $request = $client->requests()->create([
            'number' => $this->generateRequestNumber->handle(),
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => RequestStatus::Submitted,
            'source' => $data['source'] ?? RequestSource::Website,
        ]);

        defer(function () use ($request): void {
            $fresh = $request->fresh(['client']) ?? $request;
            $this->notifyEmployees->handle(
                $fresh,
                EmployeeProfession::Sales,
                "طلب جديد لقسم المبيعات\n{$fresh->number}\n{$fresh->client?->name}: {$fresh->title}\n\n{$fresh->description}\n\nللرد على الزبون: /reply {$fresh->number}",
            );
            $this->dispatchN8nEvent->handle(
                $fresh,
                WorkflowEventType::RequestSubmitted,
            );
        });

        return $request->load(['client', 'events']);
    }
}
