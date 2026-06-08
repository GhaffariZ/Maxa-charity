<?php

declare(strict_types=1);

namespace Maksa\Services\Gateway;

use Maksa\Core\Config;

/**
 * Development / no-gateway implementation. Instead of a real bank it sends the
 * user to our own callback with a synthetic authority, and "verifies" as
 * successful. Lets the whole donation flow work end-to-end before a real
 * merchant account exists. NEVER leave PAYMENT_GATEWAY=stub in production.
 */
final class StubGateway implements PaymentGateway
{
    public function request(int $amountToman, string $reference, string $callbackUrl, string $description): array
    {
        $authority = 'STUB-' . bin2hex(random_bytes(8));
        $sep = str_contains($callbackUrl, '?') ? '&' : '?';
        return [
            'authority'    => $authority,
            'redirect_url' => $callbackUrl . $sep . http_build_query([
                'authority' => $authority,
                'status'    => 'OK',
            ]),
        ];
    }

    public function verify(string $authority, array $callbackParams, int $amountToman): GatewayResult
    {
        // Honor a simulated cancel/failure for testing the unhappy path.
        if (($callbackParams['status'] ?? 'OK') !== 'OK') {
            return GatewayResult::failed('پرداخت توسط کاربر لغو شد.');
        }
        return GatewayResult::ok('STUBREF-' . substr($authority, 5, 8));
    }

    public function name(): string
    {
        return 'stub';
    }
}
