<?php

namespace App\Enums;

enum RequestSource: string
{
    case Website = 'website';
    case Telegram = 'telegram';
}
