<?php

namespace App\Http\Requests\Kiosk;

use App\Models\VisitLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Buku tamu cepat lewat Member Key (QR) anggota.
 *
 * Berbeda dari SubmitVisitRequest (form manual), di sini anggota hanya
 * menyerahkan payload QR; identitas diambil dari profil anggota sehingga
 * tidak ada satu pun kolom yang perlu diisi ulang.
 */
class SubmitMemberVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'verification_payload' => ['required', 'string', 'max:2048'],
            'purpose' => [
                'nullable',
                Rule::in(array_keys(VisitLog::purposeOptions())),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'verification_payload' => Str::of((string) $this->input('verification_payload'))
                ->trim()
                ->toString(),
        ]);
    }

    public function validatedVerificationPayload(): string
    {
        return (string) $this->validated('verification_payload');
    }

    /**
     * Tujuan kunjungan; bawaan "Baca di tempat" bila anggota tidak memilih.
     */
    public function validatedPurpose(): string
    {
        $purpose = $this->validated('purpose');

        return is_string($purpose) && $purpose !== '' ? $purpose : 'read';
    }
}
