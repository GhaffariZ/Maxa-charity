<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** @return array<string,mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM panel_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM panel_users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM panel_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower($email)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Creates user + profile + notification prefs atomically. Returns user id.
     */
    public function createDonor(string $email, string $passwordHash, ?string $firstName, ?string $lastName): int
    {
        return (int) Database::transaction(function (PDO $db) use ($email, $passwordHash, $firstName, $lastName) {
            $stmt = $db->prepare(
                'INSERT INTO panel_users (email, password_hash, status) VALUES (:email, :hash, :status)'
            );
            $stmt->execute([
                ':email'  => mb_strtolower($email),
                ':hash'   => $passwordHash,
                ':status' => 'pending',
            ]);
            $userId = (int) $db->lastInsertId();

            // Bronze tier (id derived by slug to avoid hardcoding).
            $bronze = $db->query("SELECT id FROM donor_tiers WHERE slug = 'bronze' LIMIT 1")->fetchColumn();

            $db->prepare(
                'INSERT INTO user_profiles (user_id, first_name, last_name, donor_tier_id)
                 VALUES (:uid, :fn, :ln, :tier)'
            )->execute([
                ':uid'  => $userId,
                ':fn'   => $firstName,
                ':ln'   => $lastName,
                ':tier' => $bronze !== false ? (int) $bronze : null,
            ]);

            $db->prepare('INSERT INTO user_notification_prefs (user_id) VALUES (:uid)')
               ->execute([':uid' => $userId]);

            return $userId;
        });
    }

    public function markEmailVerified(int $userId): void
    {
        $this->db->prepare(
            "UPDATE panel_users SET email_verified_at = UTC_TIMESTAMP(), status = 'active' WHERE id = :id"
        )->execute([':id' => $userId]);
    }

    public function recordSuccessfulLogin(int $userId, string $ip): void
    {
        $this->db->prepare(
            'UPDATE panel_users
                SET failed_login_attempts = 0,
                    locked_until = NULL,
                    last_login_at = UTC_TIMESTAMP(),
                    last_login_ip = :ip
              WHERE id = :id'
        )->execute([':id' => $userId, ':ip' => $ip]);
    }

    /** Increments the failure counter and locks the account past a threshold. */
    public function recordFailedLogin(int $userId): void
    {
        // Lock for 15 minutes once failures reach 5 (exponential beyond that).
        $this->db->prepare(
            "UPDATE panel_users
                SET failed_login_attempts = failed_login_attempts + 1,
                    locked_until = CASE
                        WHEN failed_login_attempts + 1 >= 5
                        THEN UTC_TIMESTAMP() + INTERVAL LEAST(POW(2, failed_login_attempts + 1 - 5) * 15, 1440) MINUTE
                        ELSE locked_until
                    END
              WHERE id = :id"
        )->execute([':id' => $userId]);
    }

    public function updatePasswordHash(int $userId, string $hash): void
    {
        $this->db->prepare('UPDATE panel_users SET password_hash = :hash WHERE id = :id')
                 ->execute([':hash' => $hash, ':id' => $userId]);
    }

    public function delete(int $userId): void
    {
        // FK cascades remove profile/prefs/tokens/notifications; panel_donations
        // are anonymized via ON DELETE SET NULL.
        $this->db->prepare('DELETE FROM panel_users WHERE id = :id')->execute([':id' => $userId]);
    }
}
