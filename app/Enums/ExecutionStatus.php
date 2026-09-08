<?php

namespace App\Enums;

enum ExecutionStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case AlmostDone = 'almost_done';
    case Completed = 'completed';
}
