<?php

declare(strict_types=1);

namespace OptStack\WordPress\Block;

use OptStack\Core\Field\Field;
use OptStack\Core\Field\FieldGroup;
use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;

/**
 * Schema to Block Attributes
 *
 * Maps OptStack stack schema to Gutenberg block.json attributes format.
 */
class SchemaToAttributes
{
    /**
     * Field type to block attribute type mapping.
     */
    private const FIELD_TYPE_MAP = [
        'text'         => 'string',
        'textarea'     => 'string',
        'url'          => 'string',
        'email'        => 'string',
        'wysiwyg'      => 'string',
        'code'         => 'string',
        'color'        => 'string',
        'date'         => 'string',
        'datetime'     => 'string',
        'time'         => 'string',
        'select'       => 'string',
        'radio'        => 'string',
        'radio-image'  => 'string',
        'image-radio'  => 'string',
        'number'       => 'number',
        'range'        => 'number',
        'toggle'       => 'boolean',
        'boolean'      => 'boolean',
        'checkbox'     => 'boolean',
        'media'        => 'object',
        'typography'   => 'object',
        'visual-builder' => 'object',
        'checkbox-group' => 'array',
        'select-wp-query' => 'number',
    ];

    /**
     * Get block attributes from a stack by ID.
     *
     * @return array<string, array{type: string, default?: mixed}>
     */
    public static function fromStackId(string $stackId): array
    {
        $stack = StackRegistry::get($stackId);

        if ($stack === null) {
            return [];
        }

        return self::fromStack($stack);
    }

    /**
     * Get block attributes from a stack instance.
     *
     * @return array<string, array{type: string, default?: mixed}>
     */
    public static function fromStack(Stack $stack): array
    {
        $attributes = [];
        $defaults = $stack->getDefaults();

        // Root-level fields
        foreach ($stack->getFields()->all() as $field) {
            $key = $field->getKey();
            $attributes[$key] = self::fieldToAttribute($field, $defaults[$key] ?? null);
        }

        // Groups
        foreach ($stack->getGroups() as $key => $group) {
            $attributes[$key] = self::groupToAttribute($group, $defaults[$key] ?? null);
        }

        // Tabs (fields/groups in tabs are at root level in data)
        foreach ($stack->getTabs() as $tab) {
            foreach ($tab->getFields()->all() as $field) {
                $key = $field->getKey();
                if (!isset($attributes[$key])) {
                    $attributes[$key] = self::fieldToAttribute($field, $defaults[$key] ?? null);
                }
            }
            foreach ($tab->getGroups() as $key => $group) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = self::groupToAttribute($group, $defaults[$key] ?? null);
                }
            }
        }

        $attributes = apply_filters('optstack_block_attributes', $attributes, $stack);
        $attributes = apply_filters('optstack_block_attributes_' . $stack->getId(), $attributes, $stack);

        return $attributes;
    }

    /**
     * Convert a field to block attribute definition.
     *
     * @param mixed $default
     * @return array{type: string, default?: mixed}
     */
    private static function fieldToAttribute(Field $field, mixed $default): array
    {
        $fieldType = $field->getType();
        $type = self::mapFieldType($fieldType);
        if ($fieldType === 'select-wp-query' && !empty($field->getAttributes()['multiple'])) {
            $type = 'array';
        }
        $def = ['type' => $type];

        if ($default !== null) {
            $def['default'] = $default;
        } elseif ($type === 'string') {
            $def['default'] = '';
        } elseif ($type === 'number') {
            $def['default'] = 0;
        } elseif ($type === 'boolean') {
            $def['default'] = false;
        } elseif ($type === 'object') {
            $def['default'] = [];
        } elseif ($type === 'array') {
            $def['default'] = [];
        }

        return $def;
    }

    /**
     * Convert a field group to block attribute definition.
     *
     * @param mixed $default
     * @return array{type: string, default?: mixed}
     */
    private static function groupToAttribute(FieldGroup $group, mixed $default): array
    {
        if ($group->isRepeatable()) {
            $def = [
                'type'    => 'array',
                'default' => $default ?? [],
            ];
        } else {
            $def = [
                'type'    => 'object',
                'default' => $default ?? [],
            ];
        }

        return $def;
    }

    /**
     * Map OptStack field type to block attribute type.
     */
    private static function mapFieldType(string $fieldType): string
    {
        return self::FIELD_TYPE_MAP[$fieldType] ?? 'string';
    }
}
