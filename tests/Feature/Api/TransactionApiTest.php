<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->for($this->user)->create();
    Sanctum::actingAs($this->user);
});

it('creates a transaction and returns dollars', function (): void {
    $this->postJson("/api/accounts/{$this->account->id}/transactions", [
        'amount_cents' => 1_299,
        'type' => 'debit',
        'category' => 'Dining',
        'occurred_on' => '2026-10-05',
    ])
        ->assertCreated()
        ->assertJsonPath('data.amount_cents', 1_299)
        ->assertJsonPath('data.amount', 12.99)
        ->assertJsonPath('data.description', null);
});

it('validates amount and type', function (array $payload, string $field): void {
    $valid = ['amount_cents' => 100, 'type' => 'debit', 'category' => 'Food', 'occurred_on' => '2026-10-01'];

    $this->postJson("/api/accounts/{$this->account->id}/transactions", [...$valid, ...$payload])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'zero amount' => [['amount_cents' => 0], 'amount_cents'],
    'negative amount' => [['amount_cents' => -5], 'amount_cents'],
    'decimal amount' => [['amount_cents' => 12.5], 'amount_cents'],
    'bad type' => [['type' => 'refund'], 'type'],
    'bad date' => [['occurred_on' => '10/05/2026'], 'occurred_on'],
    'missing category' => [['category' => ''], 'category'],
]);

it('paginates transactions newest first', function (): void {
    Transaction::factory()->for($this->account)->create(['occurred_on' => '2026-01-01']);
    Transaction::factory()->for($this->account)->create(['occurred_on' => '2026-03-01']);

    $this->getJson("/api/accounts/{$this->account->id}/transactions")
        ->assertOk()
        ->assertJsonPath('data.0.occurred_on', '2026-03-01')
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('updates and deletes via shallow routes', function (): void {
    $txn = Transaction::factory()->for($this->account)->create();

    $this->patchJson("/api/transactions/{$txn->id}", ['category' => 'Rent'])
        ->assertOk()
        ->assertJsonPath('data.category', 'Rent');

    $this->deleteJson("/api/transactions/{$txn->id}")->assertNoContent();
    expect(Transaction::find($txn->id))->toBeNull();
});

it('blocks access to another user\'s transactions', function (): void {
    $foreignAccount = Account::factory()->create();
    $foreignTxn = Transaction::factory()->for($foreignAccount)->create();

    $this->getJson("/api/accounts/{$foreignAccount->id}/transactions")->assertForbidden();
    $this->postJson("/api/accounts/{$foreignAccount->id}/transactions", [
        'amount_cents' => 100, 'type' => 'debit', 'category' => 'X', 'occurred_on' => '2026-10-01',
    ])->assertForbidden();
    $this->getJson("/api/transactions/{$foreignTxn->id}")->assertForbidden();
    $this->patchJson("/api/transactions/{$foreignTxn->id}", ['amount_cents' => 1])->assertForbidden();
    $this->deleteJson("/api/transactions/{$foreignTxn->id}")->assertForbidden();
});
