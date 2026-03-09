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

    /** @var string[] Known system / generic font families to skip when enqueuing Google Fonts */
    protected static array $systemFonts = [
        'inherit', 'initial', 'unset', 'revert',
        'system-ui', '-apple-system', 'BlinkMacSystemFont',
        'Arial', 'Helvetica', 'Helvetica Neue',
        'Georgia', 'Times New Roman', 'Times',
        'Verdana', 'Tahoma', 'Trebuchet MS',
        'Courier New', 'Courier',
        'Lucida Console', 'Lucida Sans', 'Lucida Grande',
        'Impact', 'Comic Sans MS', 'Palatino Linotype', 'Book Antiqua',
        'sans-serif', 'serif', 'monospace', 'cursive', 'fantasy',
        'ui-monospace', 'ui-serif', 'ui-sans-serif', 'ui-rounded',
        'SFMono-Regular', 'SF Mono', 'Menlo', 'Monaco', 'Consolas',
    ];

    public static function init(): void
    {
        if (self::$hooked) {
            return;
        }
        self::$hooked = true;

        if (!isset(self::$adapters['css_variables'])) {
            self::$adapters['css_variables'] = new CssVariablesAdapter();
        }

        add_action('wp_head', [self::class, 'outputGoogleFonts'], 4);
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
     * Output a Google Fonts <link> tag for all font families used in design presets.
     */
    public static function outputGoogleFonts(): void
    {
        $families = self::collectGoogleFontFamilies();

        if (empty($families)) {
            return;
        }

        if (function_exists('apply_filters')) {
            /** @var string[] $families */
            $families = apply_filters('optstack_design_google_fonts', $families);
        }

        $params = array_map(function (string $family): string {
            $encoded = str_replace(' ', '+', $family);
            return "family={$encoded}:wght@100;200;300;400;500;600;700;800;900";
        }, $families);

        $url = 'https://fonts.googleapis.com/css2?' . implode('&', $params) . '&display=swap';

        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" id="optstack-google-fonts" href="' . esc_url($url) . '">' . "\n";
    }

    /**
     * Collect all unique Google Font family names across every design_preset field.
     *
     * @return string[]
     */
    protected static function collectGoogleFontFamilies(): array
    {
        $allFamilies = [];

        foreach (StackRegistry::all() as $stack) {
            if ($stack->getStore() === null) {
                continue;
            }

            $fieldValues = self::findDesignPresetFields($stack);
            foreach ($fieldValues as $fieldValue) {
                if (!is_array($fieldValue) || empty($fieldValue)) {
                    continue;
                }
                $resolved = self::resolveTokens($fieldValue);
                self::extractFontFamilies($resolved, $allFamilies);
            }
        }

        return array_values(array_unique($allFamilies));
    }

    /**
     * Walk a resolved token tree and collect non-system font family names.
     *
     * @param array<string, mixed> $tokens
     * @param string[]             &$families
     */
    protected static function extractFontFamilies(array $tokens, array &$families): void
    {
        foreach ($tokens as $groupTokens) {
            if (!is_array($groupTokens)) {
                continue;
            }

            // Variant array
            if (!empty($groupTokens) && array_is_list($groupTokens) && is_array($groupTokens[0]) && isset($groupTokens[0]['id'])) {
                foreach ($groupTokens as $variant) {
                    if (!is_array($variant)) {
                        continue;
                    }
                    if (isset($variant['fontFamily'])) {
                        self::parseFontFamilyValue($variant['fontFamily'], $families);
                    }
                }
                continue;
            }

            // Flat group
            if (isset($groupTokens['fontFamily'])) {
                self::parseFontFamilyValue($groupTokens['fontFamily'], $families);
            }
        }
    }

    /**
     * Parse a fontFamily value (scalar or responsive) and collect Google Font names.
     *
     * @param string[] &$families
     */
    protected static function parseFontFamilyValue(mixed $value, array &$families): void
    {
        if (is_string($value)) {
            self::addGoogleFont($value, $families);
            return;
        }

        // Responsive value: { desktop: '...', tablet: '...', mobile: '...' }
        if (is_array($value)) {
            foreach (['desktop', 'tablet', 'mobile'] as $bp) {
                if (isset($value[$bp]) && is_string($value[$bp])) {
                    self::addGoogleFont($value[$bp], $families);
                }
            }
        }
    }

    /**
     * Extract the primary font name from a CSS font-family stack and add it
     * if it's not a known system font.
     *
     * @param string[] &$families
     */
    protected static function addGoogleFont(string $cssValue, array &$families): void
    {
        $primary = explode(',', $cssValue)[0];
        $primary = trim($primary, " \t\n\r\0\x0B\"'");

        if ($primary === '' || in_array(strtolower($primary), array_map('strtolower', self::$systemFonts), true)) {
            return;
        }

        $families[] = $primary;
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
