<?php

namespace App\Http\Requests;

use App\Actions\Fortify\ProfileValidationRules;
use App\Models\CatalogReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCatalogReportRequest extends FormRequest
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
            'catalog_type' => ['required', 'string', Rule::in(array_keys(CatalogReport::catalogTypeOptions()))],
            'catalog_id' => ['required', 'integer', 'min:1'],
            'reporter_name' => $this->nameRules(required: false, max: 120),
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'message' => $this->meaningfulTextRules('Laporan', min: 10, max: 2000, minWords: 4),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reporter_name' => Str::of((string) $this->input('reporter_name'))->squish()->toString(),
            'reporter_email' => Str::of((string) $this->input('reporter_email'))->trim()->lower()->toString(),
            'message' => Str::of((string) $this->input('message'))->squish()->toString(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'catalog_type.required' => 'Jenis katalog wajib dikirim.',
            'catalog_type.in' => 'Jenis katalog tidak valid.',
            'catalog_id.required' => 'Data katalog yang dilaporkan wajib dipilih.',
            'reporter_email.email' => 'Format email tidak valid.',
            'message.required' => 'Mohon jelaskan bagian data yang keliru.',
            'message.min' => 'Penjelasan laporan minimal 10 karakter.',
            'message.max' => 'Penjelasan laporan maksimal 2000 karakter.',
        ];
    }
}
