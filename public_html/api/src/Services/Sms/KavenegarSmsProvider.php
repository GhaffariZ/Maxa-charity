<?php

declare(strict_types=1);

namespace Maksa\Services\Sms;

use Maksa\Core\Config;
use Maksa\Support\Logger;

final class KavenegarSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $template;

    public function __construct()
    {
        $this->apiKey   = (string) Config::get('SMS_API_KEY', '');
        $this->template = (string) Config::get('SMS_OTP_TEMPLATE', 'verify');
    }

    public function sendOtp(string $phone, string $code): bool
    {
        if ($this->apiKey === '') {
            Logger::error('Kavenegar API key not configured');
            return false;
        }

        $url = "https://api.kavenegar.com/v1/{$this->apiKey}/verify/lookup.json";
        $data = [
            'receptor' => $phone,
            'token'    => $code,
            'template' => $this->template,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            Logger::error('Kavenegar SMS request failed', ['http_code' => $httpCode, 'error' => $err]);
            return false;
        }

        $json = json_decode((string) $response, true);
        $status = $json['return']['status'] ?? null;
        return $status === 200;
    }

    public function name(): string
    {
        return 'kavenegar';
    }
}
