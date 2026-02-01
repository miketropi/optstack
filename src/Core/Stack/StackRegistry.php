<?php

declare(strict_types=1);

namespace OptStack\Core\Stack;

use InvalidArgumentException;

/**
 * Stack Registry
 *
 * Singleton registry for managing all registered stacks.
 */
class StackRegistry
{
    /**
     * Singleton instance.
     */
    private static ?self $instance = null;

    /**
     * Registered stacks.
     *
     * @var array<string, Stack>
     */
    private array $stacks = [];

    /**
     * Private constructor for singleton.
     */
    private function __construct()
    {
    }

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register a stack.
     *
     * @throws InvalidArgumentException If stack with same ID already exists
     */
    public static function register(Stack $stack): void
    {
        $instance = self::getInstance();

        if ($instance->has($stack->getId())) {
            throw new InvalidArgumentException(
                sprintf('Stack with ID "%s" is already registered.', $stack->getId())
            );
        }

        $stack->markRegistered();
        $instance->stacks[$stack->getId()] = $stack;
    }

    /**
     * Get a stack by ID.
     */
    public static function get(string $id): ?Stack
    {
        return self::getInstance()->stacks[$id] ?? null;
    }

    /**
     * Check if a stack is registered.
     */
    public static function has(string $id): bool
    {
        return isset(self::getInstance()->stacks[$id]);
    }

    /**
     * Get all registered stacks.
     *
     * @return array<string, Stack>
     */
    public static function all(): array
    {
        return self::getInstance()->stacks;
    }

    /**
     * Get stacks by context.
     *
     * @param string $context The context to filter by
     * @return array<string, Stack>
     */
    public static function byContext(string $context): array
    {
        return array_filter(
            self::getInstance()->stacks,
            fn(Stack $stack) => $stack->getContext() === $context
        );
    }

    /**
     * Get stacks for a post type.
     *
     * @return array<string, Stack>
     */
    public static function forPostType(string $postType): array
    {
        return array_filter(
            self::getInstance()->stacks,
            fn(Stack $stack) => $stack->getContext() === 'post_type'
                && $stack->hasPostType($postType)
        );
    }

    /**
     * Get stacks for a taxonomy.
     *
     * @return array<string, Stack>
     */
    public static function forTaxonomy(string $taxonomy): array
    {
        return array_filter(
            self::getInstance()->stacks,
            fn(Stack $stack) => $stack->getContext() === 'taxonomy'
                && $stack->hasTaxonomy($taxonomy)
        );
    }

    /**
     * Unregister a stack.
     */
    public static function unregister(string $id): bool
    {
        $instance = self::getInstance();

        if (!isset($instance->stacks[$id])) {
            return false;
        }

        unset($instance->stacks[$id]);

        return true;
    }

    /**
     * Clear all registered stacks.
     * Useful for testing.
     */
    public static function clear(): void
    {
        self::getInstance()->stacks = [];
    }

    /**
     * Count registered stacks.
     */
    public static function count(): int
    {
        return count(self::getInstance()->stacks);
    }

    /**
     * Reset the singleton instance.
     * Useful for testing.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
