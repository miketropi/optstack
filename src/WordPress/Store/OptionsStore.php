<?php

declare(strict_types=1);

namespace OptStack\WordPress\Store;

use OptStack\Core\Contract\StoreInterface;

/**
 * Options Store
 *
 * Store adapter for WordPress options (wp_options table).
 * Data is stored as a single serialized array under the stack ID.
 */
class OptionsStore implements StoreInterface
{
    /**
     * The option name (stack ID).
     */
    protected string $optionName;

    /**
     * Whether to autoload the option.
     */
    protected bool $autoload;

    /**
     * Cached data.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $cache = null;

    /**
     * Create a new OptionsStore instance.
     */
    public function __construct(string $optionName, bool $autoload = true)
    {
        $this->optionName = $optionName;
        $this->autoload = $autoload;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->loadData();

        return $data[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): bool
    {
        $data = $this->loadData();
        $data[$key] = $value;

        return $this->saveData($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        $data = $this->loadData();

        if (!isset($data[$key])) {
            return false;
        }

        unset($data[$key]);

        return $this->saveData($data);
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->loadData();
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $data = $this->loadData();

        return isset($data[$key]);
    }

    /**
     * Set multiple values at once.
     *
     * @param array<string, mixed> $values
     */
    public function setMany(array $values): bool
    {
        $data = $this->loadData();
        $data = array_merge($data, $values);

        return $this->saveData($data);
    }

    /**
     * Replace all data.
     *
     * @param array<string, mixed> $data
     */
    public function replace(array $data): bool
    {
        return $this->saveData($data);
    }

    /**
     * Delete the entire option.
     */
    public function deleteAll(): bool
    {
        $this->cache = null;

        return delete_option($this->optionName);
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): void
    {
        $this->cache = null;
    }

    /**
     * Get the option name.
     */
    public function getOptionName(): string
    {
        return $this->optionName;
    }

    /**
     * Load data from WordPress option.
     *
     * @return array<string, mixed>
     */
    protected function loadData(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $data = get_option($this->optionName, []);

        if (!is_array($data)) {
            $data = [];
        }

        $this->cache = $data;

        return $data;
    }

    /**
     * Save data to WordPress option.
     *
     * @param array<string, mixed> $data
     */
    protected function saveData(array $data): bool
    {
        $this->cache = $data;

        return update_option($this->optionName, $data, $this->autoload);
    }
}
