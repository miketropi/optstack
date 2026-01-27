<?php

declare(strict_types=1);

namespace OptStack\Core\Contract;

/**
 * Validatable Interface
 *
 * Defines the contract for objects that can validate values.
 */
interface ValidatableInterface
{
    /**
     * Validate a value.
     *
     * @param mixed $value The value to validate
     * @return bool True if valid, false otherwise
     */
    public function validate(mixed $value): bool;

    /**
     * Get validation errors from the last validation.
     *
     * @return array<string> Array of error messages
     */
    public function getErrors(): array;
}
