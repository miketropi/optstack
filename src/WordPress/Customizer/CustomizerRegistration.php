<?php

declare(strict_types=1);

namespace OptStack\WordPress\Customizer;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;
use OptStack\WordPress\Admin;
use WP_Customize_Manager;

/**
 * Registers OptStack stacks (context 'customizer') in the WordPress Customizer.
 * Each stack gets a top-level section with one setting (theme_mod or option) and one
 * control that renders the OptStack React UI.
 */
class CustomizerRegistration
{
    /**
     * Register Customizer hooks. Call once (from optstack_ready).
     */
    public static function register(): void
    {
        add_action('customize_register', [self::class, 'onCustomizeRegister'], 20);
        add_action('customize_controls_enqueue_scripts', [self::class, 'onEnqueueScripts']);
    }

    /**
     * Add sections, settings, and controls for each customizer stack.
     */
    public static function onCustomizeRegister(WP_Customize_Manager $wp_customize): void
    {
        $stacks = StackRegistry::byContext('customizer');

        if (empty($stacks)) {
            return;
        }

        foreach ($stacks as $stack) {
            self::registerStack($wp_customize, $stack);
        }
    }

    /**
     * Enqueue OptStack admin assets when the Customizer is loaded so the React UI can mount.
     */
    public static function onEnqueueScripts(): void
    {
        $stacks = StackRegistry::byContext('customizer');
        if (empty($stacks)) {
            return;
        }
        Admin::getInstance()->enqueueAssetsForCustomizer($stacks);
    }

    /**
     * Register one stack in the Customizer: section, setting, control.
     *
     * Uses a top-level section (no panel) so it always appears in the sidebar,
     * even with block themes that limit the Customizer.
     */
    private static function registerStack(WP_Customize_Manager $wp_customize, Stack $stack): void
    {
        $stack_id = $stack->getId();
        $section_id = 'optstack_' . $stack_id;

        $storage = $stack->getCustomizeStorage();
        $is_theme_mod = ($storage === 'theme_mod');
        $setting_key = $stack_id;

        // Top-level section (no panel → always visible in sidebar)
        $wp_customize->add_section($section_id, [
            'title'       => $stack->getLabel(),
            'description' => $stack->getDescription() ?: '',
            'priority'    => 30,
            'capability'  => 'edit_theme_options',
        ]);

        // Setting: holds the full stack data array
        $wp_customize->add_setting($setting_key, [
            'type'              => $is_theme_mod ? 'theme_mod' : 'option',
            'default'           => '',
            'capability'        => 'edit_theme_options',
            'transport'         => 'refresh',
            'sanitize_callback' => [self::class, 'sanitizeSetting'],
        ]);

        // Control: renders the OptStack React mount point
        $wp_customize->add_control(new OptStackControl($wp_customize, 'optstack_control_' . $stack_id, [
            'label'    => $stack->getLabel(),
            'section'  => $section_id,
            'settings' => $setting_key,
            'stack'    => $stack,
        ]));
    }

    /**
     * Sanitize the Customizer setting value.
     */
    public static function sanitizeSetting(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return is_array($value) ? $value : '';
    }
}
