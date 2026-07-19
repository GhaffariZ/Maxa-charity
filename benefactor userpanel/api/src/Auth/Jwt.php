<?php

declare(strict_types=1);

namespace Maksa\Auth;

use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Support\Str;

/**
 * Minimal, dependency-free HS256 JWT (encode/decode). Kept tiny on purpose so
 * shared hosting needs no Composer. Tokens carry iat/exp/jti + the subject.
 */
final class Jwt
{
    public static function issueAccessToken(int $userId): string
    {
        $now = time();
        $ttl = Config::int('JWT_ACCESS_TTL', 900);

        return self::encode([
            'iss' => Config::get('JWT_ISSUER', 'maksa'),
            'sub' => $userId,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => Str::randomToken(16),
            'typ' => 'access',
        ]);
    }

    /** @return array<string,mixed> the validated payload */
    public static function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw ApiException::unauthorized('توکن نامعتبر است.', 'invalid_token');
        }
        [$h64, $p64, $s64] = $parts;

        $expected = self::sign("{$h64}.{$p64}");
        // Constant-time comparison to avoid signature timing leaks.
        if (!hash_equals($expected, $s64)) {
            throw ApiException::unauthorized('امضای توکن نامعتبر است.', 'invalid_token');
        }

        $header  = self::jsonDecode(self::b64decode($h64));
        $payload = self::jsonDecode(self::b64decode($p64));

        if (($header['alg'] ?? '') !== 'HS256') {
            throw ApiException::unauthorized('الگوریتم توکن پشتیبانی نمی‌شود.', 'invalid_token');
        }
        $now = time();
        if (isset($payload['nbf']) && $now + 1 < (int) $payload['nbf']) {
            throw ApiException::unauthorized('توکن هنوز معتبر نیست.', 'invalid_token');
        }
        if (!isset($payload['exp']) || $now >= (int) $payload['exp']) {
            throw ApiException::unauthorized('نشست شما منقضی شده است.', 'token_expired');
        }
        if (($payload['typ'] ?? '') !== 'access') {
            throw ApiException::unauthorized('نوع توکن نامعتبر است.', 'invalid_token');
        }

        return $payload;
    }

    /** @param array<string,mixed> $payload */
    private static function encode(array $payload): string
    {
        $header = self::b64encode(self::jsonEncode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body   = self::b64encode(self::jsonEncode($payload));
        $sig    = self::sign("{$header}.{$body}");
        return "{$header}.{$body}.{$sig}";
    }

    private static function sign(string $data): string
    {
        $secret = Config::require('JWT_SECRET');
        return self::b64encode(hash_hmac('sha256', $data, $secret, true));
    }

    private static function b64encode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function b64decode(string $b64): string
    {
        $decoded = base64_decode(strtr($b64, '-_', '+/'), true);
        if ($decoded === false) {
            throw ApiException::unauthorized('توکن نامعتبر است.', 'invalid_token');
        }
        return $decoded;
    }

    /** @return array<string,mixed> */
    private static function jsonDecode(string $json): array
    {
        try {
            $data = json_decode($json, true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ApiException::unauthorized('توکن نامعتبر است.', 'invalid_token');
        }
        return is_array($data) ? $data : [];
    }

    private static function jsonEncode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
