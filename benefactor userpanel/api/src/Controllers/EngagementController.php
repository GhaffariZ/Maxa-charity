<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\EngagementRepository;
use Maksa\Support\Audit;

final class EngagementController
{
    // ---- POST /tax-certificate  (authenticated) -----------------------------
    public function requestTaxCertificate(Request $request): void
    {
        $data = (new Validator($request->body))
            ->int('year_jalali', min: 1380, max: 1500, required: false)
            ->string('note', max: 1000, required: false)
            ->validated();

        $repo = new EngagementRepository();
        $year = $data['year_jalali'] ?? null;
        if ($repo->hasOpenTaxRequest($request->userId(), $year)) {
            throw ApiException::conflict('یک درخواست باز برای این دوره از قبل ثبت شده است.', 'duplicate_request');
        }

        $id = $repo->createTaxCertificateRequest($request->userId(), $year, $data['note'] ?? null);
        Audit::log($request->userId(), 'tax_certificate_requested', $request->ip(), $request->userAgent(), ['id' => $id]);

        Response::success(['message' => 'درخواست صدور گواهی ثبت شد و پس از بررسی برای شما ارسال می‌شود.'], 201);
    }
}
