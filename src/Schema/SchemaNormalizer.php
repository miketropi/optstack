<?php

declare(strict_types=1);

namespace OptStack\Schema;

/**
 * Schema Normalizer
 *
 * Normalizes stack arrays into a consistent schema format
 * suitable for frontend consumption.
 */
class SchemaNormalizer
{
    /**
     * Normalize a stack array to schema format.
     *
     * @param array<string, mixed> $stack
     * @return array<string, mixed>
     */
    public function normalize(array $stack): array
    {
        $schema = [
            'id' => $stack['id'] ?? '',
            'context' => $stack['context'] ?? 'options',
            'label' => $stack['label'] ?? '',
        ];

        // Optional properties
        if (!empty($stack['description'])) {
            $schema['description'] = $stack['description'];
        }

        if (!empty($stack['post_type'])) {
            $schema['postType'] = $stack['post_type'];
        }

        if (!empty($stack['taxonomy'])) {
            $schema['taxonomy'] = $stack['taxonomy'];
        }

        // Normalize fields
        if (!empty($stack['fields'])) {
            $schema['fields'] = $this->normalizeFields($stack['fields']);
        }

        // Normalize groups
        if (!empty($stack['groups'])) {
            $schema['groups'] = $this->normalizeGroups($stack['groups']);
        }

        return $schema;
    }

    /**
     * Normalize fields array.
     *
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    protected function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $field) {
            $normalized[$key] = $this->normalizeField($field);
        }

        return $normalized;
    }

    /**
     * Normalize a single field.
     *
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    protected function normalizeField(array $field): array
    {
        $normalized = [
            'key' => $field['key'] ?? '',
            'type' => $field['type'] ?? 'text',
            'label' => $field['label'] ?? '',
        ];

        // Optional properties
        if (array_key_exists('default', $field) && $field['default'] !== null) {
            $normalized['default'] = $field['default'];
        }

        if (!empty($field['description'])) {
            $normalized['description'] = $field['description'];
        }

        if (!empty($field['options'])) {
            $normalized['options'] = $this->normalizeOptions($field['options']);
        }

        if (!empty($field['attributes'])) {
            $normalized['attributes'] = $field['attributes'];
        }

        if (!empty($field['conditions'])) {
            $normalized['conditions'] = $this->normalizeConditions($field['conditions']);
        }

        return $normalized;
    }

    /**
     * Normalize groups array.
     *
     * @param array<string, array<string, mixed>> $groups
     * @return array<string, array<string, mixed>>
     */
    protected function normalizeGroups(array $groups): array
    {
        $normalized = [];

        foreach ($groups as $key => $group) {
            $normalized[$key] = $this->normalizeGroup($group);
        }

        return $normalized;
    }

    /**
     * Normalize a single group.
     *
     * @param array<string, mixed> $group
     * @return array<string, mixed>
     */
    protected function normalizeGroup(array $group): array
    {
        $normalized = [
            'key' => $group['key'] ?? '',
            'label' => $group['label'] ?? '',
            'repeatable' => $group['repeatable'] ?? false,
        ];

        if (!empty($group['description'])) {
            $normalized['description'] = $group['description'];
        }

        if ($normalized['repeatable']) {
            $normalized['minItems'] = $group['min_items'] ?? 0;
            $normalized['maxItems'] = $group['max_items'] ?? 0;
        }

        if (!empty($group['fields'])) {
            $normalized['fields'] = $this->normalizeFields($group['fields']);
        }

        if (!empty($group['groups'])) {
            $normalized['groups'] = $this->normalizeGroups($group['groups']);
        }

        if (!empty($group['conditions'])) {
            $normalized['conditions'] = $this->normalizeConditions($group['conditions']);
        }

        return $normalized;
    }

    /**
     * Normalize options (for select, radio, etc.).
     *
     * @param array<int|string, mixed> $options
     * @return array<array{value: mixed, label: string}>
     */
    protected function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $key => $value) {
            if (is_array($value) && isset($value['value'])) {
                // Already in correct format
                $normalized[] = [
                    'value' => $value['value'],
                    'label' => $value['label'] ?? (string) $value['value'],
                ];
            } elseif (is_string($key)) {
                // key => label format
                $normalized[] = [
                    'value' => $key,
                    'label' => $value,
                ];
            } else {
                // Simple array of values
                $normalized[] = [
                    'value' => $value,
                    'label' => (string) $value,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Normalize conditions.
     *
     * @param array<array<string, mixed>> $conditions
     * @return array<array<string, mixed>>
     */
    protected function normalizeConditions(array $conditions): array
    {
        return array_map(function (array $condition) {
            return [
                'field' => $condition['field'] ?? '',
                'operator' => $condition['operator'] ?? '==',
                'value' => $condition['value'] ?? null,
                'relation' => $condition['relation'] ?? 'AND',
            ];
        }, $conditions);
    }
}
