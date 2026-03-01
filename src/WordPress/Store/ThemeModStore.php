<?php

declare(strict_types=1);

namespace OptStack\WordPress\Store;

use OptStack\Core\Contract\StoreInterface;

/**
 * Theme Mod Store
 *
 * Store adapter for WordPress theme mods (get_theme_mod / set_theme_mod).
 * Data is stored as a single serialized array under one theme_mod key (e.g. stack ID).
 * Theme-specific; suitable for Customizer-backed theme options.
 */
class ThemeModStore implements StoreInterface
{
    /**
     * The theme_mod key (e.g. stack ID).
     */
    protected string $key;

    /**
     * Cached data.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $cache = null;

    /**
     * Create a new ThemeModStore instance.
     *
     * @param string $key Theme mod key (e.g. stack ID)
     */
    public function __construct(string $key)
    {
        $this->key = $key;
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
     * Delete the entire theme_mod entry.
     */
    public function deleteAll(): bool
    {
        $this->cache = [];
        remove_theme_mod($this->key);

        return true;
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): void
    {
        $this->cache = null;
    }

    /**
     * Get the theme_mod key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Load data from theme mod.
     *
     * @return array<string, mixed>
     */
    protected function loadData(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $data = get_theme_mod($this->key, []);

        if (!is_array($data)) {
            $data = [];
        }

        $this->cache = $data;

        return $data;
    }

    /**
     * Save data to theme mod.
     *
     * @param array<string, mixed> $data
     */
    protected function saveData(array $data): bool
    {
        $this->cache = $data;
        set_theme_mod($this->key, $data);

        return true;
    }
}
