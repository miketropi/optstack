<?php

declare(strict_types=1);

namespace OptStack\Core\Support;

/**
 * Array Helper
 *
 * Utility class for array operations.
 */
class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array<string, mixed> $array
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function set(array &$array, string $key, mixed $value): array
    {
        $keys = explode('.', $key);

        foreach ($keys as $i => $segment) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }

            $array = &$array[$segment];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array<string, mixed> $array
     */
    public static function has(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Remove one or more array items from a given array using "dot" notation.
     *
     * @param array<string, mixed> $array
     * @param string|array<string> $keys
     */
    public static function forget(array &$array, string|array $keys): void
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $parts = explode('.', $key);

            $target = &$array;
            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (!isset($target[$part]) || !is_array($target[$part])) {
                    continue 2;
                }

                $target = &$target[$part];
            }

            unset($target[array_shift($parts)]);
        }
    }

    /**
     * Flatten a multi-dimensional array into a single level using "dot" notation.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, self::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Convert a flattened "dot" notation array into an expanded array.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function undot(array $array): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            self::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array<string, mixed> $array
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            if (empty($array)) {
                return $default;
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array<string, mixed> $array
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : end($array);
        }

        return self::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array<string, mixed> $array
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param array<string, mixed> $array
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Recursively merge arrays, overwriting non-array values.
     *
     * @param array<string, mixed> ...$arrays
     * @return array<string, mixed>
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeRecursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
