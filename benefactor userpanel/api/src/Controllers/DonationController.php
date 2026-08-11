<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\DonationRepository;
use Maksa\Services\DonationService;
use Maksa\Support\Audit;

final class DonationController
{
    // ---- POST /donations  (authenticated) -----------------------------------
    public function create(Request $request): void
    {
        $data = (new Validator($request->body))
            ->int('amount', min: 1000, max: 500000000)
            ->string('campaign_slug', max: 120, required: false)
            ->validated();

        $result = (new DonationService())->start(
            $request->userId(),
            $data['campaign_slug'] ?? null,
            $data['amount'],
        );
        Audit::log($request->userId(), 'donation_started', $request->ip(), $request->userAgent(), [
            'reference' => $result['reference'],
            'amount'    => $data['amount'],
        ]);

        // The frontend redirects the browser to redirect_url (the bank gateway).
        Response::success($result, 201);
    }

    // ---- GET /donations/callback  (public — the gateway returns here) -------
    // This is a browser redirect target, NOT a JSON endpoint. It verifies the
    // payment then 302-redirects back into the SPA history page with a status.
    public function callback(Request $request): void
    {
        // Different gateways use different param names; pass them all through.
        $authority = (string) ($request->query['authority'] ?? $request->query['Authority'] ?? '');
        $outcome = ['status' => 'failed', 'reference' => null];

        if ($authority !== '') {
            $params = array_map(static fn($v) => is_string($v) ? $v : '', $_GET);
            $outcome = (new DonationService())->handleCallback($authority, $params);
        }

        $base = rtrim((string) Config::get('APP_URL', ''), '/');
        $query = http_build_query(array_filter([
            'payment' => $outcome['status'],
            'ref'     => $outcome['reference'],
        ]));
        $location = $base . '/history?' . $query;

        if (!headers_sent()) {
            header('Location: ' . $location, true, 302);
        }
        exit;
    }

    // ---- GET /donations/{reference}  (authenticated) ------------------------
    public function status(Request $request): void
    {
        $donation = (new DonationRepository())->findForUserByReference(
            $request->userId(),
            $request->params['reference'] ?? ''
        );
        if ($donation === null) {
            throw ApiException::notFound('تراکنش یافت نشد.');
        }
        Response::success([
            'reference'      => $donation['reference'],
            'amount'         => (int) $donation['amount'],
            'status'         => $donation['status'],
            'receipt_number' => $donation['receipt_number'],
            'paid_at'        => $donation['paid_at'],
        ]);
    }

    // ---- GET /donations?page=&per_page=&q=  (history, authenticated) --------
    public function history(Request $request): void
    {
        $page    = max(1, (int) ($request->query['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($request->query['per_page'] ?? 10)));
        $search  = trim((string) ($request->query['q'] ?? ''));
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }

        $result = (new DonationRepository())->history($request->userId(), $page, $perPage, $search);

        Response::success([
            'items'    => $result['items'],
            'total'    => $result['total'],
            'page'     => $page,
            'per_page' => $perPage,
        ]);
    }
}
