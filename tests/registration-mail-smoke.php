<?php

declare(strict_types=1);

// Uses a fake mail transport and an in-memory database; never sends real email.
namespace PHPMailer\PHPMailer {
    #[\AllowDynamicProperties]
    final class PHPMailer
    {
        public const ENCRYPTION_STARTTLS = 'tls';
        public const ENCRYPTION_SMTPS = 'ssl';
        public static string $result = 'ok';
        public static array $messages = [];
        public string $ErrorInfo = '';
        public string $Host = '';
        public int $Port = 465;
        public string $SMTPSecure = '';
        private object $smtp;
        public function __construct(bool $exceptions) { $this->smtp = (object)['Timelimit' => 300]; }
        public function isSMTP(): void {}
        public function isHTML(bool $html): void {}
        public function setFrom(string $address, string $name): void {}
        public function addReplyTo(string $address): void {}
        public function addAddress(string $address, string $name): void { $this->recipient = $address; }
        public function getSMTPInstance(): object { return $this->smtp; }
        public function smtpClose(): void {}
        public function send(): bool
        {
            self::$messages[] = clone $this;
            if (self::$result === 'throw') {
                throw new \RuntimeException('Simulated SMTP connection failure');
            }
            return self::$result !== 'false';
        }
    }
}

namespace Maksa\Core {
    final class TestResponse extends \RuntimeException
    {
        public function __construct(public mixed $data, public int $status) { parent::__construct(); }
    }
    final class Response
    {
        public static function success(mixed $data = null, int $status = 200): never
        {
            throw new TestResponse($data, $status);
        }
    }
}

