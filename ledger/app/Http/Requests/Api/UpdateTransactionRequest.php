<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Transaction $transaction */
        $transaction = $this->route('transaction');

        return $this->user()?->can('update', $transaction->account) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount_cents' => ['sometimes', 'required', 'integer', 'min:1', 'max:2000000000'],
            'type' => ['sometimes', 'required', Rule::enum(TransactionType::class)],
            'category' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'occurred_on' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Friendlier field names in validation messages ("The amount field is required").
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount_cents' => 'amount',
            'occurred_on' => 'date',
        ];
    }
}
