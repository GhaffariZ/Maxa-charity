<?php

declare(strict_types=1);

namespace Maksa\Services\Gateway;

use Maksa\Core\Config;
use Maksa\Support\Logger;

/**
 * Zarinpal implementation (Iran's most common gateway). Wired and ready — set
 * PAYMENT_GATEWAY=zarinpal and ZARINPAL_MERCHANT_ID in .env to go live.
 *
 * Zarinpal works in Rial; our amounts are Toman, so we multiply by 10 at the
 * boundary. Endpoints differ between sandbox and production.
 *
 * Docs: https://docs.zarinpal.com/paymentGateway/
 */
final class ZarinpalGateway implements PaymentGateway
{
    private string $merchantId;
    private bool $sandbox;

    public function __construct()
    {
        $this->merchantId = (string) Config::require('ZARINPAL_MERCHANT_ID');
        $this->sandbox    = Config::bool('ZARINPAL_SANDBOX', true);
    }

    public function request(int $amountToman, string $reference, string $callbackUrl, string $description): array
    {
        $res = $this->post($this->base() . 'request.json', [
            'merchant_id'  => $this->merchantId,
            'amount'       => $amountToman * 10, // Toman → Rial
            'callback_url' => $callbackUrl,
            'description'  => mb_substr($description, 0, 255),
            'metadata'     => ['order_id' => $reference],
        ]);

        $authority = $res['data']['authority'] ?? null;
        if (!is_string($authority) || $authority === '') {
            $code = $res['errors']['code'] ?? 'unknown';
            Logger::error('Zarinpal request failed', ['code' => $code]);
            throw new \RuntimeException('اتصال به درگاه پرداخت ناموفق بود.');
        }

        return [
            'authority'    => $authority,
            'redirect_url' => $this->startPayUrl() . $authority,
        ];
    }

    public function verify(string $authority, array $callbackParams, int $amountToman): GatewayResult
    {
        // The user only "succeeds" if Zarinpal returns Status=OK on the callback.
        if (strtoupper($callbackParams['Status'] ?? $callbackParams['status'] ?? '') !== 'OK') {
            return GatewayResult::failed('پرداخت توسط کاربر لغو شد.');
        }

        $res = $this->post($this->base() . 'verify.json', [
            'merchant_id' => $this->merchantId,
            'amount'      => $amountToman * 10,
            'authority'   => $authority,
        ]);

        $code = (int) ($res['data']['code'] ?? 0);
        // 100 = verified now, 101 = already verified.
        if ($code === 100 || $code === 101) {
            return GatewayResult::ok(
                (string) ($res['data']['ref_id'] ?? ''),
                (string) ($res['data']['card_pan'] ?? '')
            );
        }

        $errCode = $res['errors']['code'] ?? $code;
        return GatewayResult::failed('تأیید پرداخت ناموفق بود (کد ' . $errCode . ').');
    }

    public function name(): string
    {
        return 'zarinpal';
    }

    private function base(): string
    {
        return $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/'
            : 'https://payment.zarinpal.com/pg/v4/payment/';
    }

    private function startPayUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://payment.zarinpal.com/pg/StartPay/';
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function post(string $url, array $payload): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            Logger::error('Zarinpal HTTP error', ['error' => $err]);
            throw new \RuntimeException('خطا در ارتباط با درگاه پرداخت.');
        }
        $decoded = json_decode((string) $body, true);
        return is_array($decoded) ? $decoded : [];
    }
}
