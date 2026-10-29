<?php
declare(strict_types=1);

// Fixed merchant callback: no receipt, proof, browser session or cookies required.
$_GET['callback'] = 'token';
require __DIR__ . '/index.php';
