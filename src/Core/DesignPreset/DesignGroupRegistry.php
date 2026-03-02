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
                'variant' => false,
                'tokens' => [
                    'fontFamily'    => ['type' => 'string',  'control' => 'font-family'],
                    'fontWeight'    => ['type' => 'number',  'control' => 'select', 'options' => [100, 200, 300, 400, 500, 600, 700, 800, 900]],
                    'lineHeight'    => ['type' => 'number',  'control' => 'range', 'min' => 0.8, 'max' => 3, 'step' => 0.05],
                    'letterSpacing' => ['type' => 'string',  'control' => 'size', 'units' => ['em', 'px']],
                    'color'         => ['type' => 'string',  'control' => 'color'],
                    'sizeScale'     => ['type' => 'object',  'control' => 'scale', 'keys' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
                ],
            ],

            'body_text' => [
                'label' => 'Body Text',
                'applies_to' => ['paragraph', 'lead', 'small', 'muted'],
                'supports' => ['typography', 'color'],
                'variant' => false,
                'tokens' => [
                    'fontFamily' => ['type' => 'string',  'control' => 'font-family'],
                    'fontSize'   => ['type' => 'string',  'control' => 'size', 'units' => ['px', 'rem']],
                    'fontWeight' => ['type' => 'number',  'control' => 'select', 'options' => [300, 400, 500, 600, 700]],
                    'lineHeight' => ['type' => 'number',  'control' => 'range', 'min' => 1, 'max' => 3, 'step' => 0.05],
                    'color'      => ['type' => 'string',  'control' => 'color'],
                ],
            ],

            'inline_text' => [
                'label' => 'Inline Text',
                'applies_to' => ['link', 'inline_code', 'mark', 'kbd'],
                'supports' => ['color'],
                'variant' => false,
                'tokens' => [
                    'linkColor'      => ['type' => 'string', 'control' => 'color'],
                    'linkHoverColor' => ['type' => 'string', 'control' => 'color'],
                    'codeBackground' => ['type' => 'string', 'control' => 'color'],
                    'codeFontFamily' => ['type' => 'string', 'control' => 'font-family'],
                    'markBackground' => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'button' => [
                'label' => 'Button',
                'applies_to' => ['button', 'cta', 'icon_button'],
                'supports' => ['typography', 'spacing', 'border', 'color', 'state'],
                'variant' => true,
                'tokens' => [
                    'fontFamily'      => ['type' => 'string', 'control' => 'font-family'],
                    'fontSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'fontWeight'      => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'padding'         => ['type' => 'string', 'control' => 'spacing'],
                    'borderRadius'    => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%']],
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
                'variant' => false,
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
                'variant' => false,
                'tokens' => [
                    'background'       => ['type' => 'string', 'control' => 'color'],
                    'borderColor'      => ['type' => 'string', 'control' => 'color'],
                    'borderWidth'      => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderRadius'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'padding'          => ['type' => 'string', 'control' => 'spacing'],
                    'fontSize'         => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'color'            => ['type' => 'string', 'control' => 'color'],
                    'focusBorderColor' => ['type' => 'string', 'control' => 'color'],
                    'errorBorderColor' => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'form_choice' => [
                'label' => 'Form Choice',
                'applies_to' => ['checkbox', 'radio'],
                'supports' => ['color', 'border'],
                'variant' => false,
                'tokens' => [
                    'size'              => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderColor'       => ['type' => 'string', 'control' => 'color'],
                    'checkedBackground' => ['type' => 'string', 'control' => 'color'],
                    'borderRadius'      => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                ],
            ],

            'form_meta' => [
                'label' => 'Form Meta',
                'applies_to' => ['label', 'help_text', 'error_text', 'success_text'],
                'supports' => ['typography', 'color'],
                'variant' => false,
                'tokens' => [
                    'labelFontSize'   => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'labelFontWeight' => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'labelColor'      => ['type' => 'string', 'control' => 'color'],
                    'helpColor'       => ['type' => 'string', 'control' => 'color'],
                    'errorColor'      => ['type' => 'string', 'control' => 'color'],
                    'successColor'    => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'container' => [
                'label' => 'Container',
                'applies_to' => ['section', 'page_container'],
                'supports' => ['spacing', 'color'],
                'variant' => false,
                'tokens' => [
                    'maxWidth'   => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%']],
                    'padding'    => ['type' => 'string', 'control' => 'spacing'],
                    'background' => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'card' => [
                'label' => 'Card',
                'applies_to' => ['card', 'panel'],
                'supports' => ['color', 'border', 'spacing'],
                'variant' => true,
                'tokens' => [
                    'background'   => ['type' => 'string', 'control' => 'color'],
                    'borderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'borderWidth'  => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderColor'  => ['type' => 'string', 'control' => 'color'],
                    'padding'      => ['type' => 'string', 'control' => 'spacing'],
                    'shadow'       => ['type' => 'string', 'control' => 'shadow'],
                ],
            ],

            'navigation' => [
                'label' => 'Navigation',
                'applies_to' => ['menu', 'breadcrumb', 'pagination', 'tabs'],
                'supports' => ['typography', 'color', 'spacing', 'state'],
                'variant' => false,
                'tokens' => [
                    'fontFamily'  => ['type' => 'string', 'control' => 'font-family'],
                    'fontSize'    => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'fontWeight'  => ['type' => 'number', 'control' => 'select', 'options' => [400, 500, 600, 700]],
                    'color'       => ['type' => 'string', 'control' => 'color'],
                    'activeColor' => ['type' => 'string', 'control' => 'color'],
                    'hoverColor'  => ['type' => 'string', 'control' => 'color'],
                    'padding'     => ['type' => 'string', 'control' => 'spacing'],
                ],
            ],

            'alert' => [
                'label' => 'Alert',
                'applies_to' => ['info', 'success', 'warning', 'error'],
                'supports' => ['color', 'border', 'spacing'],
                'variant' => true,
                'tokens' => [
                    'background'   => ['type' => 'string', 'control' => 'color'],
                    'borderColor'  => ['type' => 'string', 'control' => 'color'],
                    'color'        => ['type' => 'string', 'control' => 'color'],
                    'borderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'padding'      => ['type' => 'string', 'control' => 'spacing'],
                    'iconColor'    => ['type' => 'string', 'control' => 'color'],
                ],
            ],

            'loading' => [
                'label' => 'Loading',
                'applies_to' => ['loader', 'progress_bar'],
                'supports' => ['color'],
                'variant' => false,
                'tokens' => [
                    'color'      => ['type' => 'string', 'control' => 'color'],
                    'trackColor' => ['type' => 'string', 'control' => 'color'],
                    'size'       => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                ],
            ],

            'media' => [
                'label' => 'Media',
                'applies_to' => ['image', 'video', 'gallery'],
                'supports' => ['border', 'color'],
                'variant' => false,
                'tokens' => [
                    'borderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%']],
                    'borderWidth'  => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
                    'borderColor'  => ['type' => 'string', 'control' => 'color'],
                    'shadow'       => ['type' => 'string', 'control' => 'shadow'],
                ],
            ],

            'utility' => [
                'label' => 'Utility',
                'applies_to' => ['badge', 'avatar', 'icon', 'divider'],
                'supports' => ['color', 'border'],
                'variant' => false,
                'tokens' => [
                    'badgeBackground'   => ['type' => 'string', 'control' => 'color'],
                    'badgeColor'        => ['type' => 'string', 'control' => 'color'],
                    'badgeBorderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'avatarSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
                    'avatarBorderRadius'=> ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem', '%']],
                    'dividerColor'      => ['type' => 'string', 'control' => 'color'],
                    'dividerWidth'      => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
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
