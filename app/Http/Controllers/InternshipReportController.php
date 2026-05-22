<?php

namespace App\Http\Controllers;

use App\Http\Resources\InternshipReportResource;
use App\Models\InternshipReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InternshipReportController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $year = $request->integer('year');

        $reports = InternshipReport::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            }))
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

    public function show(InternshipReport $internshipReport): Response
    {
        $internshipReport->increment('view_count');

        return Inertia::render('internship-report/show', [
            'report' => new InternshipReportResource($internshipReport->fresh()),
        ]);
    }
}
