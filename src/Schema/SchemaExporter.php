<?php

declare(strict_types=1);

namespace OptStack\Schema;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;

/**
 * Schema Exporter
 *
 * Exports stack definitions as JSON Schema-like structures
 * for consumption by frontend applications.
 */
class SchemaExporter
{
    /**
     * Export a single stack to schema.
     *
     * @return array<string, mixed>
     */
    public static function export(Stack $stack): array
    {
        return (new SchemaNormalizer())->normalize($stack->toArray());
    }

    /**
     * Export all stacks to schema.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function exportAll(): array
    {
        $schemas = [];
        $normalizer = new SchemaNormalizer();

        foreach (StackRegistry::all() as $id => $stack) {
            $schemas[$id] = $normalizer->normalize($stack->toArray());
        }

        return $schemas;
    }

    /**
     * Export stacks by context.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function exportByContext(string $context): array
    {
        $schemas = [];
        $normalizer = new SchemaNormalizer();

        foreach (StackRegistry::byContext($context) as $id => $stack) {
            $schemas[$id] = $normalizer->normalize($stack->toArray());
        }

        return $schemas;
    }

    /**
     * Export as JSON.
     */
    public static function toJson(Stack $stack, int $flags = 0): string
    {
        $flags = $flags ?: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

        return json_encode(self::export($stack), $flags) ?: '{}';
    }

    /**
     * Export all as JSON.
     */
    public static function allToJson(int $flags = 0): string
    {
        $flags = $flags ?: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

        return json_encode(self::exportAll(), $flags) ?: '{}';
    }
}
