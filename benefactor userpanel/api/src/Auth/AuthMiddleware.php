<?php

declare(strict_types=1);

namespace Maksa\Auth;

use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Repositories\UserRepository;

/**
 * Route middleware: requires a valid Bearer access token and an active account.
 * Attaches the user id to the request. Use as: $router->get(..., [Auth::class]).
 */
final class AuthMiddleware
{
    public function __invoke(Request $request): void
    {
        $token = $request->bearerToken();
        if ($token === null) {
            throw ApiException::unauthorized('توکن دسترسی ارسال نشده است.', 'missing_token');
        }

        $payload = Jwt::verify($token); // throws on invalid/expired
        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            throw ApiException::unauthorized('توکن نامعتبر است.', 'invalid_token');
        }

        $user = (new UserRepository())->findById($userId);
        if ($user === null) {
            throw ApiException::unauthorized('کاربر یافت نشد.', 'invalid_token');
        }
        if ($user['status'] === 'suspended') {
            throw ApiException::forbidden('حساب کاربری شما مسدود شده است.', 'account_suspended');
        }

        $request->authUserId = $userId;
    }
}
