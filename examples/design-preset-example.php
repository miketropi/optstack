<?php
/**
 * OptStack Example: Design Preset System (DPS)
 *
 * Demonstrates how to use the design_preset field type to manage
 * design tokens for your theme or plugin. The preset system outputs
 * CSS custom properties automatically via wp_head.
 *
 * Includes:
 *   1. Basic usage — single preset field with restricted groups
 *   2. Full theme design — all groups, Customizer integration
 *   3. Registering custom groups and presets
 *   4. Reading resolved tokens in theme templates
 *   5. Using CSS variables in your stylesheet
 *
 * Usage:
 *   require_once OPTSTACK_DIR . 'examples/design-preset-example.php';
 *
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// EXAMPLE 1: Basic Design Preset (Options Page)
// =============================================================================
//
// A simple options page with a design_preset field restricted to a few groups.
// Users can switch between built-in presets and override individual tokens.
//
// CSS output (automatic):
//   :root {
//     --os-heading-font-family: Inter, sans-serif;
//     --os-heading-font-weight: 700;
//     --os-button-primary-background: #2563EB;
//     --os-card-border-radius: 8px;
//     ...
//   }

add_action('optstack_init', function (): void {

    OptStack::make('theme_design')
        ->forOptions()
        ->menuParent('themes.php')
        ->label(__('Design System', 'optstack'))
        ->description(__('Manage your site design tokens. Changes are output as CSS variables.', 'optstack'))
        ->define(function ($stack): void {

            $stack->field('global_design', [
                'type'  => 'design_preset',
                'label' => __('Design Presets', 'optstack'),
                'description' => __('Choose a preset and customize design tokens. Tokens are output as CSS custom properties.', 'optstack'),
                'default' => [
                    'active_preset' => 'modern',
                    'overrides' => [],
                ],
                'attributes' => [
                    'default_preset' => 'modern',
                    'allow_custom'   => true,
                    'allowed_groups' => [
                        'heading',
                        'body_text',
                        'button',
                        'card',
                        'form_field',
                        'navigation',
                        'pricing_table',
                    ],
                ],
            ]);

        })
        ->build();
});


// =============================================================================
// EXAMPLE 2: Full Theme Design (Customizer)
// =============================================================================
//
// Register a design preset in the Customizer with all groups enabled.
// Uses theme_mod storage so values are theme-specific.
//
// Uncomment to activate:

// add_action('optstack_init', function (): void {
//
//     OptStack::make('theme_design_customizer')
//         ->forCustomizer('theme_mod')
//         ->label(__('Design Tokens', 'optstack'))
//         ->description(__('Full design system in the Customizer.', 'optstack'))
//         ->define(function ($stack): void {
//
//             $stack->field('design', [
//                 'type'  => 'design_preset',
//                 'label' => __('Global Design', 'optstack'),
//                 'default' => [
//                     'active_preset' => 'modern',
//                     'overrides' => [],
//                 ],
//                 'attributes' => [
//                     'default_preset' => 'modern',
//                     'allow_custom'   => true,
//                     // omit allowed_groups to enable ALL groups
//                 ],
//             ]);
//
//         })
//         ->build();
// });


// =============================================================================
// EXAMPLE 3: Register Custom Design Group
// =============================================================================
//
// Add a custom semantic group for a plugin-specific UI component.
// The group will appear in the preset editor when allowed.

add_action('optstack_init', function (): void {

    optstack_register_design_group('pricing_table', [
        'label'      => __('Pricing Table', 'optstack'),
        'applies_to' => ['pricing_card', 'pricing_header', 'pricing_feature'],
        'supports'   => ['typography', 'spacing', 'border', 'color'],
        'variant'    => true,
        'tokens'     => [
            'headerBackground' => ['type' => 'string', 'control' => 'color'],
            'headerColor'      => ['type' => 'string', 'control' => 'color'],
            'priceSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
            'priceFontWeight'  => ['type' => 'number', 'control' => 'select', 'options' => [400, 600, 700, 800]],
            'featureColor'     => ['type' => 'string', 'control' => 'color'],
            'borderRadius'     => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
            'shadow'           => ['type' => 'string', 'control' => 'shadow'],
        ],
    ]);
});


// =============================================================================
// EXAMPLE 4: Register Custom Preset
// =============================================================================
//
// Add a brand-specific preset that extends built-in token coverage.
// Users can select it alongside the 6 built-in presets.

add_action('optstack_init', function (): void {

    optstack_register_design_preset([
        'id'    => 'brand_corporate',
        'label' => __('Corporate', 'optstack'),
        'tokens' => [
            'heading' => [
                'fontFamily'    => 'Merriweather, Georgia, serif',
                'fontWeight'    => 700,
                'lineHeight'    => 1.3,
                'letterSpacing' => '0',
                'color'         => '#0F172A',
                'sizeScale'     => [
                    'h1' => '2.75rem',
                    'h2' => '2.25rem',
                    'h3' => '1.75rem',
                    'h4' => '1.5rem',
                    'h5' => '1.25rem',
                    'h6' => '1rem',
                ],
            ],
            'body_text' => [
                'fontFamily' => 'Open Sans, sans-serif',
                'fontSize'   => '16px',
                'fontWeight' => 400,
                'lineHeight' => 1.7,
                'color'      => '#334155',
            ],
            'button' => [
                [
                    'id'              => 'primary',
                    'label'           => 'Primary',
                    'fontFamily'      => 'Open Sans, sans-serif',
                    'fontSize'        => '14px',
                    'fontWeight'      => 600,
                    'padding'         => '12px 28px',
                    'borderRadius'    => '4px',
                    'borderWidth'     => '0',
                    'background'      => '#0F4C75',
                    'color'           => '#FFFFFF',
                    'hoverBackground' => '#1B6DA8',
                    'hoverColor'      => '#FFFFFF',
                ],
                [
                    'id'              => 'secondary',
                    'label'           => 'Secondary',
                    'fontFamily'      => 'Open Sans, sans-serif',
                    'fontSize'        => '14px',
                    'fontWeight'      => 600,
                    'padding'         => '12px 28px',
                    'borderRadius'    => '4px',
                    'borderWidth'     => '2px',
                    'borderColor'     => '#0F4C75',
                    'background'      => 'transparent',
                    'color'           => '#0F4C75',
                    'hoverBackground' => '#0F4C75',
                    'hoverColor'      => '#FFFFFF',
                ],
            ],
            'card' => [
                'background'   => '#FFFFFF',
                'borderRadius' => '4px',
                'borderWidth'  => '1px',
                'borderColor'  => '#E2E8F0',
                'padding'      => '28px',
                'shadow'       => '0 1px 4px rgba(0,0,0,0.06)',
            ],
            'navigation' => [
                'fontFamily'  => 'Open Sans, sans-serif',
                'fontSize'    => '14px',
                'fontWeight'  => 600,
                'color'       => '#334155',
                'activeColor' => '#0F4C75',
                'hoverColor'  => '#0F4C75',
                'padding'     => '10px 18px',
            ],
            'form_field' => [
                'background'       => '#FFFFFF',
                'borderColor'      => '#CBD5E1',
                'borderWidth'      => '1px',
                'borderRadius'     => '4px',
                'padding'          => '10px 14px',
                'fontSize'         => '14px',
                'color'            => '#0F172A',
                'focusBorderColor' => '#0F4C75',
                'errorBorderColor' => '#DC2626',
            ],
        ],
    ]);
});


// =============================================================================
// EXAMPLE 5: Reading Token Values in Theme Templates
// =============================================================================
//
// The CSS variables are output automatically in <head>. But you can also
// read the raw field value and resolve tokens in PHP if needed.

/**
 * Get the active preset label for display.
 */
