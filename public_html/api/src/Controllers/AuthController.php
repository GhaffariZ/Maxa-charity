<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Auth\Jwt;
use Maksa\Auth\PasswordPolicy;
use Maksa\Auth\RefreshTokenService;
use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Exceptions\ValidationException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Mail\EmailTemplates;
use Maksa\Mail\Mailer;
use Maksa\Repositories\OneTimeTokenRepository;
use Maksa\Repositories\UserRepository;
use Maksa\Support\Audit;
use Maksa\Support\Cookie;
use Maksa\Support\RateLimiter;

final class AuthController
{
    private UserRepository $users;
    private OneTimeTokenRepository $tokens;
    private RefreshTokenService $refresh;
    private Mailer $mailer;

    public function __construct()
    {
        $this->users   = new UserRepository();
        $this->tokens  = new OneTimeTokenRepository();
        $this->refresh = new RefreshTokenService();
        $this->mailer  = new Mailer();
    }

    // ---- POST /auth/register ------------------------------------------------
    public function register(Request $request): void
    {
        $data = (new Validator($request->body))
            ->email('email')
            ->string('first_name', max: 100, required: false)
            ->string('last_name', max: 100, required: false)
            ->string('password', max: 200)
            ->validated();

        $pwError = PasswordPolicy::validate($data['password'], $data['email']);
        if ($pwError !== null) {
            throw new ValidationException(['password' => $pwError]);
        }

        // Enumeration-safe: do NOT reveal whether the email already exists. We
        // only actually create + email when it's new; the response is identical
        // either way.
        if (!$this->users->emailExists($data['email'])) {
            $userId = $this->users->createDonor(
                $data['email'],
                PasswordPolicy::hash($data['password']),
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
            );
            $this->sendVerification($userId, $data['email'], $data['first_name'] ?? '');
            Audit::log($userId, 'register', $request->ip(), $request->userAgent());
        } else {
            Audit::log(null, 'register_existing_email', $request->ip(), $request->userAgent(), ['email' => $data['email']]);
        }

        Response::success([
            'message' => 'اگر این ایمیل جدید باشد، لینک تأیید برای شما ارسال شد. لطفاً صندوق ورودی خود را بررسی کنید.',
        ], 201);
    }

    // ---- POST /auth/verify-email -------------------------------------------
    public function verifyEmail(Request $request): void
    {
        $data = (new Validator($request->body))->string('token', min: 10, max: 200)->validated();

        $userId = $this->tokens->consumeEmailVerification($data['token']);
        if ($userId === null) {
            throw ApiException::badRequest('لینک تأیید نامعتبر یا منقضی شده است.', 'invalid_token');
        }

        $this->users->markEmailVerified($userId);
        Audit::log($userId, 'email_verified', $request->ip(), $request->userAgent());

        Response::success(['message' => 'ایمیل شما با موفقیت تأیید شد. اکنون می‌توانید وارد شوید.']);
    }

    // ---- POST /auth/resend-verification ------------------------------------
    public function resendVerification(Request $request): void
    {
        $data = (new Validator($request->body))->email('email')->validated();

        $user = $this->users->findByEmail($data['email']);
        // Only send if the account exists and is not yet verified. Always return
        // the same generic response (no enumeration).
        if ($user !== null && $user['email_verified_at'] === null) {
            $name = $this->firstName($user['id']);
            $this->sendVerification((int) $user['id'], $data['email'], $name);
        }

        Response::success(['message' => 'اگر حساب تأییدنشده‌ای با این ایمیل وجود داشته باشد، لینک تأیید مجدداً ارسال شد.']);
    }

