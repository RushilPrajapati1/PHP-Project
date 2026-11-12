<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    // Adding a transaction counts as updating the account it belongs to.
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('account')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Positive integer cents; direction comes from `type`. Upper bound keeps it inside a signed INT column.
            'amount_cents' => ['required', 'integer', 'min:1', 'max:2000000000'],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_on' => ['required', 'date_format:Y-m-d'],
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
