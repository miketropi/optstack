<?php

declare(strict_types=1);

namespace OptStack\Core\Index;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Container\Tab;
use OptStack\Core\Field\Field;
use OptStack\Core\Field\FieldCollection;
use OptStack\Core\Field\FieldGroup;

/**
 * Searchable Field Resolver
 *
 * Resolves all searchable fields in a stack and generates their indexed meta keys.
 * Walks through root-level fields, tabs, and groups to find all searchable fields.
 *
 * Meta key format: _optstack_idx_{context}_{field_path}
 * Example: _optstack_idx_post_seo_title
 */
class SearchableFieldResolver
{
    /**
     * Meta key prefix for indexed fields.
     */
    public const META_PREFIX = '_optstack_idx';

    /**
     * Resolve all searchable fields from a stack.
     *
     * @param Stack $stack The stack to resolve fields from
     * @return array<string, SearchableField> Map of field paths to SearchableField objects
     */
    public function resolve(Stack $stack): array
    {
        $searchableFields = [];
        $context = $this->normalizeContext($stack->getContext());

        // Process root-level fields
        $this->processFieldCollection(
            $stack->getFields(),
            $context,
            '',
            $searchableFields
        );

        // Process root-level groups
        foreach ($stack->getGroups() as $group) {
            $this->processGroup(
                $group,
                $context,
                '',
                $searchableFields
            );
        }

        // Process tabs
        foreach ($stack->getTabs() as $tab) {
            $this->processTab($tab, $context, $searchableFields);
        }

        return $searchableFields;
    }

    /**
     * Process a tab and its contents.
     *
     * @param Tab $tab The tab to process
     * @param string $context The context (post, term, user)
     * @param array<string, SearchableField> &$searchableFields Collection to add fields to
     */
    protected function processTab(Tab $tab, string $context, array &$searchableFields): void
    {
        // Process fields directly in the tab
        $this->processFieldCollection(
            $tab->getFields(),
            $context,
            '',
            $searchableFields
        );

        // Process groups in the tab
        foreach ($tab->getGroups() as $group) {
            $this->processGroup(
                $group,
                $context,
                '',
                $searchableFields
            );
        }
    }

    /**
     * Process a field group.
     *
     * @param FieldGroup $group The group to process
     * @param string $context The context (post, term, user)
     * @param string $parentPath Parent field path
     * @param array<string, SearchableField> &$searchableFields Collection to add fields to
     */
    protected function processGroup(
        FieldGroup $group,
        string $context,
        string $parentPath,
        array &$searchableFields
    ): void {
        // Repeatable groups cannot have searchable fields at the repeater level
        // But nested fields within repeatable groups are also excluded
        // because their values are arrays, not scalar
        if ($group->isRepeatable()) {
            return;
        }

        $groupPath = $parentPath !== '' ? "{$parentPath}.{$group->getKey()}" : $group->getKey();

        // Process fields in this group
        $this->processFieldCollection(
            $group->getFields(),
            $context,
            $groupPath,
            $searchableFields
        );

        // Process nested groups
        foreach ($group->getGroups() as $nestedGroup) {
            $this->processGroup(
                $nestedGroup,
                $context,
                $groupPath,
                $searchableFields
            );
        }
    }

    /**
     * Process a field collection.
     *
     * @param FieldCollection $fields The field collection to process
     * @param string $context The context (post, term, user)
     * @param string $parentPath Parent field path
     * @param array<string, SearchableField> &$searchableFields Collection to add fields to
     */
    protected function processFieldCollection(
        FieldCollection $fields,
        string $context,
        string $parentPath,
        array &$searchableFields
    ): void {
        foreach ($fields->all() as $field) {
            if ($field->isSearchable()) {
                $fieldPath = $parentPath !== '' ? "{$parentPath}.{$field->getKey()}" : $field->getKey();
                $metaKey = $this->generateMetaKey($context, $fieldPath);

                $searchableFields[$fieldPath] = new SearchableField(
                    $field,
                    $fieldPath,
                    $metaKey
                );
            }
        }
    }

    /**
     * Generate the indexed meta key for a field.
     *
     * Format: _optstack_idx_{context}_{field_path}
     * Dots in field path are converted to underscores.
     *
     * @param string $context The context (post, term, user)
     * @param string $fieldPath The field path (e.g., "seo.title")
     * @return string The meta key (e.g., "_optstack_idx_post_seo_title")
     */
    public function generateMetaKey(string $context, string $fieldPath): string
    {
        // Convert dots to underscores for the meta key
        $flattenedPath = str_replace('.', '_', $fieldPath);

        return sprintf('%s_%s_%s', self::META_PREFIX, $context, $flattenedPath);
    }

    /**
     * Normalize context to a simple form.
     *
     * @param string $context The stack context
     * @return string Normalized context (post, term, user)
     */
    protected function normalizeContext(string $context): string
    {
        return match ($context) {
            'post', 'post_type' => 'post',
            'term', 'taxonomy' => 'term',
            'user' => 'user',
            default => 'options',
        };
    }

    /**
     * Extract the value for a field from nested data using its path.
     *
     * @param array<string, mixed> $data The data array
     * @param string $path The field path (e.g., "seo.title")
     * @return mixed The field value or null if not found
     */
    public function extractValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
