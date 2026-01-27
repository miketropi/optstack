<?php

declare(strict_types=1);

namespace OptStack\Core\Path;

use OptStack\Core\Support\Arr;

/**
 * Path Resolver
 *
 * Resolves field paths using dot notation for nested data access.
 */
class PathResolver
{
    /**
     * Get a value from data using a path.
     *
     * @param array<string, mixed> $data
     * @param string $path Dot-notation path (e.g., "group.field" or "repeater.0.field")
     */
    public static function get(array $data, string $path, mixed $default = null): mixed
    {
        return Arr::get($data, $path, $default);
    }

    /**
     * Set a value in data using a path.
     *
     * @param array<string, mixed> $data
     * @param string $path Dot-notation path
     * @return array<string, mixed>
     */
    public static function set(array &$data, string $path, mixed $value): array
    {
        Arr::set($data, $path, $value);

        return $data;
    }

    /**
     * Check if a path exists in data.
     *
     * @param array<string, mixed> $data
     */
    public static function has(array $data, string $path): bool
    {
        return Arr::has($data, $path);
    }

    /**
     * Remove a value from data using a path.
     *
     * @param array<string, mixed> $data
     */
    public static function forget(array &$data, string $path): void
    {
        Arr::forget($data, $path);
    }

    /**
     * Parse a path into segments.
     *
     * @return array<string|int>
     */
    public static function parse(string $path): array
    {
        $segments = explode('.', $path);

        return array_map(function ($segment) {
            // Convert numeric strings to integers
            if (is_numeric($segment)) {
                return (int) $segment;
            }

            return $segment;
        }, $segments);
    }

    /**
     * Build a path from segments.
     *
     * @param array<string|int> $segments
     */
    public static function build(array $segments): string
    {
        return implode('.', $segments);
    }

    /**
     * Get the parent path.
     *
     * @return string|null Null if path is already at root
     */
    public static function parent(string $path): ?string
    {
        $segments = self::parse($path);

        if (count($segments) <= 1) {
            return null;
        }

        array_pop($segments);

        return self::build($segments);
    }

    /**
     * Get the last segment of a path (the key).
     */
    public static function key(string $path): string|int
    {
        $segments = self::parse($path);

        return end($segments);
    }

    /**
     * Check if a path represents a repeater index.
     */
    public static function isRepeaterPath(string $path): bool
    {
        foreach (self::parse($path) as $segment) {
            if (is_int($segment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all paths from a nested data structure.
     *
     * @param array<string, mixed> $data
     * @return array<string>
     */
    public static function allPaths(array $data): array
    {
        $flattened = Arr::dot($data);

        return array_keys($flattened);
    }

    /**
     * Append a segment to a path.
     */
    public static function append(string $path, string|int $segment): string
    {
        if (empty($path)) {
            return (string) $segment;
        }

        return $path . '.' . $segment;
    }

    /**
     * Prepend a segment to a path.
     */
    public static function prepend(string $path, string|int $segment): string
    {
        if (empty($path)) {
            return (string) $segment;
        }

        return $segment . '.' . $path;
    }
}
