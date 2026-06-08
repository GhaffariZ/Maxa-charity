<?php

declare(strict_types=1);

namespace Maksa\Support;

use Maksa\Core\Config;

/**
 * Minimal append-only file logger. The log file lives OUTSIDE the web root
 * (configured via LOG_PATH). Never log secrets, passwords, or full tokens.
 */
final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $path = Config::get('LOG_PATH', '');
        if ($path === '' || $path === null) {
            // Fall back to a logs/ dir beside private config, derived from env path.
            $path = \dirname(__DIR__, 3) . '/../maksa-private/logs/app.log';
        }

        $dir = \dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $line = sprintf(
            "[%s] %s: %s %s\n",
            gmdate('Y-m-d\TH:i:s\Z'),
            $level,
            $message,
            $context === [] ? '' : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}
