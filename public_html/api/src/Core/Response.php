<?php

declare(strict_types=1);

namespace Maksa\Core;

/**
 * JSON responder. Every response follows the shape:
 *   { "success": bool, "data": ..., "error": { code, message, ... } | null }
 */
final class Response
{
    public static function json(mixed $payload, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, int $status = 200): never
    {
        self::json(['success' => true, 'data' => $data, 'error' => null], $status);
    }

    /** @param array<string,mixed> $details */
    public static function error(int $status, string $code, string $message, array $details = []): never
    {
        $error = ['code' => $code, 'message' => $message];
        if ($details !== []) {
            $error += $details;
        }
        self::json(['success' => false, 'data' => null, 'error' => $error], $status);
    }

    public static function noContent(): never
    {
        if (!headers_sent()) {
            http_response_code(204);
        }
        exit;
    }
}
