<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\BorrowBookRequest;
use App\Http\Requests\Kiosk\ReturnBookRequest;
use App\Http\Requests\Kiosk\SubmitVisitRequest;
use App\Http\Requests\Kiosk\VerifyPinRequest;
use App\Models\VisitLog;
use App\Support\Kiosk\KioskPinManager;
use App\Support\Library\KioskLoanService;
use App\Support\Settings\SettingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KioskController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected KioskPinManager $kioskPinManager,
        protected KioskLoanService $kioskLoanService,
    ) {}

    public function show(Request $request): Response
    {
        $siteSettings = $this->settingRepository->sectionValues('general', [
            'site_name' => config('app.name'),
            'site_tagline' => 'Sistem pendataan pengunjung',
            'loan_max_books' => 3,
        ]);

        $kioskSettings = $this->settingRepository->sectionValues('kiosk', [
            'title' => 'Pendataan Pengunjung Perpustakaan',
            'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kiosk.',
            'waiting_message' => 'Perangkat ini sedang menunggu persetujuan super admin.',
            'waiting_refresh_seconds' => 20,
            'success_redirect_seconds' => 6,
        ]);

        if (! $this->kioskPinManager->isVerified($request)) {
            return Inertia::render('Kiosk/Index', [
                'step' => 'pin',
                'activeMenu' => 'landing',
                'pageTitle' => 'Aktifkan Kiosk',
                'pageSubtitle' => 'Masukkan PIN dari super admin untuk mengaktifkan perangkat ini.',
                'siteName' => $siteSettings['site_name'],
                'siteTagline' => $siteSettings['site_tagline'],
                'waitingMessage' => $kioskSettings['waiting_message'],
                'waitingRefreshSeconds' => max((int) $kioskSettings['waiting_refresh_seconds'], 5),
                'successRedirectSeconds' => max((int) $kioskSettings['success_redirect_seconds'], 3),
                'device' => null,
                'loanMaxBooks' => max((int) $siteSettings['loan_max_books'], 1),
                'visitorTypeOptions' => VisitLog::visitorTypeOptions(),
                'purposeOptions' => VisitLog::purposeOptions(),
            ]);
        }

        $activeMenu = $request->string('menu')->toString();

        if (! in_array($activeMenu, ['landing', 'visit', 'borrow', 'return'], true)) {
            $activeMenu = 'landing';
        }

        return Inertia::render('Kiosk/Index', [
            'step' => 'ready',
            'activeMenu' => $activeMenu,
            'pageTitle' => $kioskSettings['title'],
            'pageSubtitle' => $activeMenu === 'landing'
                ? 'Pilih layanan yang ingin digunakan pada kiosk ini.'
                : $kioskSettings['subtitle'],
            'siteName' => $siteSettings['site_name'],
            'siteTagline' => $siteSettings['site_tagline'],
            'waitingMessage' => $kioskSettings['waiting_message'],
            'waitingRefreshSeconds' => max((int) $kioskSettings['waiting_refresh_seconds'], 5),
            'successRedirectSeconds' => max((int) $kioskSettings['success_redirect_seconds'], 3),
            'device' => $request->attributes->get('kioskDevice'),
            'loanMaxBooks' => max((int) $siteSettings['loan_max_books'], 1),
            'visitorTypeOptions' => VisitLog::visitorTypeOptions(),
            'purposeOptions' => VisitLog::purposeOptions(),
        ]);
    }

    public function verifyPin(VerifyPinRequest $request): RedirectResponse
    {
        if (! $this->kioskPinManager->isConfigured()) {
            return back()->withErrors([
                'pin' => 'PIN kiosk belum diatur oleh super admin.',
            ])->onlyInput('pin');
        }

        if (! $this->kioskPinManager->verify($request->validated('pin'), $request)) {
            return back()->withErrors([
                'pin' => 'PIN kiosk tidak valid.',
            ])->onlyInput('pin');
        }

        return redirect()
            ->route('kiosk.index')
            ->with('success', 'PIN berhasil diverifikasi. Silakan tunggu persetujuan perangkat.');
    }

    public function store(SubmitVisitRequest $request): RedirectResponse
    {
        $kioskDevice = $request->attributes->get('kioskDevice');

        VisitLog::create([
            ...$request->validated(),
            'kiosk_device_id' => $kioskDevice?->id,
            'visited_at' => now(),
        ]);

        return redirect()
            ->route('kiosk.index', ['menu' => 'visit', 'submitted' => 1])
            ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function borrow(BorrowBookRequest $request): RedirectResponse
    {
        $kioskDevice = $request->attributes->get('kioskDevice');

        $loan = $this->kioskLoanService->borrow(
            $kioskDevice,
            (string) $request->validated('member_identifier'),
            $request->validatedIsbn(),
        );

        return redirect()
            ->route('kiosk.index', ['menu' => 'borrow', 'submitted' => 1])
            ->with('success', "Peminjaman untuk {$loan->user->name} berhasil disimpan.");
    }

    public function storeReturn(ReturnBookRequest $request): RedirectResponse
    {
        $returnedCount = $this->kioskLoanService->returnBooks(
            (string) $request->validated('member_identifier'),
            $request->validatedIsbn(),
        );

        return redirect()
            ->route('kiosk.index', ['menu' => 'return', 'submitted' => 1])
            ->with('success', "{$returnedCount} buku berhasil dikembalikan.");
    }
}
