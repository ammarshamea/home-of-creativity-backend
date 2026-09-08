<?php

namespace App\Actions;

use App\Enums\RequestStatus;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Services\RequestStatusTransitionService;
use Illuminate\Support\Facades\DB;

class RecordDelivery
{
    public function __construct(
        private RequestStatusTransitionService $transitions,
        private DispatchStatusWorkflow $dispatchStatusWorkflow,
    ) {}

    public function handle(
        ServiceRequest $request,
        Employee $employee,
        ?string $notes = null,
        ?string $filePath = null,
        ?string $telegramFileId = null,
    ): ServiceRequest {
        return DB::transaction(function () use ($request, $employee, $notes, $filePath, $telegramFileId): ServiceRequest {
            Delivery::query()->create([
                'request_id' => $request->id,
                'employee_id' => $employee->id,
                'notes' => $notes,
                'file_path' => $filePath,
                'telegram_file_id' => $telegramFileId,
            ]);

            $updated = $this->transitions->transition(
                $request,
                RequestStatus::ReadyForReview,
                $employee->name,
                'Delivery submitted.',
            );

            $this->dispatchStatusWorkflow->handle($updated, RequestStatus::ReadyForReview);

            return $updated;
        });
    }
}
