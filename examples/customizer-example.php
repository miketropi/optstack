<?php
/**
 * OptStack Example: Customizer (Theme Options in Appearance → Customize)
 *
 * Registers a stack that appears in the WordPress Customizer with theme_mod storage.
 * Uses flat fields (no tabs or groups) for a clean, compact Customizer layout.
 *
 * Storage options:
 *   - 'theme_mod' (default) — stored per-theme via get_theme_mod/set_theme_mod
 *   - 'option' — stored globally in wp_options
 *
 * Usage:
 *   require_once OPTSTACK_DIR . 'examples/customizer-example.php';
 *
 * Reading values in your theme:
 *   $primary = OptStack::getField('customizer_theme_options', 'primary_color', '#2271b1');
 *   $tagline = OptStack::getField('customizer_theme_options', 'site_tagline', '');
 *
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

if (!defined('ABSPATH')) {
    exit;
}

add_action('optstack_init', function (): void {
    OptStack::make('customizer_theme_options')
        ->forCustomizer('theme_mod')
        ->label(__('Theme Options', 'optstack'))
        ->description(__('Customize colors, typography and site identity.', 'optstack'))
        ->define(function ($stack): void {

            // Text
            $stack->field('site_tagline', [
                'type'  => 'text',
                'label' => __('Tagline', 'optstack'),
                'description' => __('A short description of your site.', 'optstack'),
                'attributes' => ['placeholder' => __('Just another WordPress site', 'optstack')],
            ]);

            // Textarea
            $stack->field('footer_text', [
                'type'  => 'textarea',
                'label' => __('Footer Text', 'optstack'),
                'default' => '© 2026 My Site. All rights reserved.',
                'attributes' => ['rows' => 3],
            ]);

            // Color
            $stack->field('primary_color', [
                'type'    => 'color',
                'label'   => __('Primary Color', 'optstack'),
                'default' => '#2271b1',
            ]);

            $stack->field('secondary_color', [
                'type'    => 'color',
                'label'   => __('Secondary Color', 'optstack'),
                'default' => '#135e96',
            ]);

            // Toggle
            $stack->field('show_header_search', [
                'type'    => 'toggle',
                'label'   => __('Show Header Search', 'optstack'),
                'default' => true,
                'description' => __('Display search bar in the site header.', 'optstack'),
            ]);

            $stack->field('sticky_header', [
                'type'    => 'toggle',
                'label'   => __('Sticky Header', 'optstack'),
                'default' => false,
            ]);

            // Number
            $stack->field('logo_width', [
                'type'    => 'number',
                'label'   => __('Logo Width', 'optstack'),
                'default' => 180,
                'attributes' => [
                    'min'    => 50,
                    'max'    => 500,
                    'step'   => 10,
                    'suffix' => 'px',
                ],
            ]);

            // Select
            $stack->field('header_layout', [
                'type'    => 'select',
                'label'   => __('Header Layout', 'optstack'),
                'default' => 'default',
                'options' => [
                    ['value' => 'default',  'label' => __('Default', 'optstack')],
                    ['value' => 'centered', 'label' => __('Centered', 'optstack')],
                    ['value' => 'minimal',  'label' => __('Minimal', 'optstack')],
                    ['value' => 'full',     'label' => __('Full Width', 'optstack')],
                ],
            ]);

            // Radio
            $stack->field('sidebar_position', [
                'type'    => 'radio',
                'label'   => __('Sidebar Position', 'optstack'),
                'default' => 'right',
                'options' => [
                    ['value' => 'left',  'label' => __('Left', 'optstack')],
                    ['value' => 'right', 'label' => __('Right', 'optstack')],
                    ['value' => 'none',  'label' => __('No Sidebar', 'optstack')],
                ],
            ]);

            // URL
            $stack->field('social_facebook', [
                'type'  => 'url',
                'label' => __('Facebook URL', 'optstack'),
                'attributes' => ['placeholder' => 'https://facebook.com/yourpage'],
            ]);

            $stack->field('social_twitter', [
                'type'  => 'url',
                'label' => __('Twitter / X URL', 'optstack'),
                'attributes' => ['placeholder' => 'https://x.com/yourhandle'],
            ]);

            // Media
            $stack->field('site_logo', [
                'type'  => 'media',
                'label' => __('Site Logo', 'optstack'),
                'description' => __('Upload your site logo.', 'optstack'),
                'attributes' => [
                    'allowedTypes' => ['image'],
                    'buttonText'   => __('Select Logo', 'optstack'),
                ],
            ]);

            // Checkbox group
            $stack->field('visible_sections', [
                'type'    => 'checkbox-group',
                'label'   => __('Visible Sections', 'optstack'),
                'description' => __('Choose which sections to display on the homepage.', 'optstack'),
                'options' => [
                    ['value' => 'hero',         'label' => __('Hero Banner', 'optstack')],
                    ['value' => 'features',     'label' => __('Features', 'optstack')],
                    ['value' => 'testimonials', 'label' => __('Testimonials', 'optstack')],
                    ['value' => 'cta',          'label' => __('Call to Action', 'optstack')],
                ],
                'default' => ['hero', 'features'],
            ]);
        })
        ->build();
});


add_action('wp_head', function () {
    // Get stack values from the customizer context; StackRegistry needed here
    $stacks = OptStack::getField('customizer_theme_options', 'primary_color', '#2271b1');
    var_dump($stacks);
});




