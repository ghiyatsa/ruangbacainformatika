<?php

namespace App\Http\Controllers;

use App\Models\DocumentSubmission;
use App\Services\Borrowing\LoanQrCodeService;
use App\Services\DocumentDistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentDistributionController extends Controller
{
    public function __construct(
        protected DocumentDistributionService $distributionService,
        protected LoanQrCodeService $qrCodeService,
    ) {}

    /**
     * Preview / print lembar tanda terima penyerahan dokumen (KP, Skripsi, atau Sumbangan Buku).
     */
    public function receipt(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $token = $request->query('token');

        $query = DocumentSubmission::query()
            ->with(['user', 'submittable'])
            ->where('status', DocumentSubmission::STATUS_APPROVED);

        if ($token) {
            $query->where('receipt_token', $token);
        } else {
            $query->where('user_id', $user->id);
        }

        $submission = $query->latest('reviewed_at')->first();

        if (! $submission) {
            return redirect('/dashboard')
                ->with('error', 'Lembar tanda terima hanya tersedia untuk pengajuan yang telah disetujui.');
        }

        if ($submission->user_id !== $user->id && ! $user->hasAdministrativeRole()) {
            abort(403, 'Anda tidak berhak mengakses lembar tanda terima ini.');
        }

        $catalogUrl = null;
        if ($submission->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT && $submission->submittable) {
            $catalogUrl = route('internship-reports.show', $submission->submittable->student_id);
        } elseif ($submission->type === DocumentSubmission::TYPE_SKRIPSI && $submission->submittable) {
            $catalogUrl = route('skripsi.show', $submission->submittable->student_id);
        } elseif ($submission->type === DocumentSubmission::TYPE_BOOK_DONATION && $submission->submittable) {
            $catalogUrl = route('books.show', $submission->submittable->slug);
        }

        $verificationUrl = route('verify.receipt', $submission->receipt_token);
        $qrSvg = $this->qrCodeService->generateSvg($verificationUrl);

        return Inertia::render('verify/receipt-print', [
            'receipt' => [
                'type' => $submission->type,
                'type_label' => $submission->typeLabel(),
                'receipt_number' => $submission->receipt_number,
                'student_name' => $submission->user->name,
                'student_id' => $submission->user->identityNumber() ?? '-',
                'title' => $submission->title,
                'company_name' => $submission->company_name,
                'academic_advisor' => $submission->academic_advisor,
                'author_names' => $submission->author_names,
                'publisher_name' => $submission->publisher_name,
                'isbn' => $submission->isbn,
                'approved_at' => $submission->reviewed_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
                'verification_url' => $verificationUrl,
                'qr_svg' => $qrSvg,
                'catalog_url' => $catalogUrl,
            ],
        ]);
    }

    /**
     * Halaman publik verifikasi keaslian lembar tanda terima via QR code.
     */
    public function verifyReceipt(string $token): Response
    {
        $submission = DocumentSubmission::query()
            ->with(['user', 'submittable'])
            ->where('receipt_token', $token)
            ->first();

        $catalogUrl = null;
        if ($submission && $submission->isApproved()) {
            if ($submission->type === DocumentSubmission::TYPE_INTERNSHIP_REPORT && $submission->submittable) {
                $catalogUrl = route('internship-reports.show', $submission->submittable->student_id);
            } elseif ($submission->type === DocumentSubmission::TYPE_SKRIPSI && $submission->submittable) {
                $catalogUrl = route('skripsi.show', $submission->submittable->student_id);
            } elseif ($submission->type === DocumentSubmission::TYPE_BOOK_DONATION && $submission->submittable) {
                $catalogUrl = route('books.show', $submission->submittable->slug);
            }
        }

        return Inertia::render('verify/receipt', [
            'isValid' => $submission !== null && $submission->isApproved(),
            'submission' => $submission && $submission->isApproved() ? [
                'type' => $submission->type,
                'type_label' => $submission->typeLabel(),
                'receipt_number' => $submission->receipt_number,
                'student_name' => $submission->user->name,
                'student_id' => $submission->user->identityNumber() ?? '-',
                'title' => $submission->title,
                'company_name' => $submission->company_name,
                'academic_advisor' => $submission->academic_advisor,
                'author_names' => $submission->author_names,
                'publisher_name' => $submission->publisher_name,
                'isbn' => $submission->isbn,
                'approved_at' => $submission->reviewed_at?->translatedFormat('d F Y'),
                'catalog_url' => $catalogUrl,
            ] : null,
        ]);
    }
}
