<?php

declare(strict_types=1);

namespace Maksa\Auth;

use Maksa\Core\Config;
use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Support\Audit;
use Maksa\Support\Str;
use PDO;

/**
 * Opaque refresh tokens with rotation + reuse detection.
 *
 *  - On login: mint a token with a fresh family_id.
 *  - On refresh: the presented token must be live (not revoked, not expired,
 *    not already replaced). We revoke it, mint a successor in the same family.
 *  - Reuse detection: if a token that was ALREADY rotated/revoked is presented,
 *    we assume theft and revoke the entire family.
 *
 * Only SHA-256 hashes of tokens touch the database.
 */
final class RefreshTokenService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Issues a brand-new token family (used at login). Returns the plaintext token. */
    public function issueNewFamily(int $userId, string $ip, string $userAgent): string
    {
        return $this->insert($userId, Str::uuid4(), $ip, $userAgent);
    }

    /**
     * Validates + rotates a presented refresh token. Returns [userId, newToken].
     * Throws on any invalid/reused token (and burns the family on reuse).
     *
     * @return array{0:int,1:string}
     */
    public function rotate(string $presented, string $ip, string $userAgent): array
    {
        $hash = Str::sha256($presented);

        $stmt = $this->db->prepare('SELECT * FROM refresh_tokens WHERE token_hash = :h LIMIT 1');
        $stmt->execute([':h' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            throw ApiException::unauthorized('نشست نامعتبر است. لطفاً دوباره وارد شوید.', 'invalid_refresh');
        }

        $userId   = (int) $row['user_id'];
        $familyId = (string) $row['family_id'];

        // Reuse detection: a token that was already revoked/replaced is being
        // presented again → likely stolen. Burn the whole family.
        if ($row['revoked_at'] !== null || $row['replaced_by_hash'] !== null) {
            $this->revokeFamily($familyId);
            Audit::log($userId, 'refresh_reuse_detected', $ip, $userAgent, ['family' => $familyId]);
            throw ApiException::unauthorized('فعالیت مشکوک شناسایی شد. لطفاً دوباره وارد شوید.', 'refresh_reuse');
        }

        if (strtotime((string) $row['expires_at']) <= time()) {
            throw ApiException::unauthorized('نشست منقضی شده است. لطفاً دوباره وارد شوید.', 'refresh_expired');
        }

        // Rotate within the same family.
        return (array) Database::transaction(function (PDO $db) use ($row, $userId, $familyId, $hash, $ip, $userAgent) {
            $newPlain = $this->insert($userId, $familyId, $ip, $userAgent);

            $db->prepare(
                'UPDATE refresh_tokens
                    SET revoked_at = UTC_TIMESTAMP(), replaced_by_hash = :next
                  WHERE id = :id'
            )->execute([':next' => Str::sha256($newPlain), ':id' => (int) $row['id']]);

            return [$userId, $newPlain];
        });
    }

    /** Revokes a single token by its plaintext (used at logout). */
    public function revoke(string $presented): void
    {
        $this->db->prepare(
            'UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP()
              WHERE token_hash = :h AND revoked_at IS NULL'
        )->execute([':h' => Str::sha256($presented)]);
    }

    public function revokeFamily(string $familyId): void
    {
        $this->db->prepare(
            'UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP()
              WHERE family_id = :f AND revoked_at IS NULL'
        )->execute([':f' => $familyId]);
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->db->prepare(
            'UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP()
              WHERE user_id = :u AND revoked_at IS NULL'
        )->execute([':u' => $userId]);
    }

    private function insert(int $userId, string $familyId, string $ip, string $userAgent): string
    {
        $plain = Str::randomToken(32); // 256-bit
        $ttl   = Config::int('REFRESH_TTL', 2592000);

        $this->db->prepare(
            'INSERT INTO refresh_tokens (user_id, token_hash, family_id, expires_at, user_agent, ip)
             VALUES (:uid, :hash, :family, :exp, :ua, :ip)'
        )->execute([
            ':uid'    => $userId,
            ':hash'   => Str::sha256($plain),
            ':family' => $familyId,
            ':exp'    => gmdate('Y-m-d H:i:s', time() + $ttl),
            ':ua'     => $userAgent !== '' ? $userAgent : null,
            ':ip'     => $ip,
        ]);

        return $plain;
    }
}
