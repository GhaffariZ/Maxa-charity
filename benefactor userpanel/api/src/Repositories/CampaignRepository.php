<?php

declare(strict_types=1);

namespace Maksa\Repositories;

use Maksa\Core\Database;
use PDO;

/**
 * Reads the SITE's existing `campaigns` table (shared with the rest of the
 * erfantey_macsacharity site) and presents it in the shape the panel expects.
 *
 * The site table has no slug/status/donor_count columns, so we derive them:
 *   - slug         = campaign_code, else "c{id}" (opaque key used by the panel)
 *   - status       = archived (inactive) / completed (goal met) / active
 *   - donor_count  = COUNT of successful panel_donations for the campaign
 *   - days_left    = null (the site table has no end date)
 *
 * On a successful donation we bump the shared `collected_amount` so the main
 * site reflects panel contributions too.
 */
final class CampaignRepository
{
    private PDO $db;

    // Sub-select that counts this campaign's successful panel donations.
    private const DONOR_COUNT_SQL =
        '(SELECT COUNT(*) FROM panel_donations pd
           WHERE pd.campaign_id = c.id AND pd.status = \'success\') AS donor_count';

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Lists campaigns, optionally filtered by derived status ('active'|'completed').
     * @return list<array<string,mixed>>
     */
    public function list(?string $status = null): array
    {
        $where = '1=1';
        if ($status === 'active') {
            $where = "c.is_active = 1 AND (c.target_amount IS NULL OR c.target_amount = 0 OR c.collected_amount < c.target_amount)";
        } elseif ($status === 'completed') {
            $where = "c.target_amount > 0 AND c.collected_amount >= c.target_amount";
        }

        $sql = "SELECT c.*, " . self::DONOR_COUNT_SQL . "
                  FROM campaigns c
                 WHERE {$where}
              ORDER BY c.sort_order ASC, c.id ASC";

        return array_map([self::class, 'present'], $this->db->query($sql)->fetchAll());
    }

    /** Resolve by the panel's opaque key (campaign_code, "c{id}", or numeric id). */
    public function findBySlug(string $slug): ?array
    {
        $id = self::idFromSlug($slug);

        $stmt = $this->db->prepare(
            "SELECT c.*, " . self::DONOR_COUNT_SQL . "
               FROM campaigns c
              WHERE c.campaign_code = :slug OR c.id = :id
              LIMIT 1"
        );
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id ?? 0, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? self::present($row) : null;
    }

    /** Bumps the SHARED campaigns.collected_amount after a successful donation. */
    public function applySuccessfulDonation(int $campaignId, int $amountToman): void
    {
        $this->db->prepare(
            'UPDATE campaigns SET collected_amount = collected_amount + :amt WHERE id = :id'
        )->execute([':amt' => $amountToman, ':id' => $campaignId]);
    }

    /** Extracts a numeric id from "c{id}" or a bare number; null otherwise. */
    private static function idFromSlug(string $slug): ?int
    {
        if (preg_match('/^c?(\d+)$/', $slug, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /** Maps a site `campaigns` row into the panel's API representation. */
    private static function present(array $row): array
    {
        $goal     = (int) ($row['target_amount'] ?? 0);
        $raised   = (int) ($row['collected_amount'] ?? 0);
        $progress = $goal > 0 ? min(100, (int) round($raised / $goal * 100)) : 0;

        $isActive = (int) ($row['is_active'] ?? 0) === 1;
        if (!$isActive) {
            $status = 'archived';
        } elseif ($goal > 0 && $raised >= $goal) {
            $status = 'completed';
        } else {
            $status = 'active';
        }

        $code = isset($row['campaign_code']) && $row['campaign_code'] !== ''
            ? (string) $row['campaign_code']
            : 'c' . (int) $row['id'];

        // The site separates a short tagline from the long body; prefer the
        // tagline for cards, fall back to the longer fields.
        $description = $row['short_description'] ?: ($row['description'] ?: ($row['details'] ?? null));

        return [
            'id'              => (int) $row['id'],
            'slug'            => $code,
            'title'           => $row['title'],
            'description'     => $description,
            'image_url'       => $row['image_url'],
            'category'        => null, // site table has no category/tag column
            'goal_amount'     => $goal,
            'raised_amount'   => $raised,
            'donor_count'     => (int) ($row['donor_count'] ?? 0),
            'is_general_fund' => mb_strpos((string) $row['title'], 'صندوق عمومی') !== false,
            'status'          => $status,
            'progress'        => $progress,
            'days_left'       => null,
        ];
    }
}
