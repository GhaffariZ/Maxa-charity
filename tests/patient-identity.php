<?php

declare(strict_types=1);

require __DIR__ . '/../public_html/api/src/Core/Exceptions/ApiException.php';
require __DIR__ . '/../public_html/api/src/Core/Exceptions/ValidationException.php';
require __DIR__ . '/../public_html/api/src/Support/PatientIdentity.php';

use Maksa\Support\PatientIdentity;
use Maksa\Core\Exceptions\ValidationException;

function expect(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

expect(PatientIdentity::normalizeDigits(' ۰۰۸٤٥٧۵۹۴۸ ') === '0084575948', 'Mixed Persian/Arabic digits');
expect(PatientIdentity::validCode('0084575948', false), 'Valid code with leading zeros');
foreach (['0084575949', '1111111111', '0000000000', '008457594', '00845759480', '00845759x8'] as $code) {
    expect(!PatientIdentity::validCode($code, false), 'Invalid national code accepted: ' . $code);
}
expect(PatientIdentity::validCode('012345678901', true), 'Foreign code format');
expect(!PatientIdentity::validCode('012345678901', false), 'Foreign code used as national code');
expect(!PatientIdentity::validCode('111111111111', true), 'Repeated foreign code');

$today = new DateTimeImmutable('2026-10-26', new DateTimeZone('Asia/Tehran'));
expect(PatientIdentity::birthDate(null, null, null, $today)['birth_date'] === null, 'Optional date');
foreach ([[1400, 1, 1, '2021-03-21'], [1399, 12, 30, '2021-03-20'],
          [1403, 12, 30, '2025-03-20'], [1404, 1, 1, '2025-03-21'],
          [1370, 7, 15, '1991-10-07']] as [$year, $month, $day, $expected]) {
    expect(PatientIdentity::birthDate($year, $month, $day, $today)['birth_date'] === $expected, 'Date conversion: ' . $expected);
}
expect(PatientIdentity::birthDate(1405, 8, 4, $today)['age'] === 0, 'Newborn date');
foreach ([[1404, 12, 30], [1400, 7, 31], [1400, null, 1], [1405, 8, 5], [1501, 1, 1]] as $date) {
    try {
        PatientIdentity::birthDate($date[0], $date[1], $date[2], $today);
        throw new RuntimeException('Invalid birth date accepted');
    } catch (ValidationException $e) {
        expect(isset($e->getDetails()['fields']['birth_day']), 'Field validation error');
    }
}
echo "patient identity and birth date: OK\n";
