<?php

declare(strict_types=1);

namespace OptStack;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackBuilder;
use OptStack\Core\Stack\StackRegistry;
use OptStack\Schema\SchemaExporter;

/**
 * OptStack Facade
 *
 * Main entry point for the OptStack framework.
 * Provides a clean, static API for defining and managing data stacks.
 */
class OptStack
{
    /**
     * Framework version.
     */
    public const VERSION = '0.1.3';

    /**
     * Create a new stack builder.
     *
     * @param string $id Stack identifier
     * @return StackBuilder
     *
     * @example
     * OptStack::make('site_settings')
     *     ->forOptions()
     *     ->define(function ($stack) {
     *         $stack->field('site_color', ['type' => 'text']);
     *     });
     */
    public static function make(string $id): StackBuilder
    {
        return StackBuilder::make($id);
    }

    /**
     * Get a registered stack by ID.
     *
     * @param string $id Stack identifier
     * @return Stack|null
     */
    public static function get(string $id): ?Stack
    {
        return StackRegistry::get($id);
    }

    /**
     * Check if a stack is registered.
     *
     * @param string $id Stack identifier
     * @return bool
     */
    public static function has(string $id): bool
    {
        return StackRegistry::has($id);
    }

    /**
     * Get all registered stacks.
     *
     * @return array<string, Stack>
     */
    public static function all(): array
    {
        return StackRegistry::all();
    }

    /**
     * Get stacks by context.
     *
     * @param string $context Context type (options, post, post_type, term, taxonomy, user)
     * @return array<string, Stack>
     */
    public static function byContext(string $context): array
    {
        return StackRegistry::byContext($context);
    }

    /**
     * Get stacks for a specific post type.
     *
     * @param string $postType Post type slug
     * @return array<string, Stack>
     */
    public static function forPostType(string $postType): array
    {
        return StackRegistry::forPostType($postType);
    }

    /**
     * Get stacks for a specific taxonomy.
     *
     * @param string $taxonomy Taxonomy slug
     * @return array<string, Stack>
     */
    public static function forTaxonomy(string $taxonomy): array
    {
        return StackRegistry::forTaxonomy($taxonomy);
    }

    /**
     * Get data from a stack.
     *
     * @param string $id Stack identifier
     * @param string|null $key Optional specific key to retrieve
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function getData(string $id, ?string $key = null, mixed $default = null): mixed
    {
        $stack = self::get($id);

        if ($stack === null || $stack->getStore() === null) {
            return $default;
        }

        if ($key === null) {
            return $stack->getData();
        }

        return $stack->getStore()->get($key, $default);
    }

    /**
     * Save data to a stack.
     *
     * @param string $id Stack identifier
     * @param array<string, mixed> $data Data to save
     * @return bool
     */
    public static function saveData(string $id, array $data): bool
    {
        $stack = self::get($id);

        if ($stack === null) {
            return false;
        }

        return $stack->saveData($data);
    }

    /**
     * Get a single field value from a stack.
     *
     * This is a convenience method for quickly retrieving a single field
     * without needing to fetch the entire data array.
     *
     * @param string $id Stack identifier
     * @param string $key Field key (supports dot notation for nested fields)
     * @param mixed $default Default value if field not found
     * @param int|null $objectId Object ID (post/term/user) - for context binding
     * @return mixed Field value or default
     *
     * @example
     * // Get a simple field
     * $price = OptStack::getField('product_data', 'price', 0, $post_id);
     *
     * // Get a nested field in a group
     * $regularPrice = OptStack::getField('product_data', 'pricing.regular_price', 0, $post_id);
     *
     * // Get with default value
     * $status = OptStack::getField('product_data', 'status', 'draft', $post_id);
     */
    public static function getField(string $id, string $key, mixed $default = null, ?int $objectId = null): mixed
    {
        $stack = self::get($id);

        if ($stack === null) {
            return $default;
        }

        return $stack->getField($key, $default, $objectId);
    }

    /**
     * Update a single field value in a stack.
     *
     * This is a convenience method for quickly updating a single field
     * without needing to fetch and merge the entire data array.
     *
     * If the field is marked as searchable, the indexed meta will be
     * automatically synced for efficient WP_Query operations.
     *
     * @param string $id Stack identifier
     * @param string $key Field key (supports dot notation for nested fields)
     * @param mixed $value New value
     * @param int|null $objectId Object ID (post/term/user) - required for searchable field sync
     * @return bool Success status
     *
     * @example
     * // Update a simple field
     * OptStack::updateField('product_data', 'price', 99.99, $post_id);
     *
     * // Update a nested field in a group
     * OptStack::updateField('product_data', 'pricing.regular_price', 149.99, $post_id);
     *
     * // Update with searchable field auto-sync
     * OptStack::updateField('product_data', 'status', 'active', $post_id);
     * // Automatically syncs to: _optstack_idx_post_status
     */
    public static function updateField(string $id, string $key, mixed $value, ?int $objectId = null): bool
    {
        $stack = self::get($id);

        if ($stack === null) {
            return false;
        }

        return $stack->updateField($key, $value, $objectId);
    }

    /**
     * Export a stack's schema.
     *
     * @param string $id Stack identifier
     * @return array<string, mixed>|null
     */
    public static function schema(string $id): ?array
    {
        $stack = self::get($id);

        if ($stack === null) {
            return null;
        }

        return SchemaExporter::export($stack);
    }

    /**
     * Export all stacks' schemas.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function allSchemas(): array
    {
        return SchemaExporter::exportAll();
    }

    /**
     * Get framework version.
     *
     * @return string
     */
    public static function version(): string
    {
        return self::VERSION;
    }

    /**
     * Register a stack directly (without builder).
     *
     * @param Stack $stack Stack instance
     */
    public static function register(Stack $stack): void
    {
        StackRegistry::register($stack);
    }

    /**
     * Unregister a stack.
     *
     * @param string $id Stack identifier
     * @return bool
     */
    public static function unregister(string $id): bool
    {
        return StackRegistry::unregister($id);
    }

    /**
     * Count registered stacks.
     *
     * @return int
     */
    public static function count(): int
    {
        return StackRegistry::count();
    }

    /**
     * Define a stack for options.
     *
     * Shorthand for OptStack::make($id)->forOptions()->define($callback)->build()
     *
     * @param string $id Stack identifier
     * @param callable $callback Definition callback
     * @return Stack
     */
    public static function options(string $id, callable $callback): Stack
    {
        return self::make($id)
            ->forOptions()
            ->define($callback)
            ->build();
    }

    /**
     * Define a stack for a post type.
     *
     * @param string $id Stack identifier
     * @param string $postType Post type slug
     * @param callable $callback Definition callback
     * @return Stack
     */
    public static function postType(string $id, string $postType, callable $callback): Stack
    {
        return self::make($id)
            ->forPostType($postType)
            ->define($callback)
            ->build();
    }

    /**
     * Define a stack for a taxonomy.
     *
     * @param string $id Stack identifier
     * @param string $taxonomy Taxonomy slug
     * @param callable $callback Definition callback
     * @return Stack
     */
    public static function taxonomy(string $id, string $taxonomy, callable $callback): Stack
    {
        return self::make($id)
            ->forTaxonomy($taxonomy)
            ->define($callback)
            ->build();
    }
}
