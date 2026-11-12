<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Account::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::enum(AccountType::class)],
        ];
    }
}
