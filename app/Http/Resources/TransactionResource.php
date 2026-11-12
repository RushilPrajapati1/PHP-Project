<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Transaction;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'type' => $this->type->value,
            'amount_cents' => $this->amount_cents,
            'amount' => Money::toDollars($this->amount_cents),
            'category' => $this->category,
            'description' => $this->description,
            'occurred_on' => $this->occurred_on->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
