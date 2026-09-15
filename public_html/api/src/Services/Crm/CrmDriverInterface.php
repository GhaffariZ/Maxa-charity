<?php

declare(strict_types=1);

namespace Maksa\Services\Crm;

interface CrmDriverInterface
{
    /**
     * Creates or updates a donor contact/lead in the CRM system.
     *
     * @param array{
     *     first_name?: string|null,
     *     last_name?: string|null,
     *     phone: string,
     *     national_code?: string|null,
     *     email?: string|null
     * } $donorData
     * @return string|null CRM Contact / Lead ID if created or synced
     */
    public function createOrUpdateDonor(array $donorData): ?string;

    /**
     * Records a donation activity or deal under the donor's CRM record.
     *
     * @param string $crmContactId
     * @param array{
     *     amount: int,
     *     reference: string,
     *     campaign_slug?: string|null,
     *     status: string,
     *     paid_at?: string|null
     * } $donationData
     * @return bool
     */
    public function recordDonation(string $crmContactId, array $donationData): bool;

    /**
     * Identifies the CRM driver name (e.g. 'stub', 'sarvcrm', 'didar').
     */
    public function driverName(): string;
}
