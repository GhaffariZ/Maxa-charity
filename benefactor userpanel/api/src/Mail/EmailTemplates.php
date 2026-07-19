<?php

declare(strict_types=1);

namespace Maksa\Mail;

use Maksa\Core\Config;

/**
 * Builds RTL, Persian HTML + plain-text email bodies. All dynamic values are
 * escaped with htmlspecialchars before interpolation into HTML.
 *
 * @phpstan-type Email array{subject:string,html:string,text:string}
 */
final class EmailTemplates
{
    /** @return array{subject:string,html:string,text:string} */
    public static function verifyEmail(string $name, string $link): array
    {
        $subject = 'تأیید آدرس ایمیل — خیرین مکسا';
        $intro = 'برای فعال‌سازی حساب کاربری خود در خیرین مکسا، روی دکمهٔ زیر کلیک کنید. این لینک تا ۲۴ ساعت معتبر است.';
        return [
            'subject' => $subject,
            'html'    => self::layout($subject, $name, $intro, 'تأیید ایمیل', $link,
                'اگر شما این حساب را نساخته‌اید، این ایمیل را نادیده بگیرید.'),
            'text'    => self::text($name, $intro, $link),
        ];
    }

    /** @return array{subject:string,html:string,text:string} */
    public static function passwordReset(string $name, string $link): array
    {
        $subject = 'بازنشانی رمز عبور — خیرین مکسا';
        $intro = 'درخواست بازنشانی رمز عبور برای حساب شما ثبت شد. برای تعیین رمز جدید روی دکمهٔ زیر کلیک کنید. این لینک تا ۱ ساعت معتبر است.';
        return [
            'subject' => $subject,
            'html'    => self::layout($subject, $name, $intro, 'بازنشانی رمز عبور', $link,
                'اگر شما این درخواست را نداده‌اید، رمز عبور شما تغییر نکرده و می‌توانید این ایمیل را نادیده بگیرید.'),
            'text'    => self::text($name, $intro, $link),
        ];
    }

    /** @return array{subject:string,html:string,text:string} */
    public static function passwordChanged(string $name): array
    {
        $subject = 'رمز عبور شما تغییر کرد — خیرین مکسا';
        $intro = 'رمز عبور حساب کاربری شما با موفقیت تغییر کرد. اگر این تغییر توسط شما انجام نشده است، فوراً با پشتیبانی تماس بگیرید.';
        return [
            'subject' => $subject,
            'html'    => self::layout($subject, $name, $intro, null, null,
                'این پیام صرفاً جهت اطلاع‌رسانی امنیتی ارسال شده است.'),
            'text'    => self::text($name, $intro, null),
        ];
    }

    /** @return array{subject:string,html:string,text:string} */
    public static function newDeviceLogin(string $name, string $ip, string $userAgent, string $whenUtc): array
    {
        $subject = 'ورود جدید به حساب شما — خیرین مکسا';
        $intro = sprintf(
            'ورود جدیدی به حساب شما ثبت شد:%sزمان (UTC): %s%sنشانی IP: %s%sدستگاه: %s',
            "\n", $whenUtc, "\n", $ip, "\n", $userAgent !== '' ? $userAgent : 'نامشخص'
        );
        return [
            'subject' => $subject,
            'html'    => self::layout($subject, $name, nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8')), null, null,
                'اگر این شما بوده‌اید نیازی به اقدام نیست. در غیر این صورت، رمز عبور خود را تغییر دهید.', true),
            'text'    => self::text($name, $intro, null),
        ];
    }

    // ---- rendering helpers --------------------------------------------------

    private static function layout(
        string $title,
        string $name,
        string $bodyHtml,
        ?string $ctaLabel,
        ?string $ctaUrl,
        string $footerNote,
        bool $bodyIsHtml = false
    ): string {
        $appName = htmlspecialchars((string) Config::get('APP_NAME', 'خیرین مکسا'), ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($name !== '' ? $name : 'دوست گرامی', ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $body = $bodyIsHtml ? $bodyHtml : htmlspecialchars($bodyHtml, ENT_QUOTES, 'UTF-8');
        $replyTo = htmlspecialchars((string) Config::get('MAIL_REPLY_TO', ''), ENT_QUOTES, 'UTF-8');

        $cta = '';
        if ($ctaLabel !== null && $ctaUrl !== null) {
            $safeUrl = htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8');
            $safeLabel = htmlspecialchars($ctaLabel, ENT_QUOTES, 'UTF-8');
            $cta = <<<HTML
                <tr><td style="padding:8px 0 24px;">
                  <a href="{$safeUrl}" style="display:inline-block;background:#007b7a;color:#fff;text-decoration:none;
                     padding:14px 28px;border-radius:12px;font-weight:bold;font-size:15px;">{$safeLabel}</a>
                </td></tr>
                <tr><td style="padding-bottom:16px;color:#6b7280;font-size:12px;line-height:1.9;">
                  اگر دکمه کار نکرد، این نشانی را در مرورگر باز کنید:<br>
                  <span style="word-break:break-all;color:#007b7a;">{$safeUrl}</span>
                </td></tr>
                HTML;
        }

        $contact = $replyTo !== '' ? "<br>تماس: {$replyTo}" : '';

        return <<<HTML
            <!DOCTYPE html>
            <html lang="fa" dir="rtl"><head><meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1"></head>
            <body style="margin:0;background:#f1f3f5;font-family:Tahoma,Arial,sans-serif;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f5;padding:24px 0;">
                <tr><td align="center">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:20px;overflow:hidden;">
                    <tr><td style="background:#007b7a;padding:24px 32px;color:#fff;font-size:20px;font-weight:bold;">{$appName}</td></tr>
                    <tr><td style="padding:32px;text-align:right;color:#111827;">
                      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr><td style="font-size:18px;font-weight:bold;padding-bottom:12px;">{$safeTitle}</td></tr>
                        <tr><td style="font-size:14px;line-height:2;color:#374151;padding-bottom:20px;">سلام {$safeName}،<br>{$body}</td></tr>
                        {$cta}
                        <tr><td style="border-top:1px solid #e5e7eb;padding-top:16px;color:#9ca3af;font-size:12px;line-height:1.9;">
                          {$footerNote}{$contact}
                        </td></tr>
                      </table>
                    </td></tr>
                  </table>
                  <div style="color:#9ca3af;font-size:11px;padding:16px;">© خیرین مکسا</div>
                </td></tr>
              </table>
            </body></html>
            HTML;
    }

    private static function text(string $name, string $intro, ?string $link): string
    {
        $greeting = 'سلام ' . ($name !== '' ? $name : 'دوست گرامی') . '،';
        $lines = [$greeting, '', $intro];
        if ($link !== null) {
            $lines[] = '';
            $lines[] = $link;
        }
        $lines[] = '';
        $lines[] = '— خیرین مکسا';
        return implode("\n", $lines);
    }
}
