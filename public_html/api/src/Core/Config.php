<?php

declare(strict_types=1);

namespace Maksa\Core;

use RuntimeException;

/**
 * Lightweight environment configuration loader and accessor.
 * Reads KEY=VALUE pairs from .env and exposes typed lookups.
 */
final class Config
{
    /** @var array<string,string> */
    private static array $env = [];
    private static bool $loaded = false;

    public static function load(?string $customPath = null): void
    {
        if (self::$loaded && $customPath === null) {
            return;
        }

        $candidates = array_filter([
            $customPath,
            // 1. One level above public_html (cPanel recommended)
            dirname(__DIR__, 4) . '/maksa-private/.env',
            dirname(__DIR__, 4) . '/.env',
            // 2. Project root (dev environment)
            dirname(__DIR__, 3) . '/.env',
            // 3. public_html or api dir
            dirname(__DIR__, 2) . '/.env',
            dirname(__DIR__, 1) . '/.env',
        ]);

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                self::parse($path);
                self::$loaded = true;
                return;
            }
        }

        // Even if no .env file is found, mark as loaded and pull existing env vars
        self::$loaded = true;
    }

    private static function parse(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));

            // Strip surrounding quotes
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }

            self::$env[$key] = $val;
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $val;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        if (array_key_exists($key, self::$env)) {
            return self::$env[$key];
        }

        $fromEnv = getenv($key);
        if ($fromEnv !== false) {
            return $fromEnv;
        }

        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function require(string $key): mixed
    {
        $val = self::get($key);
        if ($val === null || $val === '') {
            throw new RuntimeException("Missing required configuration variable: {$key}");
        }
        return $val;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $val = self::get($key);
        if ($val === null) {
            return $default;
        }
        if (is_bool($val)) {
            return $val;
        }
        $lower = strtolower(trim((string) $val));
        return in_array($lower, ['1', 'true', 'on', 'yes'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $val = self::get($key);
        if ($val === null || $val === '') {
            return $default;
        }
        return (int) $val;
    }

    /** For testing purposes to set config at runtime */
    public static function set(string $key, string $value): void
    {
        self::$env[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
