<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use Maksa\Support\Str;
use PDO;

final class DonationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Creates a pending donation, returns [id, reference]. @return array{0:int,1:string} */
    public function createPending(int $userId, ?int $campaignId, int $amountToman, string $gateway): array
    {
        // Retry on the (very unlikely) reference collision.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = Str::donationReference();
            try {
                $stmt = $this->db->prepare(
                    'INSERT INTO panel_donations (reference, user_id, campaign_id, amount, gateway, status, type)
                     VALUES (:ref, :uid, :cid, :amt, :gw, \'pending\', \'online\')'
                );
                $stmt->execute([
                    ':ref' => $reference,
                    ':uid' => $userId,
                    ':cid' => $campaignId,
                    ':amt' => $amountToman,
                    ':gw'  => $gateway,
                ]);
                return [(int) $this->db->lastInsertId(), $reference];
            } catch (\PDOException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }
        throw new \RuntimeException('ساخت تراکنش ناموفق بود.');
    }

    public function setAuthority(int $donationId, string $authority): void
    {
        $this->db->prepare('UPDATE panel_donations SET gateway_authority = :a WHERE id = :id')
                 ->execute([':a' => $authority, ':id' => $donationId]);
    }

    /** @return array<string,mixed>|null */
    public function findByAuthority(string $authority): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM panel_donations WHERE gateway_authority = :a LIMIT 1');
        $stmt->execute([':a' => $authority]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<string,mixed>|null */
    public function findForUserByReference(int $userId, string $reference): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM panel_donations WHERE reference = :ref AND user_id = :uid LIMIT 1'
        );
        $stmt->execute([':ref' => $reference, ':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Atomically transition a donation from 'pending' to 'success'.
     *
     * The WHERE clause includes `status = 'pending'` so only the first caller
     * that reaches this UPDATE will see rowCount() === 1. Concurrent or
     * repeated calls for the same donation will see rowCount() === 0.
     *
     * @return array{row_count:int,receipt:string}
     */
    public function markSuccess(int $donationId, ?string $refId, ?string $trackId): array
    {
        $receipt = 'RC-' . str_pad((string) $donationId, 6, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare(
            "UPDATE panel_donations
                SET status = 'success', gateway_ref_id = :ref, gateway_track_id = :trk,
                    receipt_number = :rc, paid_at = UTC_TIMESTAMP()
              WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([':ref' => $refId, ':trk' => $trackId, ':rc' => $receipt, ':id' => $donationId]);
        return ['row_count' => $stmt->rowCount(), 'receipt' => $receipt];
    }

    /**
     * Atomically transition a donation from 'pending' to 'failed'.
     *
     * @return int Number of rows affected (0 = already processed or invalid).
     */
    public function markFailed(int $donationId, string $reason): int
    {
        $stmt = $this->db->prepare(
            "UPDATE panel_donations SET status = 'failed', failure_reason = :r
              WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([':r' => mb_substr($reason, 0, 255), ':id' => $donationId]);
        return $stmt->rowCount();
    }

    /**
     * Paginated history for a user, with optional free-text search over the
     * reference and (joined) campaign title.
     * @return array{items:list<array<string,mixed>>,total:int}
     */
    public function history(int $userId, int $page, int $perPage, string $search): array
    {
        $offset = ($page - 1) * $perPage;
        $like = '%' . $search . '%';
        $hasSearch = $search !== '';

        // Distinct placeholders (:q1/:q2) — native prepares forbid reusing :q.
        $where = 'd.user_id = :uid';
        if ($hasSearch) {
            $where .= ' AND (d.reference LIKE :q1 OR c.title LIKE :q2)';
        }

        $countSql = "SELECT COUNT(*) FROM panel_donations d LEFT JOIN campaigns c ON c.id = d.campaign_id WHERE {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        if ($hasSearch) {
            $countStmt->bindValue(':q1', $like, PDO::PARAM_STR);
            $countStmt->bindValue(':q2', $like, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT d.reference, d.amount, d.type, d.status, d.receipt_number,
                       d.created_at, d.paid_at, c.title AS campaign_title
                  FROM panel_donations d
             LEFT JOIN campaigns c ON c.id = d.campaign_id
                 WHERE {$where}
              ORDER BY d.created_at DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        if ($hasSearch) {
            $stmt->bindValue(':q1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = array_map(static fn(array $r) => [
            'reference'      => $r['reference'],
            'amount'         => (int) $r['amount'],
            'type'           => $r['type'],
            'status'         => $r['status'],
            'receipt_number' => $r['receipt_number'],
            'campaign_title' => $r['campaign_title'],
            'created_at'     => $r['created_at'],
            'paid_at'        => $r['paid_at'],
        ], $stmt->fetchAll());

        return ['items' => $items, 'total' => $total];
    }

    /** Aggregate KPIs + 6-month series for the dashboard. @return array<string,mixed> */
    public function dashboardStats(int $userId): array
    {
        $totals = $this->db->prepare(
            "SELECT
                COALESCE(SUM(amount), 0)                              AS total_amount,
                COUNT(*)                                             AS donation_count,
                COUNT(DISTINCT campaign_id)                          AS campaigns_supported,
                MIN(created_at)                                      AS first_donation_at
             FROM panel_donations WHERE user_id = :uid AND status = 'success'"
        );
        $totals->execute([':uid' => $userId]);
        $agg = $totals->fetch() ?: [];

        // Monthly sums for the last 6 calendar months (UTC).
        $series = $this->db->prepare(
            "SELECT DATE_FORMAT(paid_at, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS amount
               FROM panel_donations
              WHERE user_id = :uid AND status = 'success'
                AND paid_at >= (UTC_TIMESTAMP() - INTERVAL 6 MONTH)
              GROUP BY ym ORDER BY ym ASC"
        );
        $series->execute([':uid' => $userId]);

        $daysSupporting = 0;
        if (!empty($agg['first_donation_at'])) {
            $daysSupporting = max(0, (int) floor((time() - strtotime((string) $agg['first_donation_at'])) / 86400));
        }

        return [
            'total_amount'        => (int) ($agg['total_amount'] ?? 0),
            'donation_count'      => (int) ($agg['donation_count'] ?? 0),
            'campaigns_supported' => (int) ($agg['campaigns_supported'] ?? 0),
            'days_supporting'     => $daysSupporting,
            'monthly_series'      => array_map(static fn(array $r) => [
                'month'  => $r['ym'],
                'amount' => (int) $r['amount'],
            ], $series->fetchAll()),
        ];
    }

    /** Recent activity feed for the dashboard. @return list<array<string,mixed>> */
    public function recentActivity(int $userId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.reference, d.amount, d.status, d.created_at, c.title AS campaign_title
               FROM panel_donations d
          LEFT JOIN campaigns c ON c.id = d.campaign_id
              WHERE d.user_id = :uid
           ORDER BY d.created_at DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(static fn(array $r) => [
            'reference'      => $r['reference'],
            'amount'         => (int) $r['amount'],
            'status'         => $r['status'],
            'campaign_title' => $r['campaign_title'],
            'created_at'     => $r['created_at'],
        ], $stmt->fetchAll());
    }
}
