<?php

declare(strict_types=1);

namespace Maksa\Services;

use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Services\Sms\SmsService;
use Maksa\Support\Logger;
use PDO;

final class OtpService
{
    private PDO $db;
    private SmsService $sms;

    public const EXPIRATION_SECONDS = 120;
    public const RESEND_INTERVAL_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    public function __construct(?SmsService $sms = null)
    {
        $this->db  = Database::connection();
        $this->sms = $sms ?? new SmsService();
    }

    /**
     * Generates and sends a 5-digit OTP to the phone.
     *
     * @return array{expires_in: int, resend_after: int, debug_code?: string}
     */
    public function send(string $phone, string $purpose, string $ip): array
    {
        $phone = self::normalizePhone($phone);
        if (!self::isValidPhone($phone)) {
            throw ApiException::badRequest('شماره تلفن همراه معتبر نیست.', 'invalid_phone');
        }

        // 1. Rate limiting: Check if an active OTP was issued within the last RESEND_INTERVAL_SECONDS
        $stmt = $this->db->prepare(
            'SELECT created_at, (TIMESTAMPDIFF(SECOND, created_at, UTC_TIMESTAMP())) as elapsed
               FROM otp_codes
              WHERE phone = :phone AND purpose = :purpose AND consumed_at IS NULL
              ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([':phone' => $phone, ':purpose' => $purpose]);
        $last = $stmt->fetch();

        if ($last !== false) {
            $elapsed = (int) $last['elapsed'];
            if ($elapsed < self::RESEND_INTERVAL_SECONDS) {
                $wait = self::RESEND_INTERVAL_SECONDS - $elapsed;
                throw ApiException::badRequest(
                    "لطفاً {$wait} ثانیه دیگر مجدداً تلاش کنید.",
                    'otp_rate_limited'
                );
            }
        }

        // 2. IP Rate limiting: Max 15 requests per hour
        $ipStmt = $this->db->prepare(
            'SELECT COUNT(*) FROM otp_codes
              WHERE ip_address = :ip
                AND created_at >= UTC_TIMESTAMP() - INTERVAL 1 HOUR'
        );
        $ipStmt->execute([':ip' => $ip]);
        if ((int) $ipStmt->fetchColumn() >= 15) {
            throw ApiException::badRequest(
                'تعداد درخواست‌های پیامک بیش از حد مجاز است. لطفاً ساعتی بعد تلاش کنید.',
                'ip_rate_limited'
            );
        }

        // 3. Generate 5-digit code
        $code = (string) random_int(10000, 99999);
        $codeHash = password_hash($code, PASSWORD_DEFAULT);

        // 4. Invalidate older pending OTPs for this phone and purpose
        $this->db->prepare(
            "UPDATE otp_codes SET consumed_at = UTC_TIMESTAMP()
              WHERE phone = :phone AND purpose = :purpose AND consumed_at IS NULL"
        )->execute([':phone' => $phone, ':purpose' => $purpose]);

        // 5. Insert new OTP record
        $ins = $this->db->prepare(
            'INSERT INTO otp_codes (phone, code_hash, purpose, ip_address, attempts, expires_at)
             VALUES (:phone, :hash, :purpose, :ip, 0, UTC_TIMESTAMP() + INTERVAL ' . self::EXPIRATION_SECONDS . ' SECOND)'
        );
        $ins->execute([
            ':phone'   => $phone,
            ':hash'    => $codeHash,
            ':purpose' => $purpose,
            ':ip'      => $ip,
        ]);

        // 6. Send via SMS provider
        $sent = $this->sms->sendOtp($phone, $code);
        if (!$sent) {
            Logger::error("Failed to send OTP SMS to {$phone}");
            throw ApiException::badRequest('ارسال پیامک با خطا مواجه شد. لطفاً دوباره تلاش کنید.', 'sms_dispatch_failed');
        }

        $result = [
            'expires_in'   => self::EXPIRATION_SECONDS,
            'resend_after' => self::RESEND_INTERVAL_SECONDS,
        ];

        // In mock driver, attach debug_code in development for automated testing
        if ($this->sms->providerName() === 'mock') {
            $result['debug_code'] = $code;
        }

        return $result;
    }

    /**
     * Verifies the OTP code. Consumes it on success.
     */
    public function verify(string $phone, string $code, string $purpose): bool
    {
        $phone = self::normalizePhone($phone);
        $code  = trim($code);

        $stmt = $this->db->prepare(
            'SELECT id, code_hash, attempts, expires_at,
                    (UTC_TIMESTAMP() > expires_at) as is_expired
               FROM otp_codes
              WHERE phone = :phone
                AND purpose = :purpose
                AND consumed_at IS NULL
              ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([':phone' => $phone, ':purpose' => $purpose]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw ApiException::badRequest('کد تأیید یافت نشد یا منقضی شده است. لطفاً کد جدید دریافت کنید.', 'otp_not_found');
        }

        if ((int) $row['is_expired'] === 1) {
            throw ApiException::badRequest('کد تأیید منقضی شده است. لطفاً مجدداً درخواست ارسال کد دهید.', 'otp_expired');
        }

        $id = (int) $row['id'];
        $attempts = (int) $row['attempts'];

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Invalidate code
            $this->db->prepare('UPDATE otp_codes SET consumed_at = UTC_TIMESTAMP() WHERE id = :id')
                     ->execute([':id' => $id]);
            throw ApiException::badRequest('تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً کد جدید دریافت کنید.', 'otp_max_attempts');
        }

        if (!password_verify($code, (string) $row['code_hash'])) {
            $this->db->prepare('UPDATE otp_codes SET attempts = attempts + 1 WHERE id = :id')
                     ->execute([':id' => $id]);
            throw ApiException::badRequest('کد تأیید وارد شده صحیح نیست.', 'otp_incorrect');
        }

        // Successfully verified — consume it
        $this->db->prepare('UPDATE otp_codes SET consumed_at = UTC_TIMESTAMP() WHERE id = :id')
                 ->execute([':id' => $id]);

        return true;
    }

    public static function normalizePhone(string $phone): string
    {
        // Convert Persian/Arabic digits to English
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $phone = str_replace($persian, $english, $phone);
        $phone = str_replace($arabic, $english, $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? '';

        // Handle +98 or 0098 prefixes
        if (str_starts_with($phone, '98') && strlen($phone) === 12) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    public static function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^09[0-9]{9}$/', $phone);
    }
}
