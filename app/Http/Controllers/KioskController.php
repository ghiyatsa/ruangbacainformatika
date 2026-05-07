<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kiosk\BorrowBookRequest;
use App\Http\Requests\Kiosk\ReturnBookRequest;
use App\Http\Requests\Kiosk\SubmitVisitRequest;
use App\Http\Requests\Kiosk\VerifyPinRequest;
use App\Models\VisitLog;
use App\Repositories\SettingRepository;
use App\Services\KioskLoanService;
use App\Services\KioskPinManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Contracts\CreatesNewUsers;

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
        ]);

        $librarySettings = $this->settingRepository->sectionValues('library', [
            'loan_max_books' => 3,
        ]);

        $kioskSettings = $this->settingRepository->sectionValues('kiosk', [
            'title' => 'Pendataan Pengunjung Perpustakaan',
            'subtitle' => 'Silakan masukkan PIN untuk mengaktifkan perangkat kiosk.',
        ]);

        if (! $this->kioskPinManager->isVerified($request)) {
            return Inertia::render('kiosk/index', [
                'step' => 'pin',
                'activeMenu' => 'landing',
                'pageTitle' => 'Aktifkan Kiosk',
                'pageSubtitle' => 'Masukkan PIN dari super admin untuk mengaktifkan perangkat ini.',
                'siteName' => $siteSettings['site_name'],
                'siteTagline' => $siteSettings['site_tagline'],
                'loanMaxBooks' => max((int) $librarySettings['loan_max_books'], 1),
                'visitorTypeOptions' => VisitLog::visitorTypeOptions(),
                'purposeOptions' => VisitLog::purposeOptions(),
            ]);
        }

        $activeMenu = $request->string('menu')->toString();

        if (! in_array($activeMenu, ['landing', 'visit', 'member', 'borrow', 'return'], true)) {
            $activeMenu = 'landing';
        }

        return Inertia::render('kiosk/index', [
            'step' => 'ready',
            'activeMenu' => $activeMenu,
            'pageTitle' => $kioskSettings['title'],
            'pageSubtitle' => $activeMenu === 'landing'
                ? 'Pilih layanan yang ingin digunakan pada kiosk ini.'
                : $kioskSettings['subtitle'],
            'siteName' => $siteSettings['site_name'],
            'siteTagline' => $siteSettings['site_tagline'],
            'loanMaxBooks' => max((int) $librarySettings['loan_max_books'], 1),
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
            ->with('success', 'PIN berhasil diverifikasi.');
    }

    public function storeMember(Request $request, CreatesNewUsers $creator): RedirectResponse
    {
        $creator->create($request->all());

        return redirect()
            ->route('kiosk.index')
            ->with('success', 'Pendaftaran member berhasil. Silakan gunakan akun Anda untuk layanan mandiri.');
    }

    public function store(SubmitVisitRequest $request): RedirectResponse
    {
        VisitLog::create([
            ...$request->validated(),
            'visited_at' => now(),
        ]);

        return redirect()
            ->route('kiosk.index')
            ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function borrow(BorrowBookRequest $request): RedirectResponse
    {
        $loan = $this->kioskLoanService->borrow(
            (string) $request->validated('member_identifier'),
            $request->validatedIsbns(),
        );

        return redirect()
            ->route('kiosk.index')
            ->with('success', "Peminjaman untuk {$loan->user->name} berhasil disimpan.");
    }

    public function storeReturn(ReturnBookRequest $request): RedirectResponse
    {
        $returnedCount = $this->kioskLoanService->returnBooks(
            (string) $request->validated('member_identifier'),
            $request->validatedIsbns(),
        );

        return redirect()
            ->route('kiosk.index')
            ->with('success', "{$returnedCount} buku berhasil dikembalikan.");
    }
}
