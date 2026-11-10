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

        $paths = [];
        if ($customPath !== null) {
            $paths[] = $customPath;
        }
        $envPath = getenv('MAKSA_ENV_PATH');
        if ($envPath !== false && $envPath !== '') {
            $paths[] = $envPath;
        }

        $roots = [];
        for ($level = 2; $level <= 6; $level++) {
            $roots[] = dirname(__DIR__, $level);
        }
        foreach ($roots as $root) {
            $paths[] = $root . '/maksa-private/.env';
        }
        foreach ($roots as $root) {
            $paths[] = $root . '/.env';
        }

        foreach (array_unique($paths) as $path) {
            if (@is_file($path) && @is_readable($path)) {
                self::parse($path);
            }
        }

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
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $val] = array_map('trim', explode('=', $line, 2));
            if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $key)) {
                continue;
            }

            // Quoted passwords may contain #; unquoted values allow inline comments.
            if (preg_match('/^([\'"])(.*?)\1(?:\s+#.*)?$/', $val, $match)) {
                $val = $match[2];
            } else {
                $val = trim(preg_replace('/\s+#.*$/', '', $val) ?? $val);
            }

            // The first file containing a key wins; deployment environment wins over files.
            if (array_key_exists($key, self::$env)) {
                continue;
            }
            self::$env[$key] = $val;
            if (getenv($key) === false) {
                putenv($key . '=' . $val);
            }
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

        $fromEnv = getenv($key);
        if ($fromEnv !== false) {
            return $fromEnv;
        }

        if (array_key_exists($key, self::$env)) {
            return self::$env[$key];
        }

        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        return $default;
    }

    public static function require(string $key): string
    {
        $val = self::get($key);
        if ($val === null || $val === '') {
            throw new RuntimeException("Missing required configuration: {$key}");
        }
        return (string) $val;
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
        return filter_var($val, FILTER_VALIDATE_BOOL);
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
        putenv($key . '=' . $value);
    }
}
