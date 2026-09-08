<?php

namespace App\Enums;

enum QuotationDecisionType: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
