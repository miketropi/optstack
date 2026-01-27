<?php

declare(strict_types=1);

namespace OptStack\WordPress\Store;

use OptStack\Core\Contract\StoreInterface;

/**
 * Term Store
 *
 * Store adapter for WordPress term meta (wp_termmeta table).
 * Data is stored as a single serialized array under the stack ID as meta key.
 */
class TermStore implements StoreInterface
{
    /**
     * The term ID.
     */
    protected int $termId;

    /**
     * The meta key (stack ID).
     */
    protected string $metaKey;

    /**
     * Cached data.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $cache = null;

    /**
     * Create a new TermStore instance.
     */
    public function __construct(int $termId, string $metaKey)
    {
        $this->termId = $termId;
        $this->metaKey = $metaKey;
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
     * Get the term ID.
     */
    public function getTermId(): int
    {
        return $this->termId;
    }

    /**
     * Set the term ID.
     */
    public function setTermId(int $termId): self
    {
        if ($this->termId !== $termId) {
            $this->termId = $termId;
            $this->cache = null;
        }

        return $this;
    }

    /**
     * Get the meta key.
     */
    public function getMetaKey(): string
    {
        return $this->metaKey;
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
     * Delete the entire meta key.
     */
    public function deleteAll(): bool
    {
        $this->cache = null;

        return delete_term_meta($this->termId, $this->metaKey);
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): void
    {
        $this->cache = null;
    }

    /**
     * Load data from WordPress term meta.
     *
     * @return array<string, mixed>
     */
    protected function loadData(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $data = get_term_meta($this->termId, $this->metaKey, true);

        if (!is_array($data)) {
            $data = [];
        }

        $this->cache = $data;

        return $data;
    }

    /**
     * Save data to WordPress term meta.
     *
     * @param array<string, mixed> $data
     */
    protected function saveData(array $data): bool
    {
        $this->cache = $data;

        return (bool) update_term_meta($this->termId, $this->metaKey, $data);
    }
}
