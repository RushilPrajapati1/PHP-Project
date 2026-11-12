<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Money is stored and calculated as integer cents everywhere. It is only
 * converted to dollars at the edge (API resources / UI), never stored as a float.
 */
final class Money
{
    public static function toDollars(int $cents): float
    {
        return round($cents / 100, 2);
    }
}
