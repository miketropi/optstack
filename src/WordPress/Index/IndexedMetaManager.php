<?php

declare(strict_types=1);

namespace OptStack\WordPress\Index;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Index\SearchableField;
use OptStack\Core\Index\SearchableFieldResolver;

/**
 * Indexed Meta Manager
 *
 * Handles writing and deleting indexed meta for searchable fields in WordPress.
 * Implements the dual-write strategy: main data + indexed scalar values.
 *
 * This class bridges the Core domain (SearchableFieldResolver) with WordPress
 * meta APIs (update_post_meta, update_term_meta, update_user_meta).
 */
class IndexedMetaManager
{
    /**
     * The searchable field resolver.
     */
    protected SearchableFieldResolver $resolver;

    /**
     * Create a new IndexedMetaManager instance.
     */
    public function __construct(?SearchableFieldResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new SearchableFieldResolver();
    }

    /**
     * Sync indexed meta for a stack after data is saved.
     *
     * This method should be called after the main data is saved.
     * It writes/deletes indexed meta keys based on the searchable fields.
     *
     * @param Stack $stack The stack definition
     * @param array<string, mixed> $data The saved data
     * @param int|null $objectId The object ID (post/term/user ID, null for options)
     */
    public function syncIndexedMeta(Stack $stack, array $data, ?int $objectId = null): void
    {
        $context = $stack->getContext();
        
        // Debug logging
        $debugInfo = [
            'stack_id' => $stack->getId(),
            'context' => $context,
            'object_id' => $objectId,
            'data_keys' => array_keys($data),
        ];
        
        // Options context doesn't support searchable fields (no meta_query for options)
        if ($context === 'options') {
            $debugInfo['skipped'] = 'options context';
            do_action('optstack_indexed_meta_debug', $debugInfo);
            return;
        }

        if ($objectId === null) {
            $debugInfo['skipped'] = 'null object_id';
            do_action('optstack_indexed_meta_debug', $debugInfo);
            return;
        }

        // Resolve all searchable fields in the stack
        $searchableFields = $this->resolver->resolve($stack);
        $debugInfo['searchable_fields_count'] = count($searchableFields);
        $debugInfo['searchable_fields'] = array_keys($searchableFields);
        
        $indexedCount = 0;
        $deletedCount = 0;

        foreach ($searchableFields as $searchableField) {
            $value = $this->resolver->extractValue($data, $searchableField->getPath());
            $metaKey = $searchableField->getMetaKey();

            // Determine if value should be indexed or deleted
            if ($this->shouldIndex($value)) {
                $this->updateMeta($context, $objectId, $metaKey, $value);
                $indexedCount++;
            } else {
                $this->deleteMeta($context, $objectId, $metaKey);
                $deletedCount++;
            }
        }
        
        $debugInfo['indexed_count'] = $indexedCount;
        $debugInfo['deleted_count'] = $deletedCount;
        
        /**
         * Debug action for indexed meta sync.
         * Hook into this to log/debug searchable field indexing.
         *
         * @param array $debugInfo Debug information
         */
        do_action('optstack_indexed_meta_debug', $debugInfo);
    }

    /**
     * Delete all indexed meta for a stack.
     *
     * Useful when deleting a post/term/user or clearing all indexed data.
     *
     * @param Stack $stack The stack definition
     * @param int $objectId The object ID
     */
    public function deleteAllIndexedMeta(Stack $stack, int $objectId): void
    {
        $context = $stack->getContext();
        
        if ($context === 'options') {
            return;
        }

        $searchableFields = $this->resolver->resolve($stack);

        foreach ($searchableFields as $searchableField) {
            $this->deleteMeta($context, $objectId, $searchableField->getMetaKey());
        }
    }

    /**
     * Get all indexed meta keys for a stack.
     *
     * @param Stack $stack The stack definition
     * @return array<string, string> Map of field paths to meta keys
     */
    public function getIndexedMetaKeys(Stack $stack): array
    {
        $keys = [];
        $searchableFields = $this->resolver->resolve($stack);

        foreach ($searchableFields as $path => $searchableField) {
            $keys[$path] = $searchableField->getMetaKey();
        }

        return $keys;
    }

    /**
     * Check if a value should be indexed.
     *
     * Values are indexed if they are scalar and not empty.
     * Empty strings, null, and empty arrays are not indexed.
     *
     * @param mixed $value The value to check
     * @return bool Whether the value should be indexed
     */
    protected function shouldIndex(mixed $value): bool
    {
        // Don't index null
        if ($value === null) {
            return false;
        }

        // Don't index arrays (non-scalar)
        if (is_array($value)) {
            return false;
        }

        // Don't index empty strings
        if ($value === '') {
            return false;
        }

        // Index everything else (including 0, false, "0")
        return true;
    }

    /**
     * Update meta based on context.
     *
     * @param string $context The stack context
     * @param int $objectId The object ID
     * @param string $metaKey The meta key
     * @param mixed $value The value to store
     */
    protected function updateMeta(string $context, int $objectId, string $metaKey, mixed $value): void
    {
        match ($context) {
            'post', 'post_type' => update_post_meta($objectId, $metaKey, $value),
            'term', 'taxonomy' => update_term_meta($objectId, $metaKey, $value),
            'user' => update_user_meta($objectId, $metaKey, $value),
            default => null,
        };
    }

    /**
     * Delete meta based on context.
     *
     * @param string $context The stack context
     * @param int $objectId The object ID
     * @param string $metaKey The meta key
     */
    protected function deleteMeta(string $context, int $objectId, string $metaKey): void
    {
        match ($context) {
            'post', 'post_type' => delete_post_meta($objectId, $metaKey),
            'term', 'taxonomy' => delete_term_meta($objectId, $metaKey),
            'user' => delete_user_meta($objectId, $metaKey),
            default => null,
        };
    }

    /**
     * Get the searchable field resolver.
     */
    public function getResolver(): SearchableFieldResolver
    {
        return $this->resolver;
    }
}
