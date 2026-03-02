<?php

declare(strict_types=1);

namespace OptStack\WordPress\DesignPreset;

use OptStack\Core\Contract\DesignPresetAdapterInterface;
use OptStack\Core\DesignPreset\DesignGroupRegistry;
use OptStack\Core\DesignPreset\DesignPresetRegistry;
use OptStack\Core\DesignPreset\TokenResolver;
use OptStack\Core\Stack\StackRegistry;

/**
 * Manages design preset output on the frontend.
 * Finds all stacks with design_preset fields, resolves tokens, and outputs CSS.
 */
class DesignPresetManager
{
    /** @var array<string, DesignPresetAdapterInterface> */
    protected static array $adapters = [];

    protected static bool $hooked = false;

    public static function init(): void
    {
        if (self::$hooked) {
            return;
        }
        self::$hooked = true;

        if (!isset(self::$adapters['css_variables'])) {
            self::$adapters['css_variables'] = new CssVariablesAdapter();
        }

        add_action('wp_head', [self::class, 'outputCssVariables'], 5);
    }

    public static function registerAdapter(string $key, DesignPresetAdapterInterface $adapter): void
    {
        self::$adapters[$key] = $adapter;
    }

    public static function getAdapter(string $key): ?DesignPresetAdapterInterface
    {
        return self::$adapters[$key] ?? null;
    }

    /** @return array<string, DesignPresetAdapterInterface> */
    public static function getAdapters(): array
    {
        return self::$adapters;
    }

    /**
     * Resolve tokens for a specific field value.
     *
     * @return array<string, mixed>
     */
    public static function resolveTokens(array $fieldValue): array
    {
        $resolved = TokenResolver::resolve($fieldValue);

        if (function_exists('apply_filters')) {
            /** @var array<string, mixed> $resolved */
            $resolved = apply_filters('optstack_design_resolved_tokens', $resolved, $fieldValue);
        }

        return $resolved;
    }

    /**
     * Output CSS custom properties in wp_head for all design_preset fields.
     */
    public static function outputCssVariables(): void
    {
        $css = self::generateCss();
        if ($css === '') {
            return;
        }

        if (function_exists('apply_filters')) {
            $css = apply_filters('optstack_design_css_variables', $css);
        }

        echo '<style id="optstack-design-tokens">' . "\n" . $css . "\n" . '</style>' . "\n";
    }

    /**
     * Generate CSS for all design_preset fields across all stacks.
     */
    public static function generateCss(): string
    {
        $adapter = self::$adapters['css_variables'] ?? new CssVariablesAdapter();
        $allCss = [];

        foreach (StackRegistry::all() as $stack) {
            $store = $stack->getStore();
            if ($store === null) {
                continue;
            }

            $fieldValues = self::findDesignPresetFields($stack);
            foreach ($fieldValues as $fieldValue) {
                if (!is_array($fieldValue) || empty($fieldValue)) {
                    continue;
                }

                $resolved = self::resolveTokens($fieldValue);
                $css = $adapter->render($resolved);
                if ($css !== '') {
                    $allCss[] = $css;
                }
            }
        }

        return implode("\n\n", $allCss);
    }

    /**
     * Find all design_preset field values in a stack.
     *
     * @return array<int, mixed>
     */
    protected static function findDesignPresetFields($stack): array
    {
        $values = [];
        $data = $stack->getData();

        $fields = $stack->getFields()->all();
        foreach ($fields as $field) {
            if ($field->getType() === 'design_preset') {
                $values[] = $data[$field->getKey()] ?? null;
            }
        }

        foreach ($stack->getTabs() as $tab) {
            foreach ($tab->getFields()->all() as $field) {
                if ($field->getType() === 'design_preset') {
                    $values[] = $data[$field->getKey()] ?? null;
                }
            }
        }

        foreach ($stack->getGroups() as $groupKey => $group) {
            $groupData = $data[$groupKey] ?? [];
            if (!is_array($groupData)) {
                continue;
            }
            foreach ($group->getFields()->all() as $field) {
                if ($field->getType() === 'design_preset') {
                    $values[] = $groupData[$field->getKey()] ?? null;
                }
            }
        }

        return $values;
    }

    /**
     * Provide REST data for the design preset system (groups + available presets).
     *
     * @return array{groups: array, presets: array}
     */
    public static function getRestData(): array
    {
        $groups = DesignGroupRegistry::toArray();
        $presets = DesignPresetRegistry::all();

        if (function_exists('apply_filters')) {
            $groups = apply_filters('optstack_design_groups', $groups);
            $presets = apply_filters('optstack_design_presets', $presets);
        }

        return [
            'groups' => $groups,
            'presets' => array_values($presets),
        ];
    }

    public static function reset(): void
    {
        self::$adapters = [];
        self::$hooked = false;
    }
}
