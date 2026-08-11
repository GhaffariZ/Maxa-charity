<?php

declare(strict_types=1);

namespace Maksa\Core\Exceptions;

/**
 * An exception that maps cleanly onto a JSON error response. The message is
 * safe to show to clients; never put secrets or internal detail in it.
 */
class ApiException extends \RuntimeException
{
    /** @param array<string,mixed> $details */
    public function __construct(
        private readonly int $statusCode,
        private readonly string $errorCode,
        string $message,
        private readonly array $details = []
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /** @return array<string,mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public static function badRequest(string $message, string $code = 'bad_request'): self
    {
        return new self(400, $code, $message);
    }

    public static function unauthorized(string $message = 'احراز هویت لازم است.', string $code = 'unauthorized'): self
    {
        return new self(401, $code, $message);
    }

    public static function forbidden(string $message = 'دسترسی مجاز نیست.', string $code = 'forbidden'): self
    {
        return new self(403, $code, $message);
    }

    public static function notFound(string $message = 'یافت نشد.', string $code = 'not_found'): self
    {
        return new self(404, $code, $message);
    }

    public static function tooManyRequests(string $message, string $code = 'rate_limited'): self
    {
        return new self(429, $code, $message);
    }

    public static function conflict(string $message, string $code = 'conflict'): self
    {
        return new self(409, $code, $message);
    }
}
