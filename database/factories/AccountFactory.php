<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(AccountType::cases()),
            // Resolved after states like ofType() apply, so the name always matches the final type.
            'name' => function (array $attributes): string {
                // `type` may arrive as the enum or as its raw string value (e.g. ['type' => 'savings']).
                $type = $attributes['type'] instanceof AccountType
                    ? $attributes['type']
                    : AccountType::from(is_string($attributes['type']) ? $attributes['type'] : '');

                return match ($type) {
                    AccountType::Checking => fake()->randomElement(['Everyday Checking', 'Main Checking']),
                    AccountType::Savings => fake()->randomElement(['Emergency Fund', 'High-Yield Savings']),
                    AccountType::Credit => fake()->randomElement(['Visa Card', 'Rewards Card']),
                };
            },
        ];
    }

    public function ofType(AccountType $type): static
    {
        return $this->state(fn (): array => ['type' => $type]);
    }
}
