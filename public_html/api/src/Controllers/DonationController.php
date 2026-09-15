<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Auth\Jwt;
use Maksa\Auth\RefreshTokenService;
use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\DonationRepository;
use Maksa\Repositories\UserRepository;
use Maksa\Services\Crm\CrmService;
use Maksa\Services\DonationService;
use Maksa\Services\OtpService;
use Maksa\Support\Audit;
use Maksa\Support\Cookie;

final class DonationController
{
    // ---- POST /donations/initiate  (public with OTP + Shaparak compliance) ----
    public function initiate(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('phone', min: 10, max: 20)
            ->string('code', min: 4, max: 8)
            ->string('first_name', min: 2, max: 100)
            ->string('last_name', min: 2, max: 100)
            ->string('national_code', max: 10, required: false)
            ->int('amount', min: 1000, max: 500000000)
            ->string('campaign_slug', max: 120, required: false)
            ->validated();

        $phone = OtpService::normalizePhone($data['phone']);
        if (!OtpService::isValidPhone($phone)) {
            throw ApiException::badRequest('شماره تلفن همراه معتبر نیست.', 'invalid_phone');
        }

        // 1. Verify OTP
        (new OtpService())->verify($phone, $data['code'], 'donation_auth');

        // 2. Validate optional national code
        $nationalCode = !empty($data['national_code']) ? trim((string) $data['national_code']) : null;
        if ($nationalCode !== null && (!preg_match('/^[0-9]{10}$/', $nationalCode))) {
            throw ApiException::badRequest('کد ملی وارد شده نامعتبر است (باید ۱۰ رقم باشد).', 'invalid_national_code');
        }

        // 3. Register or retrieve donor in panel_users + user_profiles
        $users = new UserRepository();
        $userResult = $users->createOrGetDonorByPhone(
            $phone,
            $data['first_name'],
            $data['last_name'],
            $nationalCode
        );
        $userId = $userResult['id'];

        // 4. Synchronize donor with CRM
        (new CrmService())->syncDonor($userId, [
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'         => $phone,
            'national_code' => $nationalCode,
        ]);

        // 5. Issue JWT & session so donor is authenticated
        $accessToken = Jwt::issueAccessToken($userId);
        $refreshToken = (new RefreshTokenService())->issueNewFamily($userId, $request->ip(), $request->userAgent());
        Cookie::setRefreshToken($refreshToken);

        // 6. Initiate pending donation with Shaparak metadata (mobile)
        $metadata = [
            'mobile' => $phone,
        ];
        $result = (new DonationService())->start(
            $userId,
            $data['campaign_slug'] ?? null,
            $data['amount'],
            $metadata
        );

        Audit::log($userId, 'donation_initiated_with_otp', $request->ip(), $request->userAgent(), [
            'reference' => $result['reference'],
            'amount'    => $data['amount'],
            'phone'     => $phone,
        ]);

        Response::success([
            'reference'    => $result['reference'],
            'redirect_url' => $result['redirect_url'],
            'access_token' => $accessToken,
            'user' => [
                'id'         => $userId,
                'phone'      => $phone,
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
            ],
        ], 201);
    }

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
