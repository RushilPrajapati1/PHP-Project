<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    // Runs before rules(), so a non-owner gets 403 rather than validation errors.
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
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'type' => ['sometimes', 'required', Rule::enum(AccountType::class)],
        ];
    }
}
