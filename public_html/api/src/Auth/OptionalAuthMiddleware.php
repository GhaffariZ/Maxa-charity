<?php

declare(strict_types=1);

namespace Maksa\Auth;

use Maksa\Core\Request;
use Maksa\Repositories\UserRepository;
use Throwable;

/**
 * Optional authentication middleware:
 * If a valid Bearer token is provided, attaches authUserId to request.
 * If no token or invalid token, allows request to proceed as guest (authUserId = null).
 */
final class OptionalAuthMiddleware
{
    public function __invoke(Request $request): void
    {
        $token = $request->bearerToken();
        if ($token === null || trim($token) === '') {
            return;
        }

        try {
            $payload = Jwt::verify($token);
            $userId = (int) ($payload['sub'] ?? 0);
            if ($userId > 0) {
                $user = (new UserRepository())->findById($userId);
                if ($user !== null && ($user['status'] ?? '') !== 'suspended') {
                    $request->authUserId = $userId;
                }
            }
        } catch (Throwable $e) {
            // Silently treat as guest
        }
    }
}
