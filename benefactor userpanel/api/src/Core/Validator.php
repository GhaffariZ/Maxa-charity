<?php

declare(strict_types=1);

namespace Maksa\Core;

use Maksa\Core\Exceptions\ValidationException;

/**
 * Whitelist validator. You declare exactly the fields you accept; anything else
 * in the payload is ignored (mass-assignment defense). Collects all field errors
 * and throws a single ValidationException.
 *
 * Usage:
 *   $data = (new Validator($request->body))
 *       ->email('email')
 *       ->string('first_name', max: 100, required: false)
 *       ->validated();
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];
    /** @var array<string,mixed> */
    private array $clean = [];

    /** @param array<string,mixed> $input */
    public function __construct(private readonly array $input) {}

    private function value(string $field): mixed
    {
        return $this->input[$field] ?? null;
    }

    private function fail(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function string(string $field, int $min = 0, int $max = 255, bool $required = true): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            if ($required) {
                $this->fail($field, 'این فیلد الزامی است.');
            }
            return $this;
        }
        if (!is_string($value)) {
            $this->fail($field, 'مقدار باید متن باشد.');
            return $this;
        }
        $value = trim($value);
        $len = mb_strlen($value);
        if ($len < $min) {
            $this->fail($field, "حداقل {$min} کاراکتر لازم است.");
        } elseif ($len > $max) {
            $this->fail($field, "حداکثر {$max} کاراکتر مجاز است.");
        } else {
            $this->clean[$field] = $value;
        }
        return $this;
    }

    public function email(string $field, bool $required = true): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            if ($required) {
                $this->fail($field, 'آدرس ایمیل الزامی است.');
            }
            return $this;
        }
        $value = strtolower(trim((string) $value));
        if (mb_strlen($value) > 255 || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->fail($field, 'آدرس ایمیل نامعتبر است.');
        } else {
            $this->clean[$field] = $value;
        }
        return $this;
    }

    public function int(string $field, ?int $min = null, ?int $max = null, bool $required = true): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            if ($required) {
                $this->fail($field, 'این فیلد الزامی است.');
            }
            return $this;
        }
        if (!is_numeric($value) || (int) $value != $value) {
            $this->fail($field, 'مقدار باید عدد صحیح باشد.');
            return $this;
        }
        $int = (int) $value;
        if ($min !== null && $int < $min) {
            $this->fail($field, "مقدار باید حداقل {$min} باشد.");
        } elseif ($max !== null && $int > $max) {
            $this->fail($field, "مقدار باید حداکثر {$max} باشد.");
        } else {
            $this->clean[$field] = $int;
        }
        return $this;
    }

    public function bool(string $field, bool $required = false): self
    {
        $value = $this->value($field);
        if ($value === null) {
            if ($required) {
                $this->fail($field, 'این فیلد الزامی است.');
            }
            return $this;
        }
        $this->clean[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        return $this;
    }

    /** @param list<string> $allowed */
    public function in(string $field, array $allowed, bool $required = true): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            if ($required) {
                $this->fail($field, 'این فیلد الزامی است.');
            }
            return $this;
        }
        if (!in_array($value, $allowed, true)) {
            $this->fail($field, 'مقدار انتخاب‌شده مجاز نیست.');
        } else {
            $this->clean[$field] = $value;
        }
        return $this;
    }

    /** Iranian mobile number: 09xxxxxxxxx (also accepts +98 / 0098 forms). */
    public function iranMobile(string $field, bool $required = false): self
    {
        $value = $this->value($field);
        if ($value === null || $value === '') {
            if ($required) {
                $this->fail($field, 'شماره همراه الزامی است.');
            }
            return $this;
        }
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
        if (str_starts_with($digits, '0098')) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        }
        if (!preg_match('/^09\d{9}$/', $digits)) {
            $this->fail($field, 'شماره همراه معتبر نیست.');
        } else {
            $this->clean[$field] = $digits;
        }
        return $this;
    }

    /** Returns only the validated (whitelisted) fields, or throws. */
    public function validated(): array
    {
        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }
        return $this->clean;
    }
}
