<?php

declare(strict_types=1);

namespace Maksa\Services\Gateway;

/**
 * Payment gateway contract. Implementations turn a pending donation into a
 * redirect to the bank, then verify the result on callback.
 *
 * Swapping gateways (stub → Zarinpal → IDPay …) means writing one class and
 * flipping PAYMENT_GATEWAY in .env — nothing else changes.
 */
interface PaymentGateway
{
    /**
     * Begin a payment. Returns where to send the user and the gateway's
     * reference ("authority") to persist on the donation.
     *
     * @param int    $amountToman  amount in Toman
     * @param string $reference    our public donation reference (MX-…)
     * @param string $callbackUrl  absolute URL the gateway returns the user to
     * @param string $description  short Persian description shown at the gateway
     * @return array{redirect_url:string,authority:string}
     */
    public function request(int $amountToman, string $reference, string $callbackUrl, string $description): array;

    /**
     * Verify a payment after the user returns. Implementations read whatever
     * query params the gateway sends ($callbackParams).
     *
     * @param array<string,string> $callbackParams
     * @param int                  $amountToman    expected amount, for cross-check
     * @return GatewayResult
     */
    public function verify(string $authority, array $callbackParams, int $amountToman): GatewayResult;

    public function name(): string;
}
