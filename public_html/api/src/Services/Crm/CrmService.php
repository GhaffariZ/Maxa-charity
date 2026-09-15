<?php

declare(strict_types=1);

namespace Maksa\Services\Crm;

use Maksa\Core\Config;
use Maksa\Core\Database;
use Maksa\Services\Crm\Drivers\StubCrmDriver;
use Maksa\Support\Audit;
use Maksa\Support\Logger;
use PDO;

final class CrmService
{
    private CrmDriverInterface $driver;
    private PDO $db;

    public function __construct(?CrmDriverInterface $driver = null)
    {
        $this->db     = Database::connection();
        $this->driver = $driver ?? self::resolveDriver();
    }

    public static function resolveDriver(): CrmDriverInterface
    {
        $name = strtolower((string) Config::get('CRM_DRIVER', 'stub'));
        return match ($name) {
            default => new StubCrmDriver(),
        };
    }

    /**
     * Creates or updates the donor in the CRM system, then records crm_id in user_profiles.
     *
     * @param int $userId
     * @param array{
     *     first_name?: string|null,
     *     last_name?: string|null,
     *     phone: string,
     *     national_code?: string|null,
     *     email?: string|null
     * } $donorData
     * @return string|null The CRM identifier
     */
    public function syncDonor(int $userId, array $donorData): ?string
    {
        try {
            $crmId = $this->driver->createOrUpdateDonor($donorData);
            if ($crmId !== null && $crmId !== '') {
                $this->db->prepare(
                    'UPDATE user_profiles
                        SET crm_id = :crm_id,
                            crm_synced_at = UTC_TIMESTAMP()
                      WHERE user_id = :uid'
                )->execute([
                    ':crm_id' => $crmId,
                    ':uid'    => $userId,
                ]);

                Audit::log($userId, 'crm_donor_synced', null, null, ['crm_id' => $crmId]);
            }
            return $crmId;
        } catch (\Throwable $e) {
            Logger::error('CRM sync failed: ' . $e->getMessage(), ['user_id' => $userId]);
            // Non-blocking: failure to sync CRM should not abort the user flow
            return null;
        }
    }

    /**
     * Records a donation in the CRM system.
     *
     * @param int $userId
     * @param array{
     *     amount: int,
     *     reference: string,
     *     campaign_slug?: string|null,
     *     status: string,
     *     paid_at?: string|null
     * } $donationData
     */
    public function syncDonation(int $userId, array $donationData): bool
    {
        try {
            $stmt = $this->db->prepare('SELECT crm_id FROM user_profiles WHERE user_id = :uid LIMIT 1');
            $stmt->execute([':uid' => $userId]);
            $crmId = (string) $stmt->fetchColumn();

            if ($crmId === '') {
                return false;
            }

            return $this->driver->recordDonation($crmId, $donationData);
        } catch (\Throwable $e) {
            Logger::error('CRM donation sync failed: ' . $e->getMessage(), ['user_id' => $userId]);
            return false;
        }
    }

    public function driverName(): string
    {
        return $this->driver->driverName();
    }
}
