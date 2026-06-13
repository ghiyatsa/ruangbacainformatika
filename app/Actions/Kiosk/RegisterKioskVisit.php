<?php

namespace App\Actions\Kiosk;

use App\Models\VisitLog;
use App\Services\KioskPinManager;
use Illuminate\Http\Request;

class RegisterKioskVisit
{
    public function __construct(
        protected KioskPinManager $kioskPinManager,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function execute(Request $request, array $validated): VisitLog
    {
        $kioskDevice = $this->kioskPinManager->currentDevice($request);

        return VisitLog::query()->create([
            ...$validated,
            'kiosk_device_id' => $kioskDevice?->getKey(),
            'visited_at' => now(),
        ]);
    }
}
