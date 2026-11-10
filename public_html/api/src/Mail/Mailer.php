<?php

declare(strict_types=1);

namespace Maksa\Mail;

use Maksa\Core\Config;
use Maksa\Support\Logger;

/**
 * Sends transactional email. Uses PHPMailer over SMTP/TLS when the library is
 * present in api/vendor. Missing SMTP support is logged as a failure rather
 * than silently switching to unreliable PHP mail(). Addresses/subjects are scrubbed
 * of CR/LF to prevent header (email) injection.
 */
final class Mailer
{
    /** Sends an email with both HTML and plain-text parts. */
    public function send(string $toEmail, string $toName, string $subject, string $html, string $text): bool
    {
        $toEmail = self::sanitizeAddress($toEmail);
        if ($toEmail === '') {
            Logger::warning('Mailer: invalid recipient, skipped send');
            return false;
        }
        $subject = self::sanitizeHeader($subject);
        $toName  = self::sanitizeHeader($toName);

        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            Logger::info('Mailer: sending via SMTP (PHPMailer)', ['to' => $toEmail, 'subject' => $subject]);
            return $this->sendViaPhpMailer($toEmail, $toName, $subject, $html, $text);
        }
        Logger::error('Mailer: PHPMailer is missing; install api/vendor/PHPMailer/src');
        return false;
    }

    private function sendViaPhpMailer(string $toEmail, string $toName, string $subject, string $html, string $text): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = (string) Config::require('MAIL_HOST');
            $mail->Port       = Config::int('MAIL_PORT', 465);
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) Config::require('MAIL_USERNAME');
            $mail->Password   = (string) Config::require('MAIL_PASSWORD');
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->Timeout    = 15;
            $mail->getSMTPInstance()->Timelimit = 15;

            $encryption = strtolower(trim((string) Config::get('MAIL_ENCRYPTION', 'auto')));
            $mail->SMTPSecure = match ($encryption) {
                'ssl', 'smtps' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS,
                'starttls' => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
                // Keep the existing tls+465 configuration working; tls+587 uses STARTTLS.
                'auto', 'tls' => $mail->Port === 465
                    ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                    : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
                default => throw new \RuntimeException('Invalid MAIL_ENCRYPTION; use smtps or starttls.'),
            };

            $mail->setFrom(
                (string) Config::require('MAIL_FROM_ADDRESS'),
                (string) Config::get('MAIL_FROM_NAME', 'خیرین مکسا')
            );
            $replyTo = (string) Config::get('MAIL_REPLY_TO', '');
            if ($replyTo !== '') {
                $mail->addReplyTo($replyTo);
            }
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;

            if (!$mail->send()) {
                throw new \RuntimeException('SMTP did not accept the message.');
            }
            Logger::info('Mailer: SMTP send OK', ['to' => $toEmail]);
            return true;
        } catch (\Throwable $e) {
            // PHPMailer puts the useful detail in ErrorInfo.
            $detail = isset($mail) ? $mail->ErrorInfo : '';
            Logger::error('Mailer (PHPMailer) failed', [
                'error' => $e->getMessage(), 'smtp' => $detail,
                'host' => $mail->Host, 'port' => $mail->Port, 'encryption' => $mail->SMTPSecure,
            ]);
            return false;
        } finally {
            $mail->smtpClose();
        }
    }

    /** Remove anything that could inject extra headers. */
    private static function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0", '%0a', '%0d'], '', $value));
    }

    private static function sanitizeAddress(string $email): string
    {
        $email = self::sanitizeHeader($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : '';
    }
}
