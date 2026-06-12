<?php

namespace App\Support;

use App\Repositories\SettingRepository;
use Carbon\CarbonInterface;

class KioskIdlePolicy
{
    public const DEFAULT_OPERATING_OPEN_TIME = '08:00';

    public const DEFAULT_OPERATING_CLOSE_TIME = '17:00';

    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return array{
     *     timezone: string,
     *     operatingOpenTime: string,
     *     operatingCloseTime: string,
     *     withinOperatingHours: bool,
     *     persistentForDevelopment: bool,
     *     sessionExpiresAtIso: string|null
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
        $withinOperatingHours = $this->isWithinOperatingHoursAt(
            $referenceTime,
            $operatingOpenTime,
            $operatingCloseTime,
        );
        $operatingWindow = $this->operatingWindowFor($referenceTime, $operatingOpenTime, $operatingCloseTime);

        return [
            'timezone' => AppTimezone::displayTimezone(),
            'operatingOpenTime' => $operatingOpenTime,
            'operatingCloseTime' => $operatingCloseTime,
            'withinOperatingHours' => $withinOperatingHours,
            'persistentForDevelopment' => $this->isPersistentForDevelopment(),
            'sessionExpiresAtIso' => $withinOperatingHours
                ? $operatingWindow['close']->toIso8601String()
                : null,
        ];
    }

    public function canStartSession(?CarbonInterface $at = null): bool
    {
        if ($this->isPersistentForDevelopment()) {
            return true;
        }

        $referenceTime = $this->resolveReferenceTime($at);

        return $this->isWithinOperatingHoursAt(
            $referenceTime,
            $this->normalizeTime(
                $this->settingRepository->get('kiosk', 'operating_open_time', self::DEFAULT_OPERATING_OPEN_TIME),
                self::DEFAULT_OPERATING_OPEN_TIME,
            ),
            $this->normalizeTime(
                $this->settingRepository->get('kiosk', 'operating_close_time', self::DEFAULT_OPERATING_CLOSE_TIME),
                self::DEFAULT_OPERATING_CLOSE_TIME,
            ),
        );
    }

    public function isSessionStillActive(?CarbonInterface $lastActiveAt, ?CarbonInterface $at = null): bool
    {
        if ($lastActiveAt === null) {
            return false;
        }

        if ($this->isPersistentForDevelopment()) {
            return true;
        }

        $referenceTime = $this->resolveReferenceTime($at);
        $lastActivityTime = $lastActiveAt->copy()->setTimezone(AppTimezone::displayTimezone());
        $operatingOpenTime = $this->normalizeTime(
            $this->settingRepository->get('kiosk', 'operating_open_time', self::DEFAULT_OPERATING_OPEN_TIME),
            self::DEFAULT_OPERATING_OPEN_TIME,
        );
        $operatingCloseTime = $this->normalizeTime(
            $this->settingRepository->get('kiosk', 'operating_close_time', self::DEFAULT_OPERATING_CLOSE_TIME),
            self::DEFAULT_OPERATING_CLOSE_TIME,
        );

        if (! $this->isWithinOperatingHoursAt($referenceTime, $operatingOpenTime, $operatingCloseTime)) {
            return false;
        }

        $operatingWindow = $this->operatingWindowFor($referenceTime, $operatingOpenTime, $operatingCloseTime);

        return $lastActivityTime->greaterThanOrEqualTo($operatingWindow['open'])
            && $lastActivityTime->lessThan($operatingWindow['close'])
            && $referenceTime->lessThan($operatingWindow['close']);
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

    /**
     * @return array{open: CarbonInterface, close: CarbonInterface}
     */
    protected function operatingWindowFor(
        CarbonInterface $referenceTime,
        string $operatingOpenTime,
        string $operatingCloseTime,
    ): array {
        $openMinutes = $this->minutesFromTime($operatingOpenTime);
        $closeMinutes = $this->minutesFromTime($operatingCloseTime);
        $reference = $referenceTime->copy()->setTimezone(AppTimezone::displayTimezone());
        $open = $this->timeOnReferenceDate($reference, $operatingOpenTime);
        $close = $this->timeOnReferenceDate($reference, $operatingCloseTime);

        if ($openMinutes === $closeMinutes) {
            return [
                'open' => $reference->copy()->startOfDay(),
                'close' => $reference->copy()->endOfDay(),
            ];
        }

        if ($openMinutes < $closeMinutes) {
            return [
                'open' => $open,
                'close' => $close,
            ];
        }

        if ($reference->lessThan($close)) {
            $open = $open->subDay();
        } else {
            $close = $close->addDay();
        }

        return [
            'open' => $open,
            'close' => $close,
        ];
    }

    protected function timeOnReferenceDate(CarbonInterface $referenceTime, string $time): CarbonInterface
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time, 2));

        return $referenceTime->copy()
            ->setTimezone(AppTimezone::displayTimezone())
            ->setTime($hours, $minutes);
    }

    protected function minutesFromTime(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time, 2));

        return ($hours * 60) + $minutes;
    }

    protected function isPersistentForDevelopment(): bool
    {
        return app()->isLocal();
    }
}
