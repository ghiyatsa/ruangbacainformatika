<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

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
        return [
            'general' => [
                'site_name' => config('app.name'),
                'site_tagline' => 'Layanan perpustakaan yang rapi dan mudah diakses',
                'support_whatsapp' => '',
            ],
            'library' => [
                'loan_max_books' => '3',
                'loan_duration_days' => '5',
            ],
            'kiosk' => [
                'title' => 'Pendataan Pengunjung Perpustakaan',
                'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kios.',
                'session_version' => '1',
            ],
            'integration' => [
                'turnstile_enabled' => '0',
                'similarity_api_url' => (string) config('services.similarity_api.url', 'http://localhost:8181'),
                'similarity_api_timeout' => (string) config('services.similarity_api.timeout', 10),
                'similarity_api_top_k' => '5',
                'similarity_api_threshold' => '0.5',
                'similarity_weight_judul' => '0.7',
                'similarity_weight_abstrak' => '0.2',
                'similarity_weight_kata_kunci' => '0.1',
                'whatsapp_api_url' => '',
                'whatsapp_api_token' => '',
            ],
        ];
    }
}
