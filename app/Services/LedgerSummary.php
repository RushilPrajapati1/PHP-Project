<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\CategoryTotal;
use App\Data\MonthlySummary;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Groups one month of transactions by category and sums them.
 * Shared by the REST API and the Inertia UI so both report identical numbers.
 */
final class LedgerSummary
{
    public function forAccount(Account $account, CarbonImmutable $month): MonthlySummary
    {
        return $this->summarize(
            Transaction::query()->where('account_id', $account->id),
            $month,
        );
    }

    public function forUser(User $user, CarbonImmutable $month): MonthlySummary
    {
        return $this->summarize(
            Transaction::query()->whereIn('account_id', $user->accounts()->select('id')),
            $month,
        );
    }

    /**
     * @param  Builder<Transaction>  $query
     */
    private function summarize(Builder $query, CarbonImmutable $month): MonthlySummary
    {
        $month = $month->startOfMonth();

        $rows = $query
            // Half-open range [1st of month, 1st of next month) — safe whether the column
            // holds a pure DATE (MySQL) or 'Y-m-d 00:00:00' (SQLite), unlike BETWEEN ... end-of-month.
            ->where('occurred_on', '>=', $month->toDateString())
            ->where('occurred_on', '<', $month->addMonth()->toDateString())
            ->selectRaw('category, type, SUM(amount_cents) AS total_cents, COUNT(*) AS txn_count')
            ->groupBy('category', 'type')
            ->orderByDesc('total_cents')
            ->toBase()
            ->get();

        // Aggregates come back from MySQL as strings, so cast explicitly.
        $categories = $rows->map(fn (object $row): CategoryTotal => new CategoryTotal(
            category: (string) $row->category,
            type: TransactionType::from((string) $row->type),
            totalCents: (int) $row->total_cents,
            count: (int) $row->txn_count,
        ))->values()->all();

        $sumOf = fn (TransactionType $type): int => array_sum(array_map(
            fn (CategoryTotal $c): int => $c->type === $type ? $c->totalCents : 0,
            $categories,
        ));

        return new MonthlySummary(
            month: $month,
            incomeCents: $sumOf(TransactionType::Credit),
            spendingCents: $sumOf(TransactionType::Debit),
            categories: $categories,
        );
    }
}
