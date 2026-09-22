<?php

declare(strict_types=1);

require __DIR__ . '/../public_html/api/src/Controllers/MedicalRecordController.php';

$controller = (new ReflectionClass(Maksa\Controllers\MedicalRecordController::class))->newInstanceWithoutConstructor();
$method = new ReflectionMethod($controller, 'splitName');
$method->setAccessible(true);

if ($method->invoke($controller, 'علی رضایی') !== ['علی', 'رضایی']) {
    throw new RuntimeException('Full-name splitting failed.');
}

echo "medical record controller smoke check: OK\n";
