<?php

namespace App\Http\Requests;

use App\Actions\Fortify\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePostCommentRequest extends FormRequest
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
            'content' => $this->meaningfulTextRules('Komentar', min: 3, max: 1000, minWords: 1),
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
            'reply_to_comment_id' => ['nullable', 'integer', 'exists:post_comments,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => Str::of((string) $this->input('content'))->squish()->toString(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Komentar wajib diisi.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
            'parent_id.exists' => 'Komentar induk tidak valid.',
            'reply_to_comment_id.exists' => 'Komentar tujuan balasan tidak valid.',
        ];
    }
}
