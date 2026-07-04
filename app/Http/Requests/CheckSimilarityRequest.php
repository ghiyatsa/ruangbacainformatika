<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

class CheckSimilarityRequest extends FormRequest
{
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
        $rules = [
            'judul' => ['required', 'string', 'min:5'],
            'document_type' => ['nullable', 'string', 'in:skripsi,internship_report'],
        ];

        if (config('services.turnstile.enabled', false)) {
            $rules['cf-turnstile-response'] = ['required', 'string', new Turnstile];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Silakan selesaikan verifikasi keamanan.',
            'cf-turnstile-response.turnstile' => 'Verifikasi keamanan gagal, silakan coba lagi.',
        ];
    }

    /**
     * Get the validated title trimmed and ready for use.
     */
    public function validatedJudul(): string
    {
        return trim((string) $this->validated('judul'));
    }

    /**
     * Get the validated document type.
     */
    public function validatedDocumentType(): ?string
    {
        return $this->validated('document_type');
    }
}
