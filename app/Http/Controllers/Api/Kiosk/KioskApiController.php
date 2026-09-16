<?php

namespace App\Http\Controllers\Api\Kiosk;

use App\Actions\Kiosk\BorrowBooksFromKiosk;
use App\Actions\Kiosk\ReturnBooksFromKiosk;
use App\Actions\Kiosk\SearchKioskBooks;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kiosk\BorrowBookRequest;
use App\Http\Requests\Kiosk\FindMemberRequest;
use App\Http\Requests\Kiosk\RegisterMemberRequest;
use App\Http\Requests\Kiosk\ReturnBookRequest;
use App\Http\Requests\Kiosk\SearchBooksRequest;
use App\Http\Requests\Kiosk\SubmitVisitRequest;
use App\Http\Requests\Kiosk\VerifyPinRequest;
use App\Http\Resources\BookResource;
use App\Models\KioskDevice;
use App\Models\MemberRegistrationClaim;
use App\Models\User;
use App\Models\VisitLog;
use App\Repositories\SettingRepository;
use App\Services\Kiosk\KioskDashboardStatsService;
use App\Services\Kiosk\KioskMemberLookupService;
use App\Services\KioskPinManager;
use App\Services\MemberRegistrationClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Endpoint kiosk berbasis JSON untuk klien non-browser (aplikasi Flutter).
 *
 * Semua logika bisnis tetap berada di Action/Service yang sama dengan kiosk web,
 * sehingga tidak ada duplikasi aturan. Controller ini hanya menjembatani
 * request/response HTTP ↔ Action.
 */
