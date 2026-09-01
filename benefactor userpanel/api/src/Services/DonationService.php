<?php

declare(strict_types=1);

namespace Maksa\Services;

use Maksa\Core\Config;
use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Repositories\CampaignRepository;
use Maksa\Repositories\DonationRepository;
use Maksa\Repositories\NotificationRepository;
use Maksa\Repositories\ProfileRepository;
use Maksa\Services\Gateway\GatewayFactory;
use Maksa\Services\Gateway\GatewayResult;

/**
 * Orchestrates the donation lifecycle across the gateway and the database.
 * Controllers stay thin; all the money rules live here.
 */
final class DonationService
{
    // 1 kindness point per 1,000 Toman donated.
    private const POINTS_PER_TOMAN = 1 / 1000;
    private const MIN_TOMAN = 1000;
    private const MAX_TOMAN = 500000000; // 500M Toman sanity ceiling

    private DonationRepository $donations;
    private CampaignRepository $campaigns;

    public function __construct()
    {
        $this->donations = new DonationRepository();
        $this->campaigns = new CampaignRepository();
    }

    /**
     * Creates a pending donation and asks the gateway for a redirect URL.
     * @return array{reference:string,redirect_url:string}
     */
    public function start(int $userId, ?string $campaignSlug, int $amountToman): array
    {
        if ($amountToman < self::MIN_TOMAN || $amountToman > self::MAX_TOMAN) {
            throw ApiException::badRequest('مبلغ واردشده مجاز نیست.', 'invalid_amount');
        }

        $campaignId = null;
        if ($campaignSlug !== null && $campaignSlug !== '') {
            $campaign = $this->campaigns->findBySlug($campaignSlug);
            if ($campaign === null || $campaign['status'] === 'draft' || $campaign['status'] === 'archived') {
                throw ApiException::badRequest('کمپین انتخاب‌شده نامعتبر است.', 'invalid_campaign');
            }
            $campaignId = $campaign['id'];
        }

        $gateway = GatewayFactory::make();
        [$donationId, $reference] = $this->donations->createPending($userId, $campaignId, $amountToman, $gateway->name());

        $callbackUrl = rtrim((string) Config::get('APP_URL', ''), '/');
        // Gateway returns the browser to this API endpoint, which then redirects
        // into the SPA. APP_URL is the SPA base; derive the same-origin API root.
        $origin = self::originOf((string) Config::get('APP_URL', ''));
        $callback = $origin . '/api/donations/callback';

        try {
            $result = $gateway->request($amountToman, $reference, $callback, 'کمک به ' . ($campaignSlug ?? 'صندوق عمومی'));
        } catch (\Throwable $e) {
            $this->donations->markFailed($donationId, 'اتصال به درگاه ناموفق بود.');
            throw ApiException::badRequest('اتصال به درگاه پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.', 'gateway_unavailable');
        }

        $this->donations->setAuthority($donationId, $result['authority']);

        return ['reference' => $reference, 'redirect_url' => $result['redirect_url']];
    }

    /**
     * Verifies a gateway callback. Returns the final status string and the
     * donation reference so the caller can redirect the browser appropriately.
     *
     * Concurrency model:
     *  1. READ the donation (no lock needed — we just check the current state).
     *  2. Verify with the gateway (external HTTP — keep outside any transaction).
     *  3. CLAIM the donation inside a short DB transaction using an atomic
     *     UPDATE … WHERE status = 'pending'.  Only the first concurrent
     *     request that reaches this UPDATE will see rowCount() === 1 and
     *     become responsible for all side effects (campaign total, points,
     *     notifications).  Any subsequent concurrent or duplicate request
     *     sees rowCount() === 0 and returns immediately.
     *
     * @param array<string,string> $params
     * @return array{status:string,reference:?string}
     */
    public function handleCallback(string $authority, array $params): array
    {
        $donation = $this->donations->findByAuthority($authority);
        if ($donation === null) {
            return ['status' => 'failed', 'reference' => null];
        }
        $reference = (string) $donation['reference'];

        // Idempotency: if already finalized, don't double-process.
        if ($donation['status'] !== 'pending') {
            return ['status' => $donation['status'], 'reference' => $reference];
        }

        $amount = (int) $donation['amount'];
        $gateway = GatewayFactory::make();
        $result = $gateway->verify($authority, $params, $amount);

        if (!$result->success) {
            // Mark failed — the atomic WHERE ensures only one request transitions.
            $this->donations->markFailed((int) $donation['id'], $result->failureReason ?? 'پرداخت ناموفق بود.');
            return ['status' => 'failed', 'reference' => $reference];
        }

        // ── Atomic claim + side-effects inside a single transaction ──────
        $donationId = (int) $donation['id'];
        $campaignId = $donation['campaign_id'] !== null ? (int) $donation['campaign_id'] : null;
        $userId     = $donation['user_id'] !== null ? (int) $donation['user_id'] : null;
        $points     = (int) floor($amount * self::POINTS_PER_TOMAN);

        try {
            $finalStatus = Database::transaction(function () use (
                $donationId, $campaignId, $userId, $amount, $points, $result
            ): string {
                // Atomic claim: only one concurrent request sees rowCount === 1.
                $mark = $this->donations->markSuccess($donationId, $result->refId, $result->trackId);

                if ($mark['row_count'] !== 1) {
                    // Already processed by another concurrent request — rollback
                    // (nothing was written beyond the status flip which is harmless
                    // to re-apply, but rolling back keeps the transaction atomic).
                    return 'duplicate';
                }

                // Side-effect: bump campaign collected_amount.
                if ($campaignId !== null) {
                    $this->campaigns->applySuccessfulDonation($campaignId, $amount);
                }

                // Side-effect: award kindness points + tier recomputation.
                if ($userId !== null && $points > 0) {
                    (new ProfileRepository())->addPointsAndRecomputeTier($userId, $points);
                }

                // Side-effect: send success notification.
                if ($userId !== null) {
                    (new NotificationRepository())->create(
                        $userId,
                        'donation_success',
                        'پرداخت شما با موفقیت انجام شد',
                        'از مشارکت شما سپاسگزاریم. رسید این کمک در بخش تاریخچه در دسترس است.',
                        '/history'
                    );
                }

                return 'success';
            });
        } catch (\Throwable $e) {
            // Transaction failed or was rolled back — donation stays 'pending'
            // and can be retried.  Log for visibility.
            error_log('[DonationService] Callback transaction failed: ' . $e->getMessage());
            return ['status' => 'failed', 'reference' => $reference];
        }

        return ['status' => $finalStatus, 'reference' => $reference];
    }

    private static function originOf(string $url): string
    {
        $parts = parse_url($url);
        if (!isset($parts['scheme'], $parts['host'])) {
            return rtrim($url, '/');
        }
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        return $origin;
    }
}
