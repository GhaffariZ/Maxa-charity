<?php

declare(strict_types=1);

namespace Maksa\Core;

/** Emits security headers and handles CORS for the configured frontend origin. */
final class Security
{
    public static function applyResponseHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        // API responses are JSON — a tight CSP that forbids any content rendering.
        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // HSTS only over HTTPS, so we never pin an insecure dev origin.
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        header_remove('X-Powered-By');
    }

    /**
     * CORS. With the app and API on the same origin this is a no-op for normal
     * requests, but we still honor the explicit allowlist (CORS_ALLOWED_ORIGINS,
     * comma-separated) — never a wildcard — for any cross-origin/dev setup.
     */
    public static function handleCors(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin === '') {
            return;
        }

        $allowed = array_filter(array_map('trim', explode(',', (string) Config::get('CORS_ALLOWED_ORIGINS', ''))));
        if (!in_array($origin, $allowed, true)) {
            // Not an allowed origin: send no CORS headers (browser blocks it).
            if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
                http_response_code(403);
                exit;
            }
            return;
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
        header('Access-Control-Max-Age: 600');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public static function isHttps(): bool
    {
        return (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443'
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' && Config::bool('TRUSTED_PROXY'));
    }
}
