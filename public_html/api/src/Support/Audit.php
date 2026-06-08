<?php

declare(strict_types=1);

namespace Maksa\Support;

use Maksa\Core\Database;

/** Writes structured rows to `audit_log`. Never pass secrets in $metadata. */
final class Audit
{
    /** @param array<string,mixed> $metadata */
    public static function log(?int $userId, string $action, string $ip, string $userAgent, array $metadata = []): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO audit_log (user_id, action, ip, user_agent, metadata)
                 VALUES (:uid, :action, :ip, :ua, :meta)'
            );
            $stmt->execute([
                ':uid'    => $userId,
                ':action' => $action,
                ':ip'     => $ip,
                ':ua'     => $userAgent !== '' ? $userAgent : null,
                ':meta'   => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            // Auditing must never break the request flow.
            Logger::warning('Audit write failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
