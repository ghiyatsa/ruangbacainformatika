<?php

namespace App\Support;

use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SiteSettings
{
    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return array<string, string>
     */
    public function values(): array
    {
        return once(function (): array {
            try {
                $values = $this->settingRepository->sectionValues('general', $this->defaults());
            } catch (Throwable) {
                $values = $this->defaults();
            }

            return [
                'site_name' => $this->stringValue($values, 'site_name', config('app.name')),
                'site_tagline' => $this->stringValue($values, 'site_tagline', 'Layanan perpustakaan yang rapi dan mudah diakses'),
                'site_description' => $this->stringValue($values, 'site_description', 'Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.'),
                'department' => $this->stringValue($values, 'department', 'Program Studi Teknik Informatika Universitas Malikussaleh'),
                'contact_email' => $this->stringValue($values, 'contact_email', 'informatika@unimal.ac.id'),
                'support_whatsapp' => $this->stringValue($values, 'support_whatsapp'),
                'address' => $this->stringValue($values, 'address', 'Jl. Cot Tengku Nie, Reuleut, Aceh Utara 24355'),
                'site_keywords' => $this->stringValue($values, 'site_keywords'),
                'seo_robots' => $this->robotsValue($values['seo_robots'] ?? null),
                'theme_color' => $this->themeColorValue($values['theme_color'] ?? null),
                'site_logo_path' => $this->stringValue($values, 'site_logo_path'),
                'og_image_path' => $this->stringValue($values, 'og_image_path'),
                'favicon_path' => $this->stringValue($values, 'favicon_path'),
                'favicon_svg_path' => $this->stringValue($values, 'favicon_svg_path'),
                'apple_touch_icon_path' => $this->stringValue($values, 'apple_touch_icon_path'),
                'hero_notice_enabled' => ($values['hero_notice_enabled'] ?? '0') === '1' ? '1' : '0',
                'hero_notice_text' => $this->stringValue($values, 'hero_notice_text'),
                'hero_notice_url' => $this->stringValue($values, 'hero_notice_url'),
                'hero_notice_link_label' => $this->stringValue($values, 'hero_notice_link_label'),
                'hero_notice_tone' => $this->noticeToneValue($values['hero_notice_tone'] ?? null),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function shared(): array
    {
        $settings = $this->values();

        return [
            'name' => $settings['site_name'],
            'site' => [
                'url' => rtrim((string) config('app.url'), '/'),
                'name' => $settings['site_name'],
                'tagline' => $settings['site_tagline'],
                'description' => $settings['site_description'],
                'department' => $settings['department'],
                'contactEmail' => $settings['contact_email'],
                'supportWhatsapp' => $settings['support_whatsapp'] !== '' ? $settings['support_whatsapp'] : null,
                'address' => $settings['address'],
                'keywords' => $settings['site_keywords'] !== '' ? $settings['site_keywords'] : null,
                'robots' => $settings['seo_robots'],
                'themeColor' => $settings['theme_color'],
                'logo' => $this->publicDiskUrl($settings['site_logo_path']),
                'ogImage' => route('og.site'),
                'ogImageType' => OpenGraphImage::MIME_TYPE,
                'ogImageWidth' => OpenGraphImage::SITE_WIDTH,
                'ogImageHeight' => OpenGraphImage::SITE_HEIGHT,
                'icons' => [
                    'favicon' => $this->publicDiskUrl($settings['favicon_path']) ?? asset('favicon-32x32.png'),
                    'faviconSvg' => $this->publicDiskUrl($settings['favicon_svg_path']) ?? asset('favicon.svg'),
                    'appleTouchIcon' => $this->publicDiskUrl($settings['apple_touch_icon_path']) ?? asset('apple-touch-icon.png'),
                ],
                'notice' => $this->sharedNotice(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sharedNotice(): array
    {
        $settings = $this->values();
        $heroNoticeText = trim($settings['hero_notice_text']);
        $heroNoticeUrl = trim($settings['hero_notice_url']);
        $heroNoticeLinkLabel = trim($settings['hero_notice_link_label']);

        return [
            'isActive' => $settings['hero_notice_enabled'] === '1' && $heroNoticeText !== '',
            'text' => $heroNoticeText,
            'url' => $heroNoticeUrl !== '' ? $heroNoticeUrl : null,
            'linkLabel' => $heroNoticeLinkLabel !== '' ? $heroNoticeLinkLabel : null,
            'tone' => $settings['hero_notice_tone'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rootViewData(): array
    {
        $shared = $this->shared();
        $site = $shared['site'];

        return [
            'siteMeta' => [
                'title' => $shared['name'],
                'description' => $site['description'],
                'keywords' => $site['keywords'],
                'robots' => $site['robots'],
                'themeColor' => $site['themeColor'],
                'ogImage' => $site['ogImage'],
                'ogImageType' => $site['ogImageType'],
                'ogImageWidth' => $site['ogImageWidth'],
                'ogImageHeight' => $site['ogImageHeight'],
                'favicon' => $site['icons']['favicon'],
                'faviconSvg' => $site['icons']['faviconSvg'],
                'appleTouchIcon' => $site['icons']['appleTouchIcon'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaults(): array
    {
        return [
            'site_name' => (string) config('app.name'),
            'site_tagline' => 'Layanan perpustakaan yang rapi dan mudah diakses',
            'site_description' => 'Perpustakaan digital resmi Program Studi Teknik Informatika Universitas Malikussaleh untuk mendukung pembelajaran, riset, dan akses koleksi akademik.',
            'department' => 'Program Studi Teknik Informatika Universitas Malikussaleh',
            'contact_email' => 'informatika@unimal.ac.id',
            'support_whatsapp' => '',
            'address' => 'Jl. Cot Tengku Nie, Reuleut, Aceh Utara 24355',
            'site_keywords' => '',
            'seo_robots' => 'index,follow',
            'theme_color' => '#ffffff',
            'site_logo_path' => '',
            'og_image_path' => '',
            'favicon_path' => '',
            'favicon_svg_path' => '',
            'apple_touch_icon_path' => '',
            'hero_notice_enabled' => '0',
            'hero_notice_text' => '',
            'hero_notice_url' => '',
            'hero_notice_link_label' => '',
            'hero_notice_tone' => 'info',
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    protected function stringValue(array $values, string $key, string $default = ''): string
    {
        $value = $values[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    protected function publicDiskUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    protected function noticeToneValue(mixed $value): string
    {
        return in_array($value, ['info', 'warning', 'success'], true)
            ? $value
            : 'info';
    }

    protected function robotsValue(mixed $value): string
    {
        return in_array($value, ['index,follow', 'noindex,follow', 'noindex,nofollow'], true)
            ? $value
            : 'index,follow';
    }

    protected function themeColorValue(mixed $value): string
    {
        if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1) {
            return strtoupper($value);
        }

        return '#ffffff';
    }
}
