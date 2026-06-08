<?php

declare(strict_types=1);

namespace Maksa\Core\Exceptions;

/** Raised when input validation fails. Carries per-field error messages. */
final class ValidationException extends ApiException
{
    /** @param array<string,string> $fieldErrors */
    public function __construct(array $fieldErrors, string $message = 'داده‌های ورودی نامعتبر است.')
    {
        parent::__construct(422, 'validation_failed', $message, ['fields' => $fieldErrors]);
    }
}
