<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case AiAnalyzing = 'ai_analyzing';
    case QuotationSent = 'quotation_sent';
    case PaymentConfirmed = 'payment_confirmed';
    case InProgress = 'in_progress';
    case ReadyForReview = 'ready_for_review';
    case RevisionInProgress = 'revision_in_progress';
    case Approved = 'approved';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function allowsStaffTransitionTo(self $next): bool
    {
        if ($next === self::PaymentConfirmed) {
            return $this === self::QuotationSent;
        }

        return true;
    }
}
