<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Data\CategoryTotal;
use App\Data\MonthlySummary;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property MonthlySummary $resource
 */
class SummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $summary = $this->resource;

        return [
            'month' => $summary->month->format('Y-m'),
            'income_cents' => $summary->incomeCents,
            'spending_cents' => $summary->spendingCents,
            'net_cents' => $summary->netCents(),
            'income' => Money::toDollars($summary->incomeCents),
            'spending' => Money::toDollars($summary->spendingCents),
            'net' => Money::toDollars($summary->netCents()),
            'categories' => array_map(fn (CategoryTotal $c): array => [
                'category' => $c->category,
                'type' => $c->type->value,
                'total_cents' => $c->totalCents,
                'total' => Money::toDollars($c->totalCents),
                'count' => $c->count,
            ], $summary->categories),
        ];
    }
}
