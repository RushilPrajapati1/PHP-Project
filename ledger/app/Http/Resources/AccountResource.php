<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Account;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Account
 */
class AccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hasBalance = array_key_exists('credits_cents', $this->resource->getAttributes());
        $balanceCents = (int) $this->credits_cents - (int) $this->debits_cents;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'balance_cents' => $this->when($hasBalance, $balanceCents),
            'balance' => $this->when($hasBalance, fn (): float => Money::toDollars($balanceCents)),
            'transactions_count' => $this->whenCounted('transactions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