namespace {
    $apiRoot = $argv[1] ?? __DIR__ . '/../public_html/api';
    spl_autoload_register(static function (string $class) use ($apiRoot): void {
        if (str_starts_with($class, 'Maksa\\')) {
            require $apiRoot . '/src/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        }
    });
    function check(bool $condition, string $message): void
    {
        if (!$condition) throw new RuntimeException($message);
    }
    function request(string $method, array $body): Maksa\Core\TestResponse
    {
        $reflection = new ReflectionClass(Maksa\Core\Request::class);
        $request = $reflection->newInstanceWithoutConstructor();
        $reflection->getConstructor()->invoke($request, 'POST', '/auth/' . $method, $body, [], [], [], []);
        try {
            (new Maksa\Controllers\AuthController())->{$method}($request);
        } catch (Maksa\Core\TestResponse $response) {
            return $response;
        }
        throw new RuntimeException('Missing API response');
    }

    $configFile = tempnam(sys_get_temp_dir(), 'macsa-mail-config-');
    $explicitFile = tempnam(sys_get_temp_dir(), 'macsa-mail-config-');
    $logFile = tempnam(sys_get_temp_dir(), 'macsa-mail-log-');
    try {
        file_put_contents($configFile, "SMTP_TEST_PRIORITY=file\nSMTP_TEST_ENCRYPTION=starttls # explanation\nSMTP_TEST_PASSWORD=\"test # password\" # explanation\nSMTP_TEST_ORDER=fallback\n");
        file_put_contents($explicitFile, "SMTP_TEST_ORDER=explicit\n");
        putenv('MAKSA_ENV_PATH=' . $configFile);
        putenv('SMTP_TEST_PRIORITY=environment');
        Maksa\Core\Config::load($explicitFile);
        check(Maksa\Core\Config::get('SMTP_TEST_PRIORITY') === 'environment', 'Environment priority');
        check(Maksa\Core\Config::get('SMTP_TEST_ORDER') === 'explicit', 'Explicit config path priority');
        check(Maksa\Core\Config::get('SMTP_TEST_ENCRYPTION') === 'starttls', 'Inline comment parsing');
        check(Maksa\Core\Config::get('SMTP_TEST_PASSWORD') === 'test # password', 'Quoted password parsing');
        foreach (['LOG_PATH' => $logFile, 'MAIL_HOST' => 'smtp.example.test', 'MAIL_USERNAME' => 'mailer@example.test',
            'MAIL_PASSWORD' => 'not-a-real-password', 'MAIL_FROM_ADDRESS' => 'mailer@example.test', 'MAIL_REPLY_TO' => '',
            'APP_URL' => 'https://example.test/benefactor-dashboard'] as $key => $value) {
            Maksa\Core\Config::set($key, $value);
        }
        $mailer = new Maksa\Mail\Mailer();
        foreach ([[465, 'tls', 'ssl'], [587, 'tls', 'tls'], [465, 'smtps', 'ssl'], [587, 'starttls', 'tls'], [587, 'auto', 'tls']] as [$port, $mode, $expected]) {
            Maksa\Core\Config::set('MAIL_PORT', (string)$port);
            Maksa\Core\Config::set('MAIL_ENCRYPTION', $mode);
            check($mailer->send('USER@example.test', 'نام', 'موضوع', '<p>متن</p>', 'متن'), 'SMTP send');
            $message = end(PHPMailer\PHPMailer\PHPMailer::$messages);
            check($message->SMTPSecure === $expected && $message->Timeout === 15 && $message->getSMTPInstance()->Timelimit === 15, 'SMTP encryption/timeout');
            check($message->recipient === 'user@example.test', 'Recipient normalization');
        }
        foreach (['false', 'throw'] as $failure) {
            PHPMailer\PHPMailer\PHPMailer::$result = $failure;
            check(!$mailer->send('user@example.test', '', 'موضوع', '<p>متن</p>', 'متن'), 'SMTP failure not detected');
        }
        check(!$mailer->send('invalid-address', '', '', '', ''), 'Invalid recipient accepted');
        Maksa\Core\Config::set('MAIL_ENCRYPTION', 'invalid');
        check(!$mailer->send('user@example.test', '', '', '', ''), 'Invalid encryption accepted');
        Maksa\Core\Config::set('MAIL_ENCRYPTION', 'starttls');

        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->sqliteCreateFunction('FROM_UNIXTIME', static fn(int $time): string => gmdate('Y-m-d H:i:s', $time));
        $db->exec("CREATE TABLE panel_users (id INTEGER PRIMARY KEY, email TEXT UNIQUE, password_hash TEXT, status TEXT, email_verified_at TEXT);
            CREATE TABLE user_profiles (user_id INTEGER, first_name TEXT, last_name TEXT, donor_tier_id INTEGER);
            CREATE TABLE donor_tiers (id INTEGER PRIMARY KEY, slug TEXT);
            CREATE TABLE user_notification_prefs (user_id INTEGER);
            CREATE TABLE email_verification_tokens (id INTEGER PRIMARY KEY, user_id INTEGER, token_hash TEXT, expires_at TEXT, used_at TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
            CREATE TABLE audit_log (user_id INTEGER, action TEXT, ip TEXT, user_agent TEXT, metadata TEXT);");
        (new ReflectionProperty(Maksa\Core\Database::class, 'pdo'))->setValue(null, $db);
        $body = ['email' => 'donor@example.test', 'password' => 'Safe!Complex4829', 'first_name' => 'نام اولیه'];
        PHPMailer\PHPMailer\PHPMailer::$result = 'false';
        $first = request('register', $body);
        check($first->status === 201 && !str_contains($first->data['message'], 'ارسال شد'), 'False delivery claim');
        $originalHash = $db->query('SELECT password_hash FROM panel_users')->fetchColumn();
        $count = count(PHPMailer\PHPMailer\PHPMailer::$messages);
        $body['password'] = 'Different!Pass4839';
        $body['first_name'] = 'نام جایگزین';
        request('register', $body);
        check(count(PHPMailer\PHPMailer\PHPMailer::$messages) === $count, 'Verification cooldown');
        $db->exec("UPDATE email_verification_tokens SET created_at = '2000-01-01 00:00:00'");
        PHPMailer\PHPMailer\PHPMailer::$result = 'ok';
        $repeat = request('register', $body);
        check(count(PHPMailer\PHPMailer\PHPMailer::$messages) === $count + 1, 'Repeated registration did not resend');
        check($repeat->data === $first->data, 'Registration exposed account existence');
        check($db->query('SELECT COUNT(*) FROM panel_users')->fetchColumn() === 1, 'Duplicate account created');
        check($db->query('SELECT password_hash FROM panel_users')->fetchColumn() === $originalHash, 'Existing password replaced');
        check($db->query('SELECT first_name FROM user_profiles')->fetchColumn() === 'نام اولیه', 'Existing profile replaced');
        check((int)$db->query('SELECT COUNT(*) FROM email_verification_tokens')->fetchColumn() === 2, 'Previous link invalidated');
        $count = count(PHPMailer\PHPMailer\PHPMailer::$messages);
        $known = request('resendVerification', ['email' => 'donor@example.test']);
        $unknown = request('resendVerification', ['email' => 'absent@example.test']);
        check($known->data === $unknown->data && $known->status === $unknown->status, 'Resend exposed account existence');
        check(count(PHPMailer\PHPMailer\PHPMailer::$messages) === $count, 'Resend bypassed cooldown');
        $db->exec("UPDATE email_verification_tokens SET created_at = '2000-01-01 00:00:00'");
        request('resendVerification', ['email' => 'donor@example.test']);
        check(count(PHPMailer\PHPMailer\PHPMailer::$messages) === $count + 1, 'Resend endpoint failed');
        $db->exec("UPDATE panel_users SET email_verified_at = '2026-01-01', status = 'active'");
        check(request('register', $body)->data === $first->data, 'Verified account response differs');
        $db->exec("UPDATE panel_users SET email_verified_at = NULL, status = 'suspended'");
        $count = count(PHPMailer\PHPMailer\PHPMailer::$messages);
        request('register', $body);
        request('resendVerification', ['email' => 'donor@example.test']);
        check(count(PHPMailer\PHPMailer\PHPMailer::$messages) === $count, 'Sent activation mail for suspended account');

        $controller = new Maksa\Controllers\AuthController();
        $appUrl = new ReflectionMethod($controller, 'appUrl');
        Maksa\Core\Config::set('APP_URL', 'https://example.test');
        check($appUrl->invoke($controller, '/verify?token=test') === 'https://example.test/benefactor-dashboard/verify?token=test', 'Verification URL missing frontend base');
        Maksa\Core\Config::set('APP_URL', '/relative');
        try {
            $appUrl->invoke($controller, '/verify');
            throw new LogicException('Relative APP_URL accepted');
        } catch (RuntimeException $error) {
            check(!$error instanceof LogicException, $error->getMessage());
        }
        $logs = file_get_contents($logFile);
        check(str_contains($logs, 'Verification email not accepted by SMTP'), 'Delivery failure not logged');
        check(!str_contains($logs, 'not-a-real-password') && !str_contains($logs, '?token='), 'Mail secrets leaked to logs');
        echo "registration mail smoke check: OK\n";
    } finally {
        unlink($configFile);
        unlink($explicitFile);
        unlink($logFile);
    }
}
