<?php

use App\Models\InternshipReport;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('internship report catalog page renders results', function () {
    InternshipReport::factory()->create([
        'title' => 'Implementasi Sistem Presensi',
        'author_name' => 'Rina Sari',
        'year' => 2024,
    ]);

    get(route('internship-reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('internship-report/index')
            ->where('total', 1)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('reports.data', 1)
                ->where('reports.data.0.title', 'Implementasi Sistem Presensi')
            ));
});

test('internship report catalog page filters by search keyword', function () {
    InternshipReport::factory()->create(['title' => 'Laporan Aplikasi Arsip']);
    InternshipReport::factory()->create(['title' => 'Laporan Monitoring Server']);

    get(route('internship-reports.index', ['search' => 'Arsip']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('internship-report/index')
            ->where('filters.search', 'Arsip')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('reports.data', 1)
                ->where('reports.data.0.title', 'Laporan Aplikasi Arsip')
            ));
});

test('internship report detail page renders correctly', function () {
    $report = InternshipReport::factory()->create([
        'title' => 'Pengembangan Dashboard Monitoring',
        'author_name' => 'Dewi Lestari',
        'student_id' => '2301700010',
        'year' => 2025,
        'abstract' => 'Abstrak laporan kerja praktik.',
        'keywords' => 'dashboard, monitoring, sistem',
    ]);

    get(route('internship-reports.show', ['internshipReport' => $report->student_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('internship-report/show')
            ->where('report.data.title', 'Pengembangan Dashboard Monitoring')
            ->where('report.data.authorName', 'Dewi Lestari')
            ->where('report.data.studentId', '2301700010')
            ->where('report.data.year', 2025)
            ->where('report.data.keywords', ['dashboard', 'monitoring', 'sistem'])
        );
});

test('internship report detail page returns 404 for unknown nim', function () {
    get(route('internship-reports.show', ['internshipReport' => '0000000000']))
        ->assertNotFound();
});

test('internship report catalog page returns the requested pagination page', function () {
    foreach (range(1, 21) as $number) {
        InternshipReport::factory()->create([
            'title' => sprintf('Laporan %02d', $number),
            'year' => 2024,
        ]);
    }

    get(route('internship-reports.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('internship-report/index')
            ->where('total', 21)
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('reports.current_page', 2)
                ->where('reports.next_page_url', null)
                ->has('reports.data', 1)
                ->where('reports.data.0.title', 'Laporan 21')
            ));
});
