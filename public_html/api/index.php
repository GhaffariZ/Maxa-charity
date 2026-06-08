<?php

declare(strict_types=1);

/**
 * Front controller for the Maksa API. Every /api/* request rewrites here
 * (see api/.htaccess). Responsibilities: bootstrap, error handling, dispatch.
 */

use Maksa\Core\Config;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Router;
use Maksa\Core\Security;
use Maksa\Support\Logger;

// ---- PSR-4-style autoloader (no Composer on the host) -----------------------
spl_autoload_register(static function (string $class): void {
    $prefix = 'Maksa\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Load PHPMailer. Two supported layouts:
//  1. Composer:   api/vendor/autoload.php
//  2. No Composer: unzip PHPMailer's release into api/vendor/PHPMailer/ so that
//     api/vendor/PHPMailer/src/{Exception,PHPMailer,SMTP}.php exist.
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
} else {
    $pmSrc = __DIR__ . '/vendor/PHPMailer/src/';
    if (is_file($pmSrc . 'PHPMailer.php')) {
        require $pmSrc . 'Exception.php';
        require $pmSrc . 'PHPMailer.php';
        require $pmSrc . 'SMTP.php';
    }
}

Config::load();

// ---- Hardened runtime defaults ----------------------------------------------
date_default_timezone_set('UTC');
$isDebug = Config::bool('APP_DEBUG', false);
ini_set('display_errors', $isDebug ? '1' : '0');
error_reporting(E_ALL);

// Surface PHP errors/notices as exceptions so nothing leaks half-rendered.
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

Security::applyResponseHeaders();
Security::handleCors();

try {
    $request = Request::capture();

    $router = new Router();
    (require __DIR__ . '/src/routes.php')($router);

    $router->dispatch($request);
} catch (ApiException $e) {
    Response::error($e->getStatusCode(), $e->getErrorCode(), $e->getMessage(), $e->getDetails());
} catch (\Throwable $e) {
    // Log the real detail server-side; return a sanitized message to the client.
    Logger::error('Unhandled exception', [
        'type'    => $e::class,
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
    Response::error(
        500,
        'server_error',
        $isDebug ? $e->getMessage() : 'خطای داخلی سرور رخ داد. لطفاً بعداً تلاش کنید.'
    );
}
