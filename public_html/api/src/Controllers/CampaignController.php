<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\CampaignRepository;
use Maksa\Repositories\EngagementRepository;
use Maksa\Support\Audit;

final class CampaignController
{
    // ---- GET /campaigns?status=active|completed -----------------------------
    public function index(Request $request): void
    {
        $status = $request->query['status'] ?? null;
        if ($status !== null && !in_array($status, ['active', 'completed'], true)) {
            $status = null;
        }
        Response::success(['campaigns' => (new CampaignRepository())->list($status)]);
    }

    // ---- GET /campaigns/{slug} ---------------------------------------------
    public function show(Request $request): void
    {
        $campaign = (new CampaignRepository())->findBySlug($request->params['slug'] ?? '');
        if ($campaign === null) {
            throw ApiException::notFound('کمپین یافت نشد.');
        }
        Response::success(['campaign' => $campaign]);
    }

    // ---- POST /campaigns/suggest  (authenticated) ---------------------------
    public function suggest(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('title', max: 200, required: false)
            ->string('description', min: 10, max: 2000)
            ->string('contact', max: 255, required: false)
            ->validated();

        $id = (new EngagementRepository())->createSuggestion(
            $request->userId(),
            $data['title'] ?? null,
            $data['description'],
            $data['contact'] ?? null,
        );
        Audit::log($request->userId(), 'campaign_suggested', $request->ip(), $request->userAgent(), ['id' => $id]);

        Response::success(['message' => 'پیشنهاد شما ثبت شد و پس از بررسی با شما در میان گذاشته می‌شود.'], 201);
    }
}
