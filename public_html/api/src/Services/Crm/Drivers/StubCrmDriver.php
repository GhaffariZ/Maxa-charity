<?php

declare(strict_types=1);

namespace Maksa\Services\Crm\Drivers;

use Maksa\Services\Crm\CrmDriverInterface;
use Maksa\Support\Logger;

/**
 * Default CRM stub driver.
 * Logs all operations and returns deterministic mock CRM IDs.
 * Allows the charity to easily replace this with an actual CRM API later.
 */
final class StubCrmDriver implements CrmDriverInterface
{
    public function createOrUpdateDonor(array $donorData): ?string
    {
        $phone = (string) ($donorData['phone'] ?? 'unknown');
        $name  = trim(($donorData['first_name'] ?? '') . ' ' . ($donorData['last_name'] ?? ''));
        $mockId = 'CRM-' . strtoupper(substr(md5($phone), 0, 8));

        Logger::info("CRM [Stub]: Synced donor account {$phone} ({$name}) -> Assigned {$mockId}");
        error_log("[CRM STUB] Created/Updated donor: {$name} [{$phone}] -> {$mockId}");

        return $mockId;
    }

    public function recordDonation(string $crmContactId, array $donationData): bool
    {
        $amount = $donationData['amount'] ?? 0;
        $ref    = $donationData['reference'] ?? 'unknown';

        Logger::info("CRM [Stub]: Recorded donation {$ref} of {$amount} Toman for contact {$crmContactId}");
        error_log("[CRM STUB] Donation {$ref} ({$amount} Toman) logged under contact {$crmContactId}");

        return true;
    }

    public function driverName(): string
    {
        return 'stub';
    }
}
