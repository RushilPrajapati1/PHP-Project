<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to login', function (): void {
    $this->get('/accounts')->assertRedirect(route('login'));
});

it('renders the accounts page with only the user\'s accounts', function (): void {
    $user = User::factory()->create();
    Account::factory()->for($user)->has(Transaction::factory()->credit()->state(['amount_cents' => 5_000]))->create(['name' => 'Mine']);
    Account::factory()->create();

    $this->actingAs($user)->get('/accounts')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('accounts/index')
            ->has('accounts', 1)
            ->where('accounts.0.name', 'Mine')
            ->where('accounts.0.balance_cents', 5_000)
            ->where('accounts.0.transactions_count', 1)
            ->where('accountTypes', ['checking', 'savings', 'credit']));
});

it('creates an account from the form and redirects back', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/accounts', ['name' => 'Travel', 'type' => 'savings'])
        ->assertRedirect('/accounts');

    expect($user->accounts()->where('name', 'Travel')->exists())->toBeTrue();
});

it('sends validation errors back to the form', function (): void {
    $this->actingAs(User::factory()->create())
        ->post('/accounts', ['name' => '', 'type' => 'crypto'])
        ->assertSessionHasErrors(['name', 'type']);
});

it('renders the account detail page with transactions and the month summary', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    Transaction::factory()->for($account)->debit()->create(['category' => 'Rent', 'amount_cents' => 90_000, 'occurred_on' => '2026-10-03']);
    Transaction::factory()->for($account)->credit()->create(['category' => 'Salary', 'amount_cents' => 200_000, 'occurred_on' => '2026-09-28']);

    $this->actingAs($user)->get("/accounts/{$account->id}?month=2026-10")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('accounts/show')
            ->where('account.balance_cents', 110_000)
            ->has('transactions.data', 2)
            ->where('transactions.meta.total', 2)
            ->where('summary.month', '2026-10')
            ->where('summary.spending_cents', 90_000)
            ->where('summary.income_cents', 0)
            ->where('months.previous', '2026-09')
            ->where('months.next', '2026-11')
            ->where('months.label', 'October 2026')
            ->where('categories', ['Rent', 'Salary']));
});

it('adds a transaction from the form', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();

    $this->actingAs($user)->from("/accounts/{$account->id}")
        ->post("/accounts/{$account->id}/transactions", [
            'amount_cents' => 1_250, 'type' => 'debit', 'category' => 'Dining', 'description' => '', 'occurred_on' => '2026-10-05',
        ])
        ->assertRedirect("/accounts/{$account->id}");

    expect($account->transactions()->sole())
        ->amount_cents->toBe(1_250)
        ->description->toBeNull();
});

it('rejects a transaction with no amount', function (): void {
    $account = Account::factory()->create();

    $this->actingAs($account->user)
        ->post("/accounts/{$account->id}/transactions", ['amount_cents' => null, 'type' => 'debit', 'category' => 'X', 'occurred_on' => '2026-10-05'])
        ->assertSessionHasErrors('amount_cents');
});

it('keeps users out of other people\'s accounts and transactions', function (): void {
    $account = Account::factory()->create();
    $transaction = Transaction::factory()->for($account)->create();
    $this->actingAs(User::factory()->create());

    $this->get("/accounts/{$account->id}")->assertForbidden();
    $this->delete("/accounts/{$account->id}")->assertForbidden();
    $this->post("/accounts/{$account->id}/transactions", [
        'amount_cents' => 100, 'type' => 'debit', 'category' => 'X', 'occurred_on' => '2026-10-05',
    ])->assertForbidden();
    $this->delete("/transactions/{$transaction->id}")->assertForbidden();

    expect(Transaction::count())->toBe(1);
});

it('deletes a transaction and an account', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($account)->create();
    $this->actingAs($user);

    $this->from("/accounts/{$account->id}")->delete("/transactions/{$transaction->id}")->assertRedirect("/accounts/{$account->id}");
    expect(Transaction::count())->toBe(0);

    $this->delete("/accounts/{$account->id}")->assertRedirect('/accounts');
    expect(Account::count())->toBe(0);
});
