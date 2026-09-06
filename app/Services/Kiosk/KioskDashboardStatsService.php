<?php

namespace App\Services\Kiosk;

use App\Models\KioskDevice;
use App\Models\LoanItem;
use App\Models\VisitLog;
use Illuminate\Http\Request;

class KioskDashboardStatsService
{
    /**
     * @return array{deviceId: string, ipAddress: string, todayVisits: int, todayBorrowed: int, todayReturned: int}
     */
    public function getStatsForRequest(Request $request, ?KioskDevice $currentDevice): array
    {
        [$todayStart, $todayEnd] = VisitLog::adminDayRange();

        $todayVisits = VisitLog::query()
            ->whereBetween('visited_at', [$todayStart, $todayEnd])
            ->count();

        $todayBorrowed = LoanItem::query()
            ->join('loans', 'loans.id', '=', 'loan_items.loan_id')
            ->whereBetween('loans.borrowed_at', [$todayStart, $todayEnd])
            ->count();

        $todayReturned = LoanItem::query()
            ->whereBetween('returned_at', [$todayStart, $todayEnd])
            ->count();

        $deviceId = $currentDevice?->name ?: ($currentDevice ? ('KIOSK-'.str_pad((string) $currentDevice->id, 2, '0', STR_PAD_LEFT)) : 'KIOSK-01');
        $ipAddress = $request->ip() ?: '127.0.0.1';

        return [
            'deviceId' => $deviceId,
            'ipAddress' => $ipAddress,
            'todayVisits' => $todayVisits,
            'todayBorrowed' => $todayBorrowed,
            'todayReturned' => $todayReturned,
        ];
    }
}
