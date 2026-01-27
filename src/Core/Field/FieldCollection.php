<?php

declare(strict_types=1);

namespace OptStack\Core\Field;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Field Collection
 *
 * A collection of Field objects with helper methods.
 *
 * @implements IteratorAggregate<string, Field>
 */
class FieldCollection implements Countable, IteratorAggregate
{
    /**
     * The fields in this collection.
     *
     * @var array<string, Field>
     */
    protected array $fields = [];

    /**
     * Add a field to the collection.
     */
    public function add(Field $field): self
    {
        $this->fields[$field->getKey()] = $field;

        return $this;
    }

    /**
     * Get a field by key.
     */
    public function get(string $key): ?Field
    {
        return $this->fields[$key] ?? null;
    }

    /**
     * Check if a field exists.
     */
    public function has(string $key): bool
    {
        return isset($this->fields[$key]);
    }

    /**
     * Remove a field by key.
     */
    public function remove(string $key): self
    {
        unset($this->fields[$key]);

        return $this;
    }

    /**
     * Get all fields.
     *
     * @return array<string, Field>
     */
    public function all(): array
    {
        return $this->fields;
    }

    /**
     * Get field keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->fields);
    }

    /**
     * Count fields.
     */
    public function count(): int
    {
        return count($this->fields);
    }

    /**
     * Check if collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->fields);
    }

    /**
     * Get iterator.
     *
     * @return Traversable<string, Field>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * Convert collection to array.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->fields as $key => $field) {
            $result[$key] = $field->toArray();
        }

        return $result;
    }

    /**
     * Filter fields by type.
     *
     * @param string $type Field type to filter by
     * @return self New collection with filtered fields
     */
    public function filterByType(string $type): self
    {
        $collection = new self();

        foreach ($this->fields as $field) {
            if ($field->getType() === $type) {
                $collection->add($field);
            }
        }

        return $collection;
    }

    /**
     * Get fields with conditions.
     *
     * @return self New collection with conditional fields
     */
    public function withConditions(): self
    {
        $collection = new self();

        foreach ($this->fields as $field) {
            if ($field->hasConditions()) {
                $collection->add($field);
            }
        }

        return $collection;
    }
}
