<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class SummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('account')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }

    /** The requested month, defaulting to the current one. */
    public function month(): CarbonImmutable
    {
        $month = $this->validated('month');

        return is_string($month)
            ? CarbonImmutable::createFromFormat('!Y-m', $month)->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();
    }
}
