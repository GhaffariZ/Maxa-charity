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
     * Creates a pending local receipt and starts the authoritative Campaign
     * Service payment for the headquarters Center.
     * @return array{reference:string,redirect_url:string,redirect_method:string,redirect_fields:array<string,string>}
     */
    public function start(int $userId, ?string $campaignSlug, int $amountToman, array $metadata = []): array
    {
        if ($amountToman < self::MIN_TOMAN || $amountToman > self::MAX_TOMAN) {
            throw ApiException::badRequest('مبلغ واردشده مجاز نیست.', 'invalid_amount');
        }

        $client = $this->campaignClient();
        if ($campaignSlug === null || $campaignSlug === '') {
            \campaign_service_load_env();
            $campaignSlug = \campaign_service_env('CAMPAIGN_HQ_ONLINE_DONATION_CAMPAIGN_ID');
        }
        $campaign = $this->campaigns->findBySlug($campaignSlug);
        if ($campaign === null || $campaign['status'] !== 'active') {
            throw ApiException::badRequest('کمپین انتخاب‌شده نامعتبر است.', 'invalid_campaign');
        }

        $profile = (new ProfileRepository())->fullProfile($userId);
        $mobile = trim((string)($profile['phone'] ?? ''));
        if ($mobile === '') {
            throw ApiException::badRequest('برای پرداخت، ابتدا شماره همراه خود را در پروفایل ثبت کنید.', 'phone_required');
        }
        $fullName = trim(implode(' ', array_filter([
            (string)($profile['first_name'] ?? ''),
            (string)($profile['last_name'] ?? ''),
        ])));

        [$donationId, $reference] = $this->donations->createPending($userId, null, $amountToman, 'campaign_service');

        try {
            $payment = $client->startPayment((string)$campaign['id'], $mobile, $fullName, $amountToman * 10, 'macsa:panel:' . $reference);
            $paymentId = (string)($payment['id'] ?? '');
            if ($paymentId === '' || empty($payment['redirect_url'])) {
                throw new \RuntimeException('Payment redirect data is missing.');
            }
            $this->donations->attachCampaignServicePayment(
                $donationId,
                $paymentId,
                (string)$campaign['id'],
                (string)$campaign['title']
            );
        } catch (\Throwable $e) {
            $this->donations->markFailed($donationId, 'شروع پرداخت در کمپین‌ساز ناموفق بود.');
            $message = $e instanceof \CampaignServiceException
                ? $e->getMessage()
                : 'اتصال به درگاه پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.';
            throw ApiException::badRequest($message, 'gateway_unavailable');
        }

        return [
            'reference' => $reference,
            'redirect_url' => (string)($payment['redirect_url'] ?? ''),
            'redirect_method' => strtoupper((string)($payment['redirect_method'] ?? 'GET')),
            'redirect_fields' => is_array($payment['redirect_fields'] ?? null) ? $payment['redirect_fields'] : [],
        ];
    }

    /** @return array<string,mixed>|null */
    public function syncStatus(int $userId, string $reference): ?array
    {
        $donation = $this->donations->findForUserByReference($userId, $reference);
        if ($donation === null || $donation['gateway'] !== 'campaign_service' || $donation['status'] !== 'pending') {
            return $donation;
        }
        $paymentId = (string)($donation['gateway_authority'] ?? '');
        if ($paymentId === '') {
            return $donation;
        }

        $remote = $this->campaignClient()->paymentStatus($paymentId);
        $remoteStatus = strtoupper((string)($remote['status'] ?? 'PENDING'));
        if ($remoteStatus === 'SUCCEEDED') {
            $amount = (int)$donation['amount'];
            $points = (int)floor($amount * self::POINTS_PER_TOMAN);
            Database::transaction(function () use ($donation, $userId, $amount, $points, $paymentId): void {
                $marked = $this->donations->markSuccess((int)$donation['id'], $paymentId, null);
                if ($marked['row_count'] !== 1) {
                    return;
                }
                if ($points > 0) {
                    (new ProfileRepository())->addPointsAndRecomputeTier($userId, $points);
                }
                (new NotificationRepository())->create(
                    $userId,
                    'donation_success',
                    'پرداخت شما با موفقیت انجام شد',
                    'از مشارکت شما سپاسگزاریم. رسید این کمک در بخش تاریخچه در دسترس است.',
                    '/history'
                );
            });
        } elseif (in_array($remoteStatus, ['FAILED', 'CANCELED', 'CANCELLED'], true)) {
            $this->donations->markFailed((int)$donation['id'], 'پرداخت در درگاه ناموفق یا لغو شد.');
        }
        return $this->donations->findForUserByReference($userId, $reference);
    }

    private function campaignClient(): \CampaignServiceClient
    {
        if (!class_exists('\CampaignServiceClient', false)) {
            foreach ([dirname(__DIR__, 3) . '/core/campaign-service.php', dirname(__DIR__, 4) . '/public_html/core/campaign-service.php'] as $path) {
                if (is_file($path)) {
                    require_once $path;
                    break;
                }
            }
        }
        if (!class_exists('\CampaignServiceClient', false)) {
            throw new \RuntimeException('Campaign Service adapter is not available.');
        }
        return new \CampaignServiceClient('hq');
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
        $gateway = GatewayFactory::make();
        $donation = $this->donations->findByAuthority($authority, $gateway->name());
        if ($donation === null) {
            return ['status' => 'failed', 'reference' => null];
        }
        $reference = (string) $donation['reference'];

        // Idempotency: if already finalized, don't double-process.
        if ($donation['status'] !== 'pending') {
            return ['status' => $donation['status'], 'reference' => $reference];
        }

        $amount = (int) $donation['amount'];
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
                    // Another callback won the atomic claim. Return the persisted
                    // final state so concurrent browser redirects remain idempotent
                    // and never expose an internal "duplicate" status to the UI.
                    return $this->donations->statusById($donationId) ?? 'failed';
                }

                // Side-effect: bump campaign collected_amount.
                if ($campaignId !== null) {
                    $this->campaigns->applySuccessfulDonation($campaignId, $amount);
                }

                // Side-effect: award kindness points + tier recomputation.
                if ($userId !== null && $points > 0) {
                    (new ProfileRepository())->addPointsAndRecomputeTier($userId, $points);
                }

                // Side-effect: send success notification and sync with CRM.
                if ($userId !== null) {
                    (new NotificationRepository())->create(
                        $userId,
                        'donation_success',
                        'پرداخت شما با موفقیت انجام شد',
                        'از مشارکت شما سپاسگزاریم. رسید این کمک در بخش تاریخچه در دسترس است.',
                        '/history'
                    );

                    (new \Maksa\Services\Crm\CrmService())->syncDonation($userId, [
                        'amount'    => $amount,
                        'reference' => $reference,
                        'status'    => 'success',
                        'paid_at'   => gmdate('Y-m-d H:i:s'),
                    ]);
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
