<?php

declare(strict_types=1);

namespace Maksa\Support;

use Maksa\Core\Database;
use PDO;

/**
 * Login rate limiting backed by the `login_attempts` table. Counts recent
 * failures per (email, IP) and enforces a sliding window. Pairs with the
 * per-account lockout tracked on `panel_users` (failed_login_attempts/locked_until).
 */
final class RateLimiter
{
    private const MAX_FAILURES = 5;       // within the window…
    private const WINDOW_MINUTES = 15;    // …per email+IP

    public static function record(string $email, string $ip, bool $success, string $userAgent): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO login_attempts (email, ip, success, user_agent) VALUES (:email, :ip, :success, :ua)'
        );
        $stmt->execute([
            ':email'   => $email !== '' ? mb_strtolower($email) : null,
            ':ip'      => $ip,
            ':success' => $success ? 1 : 0,
            ':ua'      => $userAgent !== '' ? $userAgent : null,
        ]);
    }

    /** True if this email+IP has exceeded the failed-attempt threshold recently. */
    public static function tooManyFailures(string $email, string $ip): bool
    {
        // Compute the window cutoff in PHP (UTC) and bind it as a plain param —
        // INTERVAL doesn't accept a placeholder in a MySQL prepared statement.
        $cutoff = gmdate('Y-m-d H:i:s', time() - self::WINDOW_MINUTES * 60);
        $emailNorm = $email !== '' ? mb_strtolower($email) : null;

        // Native prepared statements (EMULATE_PREPARES=false) forbid reusing a
        // named placeholder, so we use a separate :email_null flag rather than
        // referencing :email twice.
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE success = 0
               AND ip = :ip
               AND (:email_null = 1 OR email = :email)
               AND attempted_at > :cutoff'
        );
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':email', $emailNorm, $emailNorm === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':email_null', $emailNorm === null ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= self::MAX_FAILURES;
    }
}
