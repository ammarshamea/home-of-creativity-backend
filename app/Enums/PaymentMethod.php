<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Receipt = 'receipt';
    case Cash = 'cash';
}
