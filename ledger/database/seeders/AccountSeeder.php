<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Give every user without accounts a checking, savings and credit account,
     * each with a few months of fake transactions.
     */
    public function run(): void
    {
        User::query()->doesntHave('accounts')->each(function (User $user): void {
            foreach (AccountType::cases() as $type) {
                Account::factory()
                    ->for($user)
                    ->ofType($type)
                    ->has(Transaction::factory()->count(25))
                    ->create();
            }
        });
    }
}
