<?php

declare(strict_types=1);

namespace Maksa\Mail;

use Maksa\Core\Config;
use Maksa\Support\Logger;

/**
 * Sends transactional email. Uses PHPMailer over SMTP/TLS when the library is
 * present in api/vendor (recommended on cPanel); otherwise falls back to PHP
 * mail() with strictly sanitized headers. All addresses/subjects are scrubbed
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
        Logger::warning('Mailer: PHPMailer not found — falling back to PHP mail() (often dropped on shared hosting)', ['to' => $toEmail]);
        return $this->sendViaMailFn($toEmail, $subject, $html, $text);
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

            $encryption = strtolower((string) Config::get('MAIL_ENCRYPTION', 'tls'));
            $mail->SMTPSecure = $encryption === 'starttls'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;

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

            $mail->send();
            Logger::info('Mailer: SMTP send OK', ['to' => $toEmail]);
            return true;
        } catch (\Throwable $e) {
            // PHPMailer puts the useful detail in ErrorInfo.
            $detail = isset($mail) ? $mail->ErrorInfo : '';
            Logger::error('Mailer (PHPMailer) failed', ['error' => $e->getMessage(), 'smtp' => $detail]);
            return false;
        }
    }

    private function sendViaMailFn(string $toEmail, string $subject, string $html, string $text): bool
    {
        $boundary = 'b' . bin2hex(random_bytes(8));
        $from     = self::sanitizeAddress((string) Config::get('MAIL_FROM_ADDRESS', 'no-reply@localhost'));
        $fromName = self::sanitizeHeader((string) Config::get('MAIL_FROM_NAME', 'خیرین مکسا'));

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <{$from}>",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        ]);

        $body = implode("\r\n", [
            "--{$boundary}",
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($text)),
            "--{$boundary}",
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($html)),
            "--{$boundary}--",
        ]);

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $ok = @mail($toEmail, $encodedSubject, $body, $headers);
        // NOTE: mail() returning true only means the message was handed to the
        // MTA — it does NOT mean delivery. On shared hosting it's frequently
        // dropped or spam-filtered. Prefer authenticated SMTP (PHPMailer).
        Logger::info('Mailer: PHP mail() returned ' . ($ok ? 'true' : 'false'), ['to' => $toEmail]);
        return $ok;
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
