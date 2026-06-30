<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Mail\Mailer;
use Maksa\Support\Logger;

/**
 * Public "Contact us" form handler. Validates the submission, drops obvious
 * spam (honeypot), then emails the message to the organisation inbox using the
 * shared Mailer. No authentication required — this is a public endpoint.
 */
final class ContactController
{
    private Mailer $mailer;

    public function __construct()
    {
        $this->mailer = new Mailer();
    }

    // ---- POST /contact  (public) -------------------------------------------
    public function submit(Request $request): void
    {
        // Honeypot: a hidden field real users never fill. Bots usually do.
        // Silently accept so the bot believes it succeeded.
        $trap = $request->input('website', '');
        if (is_string($trap) && trim($trap) !== '') {
            Logger::info('Contact: honeypot tripped, dropping submission', ['ip' => $request->ip()]);
            Response::success(['message' => 'پیام شما با موفقیت ارسال شد. به‌زودی با شما تماس می‌گیریم.']);
        }

        $data = (new Validator($request->body))
            ->string('name', min: 2, max: 100)
            ->email('email')
            ->string('phone', max: 30, required: false)
            ->string('subject', min: 2, max: 150)
            ->string('message', min: 10, max: 5000)
            ->validated();

        $to     = (string) Config::get('MAIL_CONTACT_TO', (string) Config::get('MAIL_FROM_ADDRESS', 'info@macsa.ir'));
        $toName = (string) Config::get('MAIL_CONTACT_NAME', 'مرکز ارتباطات مکسا');

        $body  = $this->buildEmail($data, $request->ip());
        $sent  = $this->mailer->send($to, $toName, $body['subject'], $body['html'], $body['text']);

        if (!$sent) {
            Logger::error('Contact: mail send failed', ['to' => $to]);
            throw ApiException::badRequest(
                'در ارسال پیام مشکلی پیش آمد. لطفاً بعداً دوباره تلاش کنید یا با شماره‌های تماس در ارتباط باشید.',
                'mail_failed'
            );
        }

        Response::success(['message' => 'پیام شما با موفقیت ارسال شد. به‌زودی با شما تماس می‌گیریم.'], 201);
    }

    /**
     * @param array<string,mixed> $data
     * @return array{subject:string,html:string,text:string}
     */
    private function buildEmail(array $data, string $ip): array
    {
        $name    = (string) $data['name'];
        $email   = (string) $data['email'];
        $phone   = (string) ($data['phone'] ?? '—');
        $subject = (string) $data['subject'];
        $message = (string) $data['message'];
        $when    = gmdate('Y-m-d H:i') . ' UTC';

        $e = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $mailSubject = 'پیام تماس از وب‌سایت — ' . $subject;

        $html = '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"></head>'
            . '<body style="font-family:Tahoma,Arial,sans-serif;background:#f5f6f8;padding:24px;color:#1f2937">'
            . '<div style="max-width:620px;margin:auto;background:#fff;border-radius:14px;padding:28px;'
            . 'box-shadow:0 8px 24px rgba(0,0,0,.06)">'
            . '<h2 style="margin:0 0 18px;color:#07828e">پیام جدید از فرم تماس با ما</h2>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;line-height:2">'
            . '<tr><td style="color:#6b7280;width:120px">نام</td><td><strong>' . $e($name) . '</strong></td></tr>'
            . '<tr><td style="color:#6b7280">ایمیل</td><td><a href="mailto:' . $e($email) . '">' . $e($email) . '</a></td></tr>'
            . '<tr><td style="color:#6b7280">شماره تماس</td><td>' . $e($phone) . '</td></tr>'
            . '<tr><td style="color:#6b7280">موضوع</td><td>' . $e($subject) . '</td></tr>'
            . '</table>'
            . '<hr style="border:none;border-top:1px solid #eee;margin:18px 0">'
            . '<p style="color:#6b7280;margin:0 0 6px;font-size:13px">متن پیام:</p>'
            . '<div style="white-space:pre-wrap;background:#f9fafb;border:1px solid #eef0f3;border-radius:10px;'
            . 'padding:16px;font-size:14px;line-height:2">' . nl2br($e($message)) . '</div>'
            . '<p style="color:#9ca3af;margin:18px 0 0;font-size:12px">ارسال‌شده در ' . $e($when)
            . ' — IP: ' . $e($ip) . '</p>'
            . '</div></body></html>';

        $text = "پیام جدید از فرم تماس با ما\n"
            . "نام: {$name}\n"
            . "ایمیل: {$email}\n"
            . "شماره تماس: {$phone}\n"
            . "موضوع: {$subject}\n"
            . "تاریخ: {$when} — IP: {$ip}\n"
            . "----------------------------------------\n"
            . $message . "\n";

        return ['subject' => $mailSubject, 'html' => $html, 'text' => $text];
    }
}
