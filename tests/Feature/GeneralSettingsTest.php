<?php

use App\Filament\Clusters\Settings\Pages\GeneralSettings;
use App\Models\Setting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeGeneralSettingsSuperAdmin(): User
{
    $user = User::factory()->create();

    $role = Role::firstOrCreate([
        'name' => 'super_admin',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

it('general settings can persist site metadata and branding fields', function () {
    $user = makeGeneralSettingsSuperAdmin();

    actingAs($user);

    Livewire::test(GeneralSettings::class)
        ->set('data.site_name', 'Ruang Baca Informatika')
        ->set('data.site_tagline', 'Portal koleksi dan layanan digital')
        ->set('data.site_description', 'Katalog digital untuk buku, skripsi, tesis, dan laporan kerja praktik.')
        ->set('data.department', 'Teknik Informatika Universitas Malikussaleh')
        ->set('data.contact_email', 'halo@ruangbaca.test')
        ->set('data.support_whatsapp', '081234567890')
        ->set('data.address', 'Kampus Reuleut, Aceh Utara')
        ->set('data.site_keywords', 'perpustakaan digital, katalog buku, skripsi')
        ->set('data.seo_robots', 'noindex,follow')
        ->set('data.theme_color', '#123ABC')
        ->set('data.hero_notice_enabled', true)
        ->set('data.hero_notice_text', 'Layanan katalog tutup sementara pada Sabtu ini.')
        ->set('data.hero_notice_url', 'https://example.com/pengumuman')
        ->set('data.hero_notice_link_label', 'Baca info')
        ->set('data.hero_notice_tone', 'warning')
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Pengaturan umum berhasil disimpan');

    expect(Setting::query()->where('section', 'general')->where('key', 'site_name')->value('value'))->toBe('Ruang Baca Informatika')
        ->and(Setting::query()->where('section', 'general')->where('key', 'site_description')->value('value'))->toBe('Katalog digital untuk buku, skripsi, tesis, dan laporan kerja praktik.')
        ->and(Setting::query()->where('section', 'general')->where('key', 'contact_email')->value('value'))->toBe('halo@ruangbaca.test')
        ->and(Setting::query()->where('section', 'general')->where('key', 'site_keywords')->value('value'))->toBe('perpustakaan digital, katalog buku, skripsi')
        ->and(Setting::query()->where('section', 'general')->where('key', 'seo_robots')->value('value'))->toBe('noindex,follow')
        ->and(Setting::query()->where('section', 'general')->where('key', 'theme_color')->value('value'))->toBe('#123ABC')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_enabled')->value('value'))->toBe('1')
        ->and(Setting::query()->where('section', 'general')->where('key', 'hero_notice_tone')->value('value'))->toBe('warning');
});

it('public pages use the stored site metadata and icon links', function () {
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_name'],
        ['value' => 'Ruang Baca Custom'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_description'],
        ['value' => 'Deskripsi publik kustom untuk pengujian metadata.'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'department'],
        ['value' => 'Teknik Informatika Test'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'contact_email'],
        ['value' => 'kontak@test.invalid'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'address'],
        ['value' => 'Alamat test metadata'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'site_keywords'],
        ['value' => 'metadata,test,seo'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'seo_robots'],
        ['value' => 'noindex,nofollow'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'theme_color'],
        ['value' => '#0F172A'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'og_image_path'],
        ['value' => 'site-assets/custom-og.png'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'favicon_path'],
        ['value' => 'site-assets/custom-favicon.png'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'favicon_svg_path'],
        ['value' => 'site-assets/custom-favicon.svg'],
    );
    Setting::query()->updateOrCreate(
        ['section' => 'general', 'key' => 'apple_touch_icon_path'],
        ['value' => 'site-assets/custom-apple-touch.png'],
    );

    get(route('about'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('about')
                ->where('name', 'Ruang Baca Custom')
                ->where('site.description', 'Deskripsi publik kustom untuk pengujian metadata.')
                ->where('site.department', 'Teknik Informatika Test')
                ->where('site.contactEmail', 'kontak@test.invalid')
                ->where('site.address', 'Alamat test metadata')
                ->where('site.keywords', 'metadata,test,seo')
                ->where('site.robots', 'noindex,nofollow')
                ->where('site.themeColor', '#0F172A')
                ->where('site.ogImage', url('/storage/site-assets/custom-og.png'))
                ->where('site.icons.favicon', url('/storage/site-assets/custom-favicon.png'))
                ->where('site.icons.faviconSvg', url('/storage/site-assets/custom-favicon.svg'))
                ->where('site.icons.appleTouchIcon', url('/storage/site-assets/custom-apple-touch.png')),
        )
        ->assertSee('name="theme-color" content="#0F172A"', false)
        ->assertSee('name="keywords" content="metadata,test,seo"', false)
        ->assertSee('property="og:image" content="'.url('/storage/site-assets/custom-og.png').'"', false)
        ->assertSee('href="'.url('/storage/site-assets/custom-favicon.png').'"', false)
        ->assertSee('href="'.url('/storage/site-assets/custom-favicon.svg').'"', false)
        ->assertSee('href="'.url('/storage/site-assets/custom-apple-touch.png').'"', false);
});
