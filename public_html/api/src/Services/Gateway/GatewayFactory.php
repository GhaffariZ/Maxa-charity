<?php

declare(strict_types=1);

namespace Maksa\Services\Gateway;

use Maksa\Core\Config;

/** Resolves the configured gateway from PAYMENT_GATEWAY. */
final class GatewayFactory
{
    public static function make(): PaymentGateway
    {
        return match (strtolower((string) Config::get('PAYMENT_GATEWAY', 'stub'))) {
            'zarinpal' => new ZarinpalGateway(),
            default    => new StubGateway(),
        };
    }
}
