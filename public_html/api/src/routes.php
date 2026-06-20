<?php

declare(strict_types=1);

/**
 * Route table. Returns a closure that registers every route on the Router.
 * Paths are declared WITHOUT the /api prefix (the front controller strips it).
 * Guarded routes pass the AuthMiddleware instance as the last argument.
 */

use Maksa\Auth\AuthMiddleware;
use Maksa\Controllers\AuthController;
use Maksa\Controllers\CampaignController;
use Maksa\Controllers\DonationController;
use Maksa\Controllers\EngagementController;
use Maksa\Controllers\HealthController;
use Maksa\Controllers\NotificationController;
use Maksa\Controllers\TicketController;
use Maksa\Controllers\UserController;
use Maksa\Core\Router;

return static function (Router $r): void {
    $auth = [new AuthMiddleware()];

    // ---- Health -------------------------------------------------------------
    $r->get('/', [HealthController::class, 'ping']);
    $r->get('/health', [HealthController::class, 'ping']);

    // ---- Auth (public) ------------------------------------------------------
    $r->post('/auth/register',            [AuthController::class, 'register']);
    $r->post('/auth/verify-email',        [AuthController::class, 'verifyEmail']);
    $r->post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
    $r->post('/auth/login',               [AuthController::class, 'login']);
    $r->post('/auth/refresh',             [AuthController::class, 'refresh']);
    $r->post('/auth/logout',              [AuthController::class, 'logout']);
    $r->post('/auth/forgot-password',     [AuthController::class, 'forgotPassword']);
    $r->post('/auth/reset-password',      [AuthController::class, 'resetPassword']);
    // Authenticated:
    $r->post('/auth/change-password',     [AuthController::class, 'changePassword'], $auth);

    // ---- User (authenticated) ----------------------------------------------
    $r->get('/user/me',                   [UserController::class, 'me'], $auth);
    $r->patch('/user/me',                 [UserController::class, 'updateMe'], $auth);
    $r->delete('/user/me',                [UserController::class, 'deleteMe'], $auth);
    $r->post('/user/me/avatar',           [UserController::class, 'uploadAvatar'], $auth);
    $r->get('/user/notification-prefs',   [UserController::class, 'getPrefs'], $auth);
    $r->add('PUT', '/user/notification-prefs', [UserController::class, 'updatePrefs'], $auth);
    $r->get('/user/dashboard',            [UserController::class, 'dashboard'], $auth);
    // Public avatar serving (path is unguessable; route validates the filename).
    $r->get('/user/avatar/{file}',        [UserController::class, 'serveAvatar']);

    // ---- Campaigns ----------------------------------------------------------
    $r->get('/campaigns',                 [CampaignController::class, 'index']);
    $r->post('/campaigns/suggest',        [CampaignController::class, 'suggest'], $auth);
    $r->get('/campaigns/{slug}',          [CampaignController::class, 'show']);

    // ---- Donations ----------------------------------------------------------
    $r->post('/donations',                [DonationController::class, 'create'], $auth);
    $r->get('/donations',                 [DonationController::class, 'history'], $auth);
    // Public gateway return — MUST be registered before /donations/{reference}.
    $r->get('/donations/callback',        [DonationController::class, 'callback']);
    $r->get('/donations/{reference}',     [DonationController::class, 'status'], $auth);

    // ---- Notifications ------------------------------------------------------
    $r->get('/notifications',             [NotificationController::class, 'index'], $auth);
    $r->post('/notifications/read-all',   [NotificationController::class, 'markAllRead'], $auth);
    $r->post('/notifications/{id}/read',  [NotificationController::class, 'markRead'], $auth);

    // ---- Engagement ---------------------------------------------------------
    $r->post('/tax-certificate',          [EngagementController::class, 'requestTaxCertificate'], $auth);

    // ---- Support Tickets (authenticated) ------------------------------------
    $r->get('/tickets',                   [TicketController::class, 'index'],  $auth);
    $r->post('/tickets',                  [TicketController::class, 'create'], $auth);
    // NOTE: /tickets/{id}/reply must be registered before /tickets/{id} so the
    // router matches the more-specific path first.
    $r->post('/tickets/{id}/reply',       [TicketController::class, 'reply'],  $auth);
    $r->get('/tickets/{id}',              [TicketController::class, 'show'],   $auth);
};
