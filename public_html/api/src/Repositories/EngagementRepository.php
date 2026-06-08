<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

/** Campaign suggestions + tax-certificate requests. */
final class EngagementRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function createSuggestion(?int $userId, ?string $title, string $description, ?string $contact): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO campaign_suggestions (user_id, title, description, contact)
             VALUES (:uid, :title, :desc, :contact)'
        );
        $stmt->execute([':uid' => $userId, ':title' => $title, ':desc' => $description, ':contact' => $contact]);
        return (int) $this->db->lastInsertId();
    }

    public function createTaxCertificateRequest(int $userId, ?int $yearJalali, ?string $note): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tax_certificate_requests (user_id, year_jalali, note)
             VALUES (:uid, :year, :note)'
        );
        $stmt->execute([':uid' => $userId, ':year' => $yearJalali, ':note' => $note]);
        return (int) $this->db->lastInsertId();
    }

    public function hasOpenTaxRequest(int $userId, ?int $yearJalali): bool
    {
        // Distinct :year_null flag — native prepares forbid reusing :year.
        $stmt = $this->db->prepare(
            "SELECT 1 FROM tax_certificate_requests
              WHERE user_id = :uid AND status IN ('requested','processing')
                AND (:year_null = 1 OR year_jalali = :year)
              LIMIT 1"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':year', $yearJalali, $yearJalali === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':year_null', $yearJalali === null ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }
}
