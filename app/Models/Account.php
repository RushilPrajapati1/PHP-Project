<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\TransactionType;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property AccountType $type
 * @property int|null $credits_cents only when loaded via withBalance()
 * @property int|null $debits_cents only when loaded via withBalance()
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'type'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Adds credits_cents and debits_cents sums so the balance can be computed without N+1 queries.
     *
     * @param  Builder<Account>  $query
     */
    #[Scope]
    protected function withBalance(Builder $query): void
    {
        $query->withSum(['transactions as credits_cents' => fn (Builder $q) => $q->where('type', TransactionType::Credit)], 'amount_cents')
            ->withSum(['transactions as debits_cents' => fn (Builder $q) => $q->where('type', TransactionType::Debit)], 'amount_cents');
    }

    /** Same as withBalance(), for an already-loaded model. */
    public function loadBalance(): static
    {
        return $this->loadSum(['transactions as credits_cents' => fn (Builder $q) => $q->where('type', TransactionType::Credit)], 'amount_cents')
            ->loadSum(['transactions as debits_cents' => fn (Builder $q) => $q->where('type', TransactionType::Debit)], 'amount_cents');
    }
}