    // ---- POST /auth/login ---------------------------------------------------
    public function login(Request $request): void
    {
        $data = (new Validator($request->body))
            ->email('email')
            ->string('password', max: 200)
            ->validated();

        $ip = $request->ip();
        $ua = $request->userAgent();

        // IP+email sliding-window rate limit before touching the DB user row.
        if (RateLimiter::tooManyFailures($data['email'], $ip)) {
            Audit::log(null, 'login_rate_limited', $ip, $ua, ['email' => $data['email']]);
            throw ApiException::tooManyRequests('تلاش‌های ناموفق زیاد بوده است. لطفاً چند دقیقه بعد دوباره تلاش کنید.');
        }

        $user = $this->users->findByEmail($data['email']);

        // Timing-attack mitigation: always run a verify, even with no user.
        $hash = $user['password_hash'] ?? '$2y$12$0000000000000000000000000000000000000000000000000000z';
        $passwordOk = PasswordPolicy::verify($data['password'], $hash);

        $invalid = static function () use ($data, $ip, $ua) {
            RateLimiter::record($data['email'], $ip, false, $ua);
            // Identical response whether the email exists or the password is wrong.
            throw ApiException::unauthorized('ایمیل یا رمز عبور نادرست است.', 'invalid_credentials');
        };

        if ($user === null || !$passwordOk) {
            if ($user !== null) {
                $this->users->recordFailedLogin((int) $user['id']);
            }
            $invalid();
        }

        // Account lockout window.
        if ($user['locked_until'] !== null && strtotime((string) $user['locked_until']) > time()) {
            Audit::log((int) $user['id'], 'login_locked', $ip, $ua);
            throw ApiException::tooManyRequests('حساب شما موقتاً قفل شده است. لطفاً بعداً تلاش کنید.', 'account_locked');
        }

        if ($user['status'] === 'suspended') {
            throw ApiException::forbidden('حساب کاربری شما مسدود شده است.', 'account_suspended');
        }
        if ($user['email_verified_at'] === null) {
            throw ApiException::forbidden('لطفاً ابتدا ایمیل خود را تأیید کنید.', 'email_unverified');
        }

        // New-device notification (best-effort) before we overwrite last_login_ip.
        $previousIp = $user['last_login_ip'] ?? null;
        if ($previousIp !== null && $previousIp !== $ip) {
            $name = $this->firstName((int) $user['id']);
            $tpl = EmailTemplates::newDeviceLogin($name, $ip, $ua, gmdate('Y-m-d H:i:s'));
            $this->mailer->send($user['email'], $name, $tpl['subject'], $tpl['html'], $tpl['text']);
        }

        $this->users->recordSuccessfulLogin((int) $user['id'], $ip);
        RateLimiter::record($data['email'], $ip, true, $ua);
        Audit::log((int) $user['id'], 'login', $ip, $ua);

        $this->issueSession((int) $user['id'], $ip, $ua);
    }

    // ---- POST /auth/refresh -------------------------------------------------
    public function refresh(Request $request): void
    {
        $token = Cookie::refreshToken();
        if ($token === null) {
            throw ApiException::unauthorized('نشست یافت نشد.', 'no_refresh');
        }

        [$userId, $newToken] = $this->refresh->rotate($token, $request->ip(), $request->userAgent());

        Cookie::setRefreshToken($newToken);
        Response::success([
            'access_token' => Jwt::issueAccessToken($userId),
            'token_type'   => 'Bearer',
            'expires_in'   => Config::int('JWT_ACCESS_TTL', 900),
        ]);
    }

    // ---- POST /auth/logout --------------------------------------------------
    public function logout(Request $request): void
    {
        $token = Cookie::refreshToken();
        if ($token !== null) {
            $this->refresh->revoke($token);
        }
        Cookie::clearRefreshToken();
        Response::success(['message' => 'با موفقیت خارج شدید.']);
    }

    // ---- POST /auth/forgot-password ----------------------------------------
    public function forgotPassword(Request $request): void
    {
        $data = (new Validator($request->body))->email('email')->validated();

        $user = $this->users->findByEmail($data['email']);
        if ($user !== null) {
            $token = $this->tokens->createPasswordReset((int) $user['id'], $request->ip());
            $name = $this->firstName((int) $user['id']);
            $link = $this->appUrl('/reset?token=' . urlencode($token));
            $tpl = EmailTemplates::passwordReset($name, $link);
            $this->mailer->send($user['email'], $name, $tpl['subject'], $tpl['html'], $tpl['text']);
            Audit::log((int) $user['id'], 'password_reset_requested', $request->ip(), $request->userAgent());
        }

        // Always identical — never reveal whether the email is registered.
        Response::success(['message' => 'اگر این ایمیل در سیستم ثبت شده باشد، لینک بازیابی رمز عبور ارسال شد.']);
    }

