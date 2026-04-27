<?php

namespace App\Http\Requests\Kiosk;

use App\Models\VisitLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubmitVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'visitor_type' => ['required', Rule::in(array_keys(VisitLog::visitorTypeOptions()))],
            'identity_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn (): bool => $this->input('visitor_type') !== VisitLog::VISITOR_TYPE_UMUM),
            ],
            'institution' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn (): bool => $this->input('visitor_type') === VisitLog::VISITOR_TYPE_UMUM),
            ],
            'phone' => ['nullable', 'numeric', 'digits_between:10,15'],
            'purpose' => ['required', Rule::in(array_keys(VisitLog::purposeOptions()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'identity_number' => Str::of((string) $this->input('identity_number'))->trim()->toString(),
            'institution' => Str::of((string) $this->input('institution'))->squish()->toString(),
            'phone' => trim((string) $this->input('phone')),
            'notes' => Str::of((string) $this->input('notes'))->squish()->toString(),
        ]);
    }
}
