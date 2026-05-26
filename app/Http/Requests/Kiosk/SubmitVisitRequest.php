<?php

namespace App\Http\Requests\Kiosk;

use App\Actions\Fortify\ProfileValidationRules;
use App\Models\VisitLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubmitVisitRequest extends FormRequest
{
    use ProfileValidationRules;

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
            'name' => $this->nameRules(),
            'visitor_type' => ['required', Rule::in(array_keys(VisitLog::visitorTypeOptions()))],
            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if (! preg_match('/^\d{6,30}$/', $value)) {
                        $fail('Nomor identitas hanya boleh berisi 6 sampai 30 digit.');
                    }
                },
            ],
            'institution' => $this->institutionRules(),
            'phone' => $this->phoneRules(),
            'purpose' => ['required', Rule::in(array_keys(VisitLog::purposeOptions()))],
            'notes' => $this->meaningfulTextRules('Catatan', required: false, min: 10, max: 1000),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'identity_number' => Str::of((string) $this->input('identity_number'))->trim()->toString(),
            'institution' => Str::of((string) $this->input('institution'))->squish()->toString(),
            'phone' => $this->normalizePhoneNumber((string) $this->input('phone')),
            'notes' => Str::of((string) $this->input('notes'))->squish()->toString(),
        ]);
    }
}
