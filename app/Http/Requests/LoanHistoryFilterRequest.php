<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanHistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'in:all,overdue,active,returned'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array{filter: string, search: string}
     */
    public function filters(): array
    {
        return [
            'filter' => $this->validated('filter') ?? 'all',
            'search' => trim((string) ($this->validated('search') ?? '')),
        ];
    }
}
