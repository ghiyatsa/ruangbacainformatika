<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkripsiResource;
use App\Models\Skripsi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkripsiController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $year = $request->integer('year');

        $skripsis = Skripsi::query()
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

        $years = Skripsi::query()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->values();

        return Inertia::render('skripsi/index', [
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
            'years' => $years,
            'total' => $skripsis->total(),
            'skripsis' => Inertia::defer(function () use ($skripsis) {
                $paginated = $skripsis->toArray();
                $paginated['data'] = SkripsiResource::collection($skripsis->getCollection())->resolve();

                return $paginated;
            })->merge()->append('data'),
        ]);
    }

    public function show(Skripsi $skripsi): Response
    {
        $skripsi->increment('view_count');

        return Inertia::render('skripsi/show', [
            'skripsi' => new SkripsiResource($skripsi->fresh()),
        ]);
    }
}
