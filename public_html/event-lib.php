<?php
declare(strict_types=1);

function event_h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function event_jalali_to_gregorian(int $jy, int $jm, int $jd): array {
    $jy -= 979; $jm -= 1; $jd -= 1; $days = 365 * $jy + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4);
    for ($i = 0; $i < $jm; $i++) $days += $i < 6 ? 31 : 30; $days += $jd + 79;
    $gy = 1600 + 400 * intdiv($days, 146097); $days %= 146097; $leap = true;
    if ($days >= 36525) { $days--; $gy += 100 * intdiv($days, 36524); $days %= 36524; if ($days >= 365) $days++; else $leap = false; }
    $gy += 4 * intdiv($days, 1461); $days %= 1461;
    if ($days >= 366) { $leap = false; $days--; $gy += intdiv($days, 365); $days %= 365; }
    $gd = $days + 1; $gmd = [31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; $gm = 0;
    while ($gm < 12 && $gd > $gmd[$gm]) { $gd -= $gmd[$gm]; $gm++; } return [$gy, $gm + 1, $gd];
}
function event_gregorian_to_jalali(int $gy, int $gm, int $gd): array {
    $gdm = [0,31,59,90,120,151,181,212,243,273,304,334]; $gy2 = $gm > 2 ? $gy + 1 : $gy;
    $days = 355666 + 365 * $gy + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $gdm[$gm - 1];
    $jy = -1595 + 33 * intdiv($days, 12053); $days %= 12053; $jy += 4 * intdiv($days, 1461); $days %= 1461;
    if ($days > 365) { $jy += intdiv($days - 1, 365); $days = ($days - 1) % 365; }
    return [$jy, $days < 186 ? 1 + intdiv($days, 31) : 7 + intdiv($days - 186, 30), $days < 186 ? 1 + $days % 31 : 1 + ($days - 186) % 30];
}
function event_jalali_input_to_date(string $raw): ?string {
    $raw = strtr(trim(str_replace('-', '/', $raw)), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $raw, $m)) return null;
    $jy = (int)$m[1]; $jm = (int)$m[2]; $jd = (int)$m[3];
    if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > ($jm <= 6 ? 31 : ($jm <= 11 ? 30 : 30))) return null;
    [$gy, $gm, $gd] = event_jalali_to_gregorian($jy, $jm, $jd);
    if (!checkdate($gm, $gd, $gy)) return null;
    [$ry, $rm, $rd] = event_gregorian_to_jalali($gy, $gm, $gd);
    return [$ry, $rm, $rd] === [$jy, $jm, $jd] ? sprintf('%04d-%02d-%02d', $gy, $gm, $gd) : null;
}
function event_date_label(?string $date): string {
    if (!$date) return 'تاریخ مشخص نشده'; [$jy, $jm, $jd] = event_gregorian_to_jalali((int)substr($date, 0, 4), (int)substr($date, 5, 2), (int)substr($date, 8, 2));
    $months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند']; return $jd . ' ' . $months[$jm - 1] . ' ' . $jy;
}
function event_slug(string $title, int $id = 0): string {
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', trim(mb_strtolower($title, 'UTF-8'))) ?? ''; $slug = trim($slug, '-'); return ($slug !== '' ? $slug : 'event') . ($id ? '-' . $id : '-' . substr((string)time(), -5));
}
function event_asset(array $file, string $kind, array $allowed, int $maxBytes = 8388608): ?string {
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > $maxBytes) throw new RuntimeException('فایل انتخاب‌شده معتبر نیست یا حجم آن زیاد است.');
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)); if (!in_array($ext, $allowed, true)) throw new RuntimeException('فرمت فایل انتخاب‌شده مجاز نیست.');
    $dir = __DIR__ . '/uploads/events/'; if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('پوشه آپلود ساخته نشد.');
    $name = $kind . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $ext; if (!move_uploaded_file($file['tmp_name'], $dir . $name)) throw new RuntimeException('ذخیره فایل انجام نشد.'); return '/uploads/events/' . $name;
}
