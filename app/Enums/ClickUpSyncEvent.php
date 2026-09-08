<?php

namespace App\Enums;

enum ClickUpSyncEvent: string
{
    case Contacted = 'contacted';
    case Quotation = 'quotation';
    case Delivery = 'delivery';
    case Revision = 'revision';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
