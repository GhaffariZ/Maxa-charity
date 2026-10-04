<?php

declare(strict_types=1);

namespace Maksa\Repositories;

/** Headquarters Campaign Service projection for the benefactor panel. */
final class CampaignRepository
{
    /** @return list<array<string,mixed>> */
    public function list(?string $status = null): array
    {
        $items = array_map([self::class, 'present'], $this->client()->publicCampaigns($status !== 'active'));
        if ($status === 'active') {
            return array_values(array_filter($items, static fn(array $item): bool => $item['status'] === 'active'));
        }
        if ($status === 'completed') {
            return array_values(array_filter($items, static fn(array $item): bool => $item['status'] === 'completed'));
        }
        return $items;
    }

    /** @return array<string,mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        self::loadAdapter();
        if (!\campaign_service_is_uuid($slug)) {
            return null;
        }
        try {
            return self::present($this->client()->publicCampaign($slug));
        } catch (\CampaignServiceException $e) {
            if ($e->status === 404) {
                return null;
            }
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    private static function present(array $row): array
    {
        $goal = intdiv((int)($row['goal_amount'] ?? $row['goalAmount'] ?? 0), 10);
        $raised = intdiv((int)($row['raised_amount'] ?? 0), 10);
        $effective = strtoupper((string)($row['effective_status'] ?? $row['status'] ?? 'INACTIVE'));
        $status = $effective === 'ACTIVE' ? 'active' : ($effective === 'ENDED' ? 'completed' : 'archived');
        $id = (string)($row['id'] ?? '');
        $daysLeft = null;
        $endsAt = $row['ends_at'] ?? $row['endsAt'] ?? null;
        if (is_string($endsAt) && $endsAt !== '') {
            $daysLeft = max(0, (int)ceil((strtotime($endsAt) - time()) / 86400));
        }

        \campaign_service_load_env();
        return [
            'id' => $id,
            'slug' => $id,
            'title' => (string)($row['title'] ?? ''),
            'description' => (string)($row['short_description'] ?? $row['shortDescription'] ?? $row['description'] ?? ''),
            'image_url' => $row['image_url'] ?? null,
            'category' => $row['category']['name'] ?? null,
            'goal_amount' => $goal,
            'raised_amount' => $raised,
            'donor_count' => (int)($row['donor_count'] ?? 0),
            'is_general_fund' => $id !== '' && hash_equals(\campaign_service_env('CAMPAIGN_HQ_ONLINE_DONATION_CAMPAIGN_ID'), $id),
            'status' => $status,
            'progress' => $goal > 0 ? min(100, (int)round($raised / $goal * 100)) : 0,
            'days_left' => $daysLeft,
        ];
    }

    private function client(): \CampaignServiceClient
    {
        self::loadAdapter();
        return new \CampaignServiceClient('hq');
    }

    private static function loadAdapter(): void
    {
        if (class_exists('\CampaignServiceClient', false)) {
            return;
        }
        foreach ([dirname(__DIR__, 3) . '/core/campaign-service.php', dirname(__DIR__, 4) . '/public_html/core/campaign-service.php'] as $path) {
            if (is_file($path)) {
                require_once $path;
                return;
            }
        }
        throw new \RuntimeException('Campaign Service adapter is not available.');
    }
}
