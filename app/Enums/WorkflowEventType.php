<?php

namespace App\Enums;

enum WorkflowEventType: string
{
    case RequestSubmitted = 'REQUEST_SUBMITTED';
    case QuotationReady = 'QUOTATION_READY';
    case PaymentConfirmed = 'PAYMENT_CONFIRMED';
    case TasksReady = 'TASKS_READY';
    case DeliveryReady = 'DELIVERY_READY';
    case RevisionRequested = 'REVISION_REQUESTED';
    case ProjectCompleted = 'PROJECT_COMPLETED';
}
