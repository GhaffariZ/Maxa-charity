<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

final class NotificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, body, link)
             VALUES (:uid, :type, :title, :body, :link)'
        )->execute([':uid' => $userId, ':type' => $type, ':title' => $title, ':body' => $body, ':link' => $link]);
    }

    /** @return list<array<string,mixed>> */
    public function listForUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, title, body, link, read_at, created_at
               FROM notifications WHERE user_id = :uid
           ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(static fn(array $r) => [
            'id'         => (int) $r['id'],
            'type'       => $r['type'],
            'title'      => $r['title'],
            'body'       => $r['body'],
            'link'       => $r['link'],
            'read'       => $r['read_at'] !== null,
            'created_at' => $r['created_at'],
        ], $stmt->fetchAll());
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND read_at IS NULL'
        );
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $userId, int $notificationId): void
    {
        $this->db->prepare(
            'UPDATE notifications SET read_at = UTC_TIMESTAMP()
              WHERE id = :id AND user_id = :uid AND read_at IS NULL'
        )->execute([':id' => $notificationId, ':uid' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->prepare(
            'UPDATE notifications SET read_at = UTC_TIMESTAMP()
              WHERE user_id = :uid AND read_at IS NULL'
        )->execute([':uid' => $userId]);
    }
}
