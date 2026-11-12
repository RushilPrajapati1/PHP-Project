<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LedgerSummary;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\Sanctum;

it('groups a month by category and sums it', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $make = fn (string $type, string $category, int $cents, string $date) => Transaction::factory()
        ->for($account)->create(compact('type', 'category') + ['amount_cents' => $cents, 'occurred_on' => $date]);

    $make('debit', 'Groceries', 4_000, '2026-10-01');
    $make('debit', 'Groceries', 1_050, '2026-10-31');
    $make('debit', 'Rent', 120_000, '2026-10-15');
    $make('credit', 'Salary', 300_000, '2026-10-28');
    $make('debit', 'Groceries', 9_999, '2026-09-30'); // previous month, excluded
    $make('debit', 'Groceries', 9_999, '2026-11-01'); // next month, excluded

    Sanctum::actingAs($user);

    $this->getJson("/api/accounts/{$account->id}/summary?month=2026-10")
        ->assertOk()
        ->assertJson(['data' => [
            'month' => '2026-10',
            'income_cents' => 300_000,
            'spending_cents' => 125_050,
            'net_cents' => 174_950,
            'net' => 1749.5,
            'categories' => [
                ['category' => 'Salary', 'type' => 'credit', 'total_cents' => 300_000, 'count' => 1],
                ['category' => 'Rent', 'type' => 'debit', 'total_cents' => 120_000, 'count' => 1],
                ['category' => 'Groceries', 'type' => 'debit', 'total_cents' => 5_050, 'total' => 50.5, 'count' => 2],
            ],
        ]]);
});

it('validates the month format', function (): void {
    $account = Account::factory()->create();
    Sanctum::actingAs($account->user);

    $this->getJson("/api/accounts/{$account->id}/summary?month=October")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
});

it('forbids another user\'s summary', function (): void {
    $account = Account::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/accounts/{$account->id}/summary")->assertForbidden();
});

it('summarises across all of a user\'s accounts for the dashboard', function (): void {
    $user = User::factory()->create();
    foreach (range(1, 2) as $_) {
        Transaction::factory()->for(Account::factory()->for($user))->debit()
            ->create(['category' => 'Dining', 'amount_cents' => 1_000, 'occurred_on' => '2026-10-10']);
    }
    Transaction::factory()->debit()->create(['amount_cents' => 50_000, 'occurred_on' => '2026-10-10']); // another user

    $summary = app(LedgerSummary::class)->forUser($user, CarbonImmutable::parse('2026-10-01'));

    expect($summary->spendingCents)->toBe(2_000)
        ->and($summary->categories)->toHaveCount(1)
        ->and($summary->categories[0]->count)->toBe(2);
});