function mytheme_get_active_preset_label(): string
{
    $fieldValue = OptStack::getField('theme_design', 'global_design', []);
    if (!is_array($fieldValue)) {
        return 'Modern';
    }

    $presetId = $fieldValue['active_preset'] ?? 'modern';

    $preset = \OptStack\Core\DesignPreset\DesignPresetRegistry::get($presetId);
    return $preset['label'] ?? ucfirst($presetId);
}

/**
 * Resolve a specific token value from the active preset.
 *
 * @param string $path Dot-notation path, e.g. "heading.fontFamily" or "button.primary.background"
 * @param mixed $default Fallback value
 * @return mixed
 */
function mytheme_get_design_token(string $path, mixed $default = null): mixed
{
    $fieldValue = OptStack::getField('theme_design', 'global_design', []);
    if (!is_array($fieldValue)) {
        return $default;
    }

    $resolved = \OptStack\Core\DesignPreset\TokenResolver::resolve($fieldValue);
    $flat = \OptStack\Core\DesignPreset\TokenResolver::flatten($resolved);

    return $flat[$path] ?? $default;
}


// =============================================================================
// EXAMPLE 6: Using CSS Variables in Your Theme Stylesheet
// =============================================================================
//
// The design preset system outputs CSS variables with the --os- prefix.
// Reference them in your theme CSS:
//
//   /* Typography */
//   body {
//     font-family: var(--os-body-text-font-family, system-ui, sans-serif);
//     font-size: var(--os-body-text-font-size, 16px);
//     line-height: var(--os-body-text-line-height, 1.6);
//     color: var(--os-body-text-color, #374151);
//   }
//
//   h1, h2, h3, h4, h5, h6 {
//     font-family: var(--os-heading-font-family, inherit);
//     font-weight: var(--os-heading-font-weight, 700);
//     line-height: var(--os-heading-line-height, 1.2);
//     color: var(--os-heading-color, #111827);
//   }
//
//   h1 { font-size: var(--os-heading-size-scale-h1, 3rem); }
//   h2 { font-size: var(--os-heading-size-scale-h2, 2.5rem); }
//   h3 { font-size: var(--os-heading-size-scale-h3, 2rem); }
//
//   /* Buttons */
//   .btn-primary {
//     background: var(--os-button-primary-background, #2563EB);
//     color: var(--os-button-primary-color, #fff);
//     border-radius: var(--os-button-primary-border-radius, 6px);
//     padding: var(--os-button-primary-padding, 10px 20px);
//     font-weight: var(--os-button-primary-font-weight, 600);
//     border: var(--os-button-primary-border-width, 0) solid var(--os-button-primary-border-color, transparent);
//   }
//   .btn-primary:hover {
//     background: var(--os-button-primary-hover-background, #1D4ED8);
//     color: var(--os-button-primary-hover-color, #fff);
//   }
//
//   .btn-secondary {
//     background: var(--os-button-secondary-background, #fff);
//     color: var(--os-button-secondary-color, #374151);
//     border-radius: var(--os-button-secondary-border-radius, 6px);
//     padding: var(--os-button-secondary-padding, 10px 20px);
//     border: var(--os-button-secondary-border-width, 1px) solid var(--os-button-secondary-border-color, #D1D5DB);
//   }
//
//   /* Cards */
//   .card {
//     background: var(--os-card-background, #fff);
//     border-radius: var(--os-card-border-radius, 8px);
//     border: var(--os-card-border-width, 1px) solid var(--os-card-border-color, #E5E7EB);
//     padding: var(--os-card-padding, 24px);
//     box-shadow: var(--os-card-shadow, 0 1px 3px rgba(0,0,0,0.1));
//   }
//
//   /* Forms */
//   input, textarea, select {
//     background: var(--os-form-field-background, #fff);
//     border: var(--os-form-field-border-width, 1px) solid var(--os-form-field-border-color, #D1D5DB);
//     border-radius: var(--os-form-field-border-radius, 6px);
//     padding: var(--os-form-field-padding, 10px 12px);
//     font-size: var(--os-form-field-font-size, 14px);
//     color: var(--os-form-field-color, #111827);
//   }
//   input:focus, textarea:focus, select:focus {
//     border-color: var(--os-form-field-focus-border-color, #2563EB);
//   }
//
//   /* Navigation */
//   .nav-link {
//     font-family: var(--os-navigation-font-family, inherit);
//     font-size: var(--os-navigation-font-size, 14px);
//     font-weight: var(--os-navigation-font-weight, 500);
//     color: var(--os-navigation-color, #374151);
//     padding: var(--os-navigation-padding, 8px 16px);
//   }
//   .nav-link:hover {
//     color: var(--os-navigation-hover-color, #111827);
//   }
//   .nav-link.active {
//     color: var(--os-navigation-active-color, #2563EB);
//   }


// =============================================================================
// EXAMPLE 7: Register a Custom Output Adapter
// =============================================================================
//
// Create an adapter that outputs tokens as SCSS variables.
// Adapters implement DesignPresetAdapterInterface.
//
// Uncomment to activate:

// use OptStack\Core\Contract\DesignPresetAdapterInterface;
// use OptStack\Core\DesignPreset\TokenResolver;
//
// class ScssVariablesAdapter implements DesignPresetAdapterInterface
// {
//     public function render(array $resolvedTokens): string
//     {
//         $flat = TokenResolver::flatten($resolvedTokens);
//         $lines = [];
//         foreach ($flat as $path => $value) {
//             $varName = '$os-' . str_replace('.', '-', $path);
//             $varName = strtolower(preg_replace('/[A-Z]/', '-$0', $varName));
//             $lines[] = "{$varName}: {$value};";
//         }
//         return implode("\n", $lines);
//     }
//
//     public function getType(): string
//     {
//         return 'scss_variables';
//     }
// }
//
// add_action('optstack_init', function (): void {
//     optstack_register_design_adapter('scss', new ScssVariablesAdapter());
// });
