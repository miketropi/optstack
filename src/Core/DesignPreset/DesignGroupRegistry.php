<?php

declare(strict_types=1);

namespace OptStack\Core\DesignPreset;

class DesignGroupRegistry
{
    /** @var array<string, DesignGroup> */
    protected static array $groups = [];

    protected static bool $builtinsRegistered = false;

    public static function register(string $id, array $config): void
    {
        self::$groups[$id] = new DesignGroup($id, $config);
    }

    public static function get(string $id): ?DesignGroup
    {
        self::ensureBuiltins();
        return self::$groups[$id] ?? null;
    }

    /** @return array<string, DesignGroup> */
    public static function all(): array
    {
        self::ensureBuiltins();
        return self::$groups;
    }

    public static function has(string $id): bool
    {
        self::ensureBuiltins();
        return isset(self::$groups[$id]);
    }

    public static function unregister(string $id): bool
    {
        if (isset(self::$groups[$id])) {
            unset(self::$groups[$id]);
            return true;
        }
        return false;
    }

    /** @return array<string, array<string, mixed>> */
    public static function toArray(): array
    {
        self::ensureBuiltins();
        return array_map(fn(DesignGroup $g) => $g->toArray(), self::$groups);
    }

    public static function reset(): void
    {
        self::$groups = [];
        self::$builtinsRegistered = false;
    }

    protected static function ensureBuiltins(): void
    {
        if (self::$builtinsRegistered) {
            return;
        }
        self::$builtinsRegistered = true;
        self::registerBuiltinGroups();
    }

