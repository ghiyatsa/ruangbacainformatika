<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\BorrowBookRequest;
use App\Http\Requests\Kiosk\RegisterMemberRequest;
use App\Http\Requests\Kiosk\ReturnBookRequest;
use App\Http\Requests\Kiosk\SearchBooksRequest;
use App\Http\Requests\Kiosk\SubmitVisitRequest;
use App\Http\Requests\Kiosk\VerifyPinRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Loan;
use App\Models\VisitLog;
use App\Repositories\SettingRepository;
use App\Services\KioskBorrowVerificationService;
use App\Services\KioskLoanService;
use App\Services\KioskPinManager;
use App\Services\MemberRegistrationClaimService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected KioskPinManager $kioskPinManager,
        protected KioskLoanService $kioskLoanService,
        protected KioskBorrowVerificationService $kioskBorrowVerificationService,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
    ) {}

    public function show(Request $request): Response
    {
        $memberRegistrationClaim = $this->resolveMemberRegistrationClaim($request);
        $kioskSession = $this->kioskPinManager->sessionConfiguration();

        $librarySettings = $this->settingRepository->sectionValues('library', [
            'loan_max_books' => 3,
        ]);

        if (! $this->kioskPinManager->isVerified($request)) {
            return Inertia::render('kiosk/index', [
                'step' => 'pin',
                'activeMenu' => 'landing',
                'loanMaxBooks' => max((int) $librarySettings['loan_max_books'], 1),
                'visitorTypeOptions' => VisitLog::visitorTypeOptions(),
                'purposeOptions' => VisitLog::purposeOptions(),
                'kioskSession' => $kioskSession,
                'memberRegistrationClaim' => $memberRegistrationClaim,
            ]);
        }

        $activeMenu = $request->string('menu')->toString();

        if (! in_array($activeMenu, ['landing', 'visit', 'member', 'borrow', 'return'], true)) {
            $activeMenu = 'visit';
        }

        return Inertia::render('kiosk/index', [
            'step' => 'ready',
            'activeMenu' => $activeMenu,
            'loanMaxBooks' => max((int) $librarySettings['loan_max_books'], 1),
            'visitorTypeOptions' => VisitLog::visitorTypeOptions(),
            'purposeOptions' => VisitLog::purposeOptions(),
            'kioskSession' => $kioskSession,
            'memberRegistrationClaim' => $memberRegistrationClaim,
        ]);
    }

    public function verifyPin(VerifyPinRequest $request): RedirectResponse
    {
        if (! $this->kioskPinManager->isConfigured()) {
            return back()->withErrors([
                'pin' => 'PIN kiosk belum tersedia. Silakan hubungi petugas perpustakaan.',
            ])->onlyInput('pin');
        }

        if (! $this->kioskPinManager->canStartSession()) {
            return back()->withErrors([
                'pin' => 'Sesi kiosk hanya dapat dimulai pada jam operasional perpustakaan.',
            ])->onlyInput('pin');
        }

        if (! $this->kioskPinManager->verify($request->validated('pin'), $request)) {
            return back()->withErrors([
                'pin' => 'PIN kiosk tidak valid.',
            ])->onlyInput('pin');
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'PIN berhasil diverifikasi.',
        ]);

        return redirect()->route('kiosk.index');
    }

    public function storeMember(RegisterMemberRequest $request): RedirectResponse
    {
        $registration = $this->memberRegistrationClaimService->create($request->validated());

        $request->session()->put(
            'kiosk.member_registration_claim',
            $this->memberRegistrationClaimService->present(
                $registration['registration'],
                $registration['link_url'],
                $registration['qr_svg'],
            ),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'QR siap digunakan. Scan dari ponsel untuk menautkan akun Google.',
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'member']);
    }

    public function memberRegistrationStatus(Request $request): JsonResponse
    {
        return response()->json([
            'memberRegistrationClaim' => $this->resolveMemberRegistrationClaim($request),
        ]);
    }

    public function cancelMemberRegistration(Request $request): JsonResponse
    {
        $presentedClaim = $request->session()->get('kiosk.member_registration_claim');

        if (is_array($presentedClaim)) {
            $this->memberRegistrationClaimService->cancelPresentedClaim($presentedClaim);
        }

        $request->session()->forget('kiosk.member_registration_claim');

        return response()->json([
            'cancelled' => true,
        ]);
    }

    public function lock(Request $request): JsonResponse
    {
        $this->kioskPinManager->forget($request);

        return response()->json([
            'locked' => true,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     whatsapp: string,
     *     address: string,
     *     linkUrl: string,
     *     qrSvg: string,
     *     status: string,
     *     expiresAt: string,
     *     claimedAt: string|null,
     *     lastErrorMessage: string|null,
     *     lastErrorAt: string|null,
     *     approvalPending: bool
     * }|null
     */
    protected function resolveMemberRegistrationClaim(Request $request): ?array
    {
        $presentedClaim = $request->session()->get('kiosk.member_registration_claim');

        if (! is_array($presentedClaim)) {
            return null;
        }

        $claim = $this->memberRegistrationClaimService->syncPresentedClaim($presentedClaim);

        if ($claim === null) {
            $request->session()->forget('kiosk.member_registration_claim');

            return null;
        }

        $request->session()->put('kiosk.member_registration_claim', $claim);

        return $claim;
    }

    public function store(SubmitVisitRequest $request): RedirectResponse
    {
        $kioskDevice = $this->kioskPinManager->currentDevice($request);

        VisitLog::create([
            ...$request->validated(),
            'kiosk_device_id' => $kioskDevice?->getKey(),
            'visited_at' => now(),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Data kunjungan berhasil disimpan.',
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'visit']);
    }

    public function searchBooks(SearchBooksRequest $request): JsonResponse
    {
        $search = $request->validatedQuery();
        $mode = $request->validatedMode();
        $memberIdentifier = $request->validatedMemberIdentifier();

        if ($mode === 'borrow' && $search === '') {
            return response()->json([
                'books' => [],
            ]);
        }

        $books = $mode === 'return'
            ? $this->searchReturnableBooks($search, $memberIdentifier)
            : $this->searchBorrowableBooks($search);

        return response()->json([
            'books' => BookResource::collection($books)->resolve(),
        ]);
    }

    public function borrow(BorrowBookRequest $request): RedirectResponse
    {
        $member = $this->kioskBorrowVerificationService->resolveUser(
            $request->validatedVerificationPayload(),
        );
        $submittedMember = $this->kioskLoanService->findMemberByIdentifier(
            $request->validatedMemberIdentifier(),
        );

        if (! $submittedMember || $submittedMember->isNot($member)) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
            ]);
        }

        $this->kioskBorrowVerificationService->consume(
            $request->validatedVerificationPayload(),
        );

        $loan = $this->kioskLoanService->borrow(
            $member->email,
            $request->validatedBookIds(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Peminjaman untuk {$loan->user->name} berhasil disimpan. Bukti peminjaman akan dikirim ke WhatsApp anggota.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'borrow']);
    }

    public function storeReturn(ReturnBookRequest $request): RedirectResponse
    {
        $member = $this->kioskBorrowVerificationService->resolveUser(
            $request->validatedVerificationPayload(),
        );
        $submittedMember = $this->kioskLoanService->findMemberByIdentifier(
            $request->validatedMemberIdentifier(),
        );

        if (! $submittedMember || $submittedMember->isNot($member)) {
            throw ValidationException::withMessages([
                'member_identifier' => 'Identitas anggota tidak sesuai dengan member key yang discan.',
            ]);
        }

        $returnedCount = $this->kioskLoanService->returnBooksByBookIds(
            $member->email,
            $request->validatedBookIds(),
        );

        $this->kioskBorrowVerificationService->consume(
            $request->validatedVerificationPayload(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "{$returnedCount} buku berhasil dikembalikan.",
        ]);

        return redirect()->route('kiosk.index', ['menu' => 'return']);
    }

    public function findMember(Request $request): JsonResponse
    {
        $identifier = (string) $request->query('identifier', '');

        if (blank($identifier)) {
            return response()->json([
                'member' => null,
            ]);
        }

        $member = $this->kioskLoanService->findMemberByIdentifier($identifier);

        if (! $member) {
            return response()->json([
                'member' => null,
            ]);
        }

        return response()->json([
            'member' => [
                'name' => $member->name,
                'emailMasked' => $member->email,
                'whatsappMasked' => $this->maskPhoneNumber($member->whatsapp),
            ],
        ]);
    }

    protected function maskEmail(?string $email): ?string
    {
        if (! is_string($email) || $email === '' || ! str_contains($email, '@')) {
            return null;
        }

        [$localPart, $domain] = explode('@', Str::lower($email), 2);
        $localLength = Str::length($localPart);

        if ($localLength <= 2) {
            $maskedLocalPart = Str::substr($localPart, 0, 1).'*';
        } else {
            $maskedLocalPart = Str::substr($localPart, 0, 2).str_repeat('*', max($localLength - 2, 2));
        }

        return "{$maskedLocalPart}@{$domain}";
    }

    protected function maskPhoneNumber(?string $phoneNumber): ?string
    {
        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        $length = strlen($digits);

        if ($length < 4) {
            return null;
        }

        return substr($digits, 0, 4).str_repeat('*', max($length - 6, 1)).substr($digits, -2);
    }

    protected function searchBorrowableBooks(string $search): EloquentCollection
    {
        return Book::query()
            ->search($search)
            ->where('is_borrowable', true)
            ->whereHas('items', fn ($query) => $query->available())
            ->with(['authors:id,name'])
            ->withCount('items')
            ->withCount([
                'items as available_items_count' => fn ($query) => $query->available(),
            ])
            ->orderBy('title')
            ->limit(8)
            ->get();
    }

    protected function searchReturnableBooks(string $search, string $memberIdentifier): EloquentCollection
    {
        $member = filled($memberIdentifier)
            ? $this->kioskLoanService->findMemberByIdentifier($memberIdentifier)
            : null;

        if (! $member || ! $member->canBorrowBooks()) {
            return new EloquentCollection;
        }

        return Book::query()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->whereHas('items.loanItems', function ($query) use ($member) {
                $query
                    ->whereNull('returned_at', 'and', false)
                    ->whereHas('loan', fn ($loanQuery) => $loanQuery
                        ->whereBelongsTo($member)
                        ->where('status', Loan::STATUS_BORROWED));
            })
            ->with(['authors:id,name'])
            ->withCount('items')
            ->orderBy('title')
            ->limit(8)
            ->get();
    }
}
