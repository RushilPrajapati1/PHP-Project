<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TransactionType;

final readonly class CategoryTotal
{
    public function __construct(
        public string $category,
        public TransactionType $type,
        public int $totalCents,
        public int $count,
    ) {}
}
