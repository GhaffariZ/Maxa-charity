<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

final class ProfileRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Assembled profile for GET /user/me. @return array<string,mixed>|null */
    public function fullProfile(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.email, u.email_verified_at, u.status, u.created_at AS member_since,
                    u.last_login_at,
                    p.first_name, p.last_name, p.phone, p.avatar_url, p.postal_address,
                    p.timezone, p.locale, p.kindness_points,
                    t.slug AS tier_slug, t.name_fa AS tier_name
               FROM panel_users u
               JOIN user_profiles p ON p.user_id = u.id
          LEFT JOIN donor_tiers t   ON t.id = p.donor_tier_id
              WHERE u.id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @param array<string,mixed> $fields whitelist already applied by caller */
    public function updateProfile(int $userId, array $fields): void
    {
        if ($fields === []) {
            return;
        }
        $allowed = ['first_name', 'last_name', 'phone', 'postal_address'];
        $sets = [];
        $params = [':id' => $userId];
        foreach ($fields as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if ($sets === []) {
            return;
        }
        $sql = 'UPDATE user_profiles SET ' . implode(', ', $sets) . ' WHERE user_id = :id';
        $this->db->prepare($sql)->execute($params);
    }

    /** Adds kindness points and promotes the cached tier to match the new total. */
    public function addPointsAndRecomputeTier(int $userId, int $points): void
    {
        if ($points <= 0) {
            return;
        }
        $this->db->prepare(
            'UPDATE user_profiles SET kindness_points = kindness_points + :p WHERE user_id = :id'
        )->execute([':p' => $points, ':id' => $userId]);

        // Pick the highest tier whose threshold the user now meets.
        $this->db->prepare(
            'UPDATE user_profiles p
                SET p.donor_tier_id = (
                    SELECT t.id FROM donor_tiers t
                     WHERE t.min_points <= p.kindness_points
                  ORDER BY t.min_points DESC LIMIT 1
                )
              WHERE p.user_id = :id'
        )->execute([':id' => $userId]);
    }

    public function setAvatarUrl(int $userId, string $url): void
    {
        $this->db->prepare('UPDATE user_profiles SET avatar_url = :u WHERE user_id = :id')
                 ->execute([':u' => $url, ':id' => $userId]);
    }

    /** @return array{news:bool,impact_reports:bool,new_campaigns:bool} */
    public function notificationPrefs(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT news, impact_reports, new_campaigns FROM user_notification_prefs WHERE user_id = :id'
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch() ?: ['news' => 1, 'impact_reports' => 1, 'new_campaigns' => 1];
        return [
            'news'           => (bool) $row['news'],
            'impact_reports' => (bool) $row['impact_reports'],
            'new_campaigns'  => (bool) $row['new_campaigns'],
        ];
    }

    /** @param array<string,bool> $prefs */
    public function updateNotificationPrefs(int $userId, array $prefs): void
    {
        $allowed = ['news', 'impact_reports', 'new_campaigns'];
        $sets = [];
        $params = [':id' => $userId];
        foreach ($prefs as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value ? 1 : 0;
            }
        }
        if ($sets === []) {
            return;
        }
        $this->db->prepare(
            'UPDATE user_notification_prefs SET ' . implode(', ', $sets) . ' WHERE user_id = :id'
        )->execute($params);
    }
}
