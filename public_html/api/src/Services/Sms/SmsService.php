<?php

declare(strict_types=1);

namespace Maksa\Services\Sms;

use Maksa\Core\Config;

final class SmsService
{
    private SmsProviderInterface $provider;

    public function __construct(?SmsProviderInterface $provider = null)
    {
        $this->provider = $provider ?? self::resolveProvider();
    }

    public static function resolveProvider(): SmsProviderInterface
    {
        return match (strtolower((string) Config::get('SMS_DRIVER', 'mock'))) {
            'kavenegar' => new KavenegarSmsProvider(),
            'faraz', 'ippanel' => new FarazSmsProvider(),
            default     => new MockSmsProvider(),
        };
    }

    public function sendOtp(string $phone, string $code): bool
    {
        return $this->provider->sendOtp($phone, $code);
    }

    public function providerName(): string
    {
        return $this->provider->name();
    }
}
