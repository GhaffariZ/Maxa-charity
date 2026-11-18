<?php
declare(strict_types=1);
require_once __DIR__ . '/stand-gateway.php';

final class StandPayments
{
    private $gatewayFactory;
    public function __construct(private PDO $db, ?callable $gatewayFactory = null, private ?string $previewOrigin = null)
    {
        $this->gatewayFactory = $gatewayFactory ?? static fn(string $slug) => new StandVandarGateway($slug);
    }
    public function orderForUser(string $tracking, int $userId): array
    {
        $st = $this->db->prepare("SELECT o.*, b.slug AS center_slug, b.status AS branch_status
            FROM orders o JOIN branches b ON b.id = o.branch_id
            WHERE o.tracking_code = ? AND o.user_id = ? AND o.order_type = 'stand' LIMIT 1");
        $st->execute([$tracking,$userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new StandPaymentException('سفارش یافت نشد.');
        return $row;
    }
    public function start(array $order, string $host, string $mobile, string $name): array
    {
        if ($order['branch_status'] !== 'active') throw new StandPaymentException('شعبه سفارش فعال نیست.');
        $st = $this->db->prepare("SELECT 1 FROM branch_features WHERE branch_id = ? AND feature = 'stands' AND enabled = 1");
        $st->execute([$order['branch_id']]);
        if (!$st->fetchColumn()) throw new StandPaymentException('پرداخت استند برای این شعبه فعال نیست.');
        $amount = (int)$order['total_price'];
        if ($amount < 1000 || $amount > 500000000) throw new StandPaymentException('مبلغ سفارش برای پرداخت معتبر نیست.');
        $mobile = strtr(trim($mobile), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        if (!preg_match('/^(?:\+98|0098|98|0)?9\d{9}$/D',$mobile)) throw new StandPaymentException('شماره همراه معتبر برای پرداخت لازم است.');
        $origin = $this->previewOrigin;
        if ($origin === null) {
            // Prefer a configured origin; Host is validated and never used as a gateway destination.
            $configured = (string)\Maksa\Core\Config::get('STAND_PAYMENT_ORIGIN', '');
            if ($configured !== '') {
                if (!preg_match('#^https://[a-z0-9.-]+$#iD',$configured)) throw new StandPaymentException('دامنه پرداخت تنظیم نشده است.');
                $origin = $configured;
            } else {
                if (!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9-]+(?::443)?$/iD',$host)) throw new StandPaymentException('دامنه پرداخت معتبر نیست.');
                $origin = 'https://' . preg_replace('/:443$/','',$host);
            }
        }
        $gateway = ($this->gatewayFactory)($order['center_slug']);
        $st = $this->db->prepare('INSERT INTO stand_payments (order_id,branch_id,receipt_token,center_slug,amount)
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE order_id = order_id');
        $st->execute([$order['id'],$order['branch_id'],bin2hex(random_bytes(32)),$order['center_slug'],$amount]);
        $receipt = $this->byOrder((int)$order['id']);
        if ((int)$receipt['amount'] !== $amount || (int)$receipt['branch_id'] !== (int)$order['branch_id']) throw new StandPaymentException('اطلاعات مالی سفارش مطابقت ندارد.');
        if ($receipt['remote_payment_id']) $receipt = $this->sync($receipt);
        if (in_array($receipt['status'],['success','failed'],true)) return ['status'=>$receipt['status'],'tracking_code'=>$order['tracking_code']];
        if ($receipt['redirect_data']) return json_decode($receipt['redirect_data'],true,32,JSON_THROW_ON_ERROR);
        $claim = $this->db->prepare("UPDATE stand_payments SET status='requesting' WHERE id=? AND status='pending' AND remote_payment_id IS NULL");
        $claim->execute([$receipt['id']]);
        if ($claim->rowCount() !== 1) throw new StandPaymentException('درخواست پرداخت در حال پردازش است؛ چند لحظه دیگر دوباره تلاش کنید.');
        try {
            $payment = $gateway->request($amount,'stand-'.$receipt['id'], $origin.'/stand-payment-callback.php?receipt='.$receipt['receipt_token'],$mobile);
            $authority = (string)($payment['authority'] ?? '');
            if (!preg_match('/^[A-Za-z0-9._~-]{8,500}$/D',$authority)) throw new StandPaymentException('شناسه درگاه معتبر نیست.');
            $url = (string)$payment['redirect_url'];
            if ($this->previewOrigin === null) {
                if ($url !== 'https://ipg.vandar.io/v3/'.rawurlencode($authority)) throw new StandPaymentException('نشانی درگاه معتبر نیست.');
                $url = '/stand-payment-redirect.php?target='.rawurlencode(rtrim(strtr(base64_encode($url),'+/','-_'),'='));
            }
            $result = ['status'=>'pending','tracking_code'=>$order['tracking_code'],'source'=>'stands','id'=>$authority,
                'redirect_url'=>$url,'redirect_method'=>'GET','redirect_fields'=>[]];
            $st = $this->db->prepare("UPDATE stand_payments SET remote_payment_id=?,redirect_data=?,status='pending' WHERE id=? AND status='requesting'");
            $st->execute([$authority,json_encode($result,JSON_THROW_ON_ERROR),$receipt['id']]);
            return $result;
        } catch (Throwable $e) {
            // Unexposed bank tokens cannot be paid. A retry can safely request a fresh token.
            $st = $this->db->prepare("UPDATE stand_payments SET status='pending' WHERE id=? AND status='requesting' AND remote_payment_id IS NULL");
            $st->execute([$receipt['id']]);
            throw $e;
        }
    }
    public function byOrder(int $id): ?array { return $this->find('order_id',$id); }
    public function byToken(string $token): ?array
    {
        return preg_match('/^[a-f0-9]{64}$/D',$token) ? $this->find('receipt_token',$token) : null;
    }
    private function find(string $column, $value): ?array
    {
        $st=$this->db->prepare('SELECT * FROM stand_payments WHERE '.$column.' = ? LIMIT 1');
        $st->execute([$value]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function callback(string $receiptToken, string $authority): array
    {
        $receipt = $this->byToken($receiptToken);
        if (!$receipt || !$receipt['remote_payment_id'] || !hash_equals($receipt['remote_payment_id'],$authority)) throw new StandPaymentException('شناسه پرداخت با سفارش مطابقت ندارد.');
        return $this->sync($receipt);
    }
    public function sync(array $receipt): array
    {
        if ($receipt['status'] !== 'pending' || !$receipt['remote_payment_id']) return $receipt;
        $result = ($this->gatewayFactory)($receipt['center_slug'])->verify($receipt['remote_payment_id'],(int)$receipt['amount'],'stand-'.$receipt['id']);
        if ($result['status'] === 'success') {
            $st=$this->db->prepare("UPDATE stand_payments SET status='success',gateway_ref_id=?,paid_at=? WHERE id=? AND status='pending'");
            $st->execute([$result['ref_id'],$result['paid_at'],$receipt['id']]);
        } elseif ($result['status'] === 'failed') {
            $st=$this->db->prepare("UPDATE stand_payments SET status='failed' WHERE id=? AND status='pending'");
            $st->execute([$receipt['id']]);
        }
        return $this->byOrder((int)$receipt['order_id']);
    }
}