    // ---- POST /auth/reset-password -----------------------------------------
    public function resetPassword(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('token', min: 10, max: 200)
            ->string('password', max: 200)
            ->validated();

        $userId = $this->tokens->consumePasswordReset($data['token']);
        if ($userId === null) {
            throw ApiException::badRequest('لینک بازیابی نامعتبر یا منقضی شده است.', 'invalid_token');
        }

        $user = $this->users->findById($userId);
        $pwError = PasswordPolicy::validate($data['password'], $user['email'] ?? '');
        if ($pwError !== null) {
            throw new ValidationException(['password' => $pwError]);
        }

        $this->users->updatePasswordHash($userId, PasswordPolicy::hash($data['password']));
        $this->tokens->invalidateUserResets($userId);
        // Invalidate every existing session — a reset should log out other devices.
        $this->refresh->revokeAllForUser($userId);
        Audit::log($userId, 'password_reset', $request->ip(), $request->userAgent());

        if ($user !== null) {
            $name = $this->firstName($userId);
            $tpl = EmailTemplates::passwordChanged($name);
            $this->mailer->send($user['email'], $name, $tpl['subject'], $tpl['html'], $tpl['text']);
        }

        Response::success(['message' => 'رمز عبور شما با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.']);
    }

    // ---- POST /auth/change-password  (authenticated) ------------------------
    public function changePassword(Request $request): void
    {
        $userId = $request->userId();
        $data = (new Validator($request->body))
            ->string('old_password', max: 200)
            ->string('new_password', max: 200)
            ->validated();

        $user = $this->users->findById($userId);
        if ($user === null || !PasswordPolicy::verify($data['old_password'], $user['password_hash'])) {
            throw ApiException::badRequest('رمز عبور فعلی نادرست است.', 'invalid_current_password');
        }

        $pwError = PasswordPolicy::validate($data['new_password'], $user['email']);
        if ($pwError !== null) {
            throw new ValidationException(['new_password' => $pwError]);
        }

        $this->users->updatePasswordHash($userId, PasswordPolicy::hash($data['new_password']));
        // Revoke other sessions; the current client will refresh into a new one.
        $this->refresh->revokeAllForUser($userId);
        Audit::log($userId, 'password_changed', $request->ip(), $request->userAgent());

        $name = $this->firstName($userId);
        $tpl = EmailTemplates::passwordChanged($name);
        $this->mailer->send($user['email'], $name, $tpl['subject'], $tpl['html'], $tpl['text']);

        Cookie::clearRefreshToken();
        Response::success(['message' => 'رمز عبور با موفقیت تغییر کرد. لطفاً دوباره وارد شوید.']);
    }

    // ---- helpers ------------------------------------------------------------

    private function issueSession(int $userId, string $ip, string $ua): never
    {
        $refreshToken = $this->refresh->issueNewFamily($userId, $ip, $ua);
        Cookie::setRefreshToken($refreshToken);

        Response::success([
            'access_token' => Jwt::issueAccessToken($userId),
            'token_type'   => 'Bearer',
            'expires_in'   => Config::int('JWT_ACCESS_TTL', 900),
        ]);
    }

    private function sendVerification(int $userId, string $email, string $name): void
    {
        $token = $this->tokens->createEmailVerification($userId);
        $link = $this->appUrl('/verify?token=' . urlencode($token));
        $tpl = EmailTemplates::verifyEmail($name, $link);
        $this->mailer->send($email, $name, $tpl['subject'], $tpl['html'], $tpl['text']);
    }

    private function firstName(int $userId): string
    {
        $stmt = \Maksa\Core\Database::connection()->prepare(
            'SELECT first_name FROM user_profiles WHERE user_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    private function appUrl(string $path): string
    {
        return rtrim((string) Config::get('APP_URL', ''), '/') . $path;
    }
}
