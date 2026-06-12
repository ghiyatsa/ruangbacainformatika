<?php

namespace App\Support;

use App\Repositories\SettingRepository;
use Carbon\CarbonInterface;

class KioskIdlePolicy
{
    public const DEFAULT_OPERATING_OPEN_TIME = '08:00';

    public const DEFAULT_OPERATING_CLOSE_TIME = '17:00';

    public const DEFAULT_IDLE_TIMEOUT_OPEN_MINUTES = 15;

    public const DEFAULT_IDLE_TIMEOUT_CLOSED_MINUTES = 3;

    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return array{
     *     timezone: string,
     *     operatingOpenTime: string,
     *     operatingCloseTime: string,
     *     idleTimeoutOpenMinutes: int,
     *     idleTimeoutClosedMinutes: int,
     *     activeIdleTimeoutMinutes: int,
     *     withinOperatingHours: bool
     * }
     */
    public function configuration(?CarbonInterface $at = null): array
    {
        $referenceTime = $this->resolveReferenceTime($at);
        $operatingOpenTime = $this->normalizeTime(
            $this->settingRepository->get('kiosk', 'operating_open_time', self::DEFAULT_OPERATING_OPEN_TIME),
            self::DEFAULT_OPERATING_OPEN_TIME,
        );
        $operatingCloseTime = $this->normalizeTime(
            $this->settingRepository->get('kiosk', 'operating_close_time', self::DEFAULT_OPERATING_CLOSE_TIME),
            self::DEFAULT_OPERATING_CLOSE_TIME,
        );
        $idleTimeoutOpenMinutes = max(
            (int) $this->settingRepository->get('kiosk', 'idle_timeout_open_minutes', self::DEFAULT_IDLE_TIMEOUT_OPEN_MINUTES),
            1,
        );
        $idleTimeoutClosedMinutes = max(
            (int) $this->settingRepository->get('kiosk', 'idle_timeout_closed_minutes', self::DEFAULT_IDLE_TIMEOUT_CLOSED_MINUTES),
            1,
        );
        $withinOperatingHours = $this->isWithinOperatingHoursAt(
            $referenceTime,
            $operatingOpenTime,
            $operatingCloseTime,
        );

        return [
            'timezone' => AppTimezone::displayTimezone(),
            'operatingOpenTime' => $operatingOpenTime,
            'operatingCloseTime' => $operatingCloseTime,
            'idleTimeoutOpenMinutes' => $idleTimeoutOpenMinutes,
            'idleTimeoutClosedMinutes' => $idleTimeoutClosedMinutes,
            'activeIdleTimeoutMinutes' => $withinOperatingHours
                ? $idleTimeoutOpenMinutes
                : $idleTimeoutClosedMinutes,
            'withinOperatingHours' => $withinOperatingHours,
        ];
    }

    public function idleTimeoutMinutes(?CarbonInterface $at = null): int
    {
        return $this->configuration($at)['activeIdleTimeoutMinutes'];
    }

    public function isSessionStillActive(?CarbonInterface $lastActiveAt, ?CarbonInterface $at = null): bool
    {
        if ($lastActiveAt === null) {
            return false;
        }

        $referenceTime = $this->resolveReferenceTime($at);
        $lastActivityTime = $lastActiveAt->copy()->setTimezone(AppTimezone::displayTimezone());
        $idleSeconds = $this->idleTimeoutMinutes($referenceTime) * 60;

        return $lastActivityTime->diffInSeconds($referenceTime) < $idleSeconds;
    }

    protected function resolveReferenceTime(?CarbonInterface $at = null): CarbonInterface
    {
        return ($at?->copy() ?? AppTimezone::now())->setTimezone(AppTimezone::displayTimezone());
    }

    protected function normalizeTime(mixed $value, string $default): string
    {
        $time = is_string($value) ? trim($value) : '';

        if (preg_match('/^\d{2}:\d{2}$/', $time) !== 1) {
            return $default;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time, 2));

        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            return $default;
        }

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function isWithinOperatingHoursAt(
        CarbonInterface $referenceTime,
        string $operatingOpenTime,
        string $operatingCloseTime,
    ): bool {
        $openMinutes = $this->minutesFromTime($operatingOpenTime);
        $closeMinutes = $this->minutesFromTime($operatingCloseTime);
        $currentMinutes = ((int) $referenceTime->format('H') * 60) + (int) $referenceTime->format('i');

        if ($openMinutes === $closeMinutes) {
            return true;
        }

        if ($openMinutes < $closeMinutes) {
            return $currentMinutes >= $openMinutes && $currentMinutes < $closeMinutes;
        }

        return $currentMinutes >= $openMinutes || $currentMinutes < $closeMinutes;
    }

    protected function minutesFromTime(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time, 2));

        return ($hours * 60) + $minutes;
    }
}
