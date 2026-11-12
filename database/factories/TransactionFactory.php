<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    private const array DEBIT_CATEGORIES = ['Groceries', 'Rent', 'Dining', 'Transport', 'Utilities', 'Entertainment', 'Shopping', 'Health'];

    private const array CREDIT_CATEGORIES = ['Salary', 'Refund', 'Interest', 'Transfer'];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Roughly 80% spending, 20% income.
        $type = fake()->boolean(80) ? TransactionType::Debit : TransactionType::Credit;

        return [
            'account_id' => Account::factory(),
            'type' => $type,
            'amount_cents' => $type === TransactionType::Debit
                ? fake()->numberBetween(300, 25_000)       // $3 – $250
                : fake()->numberBetween(50_000, 400_000),  // $500 – $4,000
            'category' => fake()->randomElement(
                $type === TransactionType::Debit ? self::DEBIT_CATEGORIES : self::CREDIT_CATEGORIES,
            ),
            'description' => fake()->optional(0.7)->sentence(3),
            'occurred_on' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (): array => [
            'type' => TransactionType::Debit,
            'category' => fake()->randomElement(self::DEBIT_CATEGORIES),
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (): array => [
            'type' => TransactionType::Credit,
            'category' => fake()->randomElement(self::CREDIT_CATEGORIES),
        ]);
    }
}
