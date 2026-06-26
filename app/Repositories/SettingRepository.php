<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingRepository
{
    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function sectionValues(string $section, array $defaults = []): array
    {
        if (app()->runningUnitTests()) {
            $storedValues = Setting::query()
                ->where('section', $section)
                ->pluck('value', 'key')
                ->all();

            return array_replace($defaults, $storedValues);
        }

        return once(function () use ($section, $defaults) {
            $storedValues = Cache::remember(
                "site-settings:${section}",
                now()->addDay(),
                fn () => Setting::query()
                    ->where('section', $section)
                    ->pluck('value', 'key')
                    ->all()
            );

            return array_replace($defaults, $storedValues);
        });
    }

    public function get(string $section, string $key, mixed $default = null): mixed
    {
        $values = $this->sectionValues($section);

        return $values[$key] ?? $default;
    }

    public function put(string $section, string $key, mixed $value): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            [
                'section' => $section,
                'key' => $key,
            ],
            [
                'value' => filled($value) ? (string) $value : null,
            ],
        );

        Cache::forget("site-settings:${section}");

        return $setting;
    }

    public function forget(string $section, string $key): void
    {
        Setting::query()
            ->where('section', $section)
            ->where('key', $key)
            ->delete();

        Cache::forget("site-settings:${section}");
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function putMany(string $section, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->put($section, (string) $key, $value);
        }
    }
}
