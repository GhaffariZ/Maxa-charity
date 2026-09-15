<?php

declare(strict_types=1);

namespace Maksa\Services\Sms;

interface SmsProviderInterface
{
    /**
     * Sends an OTP verification code to the given Iranian mobile number.
     *
     * @param string $phone 11-digit mobile number (09xxxxxxxxx)
     * @param string $code  5-digit OTP verification code
     * @return bool True if successfully dispatched/accepted by provider
     */
    public function sendOtp(string $phone, string $code): bool;

    /**
     * Name identifier of the provider.
     */
    public function name(): string;
}
