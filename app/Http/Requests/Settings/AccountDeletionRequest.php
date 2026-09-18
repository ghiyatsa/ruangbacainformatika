<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Services\AccountDeletionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi permintaan penghapusan akun.
 *
 * Proyek ini hanya memakai autentikasi Google, sehingga tidak ada password
 * untuk diverifikasi. Proteksi yang dipakai adalah konfirmasi mengetik kata
 * kunci, sehingga akun tidak terhapus karena kesalahan klik.
 */
class AccountDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmation' => [
                'required',
                'string',
                Rule::in([AccountDeletionService::CONFIRMATION_PHRASE]),
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation.required' => 'Ketik '.AccountDeletionService::CONFIRMATION_PHRASE.' untuk mengonfirmasi.',
            'confirmation.in' => 'Kata konfirmasi tidak sesuai. Ketik '.AccountDeletionService::CONFIRMATION_PHRASE.'.',
        ];
    }
}
