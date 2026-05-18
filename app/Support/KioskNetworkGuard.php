<?php

namespace App\Support;

use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class KioskNetworkGuard
{
    public function __construct(
        protected SettingRepository $settingRepository,
    ) {}

    /**
     * @return list<string>
     */
    public function allowedNetworks(): array
    {
        return $this->normalizeNetworks(
            $this->settingRepository->get('kiosk', 'allowed_networks', ''),
        );
    }

    public function allows(Request $request): bool
    {
        $allowedNetworks = $this->allowedNetworks();

        if ($allowedNetworks === []) {
            return true;
        }

        $ipAddress = $request->ip();

        if (! is_string($ipAddress) || $ipAddress === '') {
            return false;
        }

        return IpUtils::checkIp($ipAddress, $allowedNetworks);
    }

    public function networkScopeForRequest(Request $request): ?string
    {
        return $this->networkScopeForIp($request->ip());
    }

    public function networkScopeForIp(?string $ipAddress): ?string
    {
        if (! is_string($ipAddress) || $ipAddress === '') {
            return null;
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $octets = explode('.', $ipAddress);

            return "{$octets[0]}.{$octets[1]}.{$octets[2]}.0/24";
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $binaryIp = inet_pton($ipAddress);

            if ($binaryIp === false) {
                return null;
            }

            $hextets = unpack('n8', $binaryIp);

            if (! is_array($hextets)) {
                return null;
            }

            return sprintf(
                '%x:%x:%x:%x::/64',
                $hextets[1],
                $hextets[2],
                $hextets[3],
                $hextets[4],
            );
        }

        return null;
    }

    public function isValidNetwork(string $network): bool
    {
        if (! str_contains($network, '/')) {
            return filter_var($network, FILTER_VALIDATE_IP) !== false;
        }

        [$ipAddress, $prefixLength] = explode('/', $network, 2);

        if (
            filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
            && preg_match('/^\d+$/', $prefixLength) === 1
        ) {
            return (int) $prefixLength >= 0 && (int) $prefixLength <= 32;
        }

        if (
            filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
            && preg_match('/^\d+$/', $prefixLength) === 1
        ) {
            return (int) $prefixLength >= 0 && (int) $prefixLength <= 128;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function normalizeNetworks(string|array|null $value): array
    {
        $rawNetworks = is_array($value)
            ? $value
            : (preg_split('/[\r\n,]+/', (string) $value) ?: []);

        return collect($rawNetworks)
            ->map(fn (mixed $network): string => trim((string) $network))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
