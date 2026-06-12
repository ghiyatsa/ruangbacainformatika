<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\KioskIdlePolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->defaults() as $section => $values) {
            foreach ($values as $key => $value) {
                Setting::query()->firstOrCreate(
                    [
                        'section' => $section,
                        'key' => $key,
                    ],
                    [
                        'value' => $value,
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function defaults(): array
    {
        $similaritySecret = config('services.similarity_api.secret');
        $globalNotice = config('app.seed_defaults.global_notice', []);
        $kioskDefaultPin = trim((string) config('app.seed_defaults.kiosk.pin', ''));

        return [
            'general' => [
                'site_name' => config('app.name'),
                'site_tagline' => 'Layanan perpustakaan yang rapi dan mudah diakses',
                'support_whatsapp' => '',
                'hero_notice_enabled' => ! empty($globalNotice['enabled']) ? '1' : '0',
                'hero_notice_text' => is_string($globalNotice['text'] ?? null) ? trim($globalNotice['text']) : '',
                'hero_notice_url' => is_string($globalNotice['url'] ?? null) ? trim($globalNotice['url']) : '',
                'hero_notice_link_label' => is_string($globalNotice['link_label'] ?? null) ? trim($globalNotice['link_label']) : '',
                'hero_notice_tone' => in_array($globalNotice['tone'] ?? null, ['info', 'warning', 'success'], true)
                    ? (string) $globalNotice['tone']
                    : 'info',
            ],
            'library' => [
                'loan_max_books' => '3',
                'loan_duration_days' => '5',
            ],
            'kiosk' => [
                'pin_hash' => $kioskDefaultPin !== '' ? Hash::make($kioskDefaultPin) : '',
                'session_version' => '1',
                'operating_open_time' => KioskIdlePolicy::DEFAULT_OPERATING_OPEN_TIME,
                'operating_close_time' => KioskIdlePolicy::DEFAULT_OPERATING_CLOSE_TIME,
            ],
            'integration' => [
                'turnstile_enabled' => '0',
                'similarity_api_url' => (string) config('services.similarity_api.url', 'http://localhost:8181'),
                'similarity_api_secret' => filled($similaritySecret)
                    ? encrypt((string) $similaritySecret)
                    : null,
                'similarity_api_timeout' => (string) config('services.similarity_api.timeout', 10),
                'similarity_api_top_k' => '5',
                'similarity_api_threshold' => '0.5',
                'similarity_weight_judul' => '0.7',
                'similarity_weight_abstrak' => '0.2',
                'similarity_weight_kata_kunci' => '0.1',
                'whatsapp_api_url' => (string) config('services.fonnte.url', ''),
                'whatsapp_api_token' => (string) config('services.fonnte.token', ''),
                'whatsapp_failure_pause_threshold' => (string) config('services.fonnte.failure_pause_threshold', 5),
                'whatsapp_failure_pause_window_minutes' => (string) config('services.fonnte.failure_pause_window_minutes', 15),
            ],
        ];
    }
}
