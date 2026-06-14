<?php
/**
 * Central database-credentials loader.
 *
 * Reads the real credentials from a file kept OUTSIDE the web root (and out of
 * git), so no password is ever committed or web-accessible.
 *
 * Search order (first hit wins):
 *   1. environment variables  DB_HOST / DB_NAME / DB_USER / DB_PASS
 *   2. <home>/maksa-private/db.php  (one level above public_html)
 *   3. public_html/core/db.local.php  (dev fallback only — gitignored)
 *
 * Each of those files must `return` an array, e.g.:
 *   return ['host'=>'localhost','name'=>'...','user'=>'...','pass'=>'...'];
 *
 * Usage:
 *   $DB = require __DIR__ . '/db-config.php';   // ['host','name','user','pass','charset']
 *
 * Returns an array — callers build their own PDO/mysqli connection from it.
 */

return (static function (): array {
    $defaults = ['host' => 'localhost', 'name' => '', 'user' => '', 'pass' => '', 'charset' => 'utf8mb4'];

    // 1) Environment variables take precedence (e.g. set via SetEnv in cPanel).
    $env = [
        'host' => getenv('DB_HOST') ?: null,
        'name' => getenv('DB_NAME') ?: null,
        'user' => getenv('DB_USER') ?: null,
        'pass' => getenv('DB_PASS') ?: null,
    ];
    if ($env['name'] && $env['user']) {
        return array_merge($defaults, array_filter($env, static fn ($v) => $v !== null));
    }

    // 2) / 3) Credentials file outside the web root, then a local dev fallback.
    $candidates = [
        \dirname(__DIR__, 2) . '/maksa-private/db.php', // <home>/maksa-private/db.php
        __DIR__ . '/db.local.php',                      // dev fallback (gitignored)
    ];
    foreach ($candidates as $path) {
        if (is_file($path) && is_readable($path)) {
            $cfg = require $path;
            if (is_array($cfg)) {
                return array_merge($defaults, $cfg);
            }
        }
    }

    // Nothing found — return defaults so the caller's own error handling fires.
    return $defaults;
})();
