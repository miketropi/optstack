<?php

declare(strict_types=1);

namespace OptStack\Core\Contract;

/**
 * Store Interface
 *
 * Defines the contract for data storage adapters.
 * Implementations bridge OptStack with WordPress storage mechanisms.
 */
interface StoreInterface
{
    /**
     * Get a value from the store.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The stored value or default
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a value in the store.
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value): bool;

    /**
     * Delete a value from the store.
     *
     * @param string $key The key to delete
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool;

    /**
     * Get all values from the store.
     *
     * @return array<string, mixed> All stored values
     */
    public function all(): array;

    /**
     * Check if a key exists in the store.
     *
     * @param string $key The key to check
     * @return bool True if exists, false otherwise
     */
    public function has(string $key): bool;
}
