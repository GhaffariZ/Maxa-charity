<?php

declare(strict_types=1);

namespace Maksa\Services\Sms;

use Maksa\Core\Config;
use Maksa\Support\Logger;

final class FarazSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $sender;
    private string $patternCode;

    public function __construct()
    {
        $this->apiKey      = (string) Config::get('SMS_API_KEY', '');
        $this->sender      = (string) Config::get('SMS_SENDER', '+983000505');
        $this->patternCode = (string) Config::get('SMS_OTP_PATTERN', '');
    }

    public function sendOtp(string $phone, string $code): bool
    {
        if ($this->apiKey === '') {
            Logger::error('Faraz/IPPanel API key not configured');
            return false;
        }

        $url = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';
        $payload = json_encode([
            'code'      => $this->patternCode,
            'sender'    => $this->sender,
            'recipient' => $phone,
            'variable'  => [
                'code' => $code,
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: ' . $this->apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            Logger::error('Faraz/IPPanel SMS request failed', ['http_code' => $httpCode, 'error' => $err]);
            return false;
        }

        return true;
    }

    public function name(): string
    {
        return 'faraz';
    }
}
