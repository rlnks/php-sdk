<?php

declare(strict_types=1);

namespace Rlnks\Exceptions;

/**
 * Exception thrown when request validation fails.
 *
 * Use getErrorDetails() to get field-level validation errors.
 */
class ValidationException extends RlnksException
{
    /**
     * Get validation errors grouped by field.
     *
     * @return array<string, array<string>> Field => error messages
     */
    public function getValidationErrors(): array
    {
        return $this->errorDetails ?? [];
    }

    /**
     * Get errors for a specific field.
     *
     * @return array<string>
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errorDetails[$field] ?? [];
    }

    /**
     * Check if a specific field has errors.
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errorDetails[$field]) && count($this->errorDetails[$field]) > 0;
    }
}
