<?php

namespace App\Support\Casts;

use App\Support\TextStandardizer;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SquishCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return TextStandardizer::squish($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return TextStandardizer::squish($value);
    }
}
