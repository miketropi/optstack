<?php
/**
 * OptStack Example: Standard Theme Options
 * 
 * This example demonstrates a complete theme options implementation
 * covering common use cases for WordPress themes.
 * 
 * Features:
 * - General Settings
 * - Header & Navigation
 * - Footer Configuration
 * - Typography Controls
 * - Color Scheme
 * - Social Media Links
 * - SEO Settings
 * - Custom Code (CSS/JS)
 * - Performance Options
 * - Import/Export Settings
 * 
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('optstack_init', function () {
    
    OptStack::make('theme_options')
        ->forOptions()
        ->menuParent('themes.php')
        ->menuIcon('dashicons-admin-appearance')
        ->label('Theme Options')
        ->description('Customize your theme appearance and functionality')
        ->define(function ($stack) {
            
            // ================================================================
            // TAB 1: GENERAL SETTINGS
            // ================================================================
            $stack->tab('general', function ($tab) {
                // Site Identity
                $tab->group('identity', function ($group) {
                    $group->field('site_logo', [
                        'type' => 'media',
                        'label' => 'Site Logo',
                        'description' => 'Upload your site logo',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                            'buttonText' => 'Select Logo',
                        ],
                    ]);
                    
                    $group->field('logo_width', [
                        'type' => 'number',
                        'label' => 'Logo Width',
                        'default' => 180,
                        'attributes' => [
                            'min' => 50,
                            'max' => 500,
                            'step' => 10,
                            'suffix' => 'px',
                        ],
                    ]);
                    
                    $group->field('favicon', [
                        'type' => 'media',
                        'label' => 'Favicon',
                        'description' => 'Upload site favicon (32x32 or 64x64 pixels)',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                        ],
                    ]);
                    
                    $group->field('site_tagline', [
                        'type' => 'text',
                        'label' => 'Site Tagline',
                        'description' => 'A short description of your site',
                        'attributes' => [
                            'placeholder' => 'Your tagline here',
                        ],
                    ]);
                }, [
                    'label' => 'Site Identity',
                    'layout' => 'box',
                ]);
                
                // Layout Options
                $tab->group('layout', function ($group) {
                    $group->field('container_width', [
                        'type' => 'number',
                        'label' => 'Container Max Width',
                        'default' => 1200,
                        'attributes' => [
                            'min' => 960,
                            'max' => 1920,
                            'step' => 10,
                            'suffix' => 'px',
                        ],
                    ]);
                    
                    $group->field('sidebar_layout', [
                        'type' => 'radio-image',
                        'label' => 'Default Sidebar Layout',
                        'default' => 'right',
                        'options' => [
                            [
                                'value' => 'left',
                                'label' => 'Left Sidebar',
                                'description' => 'Left',
                            ],
                            [
                                'value' => 'right',
                                'label' => 'Right Sidebar',
                                'description' => 'Right',
                            ],
                            [
                                'value' => 'none',
                                'label' => 'No Sidebar',
                                'description' => 'None',
                            ],
                        ],
                    ]);
                    
                    $group->field('content_width', [
                        'type' => 'number',
                        'label' => 'Content Width (%)',
                        'default' => 70,
                        'attributes' => [
                            'min' => 50,
                            'max' => 100,
                            'step' => 5,
                            'suffix' => '%',
                        ],
                        'conditions' => [
                            ['field' => 'sidebar_layout', 'operator' => '!=', 'value' => 'none'],
                        ],
                    ]);
                    
                    $group->field('boxed_layout', [
                        'type' => 'toggle',
                        'label' => 'Boxed Layout',
                        'default' => false,
                        'description' => 'Enable boxed layout with shadow',
                    ]);
                }, [
                    'label' => 'Layout Settings',
                    'layout' => 'box',
                ]);
                
                // Preloader
                $tab->group('preloader', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Preloader',
                        'default' => false,
                    ]);
                    
                    $group->field('style', [
                        'type' => 'select',
                        'label' => 'Preloader Style',
                        'default' => 'spinner',
                        'options' => [
                            ['value' => 'spinner', 'label' => 'Spinner'],
                            ['value' => 'dots', 'label' => 'Dots'],
                            ['value' => 'logo', 'label' => 'Logo Fade'],
                            ['value' => 'custom', 'label' => 'Custom HTML'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('background_color', [
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#ffffff',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Preloader',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'General',
                // 'icon' => 'dashicons-admin-generic',
            ]);
            
            // ================================================================
            // TAB 2: HEADER & NAVIGATION
            // ================================================================
            $stack->tab('header', function ($tab) {

                // Header Layout
                $tab->group('header_layout', function ($group) {
                    $group->field('style', [
                        'type' => 'select',
                        'label' => 'Header Style',
                        'default' => 'default',
                        'options' => [
                            ['value' => 'default', 'label' => 'Default (Logo Left, Menu Right)'],
                            ['value' => 'centered', 'label' => 'Centered Logo'],
                            ['value' => 'split', 'label' => 'Split Menu (Logo Center)'],
                            ['value' => 'vertical', 'label' => 'Vertical Sidebar'],
                        ],
                    ]);
                    
                    $group->field('sticky', [
                        'type' => 'toggle',
                        'label' => 'Sticky Header',
                        'default' => true,
                        'description' => 'Keep header visible when scrolling',
                    ]);
                    
                    $group->field('transparent', [
                        'type' => 'toggle',
                        'label' => 'Transparent Header on Homepage',
                        'default' => false,
                        'description' => 'Make header transparent over hero section',
                    ]);
                    
                    $group->field('height', [
                        'type' => 'number',
                        'label' => 'Header Height',
                        'default' => 80,
                        'attributes' => [
                            'min' => 60,
                            'max' => 150,
                            'step' => 5,
                            'suffix' => 'px',
                        ],
                    ]);
                    
                    $group->field('background', [
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#ffffff',
                        'attributes' => [
                            'alpha' => true,
                        ],
                    ]);
                }, [
                    'label' => 'Header Layout',
                    'layout' => 'box',
                ]);
                
                // Top Bar
                $tab->group('top_bar', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Top Bar',
                        'default' => false,
                    ]);
                    
                    $group->field('content_left', [
                        'type' => 'wysiwyg',
                        'label' => 'Left Content',
                        'description' => 'Contact info, opening hours, etc.',
                        'attributes' => [
                            'rows' => 3,
                            'simple' => true,
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('content_right', [
                        'type' => 'wysiwyg',
                        'label' => 'Right Content',
                        'description' => 'Social icons, quick links, etc.',
                        'attributes' => [
                            'rows' => 3,
                            'simple' => true,
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('background', [
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#f8f9fa',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Top Bar',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Mobile Menu
                $tab->group('mobile_menu', function ($group) {
                    $group->field('breakpoint', [
                        'type' => 'number',
                        'label' => 'Mobile Breakpoint',
                        'default' => 992,
                        'description' => 'Show mobile menu below this width',
                        'attributes' => [
                            'min' => 320,
                            'max' => 1200,
                            'step' => 1,
                            'suffix' => 'px',
                        ],
                    ]);
                    
                    $group->field('style', [
                        'type' => 'select',
                        'label' => 'Mobile Menu Style',
                        'default' => 'slide',
                        'options' => [
                            ['value' => 'slide', 'label' => 'Slide from Side'],
                            ['value' => 'dropdown', 'label' => 'Dropdown'],
                            ['value' => 'fullscreen', 'label' => 'Fullscreen Overlay'],
                        ],
                    ]);
                    
                    $group->field('position', [
                        'type' => 'select',
                        'label' => 'Slide Position',
                        'default' => 'left',
                        'options' => [
                            ['value' => 'left', 'label' => 'Left'],
                            ['value' => 'right', 'label' => 'Right'],
                        ],
                        'conditions' => [
                            ['field' => 'style', 'operator' => '==', 'value' => 'slide'],
                        ],
                    ]);
                }, [
                    'label' => 'Mobile Menu',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Header Elements
                $tab->group('header_elements', function ($group) {
                    $group->field('search', [
                        'type' => 'toggle',
                        'label' => 'Show Search Icon',
                        'default' => true,
                    ]);
                    
                    $group->field('cart', [
                        'type' => 'toggle',
                        'label' => 'Show Cart Icon',
                        'default' => false,
                        'description' => 'Requires WooCommerce',
                    ]);
                    
                    $group->field('account', [
                        'type' => 'toggle',
                        'label' => 'Show Account/Login Icon',
                        'default' => false,
                    ]);
                    
                    $group->field('cta_button', [
                        'type' => 'toggle',
                        'label' => 'Show CTA Button',
                        'default' => false,
                    ]);
                    
                    $group->field('cta_text', [
                        'type' => 'text',
                        'label' => 'CTA Button Text',
                        'default' => 'Get Started',
                        'conditions' => [
                            ['field' => 'cta_button', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('cta_url', [
                        'type' => 'url',
                        'label' => 'CTA Button URL',
                        'conditions' => [
                            ['field' => 'cta_button', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Header Elements',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Header & Nav',
                // 'icon' => 'dashicons-menu',
            ]);
            
            // ================================================================
            // TAB 3: FOOTER
            // ================================================================
            $stack->tab('footer', function ($tab) {
                // Footer Layout
                $tab->group('footer_layout', function ($group) {
                    $group->field('columns', [
                        'type' => 'select',
                        'label' => 'Footer Widget Columns',
                        'default' => '4',
                        'options' => [
                            ['value' => '1', 'label' => '1 Column'],
                            ['value' => '2', 'label' => '2 Columns'],
                            ['value' => '3', 'label' => '3 Columns'],
                            ['value' => '4', 'label' => '4 Columns'],
                        ],
                    ]);
                    
                    $group->field('background', [
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#1f2937',
                    ]);
                    
                    $group->field('text_color', [
                        'type' => 'color',
                        'label' => 'Text Color',
                        'default' => '#9ca3af',
                    ]);
                    
                    $group->field('heading_color', [
                        'type' => 'color',
                        'label' => 'Heading Color',
                        'default' => '#ffffff',
                    ]);
                }, [
                    'label' => 'Footer Layout',
                    'layout' => 'box',
                ]);
                
                // Copyright Bar
                $tab->group('copyright', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Show Copyright Bar',
                        'default' => true,
                    ]);
                    
                    $group->field('text', [
                        'type' => 'textarea',
                        'label' => 'Copyright Text',
                        'default' => '© {year} {site_name}. All rights reserved.',
                        'description' => 'Use {year} for current year, {site_name} for site name',
                        'attributes' => [
                            'rows' => 2,
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('links', [
                        'type' => 'textarea',
                        'label' => 'Footer Links',
                        'description' => 'Add links like: <a href="#">Privacy</a> | <a href="#">Terms</a>',
                        'attributes' => [
                            'rows' => 2,
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('background', [
                        'type' => 'color',
                        'label' => 'Background Color',
                        'default' => '#111827',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Copyright Bar',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Back to Top Button
                $tab->group('back_to_top', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Show Back to Top Button',
                        'default' => true,
                    ]);
                    
                    $group->field('position', [
                        'type' => 'select',
                        'label' => 'Button Position',
                        'default' => 'right',
                        'options' => [
                            ['value' => 'left', 'label' => 'Bottom Left'],
                            ['value' => 'right', 'label' => 'Bottom Right'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('style', [
                        'type' => 'select',
                        'label' => 'Button Style',
                        'default' => 'circle',
                        'options' => [
                            ['value' => 'circle', 'label' => 'Circle'],
                            ['value' => 'square', 'label' => 'Square'],
                            ['value' => 'pill', 'label' => 'Pill'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Back to Top',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Footer',
                // 'icon' => 'dashicons-align-full-width',
            ]);
            
            // ================================================================
            // TAB 4: TYPOGRAPHY
            // ================================================================
            $stack->tab('typography', function ($tab) {
                // Body Typography
                $tab->field('body_font', [
                    'type' => 'typography',
                    'label' => 'Body Font',
                    'default' => [
                        'fontFamily' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'fontSize' => 16,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '400',
                        'lineHeight' => 1.6,
                        'color' => '#374151',
                    ],
                    'responsive' => true,
                ]);
                
                // Heading Typography
                $tab->field('heading_font', [
                    'type' => 'typography',
                    'label' => 'Heading Font (H1-H6)',
                    'default' => [
                        'fontFamily' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'fontWeight' => '700',
                        'lineHeight' => 1.2,
                        'textTransform' => 'none',
                        'color' => '#111827',
                    ],
                    'responsive' => true,
                ]);
                
                // Individual Heading Sizes
                $tab->group('heading_sizes', function ($group) {
                    $group->field('h1_size', [
                        'type' => 'number',
                        'label' => 'H1 Font Size',
                        'description' => 'Set the font size for H1 headings.',
                        'default' => 48,
                        'attributes' => [
                            'min' => 24,
                            'max' => 72,
                            'step' => 2,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                    
                    $group->field('h2_size', [
                        'type' => 'number',
                        'label' => 'H2 Font Size',
                        'default' => 36,
                        'attributes' => [
                            'min' => 20,
                            'max' => 60,
                            'step' => 2,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                    
                    $group->field('h3_size', [
                        'type' => 'number',
                        'label' => 'H3 Font Size',
                        'default' => 28,
                        'attributes' => [
                            'min' => 18,
                            'max' => 48,
                            'step' => 2,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                    
                    $group->field('h4_size', [
                        'type' => 'number',
                        'label' => 'H4 Font Size',
                        'default' => 22,
                        'attributes' => [
                            'min' => 16,
                            'max' => 36,
                            'step' => 1,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                    
                    $group->field('h5_size', [
                        'type' => 'number',
                        'label' => 'H5 Font Size',
                        'default' => 18,
                        'attributes' => [
                            'min' => 14,
                            'max' => 28,
                            'step' => 1,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                    
                    $group->field('h6_size', [
                        'type' => 'number',
                        'label' => 'H6 Font Size',
                        'default' => 16,
                        'attributes' => [
                            'min' => 12,
                            'max' => 24,
                            'step' => 1,
                            'suffix' => 'px',
                        ],
                        'responsive' => true,
                    ]);
                }, [
                    'label' => 'Heading Sizes',
                    'description' => 'Customize individual heading sizes',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Menu Typography
                $tab->field('menu_font', [
                    'type' => 'typography',
                    'label' => 'Menu Font',
                    'default' => [
                        'fontFamily' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'fontSize' => 15,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '500',
                        'textTransform' => 'none',
                        'letterSpacing' => 0.5,
                        'letterSpacingUnit' => 'px',
                    ],
                    'responsive' => true,
                ]);
                
                // Button Typography
                $tab->field('button_font', [
                    'type' => 'typography',
                    'label' => 'Button Font',
                    'default' => [
                        'fontFamily' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        'fontSize' => 15,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '600',
                        'textTransform' => 'none',
                        'letterSpacing' => 0.5,
                        'letterSpacingUnit' => 'px',
                    ],
                    'responsive' => true,
                ]);
            }, [
                'label' => 'Typography',
                // 'icon' => 'dashicons-editor-textcolor',
            ]);
            
            // ================================================================
            // TAB 5: COLORS
            // ================================================================
            $stack->tab('colors', function ($tab) {
                // Primary Colors
                $tab->group('primary', function ($group) {
                    $group->field('brand', [
                        'type' => 'color',
                        'label' => 'Primary Brand Color',
                        'default' => '#3b82f6',
                    ]);
                    
                    $group->field('secondary', [
                        'type' => 'color',
                        'label' => 'Secondary Color',
                        'default' => '#8b5cf6',
                    ]);
                    
                    $group->field('accent', [
                        'type' => 'color',
                        'label' => 'Accent Color',
                        'default' => '#10b981',
                    ]);
                }, [
                    'label' => 'Primary Colors',
                    'layout' => 'box',
                ]);
                
                // Text Colors
                $tab->group('text', function ($group) {
                    $group->field('primary', [
                        'type' => 'color',
                        'label' => 'Primary Text',
                        'default' => '#111827',
                    ]);
                    
                    $group->field('secondary', [
                        'type' => 'color',
                        'label' => 'Secondary Text',
                        'default' => '#6b7280',
                    ]);
                    
                    $group->field('muted', [
                        'type' => 'color',
                        'label' => 'Muted Text',
                        'default' => '#9ca3af',
                    ]);
                }, [
                    'label' => 'Text Colors',
                    'layout' => 'box',
                ]);
                
                // Link Colors
                $tab->group('links', function ($group) {
                    $group->field('default', [
                        'type' => 'color',
                        'label' => 'Link Color',
                        'default' => '#3b82f6',
                    ]);
                    
                    $group->field('hover', [
                        'type' => 'color',
                        'label' => 'Link Hover Color',
                        'default' => '#2563eb',
                    ]);
                }, [
                    'label' => 'Link Colors',
                    'layout' => 'inline',
                ]);
                
                // Button Colors
                $tab->group('buttons', function ($group) {
                    $group->field('primary_bg', [
                        'type' => 'color',
                        'label' => 'Primary Button Background',
                        'default' => '#3b82f6',
                    ]);
                    
                    $group->field('primary_text', [
                        'type' => 'color',
                        'label' => 'Primary Button Text',
                        'default' => '#ffffff',
                    ]);
                    
                    $group->field('primary_hover', [
                        'type' => 'color',
                        'label' => 'Primary Button Hover',
                        'default' => '#2563eb',
                    ]);
                    
                    $group->field('secondary_bg', [
                        'type' => 'color',
                        'label' => 'Secondary Button Background',
                        'default' => '#6b7280',
                    ]);
                    
                    $group->field('secondary_text', [
                        'type' => 'color',
                        'label' => 'Secondary Button Text',
                        'default' => '#ffffff',
                    ]);
                }, [
                    'label' => 'Button Colors',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Background Colors
                $tab->group('backgrounds', function ($group) {
                    $group->field('body', [
                        'type' => 'color',
                        'label' => 'Body Background',
                        'default' => '#ffffff',
                    ]);
                    
                    $group->field('alternate', [
                        'type' => 'color',
                        'label' => 'Alternate Section Background',
                        'default' => '#f9fafb',
                    ]);
                    
                    $group->field('sidebar', [
                        'type' => 'color',
                        'label' => 'Sidebar Background',
                        'default' => '#f9fafb',
                    ]);
                }, [
                    'label' => 'Background Colors',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Border Colors
                $tab->group('borders', function ($group) {
                    $group->field('default', [
                        'type' => 'color',
                        'label' => 'Default Border',
                        'default' => '#e5e7eb',
                    ]);
                    
                    $group->field('strong', [
                        'type' => 'color',
                        'label' => 'Strong Border',
                        'default' => '#d1d5db',
                    ]);
                }, [
                    'label' => 'Border Colors',
                    'layout' => 'inline',
                ]);
            }, [
                'label' => 'Colors',
                // 'icon' => 'dashicons-art',
            ]);
            
            // ================================================================
            // TAB 6: SOCIAL MEDIA
            // ================================================================
            $stack->tab('social', function ($tab) {
                // Social Links
                $tab->group('links', function ($group) {
                    $group->repeatable(0, 15);
                    
                    $group->field('platform', [
                        'type' => 'select',
                        'label' => 'Platform',
                        'options' => [
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'twitter', 'label' => 'Twitter/X'],
                            ['value' => 'instagram', 'label' => 'Instagram'],
                            ['value' => 'linkedin', 'label' => 'LinkedIn'],
                            ['value' => 'youtube', 'label' => 'YouTube'],
                            ['value' => 'pinterest', 'label' => 'Pinterest'],
                            ['value' => 'tiktok', 'label' => 'TikTok'],
                            ['value' => 'github', 'label' => 'GitHub'],
                            ['value' => 'dribbble', 'label' => 'Dribbble'],
                            ['value' => 'behance', 'label' => 'Behance'],
                            ['value' => 'medium', 'label' => 'Medium'],
                            ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                            ['value' => 'telegram', 'label' => 'Telegram'],
                        ],
                    ]);
                    
                    $group->field('url', [
                        'type' => 'url',
                        'label' => 'Profile URL',
                        'attributes' => [
                            'placeholder' => 'https://...',
                        ],
                    ]);
                    
                    $group->field('label', [
                        'type' => 'text',
                        'label' => 'Custom Label',
                        'description' => 'Optional: Override default platform name',
                    ]);
                }, [
                    'label' => 'Social Media Links',
                    'description' => 'Add your social media profiles',
                    'layout' => 'box',
                ]);
                
                // Social Sharing
                $tab->group('sharing', function ($group) {
                    $group->field('enable_posts', [
                        'type' => 'toggle',
                        'label' => 'Enable Sharing on Posts',
                        'default' => true,
                    ]);
                    
                    $group->field('enable_pages', [
                        'type' => 'toggle',
                        'label' => 'Enable Sharing on Pages',
                        'default' => false,
                    ]);
                    
                    $group->field('platforms', [
                        'type' => 'checkbox-group',
                        'label' => 'Sharing Platforms',
                        'default' => ['facebook', 'twitter', 'linkedin'],
                        'options' => [
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'twitter', 'label' => 'Twitter/X'],
                            ['value' => 'linkedin', 'label' => 'LinkedIn'],
                            ['value' => 'pinterest', 'label' => 'Pinterest'],
                            ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                            ['value' => 'telegram', 'label' => 'Telegram'],
                            ['value' => 'email', 'label' => 'Email'],
                        ],
                        'conditions' => [
                            ['field' => 'enable_posts', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('position', [
                        'type' => 'select',
                        'label' => 'Sharing Button Position',
                        'default' => 'bottom',
                        'options' => [
                            ['value' => 'top', 'label' => 'Top of Content'],
                            ['value' => 'bottom', 'label' => 'Bottom of Content'],
                            ['value' => 'both', 'label' => 'Both Top and Bottom'],
                            ['value' => 'float', 'label' => 'Floating Sidebar'],
                        ],
                        'conditions' => [
                            ['field' => 'enable_posts', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Social Sharing',
                    'description' => 'Configure social sharing buttons',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Social Media',
                // 'icon' => 'dashicons-share',
            ]);
            
            // ================================================================
            // TAB 7: SEO & META
            // ================================================================
            $stack->tab('seo', function ($tab) {
                // Basic SEO
                $tab->group('basic', function ($group) {
                    $group->field('site_name', [
                        'type' => 'text',
                        'label' => 'Site Name',
                        'description' => 'Used in title tags and meta',
                    ]);
                    
                    $group->field('separator', [
                        'type' => 'select',
                        'label' => 'Title Separator',
                        'default' => '|',
                        'options' => [
                            ['value' => '|', 'label' => '|'],
                            ['value' => '-', 'label' => '-'],
                            ['value' => '–', 'label' => '–'],
                            ['value' => '—', 'label' => '—'],
                            ['value' => '•', 'label' => '•'],
                            ['value' => '/', 'label' => '/'],
                        ],
                    ]);
                    
                    $group->field('home_title', [
                        'type' => 'text',
                        'label' => 'Homepage Title',
                        'description' => 'Leave empty to use site title',
                        'attributes' => [
                            'maxlength' => 60,
                        ],
                    ]);
                    
                    $group->field('home_description', [
                        'type' => 'textarea',
                        'label' => 'Homepage Meta Description',
                        'attributes' => [
                            'rows' => 2,
                            'maxlength' => 160,
                        ],
                    ]);
                    
                    $group->field('keywords', [
                        'type' => 'text',
                        'label' => 'Meta Keywords',
                        'description' => 'Comma-separated keywords',
                    ]);
                }, [
                    'label' => 'Basic SEO',
                    'layout' => 'box',
                ]);
                
                // Open Graph
                $tab->group('opengraph', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Open Graph Tags',
                        'default' => true,
                        'description' => 'For Facebook, LinkedIn sharing',
                    ]);
                    
                    $group->field('default_image', [
                        'type' => 'media',
                        'label' => 'Default Share Image',
                        'description' => 'Used when no featured image (1200x630px recommended)',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Open Graph (Facebook)',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Twitter Cards
                $tab->group('twitter', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Twitter Cards',
                        'default' => true,
                    ]);
                    
                    $group->field('card_type', [
                        'type' => 'select',
                        'label' => 'Card Type',
                        'default' => 'summary_large_image',
                        'options' => [
                            ['value' => 'summary', 'label' => 'Summary'],
                            ['value' => 'summary_large_image', 'label' => 'Summary with Large Image'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('username', [
                        'type' => 'text',
                        'label' => 'Twitter Username',
                        'description' => 'Without @',
                        'attributes' => [
                            'placeholder' => 'username',
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Twitter Cards',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Schema Markup
                $tab->group('schema', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Schema Markup',
                        'default' => true,
                        'description' => 'JSON-LD structured data',
                    ]);
                    
                    $group->field('organization_name', [
                        'type' => 'text',
                        'label' => 'Organization Name',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('organization_logo', [
                        'type' => 'media',
                        'label' => 'Organization Logo',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Schema Markup',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'SEO & Meta',
                // 'icon' => 'dashicons-visibility',
            ]);
            
            // ================================================================
            // TAB 8: CUSTOM CODE
            // ================================================================
            $stack->tab('custom_code', function ($tab) {
                // Custom CSS
                $tab->field('custom_css', [
                    'type' => 'code',
                    'label' => 'Custom CSS',
                    'description' => 'Add your custom CSS here',
                    'attributes' => [
                        'language' => 'text/css',
                        'rows' => 20,
                    ],
                ]);
                
                // Custom JavaScript
                $tab->field('custom_js', [
                    'type' => 'code',
                    'label' => 'Custom JavaScript',
                    'description' => 'Add your custom JS here (without <script> tags)',
                    'attributes' => [
                        'language' => 'application/javascript',
                        'rows' => 15,
                    ],
                ]);
                
                // Header Code
                $tab->field('header_code', [
                    'type' => 'code',
                    'label' => 'Header Code',
                    'description' => 'Code to insert in <head> section',
                    'attributes' => [
                        'language' => 'text/html',
                        'rows' => 10,
                    ],
                ]);
                
                // Footer Code
                $tab->field('footer_code', [
                    'type' => 'code',
                    'label' => 'Footer Code',
                    'description' => 'Code to insert before </body> tag',
                    'attributes' => [
                        'language' => 'text/html',
                        'rows' => 10,
                    ],
                ]);
                
                // Google Analytics
                $tab->group('analytics', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Google Analytics',
                        'default' => false,
                    ]);
                    
                    $group->field('tracking_id', [
                        'type' => 'text',
                        'label' => 'Tracking ID',
                        'description' => 'GA4 Measurement ID (G-XXXXXXXXXX)',
                        'attributes' => [
                            'placeholder' => 'G-XXXXXXXXXX',
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('anonymize_ip', [
                        'type' => 'toggle',
                        'label' => 'Anonymize IP',
                        'default' => true,
                        'description' => 'GDPR compliance',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Google Analytics',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Custom Code',
                // 'icon' => 'dashicons-editor-code',
            ]);
            
            // ================================================================
            // TAB 9: PERFORMANCE
            // ================================================================
            $stack->tab('performance', function ($tab) {
                // Optimization
                $tab->group('optimization', function ($group) {
                    $group->field('lazy_load_images', [
                        'type' => 'toggle',
                        'label' => 'Lazy Load Images',
                        'default' => true,
                        'description' => 'Load images as they come into viewport',
                    ]);
                    
                    $group->field('disable_emojis', [
                        'type' => 'toggle',
                        'label' => 'Disable WordPress Emojis',
                        'default' => false,
                        'description' => 'Remove emoji scripts if not needed',
                    ]);
                    
                    $group->field('disable_embeds', [
                        'type' => 'toggle',
                        'label' => 'Disable Embeds',
                        'default' => false,
                        'description' => 'Remove oEmbed scripts',
                    ]);
                    
                    $group->field('remove_query_strings', [
                        'type' => 'toggle',
                        'label' => 'Remove Query Strings',
                        'default' => false,
                        'description' => 'Remove ?ver= from static resources',
                    ]);
                }, [
                    'label' => 'Optimization',
                    'layout' => 'box',
                ]);
                
                // Asset Loading
                $tab->group('assets', function ($group) {
                    $group->field('minify_css', [
                        'type' => 'toggle',
                        'label' => 'Minify CSS',
                        'default' => false,
                        'description' => 'Combine and minify CSS files',
                    ]);
                    
                    $group->field('minify_js', [
                        'type' => 'toggle',
                        'label' => 'Minify JavaScript',
                        'default' => false,
                        'description' => 'Combine and minify JS files',
                    ]);
                    
                    $group->field('defer_js', [
                        'type' => 'toggle',
                        'label' => 'Defer JavaScript',
                        'default' => false,
                        'description' => 'Load JS after page content',
                    ]);
                }, [
                    'label' => 'Asset Loading',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Fonts
                $tab->group('fonts', function ($group) {
                    $group->field('google_fonts_display', [
                        'type' => 'select',
                        'label' => 'Google Fonts Display',
                        'default' => 'swap',
                        'options' => [
                            ['value' => 'auto', 'label' => 'Auto'],
                            ['value' => 'block', 'label' => 'Block'],
                            ['value' => 'swap', 'label' => 'Swap (Recommended)'],
                            ['value' => 'fallback', 'label' => 'Fallback'],
                            ['value' => 'optional', 'label' => 'Optional'],
                        ],
                        'description' => 'Font display strategy',
                    ]);
                    
                    $group->field('preload_fonts', [
                        'type' => 'toggle',
                        'label' => 'Preload Web Fonts',
                        'default' => false,
                        'description' => 'Preload critical fonts',
                    ]);
                }, [
                    'label' => 'Font Loading',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Caching
                $tab->group('caching', function ($group) {
                    $group->field('browser_cache', [
                        'type' => 'toggle',
                        'label' => 'Enable Browser Caching',
                        'default' => true,
                        'description' => 'Add cache-control headers',
                    ]);
                    
                    $group->field('cache_duration', [
                        'type' => 'number',
                        'label' => 'Cache Duration (days)',
                        'default' => 7,
                        'attributes' => [
                            'min' => 1,
                            'max' => 365,
                            'step' => 1,
                        ],
                        'conditions' => [
                            ['field' => 'browser_cache', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Caching',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Performance',
                // 'icon' => 'dashicons-performance',
            ]);
            
            // ================================================================
            // TAB 10: ADVANCED
            // ================================================================
            $stack->tab('advanced', function ($tab) {
                // Maintenance Mode
                $tab->group('maintenance', function ($group) {
                    $group->field('enable', [
                        'type' => 'toggle',
                        'label' => 'Enable Maintenance Mode',
                        'default' => false,
                        'description' => 'Show maintenance page to visitors (admins can still access)',
                    ]);
                    
                    $group->field('title', [
                        'type' => 'text',
                        'label' => 'Maintenance Title',
                        'default' => 'Site Under Maintenance',
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                    
                    $group->field('message', [
                        'type' => 'wysiwyg',
                        'label' => 'Maintenance Message',
                        'default' => '<p>We are currently performing scheduled maintenance. Please check back soon.</p>',
                        'attributes' => [
                            'rows' => 5,
                            'simple' => true,
                        ],
                        'conditions' => [
                            ['field' => 'enable', 'operator' => '==', 'value' => true],
                        ],
                    ]);
                }, [
                    'label' => 'Maintenance Mode',
                    'layout' => 'box',
                ]);
                
                // Import/Export
                $tab->group('import_export', function ($group) {
                    $group->field('info', [
                        'type' => 'text',
                        'label' => 'Backup & Restore',
                        'description' => 'Use WordPress Tools → Export/Import to backup theme options',
                        'attributes' => [
                            'readonly' => true,
                        ],
                    ]);
                }, [
                    'label' => 'Import / Export',
                    'description' => 'Backup and restore your theme settings',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
                
                // Reset Options
                $tab->group('reset', function ($group) {
                    $group->field('warning', [
                        'type' => 'text',
                        'label' => 'Reset Theme Options',
                        'description' => '⚠️ This will delete all theme options and restore defaults. This action cannot be undone!',
                        'attributes' => [
                            'readonly' => true,
                        ],
                    ]);
                }, [
                    'label' => 'Reset Options',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            }, [
                'label' => 'Advanced',
                // 'icon' => 'dashicons-admin-tools',
            ]);
        })
        ->build();
});

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get all theme options.
 *
 * @return array
 */
