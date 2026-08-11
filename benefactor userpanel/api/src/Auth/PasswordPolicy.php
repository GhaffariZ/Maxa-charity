<?php

declare(strict_types=1);

namespace Maksa\Auth;

/** Password hashing + strength policy. */
final class PasswordPolicy
{
    public const MIN_LENGTH = 10;

    /** A small list of obviously-compromised / trivial passwords to reject. */
    private const BLOCKLIST = [
        'password', 'password1', '12345678', '123456789', '1234567890',
        'qwerty123', 'iloveyou', 'admin123', 'welcome1', 'letmein123',
        'changeme1', 'maksa1234', 'p@ssw0rd', 'qwertyuiop',
    ];

    public static function hash(string $plain): string
    {
        // Argon2id where available (PHP 8.1+ on a modern host), else bcrypt(12).
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($plain, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 1,
            ]);
        }
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 1,
            ]);
        }
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Returns an error message if the password is unacceptable, or null if OK.
     * Strength: length + variety, plus a blocklist check (zxcvbn-lite).
     */
    public static function validate(string $plain, string $email = ''): ?string
    {
        $len = mb_strlen($plain);
        if ($len < self::MIN_LENGTH) {
            return 'رمز عبور باید حداقل ' . self::MIN_LENGTH . ' کاراکتر باشد.';
        }
        if ($len > 200) {
            return 'رمز عبور بیش از حد طولانی است.';
        }
        if (in_array(mb_strtolower($plain), self::BLOCKLIST, true)) {
            return 'این رمز عبور بسیار رایج است؛ رمزی قوی‌تر انتخاب کنید.';
        }
        if ($email !== '' && stripos($plain, explode('@', $email)[0]) !== false) {
            return 'رمز عبور نباید شامل بخشی از ایمیل شما باشد.';
        }

        // Require at least 3 of: lower, upper, digit, symbol.
        $classes = 0;
        $classes += preg_match('/[a-z]/', $plain) ? 1 : 0;
        $classes += preg_match('/[A-Z]/', $plain) ? 1 : 0;
        $classes += preg_match('/[0-9]/', $plain) ? 1 : 0;
        $classes += preg_match('/[^A-Za-z0-9]/', $plain) ? 1 : 0;
        if ($classes < 3) {
            return 'رمز عبور باید ترکیبی از حروف بزرگ، کوچک، اعداد و نمادها باشد.';
        }

        return null;
    }
}
