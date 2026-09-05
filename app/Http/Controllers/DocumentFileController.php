<?php

namespace App\Http\Controllers;

use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    /**
     * Serve berkas dokumen submission (PDF / gambar) dengan verifikasi kepemilikan.
     *
     * Hanya pemilik submission atau staff/admin yang dapat mengakses berkas.
     * Berkas disimpan di disk 'documents' (private) — tidak pernah diekspos langsung via URL publik.
     */
    public function show(Request $request, DocumentSubmission $submission, string $field): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Hanya pemilik atau staff/admin
        abort_unless(
            $user->id === $submission->user_id || $user->hasAdministrativeRole(),
            403,
            'Anda tidak memiliki akses untuk mengunduh berkas ini.'
        );

        $path = match ($field) {
            'document' => $submission->document_file_path,
            'endorsement' => $submission->endorsement_file_path,
            default => null,
        };

        abort_if($path === null || $path === '', 404, 'Berkas tidak ditemukan.');

        abort_unless(Storage::disk('documents')->exists($path), 404, 'Berkas tidak ditemukan di server.');

        $mimeType = Storage::disk('documents')->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return Storage::disk('documents')->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