    protected static function registerBuiltinGroups(): void
    {
        $builtins = [
            'heading' => [
                'label' => 'Heading',
                'applies_to' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                'supports' => ['typography', 'color'],
                'tokens' => [
                    'fontFamily'    => ['type' => 'string',  'control' => 'font-family'],
                    'fontWeight'    => ['type' => 'number',  'control' => 'select', 'options' => [100, 200, 300, 400, 500, 600, 700, 800, 900]],
                    'lineHeight'    => ['type' => 'number',  'control' => 'range', 'min' => 0.8, 'max' => 3, 'step' => 0.05, 'responsive' => true],
                    'letterSpacing' => ['type' => 'string',  'control' => 'size', 'units' => ['em', 'px'], 'responsive' => true],
                    'color'         => ['type' => 'string',  'control' => 'color'],
                    'sizeScale'     => ['type' => 'object',  'control' => 'scale', 'keys' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], 'responsive' => true],
                ],
            ],

            'body_text' => [
                'label' => 'Body Text',
                'applies_to' => ['paragraph', 'lead', 'small', 'muted'],
                'supports' => ['typography', 'color'],
                'tokens' => [
                    'fontFamily' => ['type' => 'string',  'control' => 'font-family'],
                    'fontSize'   => ['type' => 'string',  'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'fontWeight' => ['type' => 'number',  'control' => 'select', 'options' => [300, 400, 500, 600, 700]],
                    'lineHeight' => ['type' => 'number',  'control' => 'range', 'min' => 1, 'max' => 3, 'step' => 0.05, 'responsive' => true],
                    'color'      => ['type' => 'string',  'control' => 'color'],
                ],
            ],

            'button' => [
                'label' => 'Button',
                'applies_to' => ['button', 'cta', 'icon_button'],
                'supports' => ['typography', 'spacing', 'border', 'color', 'state'],
                'variant' => true,
                'tokens' => [
                    'fontFamily'      => ['type' => 'string', 'control' => 'font-family'],
                    'fontSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'fontWeight'      => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'padding'         => ['type' => 'string', 'control' => 'spacing', 'responsive' => true],
                    'borderRadius'    => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%'], 'responsive' => true],
                    'borderWidth'     => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderColor'     => ['type' => 'string', 'control' => 'color'],
                    'background'      => ['type' => 'string', 'control' => 'color'],
                    'color'           => ['type' => 'string', 'control' => 'color'],
                    'hoverBackground' => ['type' => 'string', 'control' => 'color'],
                    'hoverColor'      => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'link' => [
                'label' => 'Link',
                'applies_to' => ['inline_link', 'nav_link'],
                'supports' => ['color', 'state'],
                'tokens' => [
                    'color'           => ['type' => 'string', 'control' => 'color'],
                    'hoverColor'      => ['type' => 'string', 'control' => 'color'],
                    'decoration'      => ['type' => 'string', 'control' => 'select', 'options' => ['none', 'underline', 'dotted']],
                    'hoverDecoration' => ['type' => 'string', 'control' => 'select', 'options' => ['none', 'underline', 'dotted']],
                ],
            ],

            'form_field' => [
                'label' => 'Form Field',
                'applies_to' => ['input', 'textarea', 'select'],
                'supports' => ['color', 'border', 'spacing', 'typography', 'state'],
                'tokens' => [
                    'background'       => ['type' => 'string', 'control' => 'color'],
                    'borderColor'      => ['type' => 'string', 'control' => 'color'],
                    'borderWidth'      => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderRadius'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'padding'          => ['type' => 'string', 'control' => 'spacing', 'responsive' => true],
                    'fontSize'         => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'color'            => ['type' => 'string', 'control' => 'color'],
                    'focusBorderColor' => ['type' => 'string', 'control' => 'color'],
                    'errorBorderColor' => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'form_meta' => [
                'label' => 'Form Meta',
                'applies_to' => ['label', 'help_text', 'error_text', 'success_text'],
                'supports' => ['typography', 'color'],
                'tokens' => [
                    'labelFontSize'   => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'labelFontWeight' => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'labelColor'      => ['type' => 'string', 'control' => 'color'],
                    'helpColor'       => ['type' => 'string', 'control' => 'color'],
                    'errorColor'      => ['type' => 'string', 'control' => 'color'],
                    'successColor'    => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'container' => [
                'label' => 'Container',
                'applies_to' => ['section', 'page_container', 'wrapper'],
                'supports' => ['spacing', 'color', 'border'],
                'tokens' => [
                    'maxWidth'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%'], 'responsive' => true],
                    'padding'      => ['type' => 'string', 'control' => 'spacing', 'responsive' => true],
                    'background'   => ['type' => 'string', 'control' => 'color'],
                    'borderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'borderWidth'  => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderColor'  => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'table' => [
                'label' => 'Table',
                'applies_to' => ['table', 'thead', 'tbody', 'tr', 'th', 'td'],
                'supports' => ['typography', 'color', 'border', 'spacing'],
                'tokens' => [
                    'headerBackground' => ['type' => 'string', 'control' => 'color'],
                    'headerColor'      => ['type' => 'string', 'control' => 'color'],
                    'headerFontWeight' => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'cellPadding'      => ['type' => 'string', 'control' => 'spacing', 'responsive' => true],
                    'cellFontSize'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'cellColor'        => ['type' => 'string', 'control' => 'color'],
                    'borderColor'      => ['type' => 'string', 'control' => 'color'],
                    'borderWidth'      => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'stripedBackground'=> ['type' => 'string', 'control' => 'color'],
                    'hoverBackground'  => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'list' => [
                'label' => 'List',
                'applies_to' => ['ul', 'ol', 'li'],
                'supports' => ['typography', 'color', 'spacing'],
                'tokens' => [
                    'fontSize'      => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'lineHeight'    => ['type' => 'number', 'control' => 'range', 'min' => 1, 'max' => 3, 'step' => 0.05, 'responsive' => true],
                    'color'         => ['type' => 'string', 'control' => 'color'],
                    'markerColor'   => ['type' => 'string', 'control' => 'color'],
                    'markerSize'    => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'itemSpacing'   => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                    'indentSize'    => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
                ],
            ],
        ];

        foreach ($builtins as $id => $config) {
            if (!isset(self::$groups[$id])) {
                self::$groups[$id] = new DesignGroup($id, $config);
            }
        }
    }
}
