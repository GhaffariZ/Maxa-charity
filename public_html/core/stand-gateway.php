<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/src/Core/Config.php';

final class StandPaymentException extends RuntimeException {}

/** Direct merchant integration. No campaign API or campaign identifier is involved. */
final class StandVandarGateway
{
    private string $key;
    private $transport;
    public function __construct(string $branchSlug, ?callable $transport = null)
    {
        $prefix = 'STAND_VANDAR_' . strtoupper(preg_replace('/[^a-z0-9]+/i', '_', $branchSlug)) . '_API_KEY';
        $this->key = (string)\Maksa\Core\Config::get($prefix, \Maksa\Core\Config::get('STAND_VANDAR_API_KEY', ''));
        if ($this->key === '') throw new StandPaymentException('اتصال درگاه استند هنوز تنظیم نشده است؛ با پشتیبانی تماس بگیرید.');
        $this->transport = $transport;
    }
    public function request(int $toman, string $invoice, string $callback, string $mobile): array
    {
        $response = $this->post('send', ['amount'=>$toman * 10, 'callback_url'=>$callback,
            'mobile_number'=>$mobile, 'factorNumber'=>$invoice, 'description'=>'خرید استند مکسا — ' . $invoice]);
        $token = (string)($response['token'] ?? '');
        if ((int)($response['status'] ?? 0) !== 1 || !preg_match('/^[A-Za-z0-9._~-]{8,500}$/D', $token)) {
            throw new StandPaymentException('دریافت شناسه پرداخت از درگاه ناموفق بود؛ دوباره تلاش کنید.');
        }
        return ['authority'=>$token, 'redirect_url'=>'https://ipg.vandar.io/v3/' . rawurlencode($token)];
    }
    public function verify(string $token, int $toman, string $invoice): array
    {
        $payload = ['token'=>$token];
        $transaction = $this->post('transaction', $payload);
        $code = (int)($transaction['code'] ?? -1);
        if ((int)($transaction['status'] ?? 0) !== 1 || !in_array($code, [1,2,3,4], true)) return ['status'=>'pending'];
        $this->checkBinding($transaction, $toman, $invoice);
        if (in_array($code, [3,4], true)) return ['status'=>'failed'];
        if ($code === 1) {
            $verified = $this->post('verify', $payload);
            if ((int)($verified['status'] ?? 0) === 1) {
                $this->checkBinding($verified, $toman, $invoice);
                return $this->success($verified);
            }
            // Recover a concurrent verification or a response lost after the bank committed.
            $transaction = $this->post('transaction', $payload);
            $this->checkBinding($transaction, $toman, $invoice);
            if ((int)($transaction['status'] ?? 0) !== 1 || (int)($transaction['code'] ?? 0) !== 2) return ['status'=>'pending'];
        }
        return $this->success($transaction);
    }
    private function checkBinding(array $data, int $toman, string $invoice): void
    {
        $amount = (string)($data['amount'] ?? '');
        if (!preg_match('/^\d+(?:\.0{1,2})?$/D', $amount)
            || (int)explode('.', $amount)[0] !== $toman * 10 || (string)($data['factorNumber'] ?? '') !== $invoice) {
            throw new StandPaymentException('مبلغ یا شماره سفارش در پاسخ درگاه مطابقت ندارد.');
        }
    }
    private function success(array $data): array
    {
        $ref = (string)($data['transId'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{1,100}$/D', $ref)) throw new StandPaymentException('شناسه تأیید پرداخت معتبر نیست.');
        $date = new DateTimeImmutable('now', new DateTimeZone('Asia/Tehran'));
        if (!empty($data['paymentDate'])) {
            try { $date = new DateTimeImmutable((string)$data['paymentDate'], new DateTimeZone('Asia/Tehran')); }
            catch (Throwable $e) { throw new StandPaymentException('تاریخ تأیید پرداخت معتبر نیست.'); }
        }
        return ['status'=>'success', 'ref_id'=>$ref, 'paid_at'=>$date->setTimezone(new DateTimeZone('Asia/Tehran'))->format('Y-m-d H:i:s')];
    }
    private function post(string $method, array $payload): array
    {
        $payload = ['api_key'=>$this->key] + $payload;
        if ($this->transport !== null) return ($this->transport)($method, $payload);
        if (!function_exists('curl_init')) throw new StandPaymentException('اتصال به درگاه پرداخت در دسترس نیست.');
        $handle = curl_init('https://ipg.vandar.io/api/v3/' . $method);
        curl_setopt_array($handle, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER=>['Content-Type: application/json', 'Accept: application/json'], CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_CONNECTTIMEOUT=>5, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2]);
        $raw = curl_exec($handle);
        $http = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        $response = is_string($raw) ? json_decode($raw, true) : null;
        if ($http === 0 || $http >= 500 || !is_array($response)) throw new StandPaymentException('ارتباط با درگاه پرداخت ناموفق بود؛ دوباره تلاش کنید.');
        return $response;
    }
}
