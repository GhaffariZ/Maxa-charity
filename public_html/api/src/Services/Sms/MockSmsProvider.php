<?php

declare(strict_types=1);

namespace Maksa\Services\Sms;

use Maksa\Support\Logger;

final class MockSmsProvider implements SmsProviderInterface
{
    /** @var array<string,string> in-memory store of recently sent codes for test verification */
    private static array $sentCodes = [];

    public function sendOtp(string $phone, string $code): bool
    {
        self::$sentCodes[$phone] = $code;
        Logger::info("Mock SMS OTP sent to {$phone}: [{$code}]");
        error_log("[MOCK SMS] Verification code for {$phone} is: {$code}");
        return true;
    }

    public static function getLastCodeFor(string $phone): ?string
    {
        return self::$sentCodes[$phone] ?? null;
    }

    public function name(): string
    {
        return 'mock';
    }
}
