<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case ShamCash = 'sham_cash';
}