class KioskApiController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected KioskPinManager $kioskPinManager,
        protected KioskMemberLookupService $kioskMemberLookupService,
        protected MemberRegistrationClaimService $memberRegistrationClaimService,
        protected KioskDashboardStatsService $kioskDashboardStatsService,
    ) {}

    /**
     * Aktifkan perangkat: tukar PIN kiosk menjadi device token.
     */
    public function activateDevice(VerifyPinRequest $request): JsonResponse
    {
        if (! $this->kioskPinManager->isConfigured()) {
            return response()->json([
                'message' => 'PIN kiosk belum tersedia. Silakan hubungi petugas perpustakaan.',
            ], 503);
        }

        if (! $this->kioskPinManager->canStartSession()) {
            return response()->json([
                'message' => 'Sesi kiosk hanya dapat dimulai pada jam operasional perpustakaan.',
            ], 403);
        }

        $pin = (string) $request->validated('pin');
        $expectedHash = $this->kioskPinManager->currentPinHash();

        if ($expectedHash === null || ! Hash::check($pin, $expectedHash)) {
            return response()->json([
                'message' => 'PIN kiosk tidak valid.',
                'errors' => ['pin' => ['PIN kiosk tidak valid.']],
            ], 422);
        }

        $device = $this->registerDevice($request);

        return response()->json([
            'device_token' => $device->device_token,
            'expires_at' => now()->addHours(24)->toIso8601String(),
            'session' => $this->kioskPinManager->sessionConfiguration(),
        ]);
    }

    /**
     * Data awal yang dibutuhkan aplikasi kiosk saat dibuka.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $librarySettings = $this->settingRepository->sectionValues('library', [
            'loan_max_books' => 3,
        ]);

        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        return response()->json([
            'loan_max_books' => max((int) $librarySettings['loan_max_books'], 1),
            'visitor_type_options' => VisitLog::visitorTypeOptions(),
            'purpose_options' => VisitLog::purposeOptions(),
            'session' => $this->kioskPinManager->sessionConfiguration(),
            'stats' => $this->kioskDashboardStatsService->getStatsForRequest($request, $device),
        ]);
    }

    /**
     * Kunci perangkat: batalkan device token.
     */
    public function lock(Request $request): JsonResponse
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        if ($device instanceof KioskDevice) {
            $device->delete();
        }

        return response()->json(['locked' => true]);
    }

    /**
     * Catat kunjungan perpustakaan.
     */
    public function storeVisit(SubmitVisitRequest $request): JsonResponse
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        $visit = VisitLog::query()->create([
            ...$request->validated(),
            'kiosk_device_id' => $device?->getKey(),
            'visited_at' => now(),
        ]);

        return response()->json([
            'visit' => [
                'id' => $visit->getKey(),
                'name' => $visit->name,
                'visited_at' => $visit->visited_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Cari buku untuk mode pinjam atau kembali.
     */
    public function searchBooks(SearchBooksRequest $request, SearchKioskBooks $searchKioskBooks): JsonResponse
    {
        $search = $request->validatedQuery();
        $mode = $request->validatedMode();
        $memberIdentifier = $request->validatedMemberIdentifier();

        if ($mode === 'borrow' && $search === '') {
            return response()->json(['books' => []]);
        }

        $books = $searchKioskBooks->execute($search, $mode, $memberIdentifier);

        return response()->json([
            'books' => BookResource::collection($books)->resolve(),
        ]);
    }

    /**
     * Pinjam buku via QR verifikasi anggota.
     */
    public function borrow(BorrowBookRequest $request, BorrowBooksFromKiosk $borrowBooksFromKiosk): JsonResponse
    {
        $loan = $borrowBooksFromKiosk->execute(
            $request->validatedVerificationPayload(),
            $request->validatedMemberIdentifier(),
            $request->validatedBookIds(),
        );

        $this->recordKioskVisit($request, $loan->user, 'Peminjaman mandiri di kiosk');

        return response()->json([
            'loan' => [
                'id' => $loan->getKey(),
                'member' => ['name' => $loan->user->name],
                'books_count' => $loan->items()->count(),
                'borrowed_at' => $loan->borrowed_at?->toIso8601String(),
                'due_at' => $loan->due_at?->toIso8601String(),
            ],
            'message' => "Peminjaman untuk {$loan->user->name} berhasil disimpan. Bukti akan dikirim ke WhatsApp anggota.",
        ], 201);
    }

    /**
     * Kembalikan buku via QR verifikasi anggota.
     */
    public function storeReturn(ReturnBookRequest $request, ReturnBooksFromKiosk $returnBooksFromKiosk): JsonResponse
    {
        $result = $returnBooksFromKiosk->execute(
            $request->validatedVerificationPayload(),
            $request->validatedMemberIdentifier(),
            $request->validatedBookIds(),
        );

        $this->recordKioskVisit($request, $result['member'], 'Pengembalian buku di kiosk');

        return response()->json([
            'returned_count' => $result['returned_count'],
            'member' => [
                'id' => $result['member']->getKey(),
                'name' => $result['member']->name,
            ],
            'message' => "{$result['returned_count']} buku berhasil dikembalikan.",
        ]);
    }

    /**
     * Registrasi anggota baru: buat claim + QR untuk ditautkan ke akun Google.
     */
    public function storeMember(RegisterMemberRequest $request): JsonResponse
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        $registration = $this->memberRegistrationClaimService->create($request->validated());

        /** @var MemberRegistrationClaim $claim */
        $claim = $registration['registration'];
        $claim->forceFill(['kiosk_device_id' => $device?->getKey()])->save();

        $presented = $this->memberRegistrationClaimService->present(
            $claim,
            $registration['link_url'],
            $registration['qr_svg'],
        );

        return response()->json([
            'claim' => $presented,
            'message' => 'QR siap digunakan. Scan dari ponsel untuk menautkan akun Google.',
        ], 201);
    }

    /**
     * Status claim registrasi aktif untuk perangkat ini.
     */
    public function memberRegistrationStatus(Request $request): JsonResponse
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        if (! $device instanceof KioskDevice) {
            return response()->json(['claim' => null]);
        }

        $claim = MemberRegistrationClaim::query()
            ->where('kiosk_device_id', $device->getKey())
            ->latest('id')
            ->first();

        if (! $claim instanceof MemberRegistrationClaim) {
            return response()->json(['claim' => null]);
        }

        if ($claim->isExpired() && $claim->status === MemberRegistrationClaim::STATUS_PENDING) {
            $claim->markAsExpired();
        }

        return response()->json([
            'claim' => [
                'id' => $claim->getKey(),
                'status' => $claim->status,
                'approval_pending' => $claim->status === MemberRegistrationClaim::STATUS_PENDING,
                'last_error_message' => $claim->last_error_message,
            ],
        ]);
    }

    /**
     * Batalkan claim registrasi aktif perangkat ini.
     */
    public function cancelMemberRegistration(Request $request): JsonResponse
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        if ($device instanceof KioskDevice) {
            $claim = MemberRegistrationClaim::query()
                ->where('kiosk_device_id', $device->getKey())
                ->where('status', MemberRegistrationClaim::STATUS_PENDING)
                ->latest('id')
                ->first();

            if ($claim instanceof MemberRegistrationClaim) {
                $claim->markAsExpired();
            }
        }

        return response()->json(['cancelled' => true]);
    }

    /**
     * Cari anggota berdasarkan NIM / email / nomor HP (tanpa membocorkan email penuh).
     */
    public function findMember(FindMemberRequest $request): JsonResponse
    {
        return response()->json([
            'member' => $this->kioskMemberLookupService->preview($request->validatedIdentifier()),
        ]);
    }

    /**
     * Daftarkan perangkat baru dan hasilkan device token.
     */
    protected function registerDevice(Request $request): KioskDevice
    {
        $deviceName = Str::of((string) $request->input('device_name', ''))
            ->squish()
            ->limit(120, '')
            ->toString();

        return KioskDevice::query()->create([
            'name' => $deviceName !== '' ? $deviceName : null,
            'session_id' => 'api:'.Str::uuid()->toString(),
            'device_token' => Str::random(64),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_active_at' => now(),
        ]);
    }

    /**
     * Catat kunjungan otomatis saat pinjam/kembali (selaras dengan kiosk web).
     */
    protected function recordKioskVisit(Request $request, User $user, string $notes): void
    {
        /** @var KioskDevice|null $device */
        $device = $request->attributes->get('kiosk_device');

        $identityNumber = $user->identityNumber() ?? $user->nim();
        $isMahasiswa = $user->isMahasiswa();

        $visitorType = match (true) {
            $isMahasiswa => VisitLog::VISITOR_TYPE_MAHASISWA,
            $user->hasRole('staff') || $user->hasRole('super_admin') => VisitLog::VISITOR_TYPE_STAFF,
            str_ends_with($user->email, '@unimal.ac.id') => VisitLog::VISITOR_TYPE_DOSEN,
            default => VisitLog::VISITOR_TYPE_UMUM,
        };

        VisitLog::query()->create([
            'kiosk_device_id' => $device?->getKey(),
            'name' => $user->name,
            'visitor_type' => $visitorType,
            'identity_number' => $identityNumber ?: null,
            'institution' => str_ends_with($user->email, 'unimal.ac.id') ? 'Universitas Malikussaleh' : null,
            'phone' => $user->whatsapp ?: null,
            'purpose' => 'borrow_return',
            'notes' => $notes,
            'visited_at' => now(),
        ]);
    }
}
