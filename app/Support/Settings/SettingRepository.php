<?php

namespace App\Support\Settings;

use App\Models\Setting;

class SettingRepository
{
    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function sectionValues(string $section, array $defaults = []): array
    {
        $storedValues = Setting::query()
            ->where('section', $section)
            ->pluck('value', 'key')
            ->all();

        return array_replace($defaults, $storedValues);
    }

    public function get(string $section, string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()
            ->where('section', $section)
            ->where('key', $key)
            ->first();

        return $setting?->value ?? $default;
    }

    public function put(string $section, string $key, mixed $value): Setting
    {
        return Setting::query()->updateOrCreate(
            [
                'section' => $section,
                'key' => $key,
            ],
            [
                'value' => filled($value) ? (string) $value : null,
            ],
        );
    }

    public function forget(string $section, string $key): void
    {
        Setting::query()
            ->where('section', $section)
            ->where('key', $key)
            ->delete();
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
