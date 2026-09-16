<?php

declare(strict_types=1);

namespace Maksa\Core;

final class Config
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) return;
        self::$loaded = true;

        $roots = [];
        for ($level = 2; $level <= 6; $level++) {
            $roots[] = dirname(__DIR__, $level);
        }
        $paths = [];
        foreach ($roots as $root) $paths[] = $root . '/maksa-private/.env';
        foreach ($roots as $root) $paths[] = $root . '/.env';

        foreach (array_unique($paths) as $path) {
            if (!is_readable($path)) continue;
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) continue;
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $key) || getenv($key) !== false) continue;
                $value = trim($value, " \t\n\r\0\x0B\"'");
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        $value = getenv($key);
        return $value === false || $value === '' ? $default : $value;
    }

    public static function require(string $key): string
    {
        $value = (string)self::get($key, '');
        if ($value === '') throw new \RuntimeException("Missing required configuration: {$key}");
        return $value;
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int)self::get($key, $default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, null);
        if ($value === null) return $default;
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
