<?php

declare(strict_types=1);

namespace Maksa\Services\Gateway;

/** Outcome of a gateway verification. */
final class GatewayResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $refId = null,      // bank reference / RRN
        public readonly ?string $trackId = null,
        public readonly ?string $failureReason = null,
    ) {}

    public static function ok(?string $refId, ?string $trackId = null): self
    {
        return new self(true, $refId, $trackId, null);
    }

    public static function failed(string $reason): self
    {
        return new self(false, null, null, $reason);
    }
}
