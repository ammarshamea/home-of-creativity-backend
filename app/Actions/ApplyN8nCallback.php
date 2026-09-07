<?php

namespace App\Actions;

use App\Enums\EmployeeProfession;
use App\Enums\RequestStatus;
use App\Enums\WorkflowEventType;
use App\Models\DepartmentBrief;
use App\Models\ServiceRequest;
use Illuminate\Validation\ValidationException;

class ApplyN8nCallback
{
    public function __construct(private NotifyEmployees $notifyEmployees) {}

    public function handle(ServiceRequest $request, string $event, array $payload = []): ServiceRequest
    {
        $type = WorkflowEventType::tryFrom($event);
        if (! $type) {
            throw ValidationException::withMessages([
                'event' => 'Unknown workflow event.',
            ]);
        }

        match ($type) {
            WorkflowEventType::RequestSubmitted => $request->status = RequestStatus::AiAnalyzing,
            WorkflowEventType::QuotationReady => $this->applyQuotation($request, $payload),
            WorkflowEventType::PaymentConfirmed => $this->applyPayment($request, $payload),
            WorkflowEventType::TasksReady => $this->applyTasks($request, $payload),
            WorkflowEventType::DeliveryReady => $request->status = RequestStatus::ReadyForReview,
            WorkflowEventType::RevisionRequested => $request->status = RequestStatus::RevisionInProgress,
            WorkflowEventType::ProjectCompleted => $request->status = RequestStatus::Completed,
        };

        if (isset($payload['ai_analysis']) && is_array($payload['ai_analysis'])) {
            $request->ai_analysis = $payload['ai_analysis'];
        }

        $request->save();

        $request->events()->create([
            'type' => $type,
            'payload' => $payload,
            'dispatched_at' => now(),
        ]);

        return $request->fresh(['client', 'briefs', 'events']) ?? $request;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyQuotation(ServiceRequest $request, array $payload): void
    {
        $request->status = RequestStatus::QuotationSent;
        if (isset($payload['odoo_quotation_id'])) {
            $request->odoo_quotation_id = (string) $payload['odoo_quotation_id'];
        }
        if (isset($payload['odoo_partner_id']) && $request->client) {
            $request->client->forceFill([
                'odoo_partner_id' => (string) $payload['odoo_partner_id'],
            ])->save();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyPayment(ServiceRequest $request, array $payload): void
    {
        $request->status = RequestStatus::PaymentConfirmed;
        $request->paid_at = now();
        if (isset($payload['odoo_invoice_id'])) {
            $request->odoo_invoice_id = (string) $payload['odoo_invoice_id'];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyTasks(ServiceRequest $request, array $payload): void
    {
        $request->status = RequestStatus::InProgress;
        if (isset($payload['odoo_invoice_id'])) {
            $request->odoo_invoice_id = (string) $payload['odoo_invoice_id'];
        }
        $briefs = $payload['briefs'] ?? [];
        if (! is_array($briefs)) {
            return;
        }

        foreach ($briefs as $brief) {
            if (! is_array($brief) || ! isset($brief['department'])) {
                continue;
            }

            DepartmentBrief::query()->create([
                'request_id' => $request->id,
                'department' => (string) $brief['department'],
                'brief' => isset($brief['brief']) ? (string) $brief['brief'] : null,
                'clickup_task_id' => isset($brief['clickup_task_id']) ? (string) $brief['clickup_task_id'] : null,
            ]);

            $profession = EmployeeProfession::tryFrom((string) $brief['department']);
            if ($profession instanceof EmployeeProfession && $profession !== EmployeeProfession::Sales) {
                $this->notifyEmployees->handle(
                    $request,
                    $profession,
                    "مهمة جديدة في قسمك\n{$request->number}\n{$request->title}\n\n".((string) ($brief['brief'] ?? ''))."\n\nللرد على الزبون: /reply {$request->number}",
                );
            }
        }
    }
}
