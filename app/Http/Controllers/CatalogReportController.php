<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogReportRequest;
use App\Models\Book;
use App\Models\CatalogReport;
use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CatalogReportController extends Controller
{
    public function store(StoreCatalogReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $reportable = $this->resolveReportable(
            $validated['catalog_type'],
            (int) $validated['catalog_id'],
        );

        $user = $request->user();

        $report = new CatalogReport([
            'catalog_type' => $validated['catalog_type'],
            'catalog_title' => $this->catalogTitle($reportable),
            'catalog_url' => $this->catalogUrl($reportable),
            'reporter_name' => ($validated['reporter_name'] ?? null) ?: $user?->name,
            'reporter_email' => ($validated['reporter_email'] ?? null) ?: $user?->email,
            'message' => $validated['message'],
            'status' => CatalogReport::STATUS_PENDING,
        ]);

        $report->user()->associate($user);
        $report->reportable()->associate($reportable);
        $report->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Laporan berhasil dikirim. Tim pengelola perpustakaan akan meninjau data ini.',
        ]);

        return back(status: 303);
    }

    private function resolveReportable(string $catalogType, int $catalogId): Model
    {
        $reportable = match ($catalogType) {
            CatalogReport::CATALOG_TYPE_BOOK => Book::query()->findOrFail($catalogId),
            CatalogReport::CATALOG_TYPE_SKRIPSI => Skripsi::query()->findOrFail($catalogId),
            CatalogReport::CATALOG_TYPE_THESIS => Thesis::query()->findOrFail($catalogId),
            CatalogReport::CATALOG_TYPE_INTERNSHIP_REPORT => InternshipReport::query()->findOrFail($catalogId),
            default => abort(404),
        };

        if ($reportable instanceof Book && ! $reportable->is_published) {
            abort(404);
        }

        return $reportable;
    }

    private function catalogTitle(Model $reportable): string
    {
        /** @var object{title:string} $reportable */
        return $reportable->title;
    }

    private function catalogUrl(Model $reportable): string
    {
        return match (true) {
            $reportable instanceof Book => route('books.show', $reportable, absolute: false),
            $reportable instanceof Skripsi => route('skripsi.show', ['skripsi' => $reportable->student_id], absolute: false),
            $reportable instanceof Thesis => route('thesis.show', ['thesis' => $reportable->student_id], absolute: false),
            $reportable instanceof InternshipReport => route('internship-reports.show', ['internshipReport' => $reportable->student_id], absolute: false),
            default => '',
        };
    }
}
