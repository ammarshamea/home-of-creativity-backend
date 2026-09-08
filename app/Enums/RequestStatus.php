<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Submitted = 'submitted';
    case QuotationSent = 'quotation_sent';
    case QuotationRejected = 'quotation_rejected';
    case AwaitingPayment = 'awaiting_payment';
    case PaymentConfirmed = 'payment_confirmed';
    case InProgress = 'in_progress';
    case ReadyForReview = 'ready_for_review';
    case RevisionRequested = 'revision_requested';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::QuotationSent, self::Cancelled],
            self::QuotationSent => [self::QuotationRejected, self::AwaitingPayment, self::Cancelled],
            self::QuotationRejected => [self::QuotationSent, self::Cancelled],
            self::AwaitingPayment => [self::PaymentConfirmed, self::Cancelled],
            self::PaymentConfirmed => [self::InProgress, self::Cancelled],
            self::InProgress => [self::ReadyForReview, self::Cancelled],
            self::ReadyForReview => [self::Completed, self::RevisionRequested, self::Cancelled],
            self::RevisionRequested => [self::InProgress, self::ReadyForReview, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function allowsClientEdit(): bool
    {
        return $this === self::Submitted;
    }
}
