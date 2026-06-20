<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

final class TicketRepository
{
    private PDO $db;

    private const CATEGORIES = ['general', 'donation', 'technical', 'campaign', 'other'];
    private const PRIORITIES  = ['low', 'normal', 'high'];
    private const STATUSES    = ['open', 'answered', 'closed'];

    private const CATEGORY_FA = [
        'general'   => 'عمومی',
        'donation'  => 'کمک‌ها و پرداخت',
        'technical' => 'مشکل فنی',
        'campaign'  => 'کمپین‌ها',
        'other'     => 'سایر',
    ];
    private const PRIORITY_FA = ['low' => 'کم', 'normal' => 'عادی', 'high' => 'فوری'];
    private const STATUS_FA   = ['open' => 'باز', 'answered' => 'پاسخ داده شد', 'closed' => 'بسته‌شده'];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $name, string $subject, string $message, string $category, string $priority, ?string $contact): int
    {
        $category = in_array($category, self::CATEGORIES, true) ? $category : 'general';
        $priority = in_array($priority, self::PRIORITIES, true)  ? $priority : 'normal';

        $this->db->prepare(
            'INSERT INTO tickets (user_id, name, contact, subject, category, priority, message, status, admin_unread, user_unread)
             VALUES (:uid, :name, :contact, :subject, :category, :priority, :message, \'open\', 1, 0)'
        )->execute([
            ':uid'      => $userId,
            ':name'     => mb_substr($name, 0, 120),
            ':contact'  => $contact !== null ? mb_substr($contact, 0, 255) : null,
            ':subject'  => mb_substr($subject, 0, 200),
            ':category' => $category,
            ':priority' => $priority,
            ':message'  => mb_substr($message, 0, 5000),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, subject, category, priority, status, user_unread, created_at, updated_at
               FROM tickets WHERE user_id = :uid ORDER BY updated_at DESC'
        );
        $stmt->execute([':uid' => $userId]);

        return array_map(fn(array $r) => $this->formatSummary($r), $stmt->fetchAll());
    }

    /** Single ticket + reply thread, only if it belongs to the given user. */
    public function findForUser(int $ticketId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM tickets WHERE id = :id AND user_id = :uid LIMIT 1'
        );
        $stmt->execute([':id' => $ticketId, ':uid' => $userId]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            return null;
        }

        // Clear user_unread flag now that user is reading
        $this->db->prepare('UPDATE tickets SET user_unread = 0 WHERE id = :id')
                 ->execute([':id' => $ticketId]);

        $ticket['user_unread'] = 0;
        $ticket['replies']     = $this->repliesFor($ticketId);

        return $this->formatDetail($ticket);
    }

    public function addUserReply(int $ticketId, int $userId, string $message): ?int
    {
        // Verify ownership
        $stmt = $this->db->prepare('SELECT id, status FROM tickets WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute([':id' => $ticketId, ':uid' => $userId]);
        $ticket = $stmt->fetch();
        if (!$ticket || $ticket['status'] === 'closed') {
            return null;
        }

        $this->db->prepare(
            'INSERT INTO ticket_replies (ticket_id, author, message) VALUES (:tid, \'user\', :msg)'
        )->execute([':tid' => $ticketId, ':msg' => mb_substr($message, 0, 5000)]);

        $replyId = (int) $this->db->lastInsertId();

        $this->db->prepare(
            'UPDATE tickets SET status = \'open\', admin_unread = 1, updated_at = NOW() WHERE id = :id'
        )->execute([':id' => $ticketId]);

        return $replyId;
    }

    // -------------------------------------------------------------------------

    /** @return list<array<string,mixed>> */
    private function repliesFor(int $ticketId): array
    {
        $stmt = $this->db->prepare(
            'SELECT author, message, created_at FROM ticket_replies
              WHERE ticket_id = :tid ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute([':tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    /** @param array<string,mixed> $r */
    private function formatSummary(array $r): array
    {
        return [
            'id'          => (int) $r['id'],
            'subject'     => $r['subject'],
            'category'    => $r['category'],
            'category_fa' => self::CATEGORY_FA[$r['category']] ?? $r['category'],
            'priority'    => $r['priority'],
            'priority_fa' => self::PRIORITY_FA[$r['priority']] ?? $r['priority'],
            'status'      => $r['status'],
            'status_fa'   => self::STATUS_FA[$r['status']] ?? $r['status'],
            'has_unread'  => (bool) $r['user_unread'],
            'created_at'  => $r['created_at'],
            'updated_at'  => $r['updated_at'],
        ];
    }

    /** @param array<string,mixed> $r */
    private function formatDetail(array $r): array
    {
        return array_merge($this->formatSummary($r), [
            'message' => $r['message'],
            'contact' => $r['contact'],
            'replies' => array_map(fn(array $reply) => [
                'author'     => $reply['author'],
                'message'    => $reply['message'],
                'created_at' => $reply['created_at'],
            ], $r['replies']),
        ]);
    }
}
