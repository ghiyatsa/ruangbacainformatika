<?php

namespace App\Http\Controllers;

use App\Http\Resources\InternshipReportResource;
use App\Models\InternshipReport;
use App\Services\RelatedCatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InternshipReportController extends Controller
{
    public function __construct(
        protected RelatedCatalogService $relatedCatalogService,
        protected PageMeta $pageMeta,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $year = $request->integer('year');

        $reports = InternshipReport::query()
            ->search($search)
            ->when($request->filled('year'), fn ($q) => $q->where('year', $year))
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $years = InternshipReport::query()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->values();

        return Inertia::render('internship-report/index', [
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
            'years' => $years,
            'total' => $reports->total(),
            'reports' => Inertia::defer(function () use ($reports) {
                $paginated = $reports->toArray();
                $paginated['data'] = InternshipReportResource::collection($reports->getCollection())->resolve();

                return $paginated;
            })->merge()->append('data'),
        ]);
    }

    public function show(Request $request, InternshipReport $internshipReport): Response
    {
        if (! $request->prefetch() && ! $request->hasHeader('X-Inertia-Partial-Component')) {
            $internshipReport->increment('view_count');
        }

        return Inertia::render('internship-report/show', [
            'report' => new InternshipReportResource($internshipReport->fresh()),
            'relatedReports' => Inertia::defer(
                fn () => InternshipReportResource::collection($this->relatedCatalogService->forInternshipReport($internshipReport))->resolve(),
                rescue: true,
            ),
        ])->withViewData([
            'meta' => $this->pageMeta->forAcademicDocument(
                title: $internshipReport->title,
                authorName: $internshipReport->author_name,
                studentId: $internshipReport->student_id,
                abstract: $internshipReport->abstract,
                keywords: $internshipReport->keywords
                    ? array_map('trim', explode(',', $internshipReport->keywords))
                    : [],
                catalogLabel: 'laporan kerja praktik',
                canonicalUrl: route('internship-reports.show', $internshipReport),
                ogRouteName: 'og.internship-reports.show',
                document: $internshipReport,
            ),
        ]);
    }
}
