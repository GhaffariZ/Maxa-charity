<?php

declare(strict_types=1);

namespace Maksa\Support;

use DateTimeImmutable;
use DateTimeZone;
use Maksa\Core\Exceptions\ValidationException;

final class PatientIdentity
{
    public static function normalizeDigits(string $value): string
    {
        return strtr(trim($value), array_combine(
            preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY),
            str_split('01234567890123456789')
        ));
    }

    public static function validCode(string $code, bool $foreign): bool
    {
        if (!preg_match($foreign ? '/^[0-9]{12}$/' : '/^[0-9]{10}$/', $code) ||
            preg_match('/^([0-9])\1+$/', $code)) {
            return false;
        }
        if ($foreign) {
            return true;
        }
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        return (int) $code[9] === ($remainder < 2 ? $remainder : 11 - $remainder);
    }

    /** Borkowski's 1210–1634 interval, used only for birth years 1280–1500. */
    public static function isLeapYear(int $year): bool
    {
        return ((($year - 1210 + 1) % 33) - 1) % 4 === 0;
    }

    /** @return array{birth_date: ?string, age: ?int} Gregorian date for storage. */
    public static function birthDate(?int $year, ?int $month, ?int $day, ?DateTimeImmutable $today = null): array
    {
        if ($year === null && $month === null && $day === null) {
            return ['birth_date' => null, 'age' => null];
        }
        if ($year === null || $month === null || $day === null || $year < 1280 || $year > 1500 ||
            $month < 1 || $month > 12 || $day < 1 ||
            $day > ($month <= 6 ? 31 : ($month < 12 || self::isLeapYear($year) ? 30 : 29))) {
            throw new ValidationException(['birth_day' => 'تاریخ تولد شمسی را کامل و معتبر انتخاب کنید.']);
        }
        $zone = new DateTimeZone('Asia/Tehran');
        // Anchor at 1400/01/01, then count complete Persian years and months.
        $offset = 0;
        for ($y = min($year, 1400); $y < max($year, 1400); $y++) {
            $offset += self::isLeapYear($y) ? 366 : 365;
        }
        if ($year < 1400) {
            $offset = -$offset;
        }
        $offset += $month <= 6 ? ($month - 1) * 31 : 186 + ($month - 7) * 30;
        $offset += $day - 1;
        $birth = (new DateTimeImmutable('2021-03-21', $zone))->modify(sprintf('%+d days', $offset));
        $today = ($today ?? new DateTimeImmutable('now', $zone))->setTimezone($zone)->setTime(0, 0);
        if ($birth > $today) {
            throw new ValidationException(['birth_day' => 'تاریخ تولد نمی‌تواند در آینده باشد.']);
        }
        return ['birth_date' => $birth->format('Y-m-d'), 'age' => $birth->diff($today)->y];
    }
}
