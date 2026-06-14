<?php

declare(strict_types=1);

namespace Maksa\Support;

use Maksa\Core\Config;
use Maksa\Core\Security;

/** Sets/clears the httpOnly refresh-token cookie with hardened attributes. */
final class Cookie
{
    public static function setRefreshToken(string $token): void
    {
        self::write($token, Config::int('REFRESH_TTL', 31536000));
    }

    public static function clearRefreshToken(): void
    {
        self::write('', -3600);
    }

    private static function write(string $value, int $maxAgeSeconds): void
    {
        $name   = Config::get('REFRESH_COOKIE_NAME', 'maksa_rt');
        $path   = Config::get('REFRESH_COOKIE_PATH', '/api/auth');
        $domain = (string) Config::get('COOKIE_DOMAIN', '');

        setcookie($name, $value, [
            'expires'  => time() + $maxAgeSeconds,
            'path'     => $path,
            'domain'   => $domain,                 // empty => host-only cookie
            'secure'   => Security::isHttps(),     // always true in production
            'httponly' => true,                    // JS can never read it
            'samesite' => 'Strict',                // blocks cross-site sending (CSRF)
        ]);
    }

    public static function refreshToken(): ?string
    {
        $name = Config::get('REFRESH_COOKIE_NAME', 'maksa_rt');
        $value = $_COOKIE[$name] ?? null;
        return is_string($value) && $value !== '' ? $value : null;
    }
}
