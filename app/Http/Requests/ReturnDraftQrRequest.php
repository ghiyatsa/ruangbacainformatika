<?php

namespace App\Http\Requests;

use App\Models\LoanItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnDraftQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'loan_item_ids' => ['required', 'array', 'min:1'],
            'loan_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(LoanItem::class, 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $loanItemIds = collect($this->input('loan_item_ids', []))
            ->map(fn (mixed $loanItemId): ?int => is_numeric($loanItemId)
                ? (int) $loanItemId
                : null)
            ->filter(fn (?int $loanItemId): bool => $loanItemId !== null)
            ->values()
            ->all();

        $this->merge([
            'loan_item_ids' => $loanItemIds,
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function validatedLoanItemIds(): array
    {
        return array_map('intval', (array) $this->validated('loan_item_ids'));
    }
}
