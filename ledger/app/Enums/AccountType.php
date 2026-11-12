<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';
}
