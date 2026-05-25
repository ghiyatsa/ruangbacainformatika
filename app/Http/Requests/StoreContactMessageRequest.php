<?php

namespace App\Http\Requests;

use App\Actions\Fortify\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContactMessageRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(max: 120),
            'email' => ['required', 'email', 'max:255'],
            'phone' => $this->phoneRules(),
            'subject' => $this->meaningfulTextRules('Subjek pesan', min: 5, max: 160),
            'message' => $this->meaningfulTextRules('Pesan', min: 20, max: 3000, minWords: 4),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))->squish()->toString(),
            'phone' => $this->normalizePhoneNumber((string) $this->input('phone')),
            'subject' => Str::of((string) $this->input('subject'))->squish()->toString(),
            'message' => Str::of((string) $this->input('message'))->squish()->toString(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 15 digit.',
            'subject.required' => 'Subjek pesan wajib diisi.',
            'subject.min' => 'Subjek pesan minimal 5 karakter.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal 20 karakter agar kami bisa memahami kebutuhan Anda.',
        ];
    }
}
