<?php

declare(strict_types=1);

namespace OptStack\Core\Contract;

/**
 * Sanitizable Interface
 *
 * Defines the contract for objects that can sanitize values.
 */
interface SanitizableInterface
{
    /**
     * Sanitize a value.
     *
     * @param mixed $value The value to sanitize
     * @return mixed The sanitized value
     */
    public function sanitize(mixed $value): mixed;
}
