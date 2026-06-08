<?php

declare(strict_types=1);

namespace Maksa\Support;

/** Cryptographic + string helpers. */
final class Str
{
    /** A URL-safe opaque token with `$bytes` of entropy (default 256-bit). */
    public static function randomToken(int $bytes = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    /** SHA-256 hex digest — used to store token *hashes* (never plaintext). */
    public static function sha256(string $value): string
    {
        return hash('sha256', $value);
    }

    public static function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /** Public donation reference like MX-3F8A2C. */
    public static function donationReference(): string
    {
        return 'MX-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
