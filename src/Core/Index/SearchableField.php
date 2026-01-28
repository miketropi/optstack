<?php

declare(strict_types=1);

namespace OptStack\Core\Index;

use OptStack\Core\Field\Field;

/**
 * Searchable Field
 *
 * Represents a searchable field with its resolved path and meta key.
 * Used by the SearchableFieldResolver to track indexed fields.
 */
class SearchableField
{
    /**
     * The original field instance.
     */
    protected Field $field;

    /**
     * The full path to the field (e.g., "seo.title").
     */
    protected string $path;

    /**
     * The indexed meta key (e.g., "_optstack_idx_post_seo_title").
     */
    protected string $metaKey;

    /**
     * Create a new SearchableField instance.
     *
     * @param Field $field The field instance
     * @param string $path The field path
     * @param string $metaKey The indexed meta key
     */
    public function __construct(Field $field, string $path, string $metaKey)
    {
        $this->field = $field;
        $this->path = $path;
        $this->metaKey = $metaKey;
    }

    /**
     * Get the field instance.
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * Get the field key.
     */
    public function getKey(): string
    {
        return $this->field->getKey();
    }

    /**
     * Get the field type.
     */
    public function getType(): string
    {
        return $this->field->getType();
    }

    /**
     * Get the full field path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the indexed meta key.
     */
    public function getMetaKey(): string
    {
        return $this->metaKey;
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->field->getKey(),
            'type' => $this->field->getType(),
            'path' => $this->path,
            'metaKey' => $this->metaKey,
        ];
    }
}
