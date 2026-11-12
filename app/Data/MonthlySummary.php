<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class MonthlySummary
{
    /**
     * @param  list<CategoryTotal>  $categories  sorted by total, largest first
     */
    public function __construct(
        public CarbonImmutable $month,
        public int $incomeCents,
        public int $spendingCents,
        public array $categories,
    ) {}

    public function netCents(): int
    {
        return $this->incomeCents - $this->spendingCents;
    }
}
