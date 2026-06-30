<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use Maksa\Support\Str;
use PDO;

/**
 * Single-use, hashed tokens for email verification and password reset.
 * Returns plaintext tokens to the caller (to embed in emails); only their
 * SHA-256 hashes are stored.
 */
final class OneTimeTokenRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function createEmailVerification(int $userId, int $ttlSeconds = 86400): string
    {
        $plain = Str::randomToken(32);
        $this->db->prepare(
            'INSERT INTO email_verification_tokens (user_id, token_hash, expires_at)
             VALUES (:uid, :hash, :exp)'
        )->execute([
            ':uid'  => $userId,
            ':hash' => Str::sha256($plain),
            ':exp'  => gmdate('Y-m-d H:i:s', time() + $ttlSeconds),
        ]);
        return $plain;
    }

    /** Consumes a verification token. Returns user id on success, null otherwise. */
    public function consumeEmailVerification(string $plain): ?int
    {
        return $this->consume('email_verification_tokens', $plain);
    }

    public function createPasswordReset(int $userId, string $ip, int $ttlSeconds = 3600): string
    {
        $plain = Str::randomToken(32);
        $this->db->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, ip_requested_from)
             VALUES (:uid, :hash, :exp, :ip)'
        )->execute([
            ':uid'  => $userId,
            ':hash' => Str::sha256($plain),
            ':exp'  => gmdate('Y-m-d H:i:s', time() + $ttlSeconds),
            ':ip'   => $ip,
        ]);
        return $plain;
    }

    public function consumePasswordReset(string $plain): ?int
    {
        return $this->consume('password_reset_tokens', $plain);
    }

    /** Invalidate any outstanding reset tokens for a user (e.g. after success). */
    public function invalidateUserResets(int $userId): void
    {
        $this->db->prepare(
            'UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP()
              WHERE user_id = :uid AND used_at IS NULL'
        )->execute([':uid' => $userId]);
    }

    /**
     * Atomically validate + mark-used a token in the given table. The UPDATE…WHERE
     * used_at IS NULL guarantees single use even under concurrent requests.
     */
    private function consume(string $table, string $plain): ?int
    {
        $hash = Str::sha256($plain);

        return Database::transaction(function (PDO $db) use ($table, $hash): ?int {
            $stmt = $db->prepare(
                "SELECT id, user_id, expires_at, used_at FROM {$table}
                  WHERE token_hash = :h LIMIT 1 FOR UPDATE"
            );
            $stmt->execute([':h' => $hash]);
            $row = $stmt->fetch();

            if (!$row || $row['used_at'] !== null || strtotime((string) $row['expires_at']) <= time()) {
                return null;
            }

            $db->prepare("UPDATE {$table} SET used_at = UTC_TIMESTAMP() WHERE id = :id")
               ->execute([':id' => (int) $row['id']]);

            return (int) $row['user_id'];
        });
    }
}
