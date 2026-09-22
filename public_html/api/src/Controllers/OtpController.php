<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Services\OtpService;
use Maksa\Support\Audit;

final class OtpController
{
    private OtpService $otp;

    public function __construct()
    {
        $this->otp = new OtpService();
    }

    // ---- POST /auth/otp/send ------------------------------------------------
    public function send(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('phone', min: 10, max: 20)
            ->string('purpose', max: 50, required: false)
            ->validated();

        $purpose = $data['purpose'] ?? 'donation_auth';
        if (!in_array($purpose, ['donation_auth', 'login'], true)) {
            $purpose = 'donation_auth';
        }

        $result = $this->otp->send($data['phone'], $purpose, $request->ip());

        Audit::log(null, 'otp_requested', $request->ip(), $request->userAgent(), [
            'phone'   => $data['phone'],
            'purpose' => $purpose,
        ]);

        Response::success([
            'message'      => 'کد تأیید به شماره همراه شما پیامک شد.',
            'expires_in'   => $result['expires_in'],
            'resend_after' => $result['resend_after'],
            'debug_code'   => $result['debug_code'] ?? null,
        ]);
    }

    // ---- POST /auth/otp/verify ----------------------------------------------
    public function verify(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('phone', min: 10, max: 20)
            ->string('code', min: 4, max: 8)
            ->string('purpose', max: 50, required: false)
            ->validated();

        $purpose = $data['purpose'] ?? 'donation_auth';
        if (!in_array($purpose, ['donation_auth', 'login'], true)) {
            $purpose = 'donation_auth';
        }

        $valid = $this->otp->verify($data['phone'], $data['code'], $purpose);

        Audit::log(null, 'otp_verified', $request->ip(), $request->userAgent(), [
            'phone'   => $data['phone'],
            'purpose' => $purpose,
        ]);

        Response::success([
            'valid'   => $valid,
            'message' => 'کد با موفقیت تأیید شد.',
        ]);
    }
}
