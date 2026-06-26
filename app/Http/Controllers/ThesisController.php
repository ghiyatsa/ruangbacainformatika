<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThesisResource;
use App\Models\Thesis;
use App\Services\RelatedCatalogService;
use App\Support\PageMeta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThesisController extends Controller
{
    public function __construct(
        protected RelatedCatalogService $relatedCatalogService,
        protected PageMeta $pageMeta,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $year = $request->integer('year');

        $theses = Thesis::query()
            ->search($search)
            ->when($request->filled('year'), fn ($q) => $q->where('year', $year))
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $years = Thesis::query()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->values();

        return Inertia::render('thesis/index', [
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
            'years' => $years,
            'total' => $theses->total(),
            'theses' => Inertia::defer(function () use ($theses) {
                $paginated = $theses->toArray();
                $paginated['data'] = ThesisResource::collection($theses->getCollection())->resolve();

                return $paginated;
            })->merge()->append('data'),
        ]);
    }

    public function show(Thesis $thesis): Response
    {
        $thesis->increment('view_count');

        return Inertia::render('thesis/show', [
            'thesis' => new ThesisResource($thesis->fresh()),
            'relatedTheses' => Inertia::defer(
                fn () => ThesisResource::collection($this->relatedCatalogService->forThesis($thesis))->resolve(),
                rescue: true,
            ),
        ])->withViewData([
            'meta' => $this->pageMeta->forAcademicDocument(
                title: $thesis->title,
                authorName: $thesis->author_name,
                studentId: $thesis->student_id,
                abstract: $thesis->abstract,
                keywords: $thesis->keywords
                    ? array_map('trim', explode(',', $thesis->keywords))
                    : [],
                catalogLabel: 'tesis',
                canonicalUrl: route('thesis.show', $thesis),
                ogRouteName: 'og.thesis.show',
                document: $thesis,
            ),
        ]);
    }
}
