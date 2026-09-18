<?php

declare(strict_types=1);

namespace App\Support\Casts;

use App\Support\TextStandardizer;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TitleCaseCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        return TextStandardizer::titleCase((string) $value);
    }
}