function mytheme_get_options(): array
{
    return get_option('theme_options', []);
}

/**
 * Get a specific theme option with dot notation support.
 *
 * @param string $key     Option key (supports dot notation: 'header_layout.sticky')
 * @param mixed  $default Default value if not found
 * @return mixed
 */
function mytheme_option(string $key, mixed $default = null): mixed
{
    $options = mytheme_get_options();
    
    // Support dot notation
    $keys = explode('.', $key);
    $value = $options;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Output custom CSS from theme options.
 */
function mytheme_output_custom_css(): void
{
    $custom_css = mytheme_option('custom_css', '');
    
    if (!empty($custom_css)) {
        echo '<style id="mytheme-custom-css">' . wp_strip_all_tags($custom_css) . '</style>';
    }
}
add_action('wp_head', 'mytheme_output_custom_css', 999);

/**
 * Output custom JavaScript from theme options.
 */
function mytheme_output_custom_js(): void
{
    $custom_js = mytheme_option('custom_js', '');
    
    if (!empty($custom_js)) {
        echo '<script id="mytheme-custom-js">' . $custom_js . '</script>';
    }
}
add_action('wp_footer', 'mytheme_output_custom_js', 999);

/**
 * Output header code from theme options.
 */
function mytheme_output_header_code(): void
{
    $header_code = mytheme_option('header_code', '');
    
    if (!empty($header_code)) {
        echo $header_code;
    }
}
add_action('wp_head', 'mytheme_output_header_code', 1);

/**
 * Output footer code from theme options.
 */
function mytheme_output_footer_code(): void
{
    $footer_code = mytheme_option('footer_code', '');
    
    if (!empty($footer_code)) {
        echo $footer_code;
    }
}
add_action('wp_footer', 'mytheme_output_footer_code', 999);

/**
 * Output Google Analytics tracking code.
 */
function mytheme_output_google_analytics(): void
{
    if (!mytheme_option('analytics.enable', false)) {
        return;
    }
    
    $tracking_id = mytheme_option('analytics.tracking_id', '');
    $anonymize = mytheme_option('analytics.anonymize_ip', true);
    
    if (empty($tracking_id)) {
        return;
    }
    
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($tracking_id); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo esc_js($tracking_id); ?>'<?php echo $anonymize ? ", { 'anonymize_ip': true }" : ''; ?>);
    </script>
    <?php
}
add_action('wp_head', 'mytheme_output_google_analytics', 1);

/**
 * Add favicon to site head.
 */
function mytheme_add_favicon(): void
{
    $favicon_id = mytheme_option('identity.favicon');
    
    if ($favicon_id) {
        $favicon_url = wp_get_attachment_url($favicon_id);
        if ($favicon_url) {
            echo '<link rel="icon" href="' . esc_url($favicon_url) . '" type="image/x-icon">';
        }
    }
}
add_action('wp_head', 'mytheme_add_favicon');

/**
 * Get social media links.
 *
 * @return array
 */
function mytheme_get_social_links(): array
{
    return mytheme_option('links', []);
}

/**
 * Display social media icons.
 *
 * @param array $args Display arguments
 */
function mytheme_social_icons(array $args = []): void
{
    $links = mytheme_get_social_links();
    
    if (empty($links)) {
        return;
    }
    
    $defaults = [
        'before' => '<ul class="social-icons">',
        'after' => '</ul>',
        'item_before' => '<li>',
        'item_after' => '</li>',
        'show_label' => false,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    echo $args['before'];
    
    foreach ($links as $link) {
        $platform = $link['platform'] ?? '';
        $url = $link['url'] ?? '';
        $label = $link['label'] ?? ucfirst($platform);
        
        if (empty($url)) {
            continue;
        }
        
        echo $args['item_before'];
        printf(
            '<a href="%s" target="_blank" rel="noopener" class="social-icon social-icon-%s" aria-label="%s">',
            esc_url($url),
            esc_attr($platform),
            esc_attr($label)
        );
        
        if ($args['show_label']) {
            echo '<span>' . esc_html($label) . '</span>';
        } else {
            echo '<i class="icon-' . esc_attr($platform) . '"></i>';
        }
        
        echo '</a>';
        echo $args['item_after'];
    }
    
    echo $args['after'];
}

/**
 * Check if maintenance mode is enabled.
 *
 * @return bool
 */
function mytheme_is_maintenance_mode(): bool
{
    if (current_user_can('administrator')) {
        return false;
    }
    
    return mytheme_option('maintenance.enable', false);
}

/**
 * Display maintenance page.
 */
function mytheme_show_maintenance_page(): void
{
    if (!mytheme_is_maintenance_mode()) {
        return;
    }
    
    $title = mytheme_option('maintenance.title', 'Site Under Maintenance');
    $message = mytheme_option('maintenance.message', '');
    
    wp_die(
        wp_kses_post($message),
        esc_html($title),
        ['response' => 503]
    );
}
add_action('template_redirect', 'mytheme_show_maintenance_page', 1);

/**
 * Resolve typography value for a breakpoint (responsive typography support).
 * When a typography field has responsive enabled, all sub-keys (fontFamily, fontSize, fontSizeUnit,
 * fontWeight, fontStyle, lineHeight, lineHeightUnit, letterSpacing, letterSpacingUnit,
 * textTransform, textDecoration, color) may be stored as arrays with 'desktop', 'tablet', 'mobile'.
 * This returns a flat array with scalar values for the given breakpoint (fallback: desktop -> tablet -> mobile).
 *
 * @param array<string, mixed> $typography Typography settings (may contain responsive sub-keys).
 * @param string               $breakpoint One of 'desktop', 'tablet', 'mobile'.
 * @return array<string, mixed> Typography with scalar values for the breakpoint.
 */
function optstack_resolve_typography_for_breakpoint(array $typography, string $breakpoint = 'desktop'): array
{
    $resolved = $typography;
    $keys = [
        'fontFamily', 'fontSize', 'fontSizeUnit', 'fontWeight', 'fontStyle',
        'lineHeight', 'lineHeightUnit', 'letterSpacing', 'letterSpacingUnit',
        'textTransform', 'textDecoration', 'color',
    ];
    $order = $breakpoint === 'mobile' ? ['mobile', 'tablet', 'desktop'] : ($breakpoint === 'tablet' ? ['tablet', 'desktop', 'mobile'] : ['desktop', 'tablet', 'mobile']);
    foreach ($keys as $key) {
        if (!isset($resolved[$key])) {
            continue;
        }
        $v = $resolved[$key];
        if (!is_array($v) || (!isset($v['desktop']) && !isset($v['tablet']) && !isset($v['mobile']))) {
            continue;
        }
        $val = null;
        foreach ($order as $mode) {
            if (isset($v[$mode])) {
                $val = $v[$mode];
                break;
            }
        }
        $resolved[$key] = $val ?? $resolved[$key];
    }
    return $resolved;
}

add_action( 'wp_head', function() {
    // get heading opts
    // $heading_opts = mytheme_option('button_font');
    // $resolved = optstack_resolve_typography_for_breakpoint($heading_opts, 'desktop');
    // var_dump( $heading_opts );
}, 20 );