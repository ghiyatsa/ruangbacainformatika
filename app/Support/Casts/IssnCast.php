<?php

namespace App\Support\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class IssnCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return static::normalize($value);
    }

    protected static function normalize(mixed $value): ?string
    {
        $normalized = is_string($value) ? trim(mb_strtoupper($value)) : null;

        return filled($normalized) ? $normalized : null;
    }
}
