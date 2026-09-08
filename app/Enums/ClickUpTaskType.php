<?php

namespace App\Enums;

enum ClickUpTaskType: string
{
    case Sales = 'sales';
    case Design = 'design';
    case Content = 'content';
    case Revision = 'revision';
}
