<?php

declare(strict_types=1);

namespace Maksa\Core;

use Maksa\Core\Exceptions\ApiException;

/** Immutable view over the incoming HTTP request. */
final class Request
{
    /** Set by AuthMiddleware once a valid access token is verified. */
    public ?int $authUserId = null;

    /** @param array<string,mixed> $body @param array<string,string> $query @param array<string,string> $params */
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $body,
        public readonly array $query,
        public array $params,           // route params, filled by the router
        public readonly array $headers,
        public readonly array $cookies,
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        // Strip a leading /api so routes are declared without that prefix.
        $path = '/' . ltrim($path, '/');
        if (str_starts_with($path, '/api/')) {
            $path = substr($path, 4);
        } elseif ($path === '/api') {
            $path = '/';
        }
        $path = '/' . trim($path, '/');

        return new self(
            method:  $method,
            path:    $path === '' ? '/' : $path,
            body:    self::parseBody(),
            query:   array_map(fn($v) => is_string($v) ? $v : '', $_GET),
            params:  [],
            headers: self::headers(),
            cookies: $_COOKIE,
        );
    }

    /** @return array<string,mixed> */
    private static function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            // Strict JSON size limit (256 KB) to blunt memory-exhaustion attempts.
            if (strlen($raw) > 262144) {
                throw ApiException::badRequest('حجم درخواست بیش از حد مجاز است.', 'payload_too_large');
            }
            if ($raw === '') {
                return [];
            }
            try {
                $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw ApiException::badRequest('بدنهٔ JSON نامعتبر است.', 'invalid_json');
            }
            return is_array($decoded) ? $decoded : [];
        }

        // multipart/form-data (file uploads) and urlencoded forms.
        return $_POST;
    }

    /** @return array<string,string> */
    private static function headers(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = (string) $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = (string) $_SERVER['CONTENT_TYPE'];
        }
        return $headers;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->headers['Authorization'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    public function cookie(string $name): ?string
    {
        $value = $this->cookies[$name] ?? null;
        return is_string($value) ? $value : null;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function ip(): string
    {
        // Trust REMOTE_ADDR by default. Only honor a proxy header if you front
        // the app with a proxy you control (then set TRUSTED_PROXY=true).
        if (Config::bool('TRUSTED_PROXY')) {
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
            if ($forwarded !== '') {
                $first = trim(explode(',', $forwarded)[0]);
                if (filter_var($first, FILTER_VALIDATE_IP)) {
                    return $first;
                }
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    /** The authenticated user id, or throw 401 if the route wasn't guarded. */
    public function userId(): int
    {
        if ($this->authUserId === null) {
            throw \Maksa\Core\Exceptions\ApiException::unauthorized();
        }
        return $this->authUserId;
    }
}
