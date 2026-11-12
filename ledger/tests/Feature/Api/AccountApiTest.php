<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('requires authentication', function (): void {
    $this->getJson('/api/accounts')->assertUnauthorized();
});

it('lists only the current user\'s accounts with balances', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'Mine']);
    Transaction::factory()->for($account)->credit()->create(['amount_cents' => 10_000]);
    Transaction::factory()->for($account)->debit()->create(['amount_cents' => 2_550]);
    Account::factory()->create(['name' => 'Someone else']);

    Sanctum::actingAs($user);

    $this->getJson('/api/accounts')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Mine')
        ->assertJsonPath('data.0.balance_cents', 7_450)
        ->assertJsonPath('data.0.balance', 74.5);
});

it('creates an account', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/accounts', ['name' => 'Travel Fund', 'type' => 'savings'])
        ->assertCreated()
        ->assertJsonPath('data.type', 'savings');

    expect($user->accounts()->count())->toBe(1);
});

it('rejects an unknown account type', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/accounts', ['name' => 'X', 'type' => 'crypto'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('type');
});

it('forbids viewing, updating or deleting another user\'s account', function (): void {
    $other = Account::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/accounts/{$other->id}")->assertForbidden();
    $this->patchJson("/api/accounts/{$other->id}", ['name' => 'Hacked'])->assertForbidden();
    $this->deleteJson("/api/accounts/{$other->id}")->assertForbidden();

    expect($other->fresh()->name)->not->toBe('Hacked');
});

it('deletes an account and its transactions', function (): void {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->has(Transaction::factory()->count(3))->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/accounts/{$account->id}")->assertNoContent();

    expect(Transaction::count())->toBe(0);
});
