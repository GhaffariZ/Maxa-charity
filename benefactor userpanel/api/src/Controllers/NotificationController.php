<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Repositories\NotificationRepository;

final class NotificationController
{
    // ---- GET /notifications -------------------------------------------------
    public function index(Request $request): void
    {
        $repo = new NotificationRepository();
        Response::success([
            'notifications' => $repo->listForUser($request->userId()),
            'unread'        => $repo->unreadCount($request->userId()),
        ]);
    }

    // ---- POST /notifications/{id}/read -------------------------------------
    public function markRead(Request $request): void
    {
        (new NotificationRepository())->markRead($request->userId(), (int) ($request->params['id'] ?? 0));
        Response::success(['message' => 'خوانده شد.']);
    }

    // ---- POST /notifications/read-all --------------------------------------
    public function markAllRead(Request $request): void
    {
        (new NotificationRepository())->markAllRead($request->userId());
        Response::success(['message' => 'همهٔ اعلان‌ها خوانده شد.']);
    }
}
