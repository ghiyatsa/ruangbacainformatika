<?php

namespace App\Http\Controllers;

use App\Models\InternshipReport;
use App\Models\Skripsi;
use App\Models\Thesis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicFileController extends Controller
{
    /**
     * Serve berkas PDF karya ilmiah (Skripsi, KP, Tesis) dengan verifikasi akses anggota.
     *
     * Berkas disimpan di disk 'documents' (private) — tidak pernah diekspos via URL publik statis.
     * Hanya user dengan role 'member' atau staff/admin yang boleh mengunduh.
     */
    public function skripsi(Request $request, Skripsi $skripsi): StreamedResponse
    {
        return $this->streamDocument($skripsi, $skripsi->title);
    }

    public function internshipReport(Request $request, InternshipReport $internshipReport): StreamedResponse
    {
        return $this->streamDocument($internshipReport, $internshipReport->title);
    }

    public function thesis(Request $request, Thesis $thesis): StreamedResponse
    {
        return $this->streamDocument($thesis, $thesis->title);
    }

    private function streamDocument(Model $document, string $title): StreamedResponse
    {
        $path = $document->file_path; // @phpstan-ignore-line

        abort_if(! filled($path), 404, 'Berkas tidak tersedia untuk dokumen ini.');

        abort_unless(Storage::disk('documents')->exists($path), 404, 'Berkas tidak ditemukan di server.');

        $mimeType = Storage::disk('documents')->mimeType($path) ?: 'application/pdf';
        $filename = basename($path);

        return Storage::disk('documents')->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
