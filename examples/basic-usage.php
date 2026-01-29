<?php
/**
 * OptStack Usage Examples
 *
 * This file demonstrates how to use OptStack to define and manage
 * structured data in WordPress across different contexts.
 *
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register OptStack definitions.
 * Use the 'optstack_init' hook to ensure the framework is ready.
 */
add_action('optstack_init', function () {

    // =========================================================================
    // EXAMPLE 1: Simple Options Page (Under OptStack Menu)
    // =========================================================================
    OptStack::make('site_settings')
        ->forOptions()
        ->menuParent('optstack')
        ->label('Site Settings')
        ->description('Global site configuration')
        ->define(function ($stack) {
            // Text field
            $stack->field('site_tagline', [
                'type' => 'text',
                'label' => 'Site Tagline',
                'default' => 'Just another WordPress site',
                'description' => 'A short description of your site.',
            ]);

            // Color picker
            $stack->field('brand_color', [
                'type' => 'color',
                'label' => 'Brand Color',
                'default' => '#2271b1',
                'description' => 'Primary brand color used across the site.',
            ]);

            // Toggle with conditional field
            $stack->field('maintenance_mode', [
                'type' => 'toggle',
                'label' => 'Maintenance Mode',
                'default' => false,
                'description' => 'Enable to show maintenance page to visitors.',
            ]);

            $stack->field('maintenance_message', [
                'type' => 'wysiwyg',
                'label' => 'Maintenance Message',
                'default' => '<p>We are currently performing maintenance. Please check back soon.</p>',
                'attributes' => ['rows' => 5, 'simple' => true],
                'conditions' => [
                    ['field' => 'maintenance_mode', 'operator' => '==', 'value' => true],
                ],
            ]);

            // Group: Social Links
            $stack->group('social_links', function ($group) {
                $group->field('twitter', [
                    'type' => 'url',
                    'label' => 'Twitter/X',
                    'attributes' => ['placeholder' => 'https://x.com/...'],
                ]);

                $group->field('facebook', [
                    'type' => 'url',
                    'label' => 'Facebook',
                    'attributes' => ['placeholder' => 'https://facebook.com/...'],
                ]);

                $group->field('instagram', [
                    'type' => 'url',
                    'label' => 'Instagram',
                    'attributes' => ['placeholder' => 'https://instagram.com/...'],
                ]);

                $group->field('linkedin', [
                    'type' => 'url',
                    'label' => 'LinkedIn',
                    'attributes' => ['placeholder' => 'https://linkedin.com/...'],
                ]);
            }, ['label' => 'Social Links', 'description' => 'Connect your social media profiles.']);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 2: Theme Options with Tabs (Under Appearance Menu)
    // =========================================================================
    OptStack::make('theme_options')
        ->forOptions()
        ->menuParent('themes.php')
        ->label('Theme Options')
        ->description('Customize your theme appearance and behavior')
        ->define(function ($stack) {
            // -----------------------------------------------------------------
            // Tab: General
            // -----------------------------------------------------------------
            $stack->tab('general', function ($tab) {
                $tab->label('General')
                    // ->icon('dashicons-admin-home')
                    ->priority(10)
                    ->description('Basic theme configuration');

                $tab->field('site_logo', [
                    'type' => 'media',
                    'label' => 'Site Logo',
                    'description' => 'Upload your site logo (recommended: 200x50px).',
                    'attributes' => [
                        'allowedTypes' => ['image'],
                        'buttonText' => 'Select Logo',
                    ],
                ]);

                $tab->field('favicon', [
                    'type' => 'media',
                    'label' => 'Favicon',
                    'description' => 'Site favicon (32x32px recommended).',
                    'attributes' => [
                        'allowedTypes' => ['image'],
                        'buttonText' => 'Select Favicon',
                    ],
                ]);

                $tab->field('copyright_text', [
                    'type' => 'text',
                    'label' => 'Copyright Text',
                    'default' => '© {year} Your Company. All rights reserved.',
                    'description' => 'Use {year} for dynamic year.',
                ]);

                // Group: Social Links
                $tab->group('social_links', function ($group) {
                    $group->field('twitter', [
                        'type' => 'url',
                        'label' => 'Twitter/X',
                        'attributes' => ['placeholder' => 'https://x.com/...'],
                    ]);

                    $group->field('facebook', [
                        'type' => 'url',
                        'label' => 'Facebook',
                        'attributes' => ['placeholder' => 'https://facebook.com/...'],
                    ]);

                    $group->field('instagram', [
                        'type' => 'url',
                        'label' => 'Instagram',
                        'attributes' => ['placeholder' => 'https://instagram.com/...'],
                    ]);

                    $group->field('linkedin', [
                        'type' => 'url',
                        'label' => 'LinkedIn',
                        'attributes' => ['placeholder' => 'https://linkedin.com/...'],
                    ]);
                }, ['label' => 'Social Links', 'description' => 'Connect your social media profiles.']);
            });

            // -----------------------------------------------------------------
            // Tab: Colors
            // -----------------------------------------------------------------
            $stack->tab('colors', function ($tab) {
                $tab->label('Colors')
                    // ->icon('dashicons-art')
                    ->priority(20);

                $tab->field('primary_color', [
                    'type' => 'color',
                    'label' => 'Primary Color',
                    'default' => '#2271b1',
                    'description' => 'Main brand color for buttons, links, and accents.',
                    'attributes' => [
                        'alpha' => true,
                    ]
                ]);

                $tab->field('secondary_color', [
                    'type' => 'color',
                    'label' => 'Secondary Color',
                    'default' => '#135e96',
                ]);

                $tab->field('text_color', [
                    'type' => 'color',
                    'label' => 'Text Color',
                    'default' => '#1d2327',
                ]);

                $tab->field('background_color', [
                    'type' => 'color',
                    'label' => 'Background Color',
                    'default' => '#ffffff',
                ]);

                $tab->field('header_bg_color', [
                    'type' => 'color',
                    'label' => 'Header Background',
                    'default' => '#1d2327',
                ]);

                $tab->field('footer_bg_color', [
                    'type' => 'color',
                    'label' => 'Footer Background',
                    'default' => '#1d2327',
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Typography
            // -----------------------------------------------------------------
            $stack->tab('typography', function ($tab) {
                $tab->label('Typography')
                    // ->icon('dashicons-editor-textcolor')
                    ->priority(30)
                    ->description('Customize fonts and text styles for your theme.');

                // Typography field with full controls (includes Google Fonts)
                $tab->field('heading_typography', [
                    'type' => 'typography',
                    'label' => 'Heading Typography',
                    'description' => 'Typography settings for headings (H1-H6).',
                    'default' => [
                        'fontFamily' => '"Montserrat", sans-serif',
                        'fontSize' => 32,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '700',
                        'fontStyle' => 'normal',
                        'lineHeight' => 1.3,
                        'lineHeightUnit' => '',
                        'letterSpacing' => -0.5,
                        'letterSpacingUnit' => 'px',
                        'textTransform' => 'none',
                        'textDecoration' => 'none',
                        'color' => '#111827',
                    ],
                ]);

                $tab->field('body_typography', [
                    'type' => 'typography',
                    'label' => 'Body Typography',
                    'description' => 'Typography settings for body text and paragraphs.',
                    'default' => [
                        'fontFamily' => '"Inter", sans-serif',
                        'fontSize' => 16,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '400',
                        'fontStyle' => 'normal',
                        'lineHeight' => 1.6,
                        'lineHeightUnit' => '',
                        'letterSpacing' => 0,
                        'letterSpacingUnit' => 'px',
                        'textTransform' => 'none',
                        'textDecoration' => 'none',
                        'color' => '#374151',
                    ],
                ]);

                // Typography field with system fonts only (no Google Fonts)
                $tab->field('button_typography', [
                    'type' => 'typography',
                    'label' => 'Button Typography',
                    'description' => 'Typography settings for buttons.',
                    'default' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => 14,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '600',
                        'fontStyle' => 'normal',
                        'lineHeight' => 1.5,
                        'lineHeightUnit' => '',
                        'letterSpacing' => 0.5,
                        'letterSpacingUnit' => 'px',
                        'textTransform' => 'uppercase',
                        'textDecoration' => 'none',
                        'color' => '#FFFFFF',
                    ],
                    'attributes' => [
                        'disableGoogleFonts' => true, // Only show system fonts
                    ],
                ]);

                // Typography field with custom font list
                $tab->field('nav_typography', [
                    'type' => 'typography',
                    'label' => 'Navigation Typography',
                    'description' => 'Typography for navigation menu items.',
                    'default' => [
                        'fontFamily' => '"Poppins", sans-serif',
                        'fontSize' => 14,
                        'fontSizeUnit' => 'px',
                        'fontWeight' => '500',
                        'textTransform' => 'none',
                        'color' => '#1F2937',
                    ],
                    'attributes' => [
                        // Custom font list - only these fonts will be shown
                        'fonts' => [
                            ['value' => 'inherit', 'label' => 'Default (Inherit)', 'category' => 'system'],
                            ['value' => 'system-ui, sans-serif', 'label' => 'System UI', 'category' => 'system'],
                            ['value' => 'Inter', 'label' => 'Inter', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
                            ['value' => 'Poppins', 'label' => 'Poppins', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
                            ['value' => 'Montserrat', 'label' => 'Montserrat', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
                            ['value' => 'Roboto', 'label' => 'Roboto', 'category' => 'google', 'variants' => ['400', '500', '700']],
                        ],
                    ],
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Layout
            // -----------------------------------------------------------------
            $stack->tab('layout', function ($tab) {
                $tab->label('Layout')
                    // ->icon('dashicons-layout')
                    ->priority(40);

                $tab->field('container_width', [
                    'type' => 'range',
                    'label' => 'Container Width',
                    'default' => 1200,
                    'attributes' => [
                        'min' => 960,
                        'max' => 1600,
                        'step' => 20,
                        'unit' => 'px',
                    ],
                ]);

                // Radio Image: Sidebar Position with visual layout diagrams
                $tab->field('sidebar_position', [
                    'type' => 'radio-image',
                    'label' => 'Default Sidebar Position',
                    'description' => 'Choose the default page layout. Can be overridden per page.',
                    'default' => 'right',
                    'options' => [
                        [
                            'value' => 'left',
                            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=◀+Content',
                            'label' => 'Left Sidebar',
                            'description' => 'Left',
                        ],
                        [
                            'value' => 'right',
                            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=Content+▶',
                            'label' => 'Right Sidebar',
                            'description' => 'Right',
                        ],
                        [
                            'value' => 'none',
                            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=Full+Width',
                            'label' => 'No Sidebar',
                            'description' => 'Full Width',
                        ],
                        [
                            'value' => 'both',
                            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=◀+Content+▶',
                            'label' => 'Both Sidebars',
                            'description' => 'Both',
                        ],
                    ],
                    'attributes' => [
                        'columns' => 4,
                        'imageWidth' => '120px',
                        'imageHeight' => '80px',
                    ],
                ]);

                // Radio Image: Header Style with visual previews
                $tab->field('header_style', [
                    'type' => 'radio-image',
                    'label' => 'Header Style',
                    'description' => 'Select the header layout style.',
                    'default' => 'standard',
                    'options' => [
                        [
                            'value' => 'standard',
                            'image' => 'https://placehold.co/140x60/1d2327/ffffff?text=Logo++++Menu',
                            'label' => 'Standard Header',
                            'description' => 'Standard',
                        ],
                        [
                            'value' => 'centered',
                            'image' => 'https://placehold.co/140x60/1d2327/ffffff?text=+++Logo+++',
                            'label' => 'Centered Logo',
                            'description' => 'Centered',
                        ],
                        [
                            'value' => 'transparent',
                            'image' => 'https://placehold.co/140x60/6c757d/ffffff?text=Transparent',
                            'label' => 'Transparent Header',
                            'description' => 'Transparent',
                        ],
                        [
                            'value' => 'minimal',
                            'image' => 'https://placehold.co/140x60/f8f9fa/1d2327?text=≡+Logo',
                            'label' => 'Minimal (Hamburger)',
                            'description' => 'Minimal',
                        ],
                    ],
                    'attributes' => [
                        'columns' => 4,
                        'imageWidth' => '140px',
                        'imageHeight' => '60px',
                    ],
                ]);

                $tab->field('sticky_header', [
                    'type' => 'toggle',
                    'label' => 'Sticky Header',
                    'default' => true,
                    'description' => 'Keep header fixed at top when scrolling.',
                ]);

                // Radio Image: Blog Layout Style
                $tab->field('blog_layout', [
                    'type' => 'radio-image',
                    'label' => 'Blog Layout',
                    'description' => 'Choose how blog posts are displayed on archive pages.',
                    'default' => 'grid',
                    'options' => [
                        [
                            'value' => 'list',
                            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=═══%0A═══%0A═══',
                            'label' => 'List Layout',
                            'description' => 'List',
                        ],
                        [
                            'value' => 'grid',
                            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=▢+▢+▢%0A▢+▢+▢',
                            'label' => 'Grid Layout',
                            'description' => 'Grid',
                        ],
                        [
                            'value' => 'masonry',
                            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=▢+▯+▢%0A▯+▢+▯',
                            'label' => 'Masonry Layout',
                            'description' => 'Masonry',
                        ],
                    ],
                    'attributes' => [
                        'columns' => 3,
                        'imageWidth' => '100px',
                        'imageHeight' => '80px',
                    ],
                ]);

                $tab->field('show_breadcrumbs', [
                    'type' => 'toggle',
                    'label' => 'Show Breadcrumbs',
                    'default' => true,
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Footer
            // -----------------------------------------------------------------
            $stack->tab('footer', function ($tab) {
                $tab->label('Footer')
                    // ->icon('dashicons-arrow-down-alt')
                    ->priority(45);

                // Radio Image: Footer Layout with visual column diagrams
                $tab->field('footer_columns', [
                    'type' => 'radio-image',
                    'label' => 'Footer Widget Layout',
                    'description' => 'Choose the footer widget area layout.',
                    'default' => '4',
                    'options' => [
                        [
                            'value' => '1',
                            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━━━━━━━',
                            'label' => '1 Column (Full Width)',
                            'description' => '1 Column',
                        ],
                        [
                            'value' => '2',
                            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━━+━━━',
                            'label' => '2 Columns',
                            'description' => '2 Columns',
                        ],
                        [
                            'value' => '3',
                            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━+━━+━━',
                            'label' => '3 Columns',
                            'description' => '3 Columns',
                        ],
                        [
                            'value' => '4',
                            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━+━+━+━',
                            'label' => '4 Columns',
                            'description' => '4 Columns',
                        ],
                        [
                            'value' => '5',
                            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━+━+━+━',
                            'label' => '5 Columns',
                            'description' => '5 Columns',
                        ],
                    ],
                    'attributes' => [
                        'columns' => 4,
                        'imageWidth' => '100px',
                        'imageHeight' => '50px',
                    ],
                ]);

                // Radio Image: Footer Style
                $tab->field('footer_style', [
                    'type' => 'radio-image',
                    'label' => 'Footer Style',
                    'description' => 'Select the overall footer appearance.',
                    'default' => 'dark',
                    'options' => [
                        [
                            'value' => 'dark',
                            'image' => 'https://placehold.co/80x50/1d2327/ffffff?text=Dark',
                            'label' => 'Dark Footer',
                        ],
                        [
                            'value' => 'light',
                            'image' => 'https://placehold.co/80x50/f8f9fa/1d2327?text=Light',
                            'label' => 'Light Footer',
                        ],
                        [
                            'value' => 'colored',
                            'image' => 'https://placehold.co/80x50/2271b1/ffffff?text=Brand',
                            'label' => 'Brand Color',
                        ],
                    ],
                    'attributes' => [
                        'imageWidth' => '80px',
                        'imageHeight' => '50px',
                    ],
                ]);

                $tab->field('footer_content', [
                    'type' => 'wysiwyg',
                    'label' => 'Footer Content',
                    'description' => 'Additional content displayed in the footer.',
                    'attributes' => ['rows' => 6],
                ]);

                $tab->field('show_back_to_top', [
                    'type' => 'toggle',
                    'label' => 'Show Back to Top Button',
                    'default' => true,
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Advanced
            // -----------------------------------------------------------------
            $stack->tab('advanced', function ($tab) {
                $tab->label('Advanced')
                    // ->icon('dashicons-admin-tools')
                    ->priority(50);

                $tab->field('custom_css', [
                    'type' => 'code',
                    'label' => 'Custom CSS',
                    'description' => 'Add custom CSS styles. These will be loaded after theme styles.',
                    'attributes' => [
                        'language' => 'text/css',
                        'rows' => 15,
                    ],
                ]);

                $tab->field('header_scripts', [
                    'type' => 'code',
                    'label' => 'Header Scripts',
                    'description' => 'Scripts added before closing </head> tag (e.g., analytics).',
                    'attributes' => [
                        'language' => 'text/html',
                        'rows' => 8,
                    ],
                ]);

                $tab->field('footer_scripts', [
                    'type' => 'code',
                    'label' => 'Footer Scripts',
                    'description' => 'Scripts added before closing </body> tag.',
                    'attributes' => [
                        'language' => 'text/html',
                        'rows' => 8,
                    ],
                ]);

                $tab->field('enabled_features', [
                    'type' => 'checkbox-group',
                    'label' => 'Performance Features',
                    'default' => ['lazy_loading', 'smooth_scroll'],
                    'options' => [
                        ['value' => 'lazy_loading', 'label' => 'Lazy Load Images'],
                        ['value' => 'smooth_scroll', 'label' => 'Smooth Scrolling'],
                        ['value' => 'preload_fonts', 'label' => 'Preload Fonts'],
                        ['value' => 'minify_html', 'label' => 'Minify HTML Output'],
                    ],
                ]);
            });
        })
        ->build();

    // =========================================================================
    // EXAMPLE 3: Post Type Meta Box
    // =========================================================================
    OptStack::make('product_data')
        ->forPostType('post') // Change to 'product' or your CPT
        ->label('Product Information')
        ->define(function ($stack) {
            // Group: Pricing
            $stack->group('pricing', function ($group) {
                $group->field('regular_price', [
                    'type' => 'number',
                    'label' => 'Regular Price',
                    'attributes' => ['min' => 0, 'step' => 0.01],
                ]);

                $group->field('sale_price', [
                    'type' => 'number',
                    'label' => 'Sale Price',
                    'description' => 'Leave empty if not on sale.',
                    'attributes' => ['min' => 0, 'step' => 0.01],
                ]);

                $group->field('currency', [
                    'type' => 'select',
                    'label' => 'Currency',
                    'default' => 'USD',
                    'options' => [
                        ['value' => 'USD', 'label' => 'US Dollar ($)'],
                        ['value' => 'EUR', 'label' => 'Euro (€)'],
                        ['value' => 'GBP', 'label' => 'British Pound (£)'],
                        ['value' => 'JPY', 'label' => 'Japanese Yen (¥)'],
                    ],
                ]);
            }, ['label' => 'Pricing']);

            // Group: Inventory
            $stack->group('inventory', function ($group) {
                $group->field('sku', [
                    'type' => 'text',
                    'label' => 'SKU',
                    'description' => 'Stock Keeping Unit - unique product identifier.',
                ]);

                $group->field('manage_stock', [
                    'type' => 'toggle',
                    'label' => 'Track Inventory',
                    'default' => false,
                ]);

                $group->field('stock_quantity', [
                    'type' => 'number',
                    'label' => 'Stock Quantity',
                    'default' => 0,
                    'attributes' => ['min' => 0],
                    'conditions' => [
                        ['field' => 'inventory.manage_stock', 'operator' => '==', 'value' => true],
                    ],
                ]);

                $group->field('stock_status', [
                    'type' => 'select',
                    'label' => 'Stock Status',
                    'default' => 'instock',
                    'options' => [
                        ['value' => 'instock', 'label' => 'In Stock'],
                        ['value' => 'outofstock', 'label' => 'Out of Stock'],
                        ['value' => 'onbackorder', 'label' => 'On Backorder'],
                    ],
                ]);
            }, ['label' => 'Inventory']);

            // Group: Gallery (repeatable)
            $stack->group('gallery', function ($group) {
                $group->repeatable(0, 10);

                $group->field('image', [
                    'type' => 'media',
                    'label' => 'Image',
                    'attributes' => [
                        // 'allowedTypes' => ['image'],
                        // 'buttonText' => 'Add Image',
                        'multiple' => true,      // Enable multi-select
                        'maxFiles' => 10,        // Optional: limit to 10 images
                        'allowedTypes' => ['image'],
                        'previewSize' => 'thumbnail',
                    ],
                ]);

                $group->field('caption', [
                    'type' => 'text',
                    'label' => 'Caption',
                ]);
            }, ['label' => 'Product Gallery', 'description' => 'Add up to 10 product images.']);

            // Group: Features (repeatable)
            $stack->group('features', function ($group) {
                $group->repeatable(0, 20);

                $group->field('icon', [
                    'type' => 'text',
                    'label' => 'Icon',
                    'description' => 'Dashicon class (e.g., dashicons-yes)',
                    'attributes' => ['placeholder' => 'dashicons-yes'],
                ]);

                $group->field('title', [
                    'type' => 'text',
                    'label' => 'Feature Title',
                ]);

                $group->field('description', [
                    'type' => 'textarea',
                    'label' => 'Description',
                    'attributes' => ['rows' => 2],
                ]);
            }, ['label' => 'Product Features']);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 4: Taxonomy Term Meta
    // =========================================================================
    OptStack::make('category_settings')
        ->forTaxonomy('category')
        ->label('Category Settings')
        ->define(function ($stack) {
            $stack->field('featured_image', [
                'type' => 'media',
                'label' => 'Category Image',
                'description' => 'Featured image for this category.',
                'attributes' => [
                    'allowedTypes' => ['image'],
                    'buttonText' => 'Select Image',
                ],
            ]);

            $stack->field('icon', [
                'type' => 'text',
                'label' => 'Icon Class',
                'description' => 'Dashicons class (e.g., dashicons-category).',
                'attributes' => ['placeholder' => 'dashicons-category'],
            ]);

            $stack->field('color', [
                'type' => 'color',
                'label' => 'Category Color',
                'default' => '#2271b1',
                'description' => 'Used for category badges and highlights.',
            ]);

            $stack->field('layout', [
                'type' => 'radio',
                'label' => 'Archive Layout',
                'default' => 'grid',
                'options' => [
                    ['value' => 'grid', 'label' => 'Grid'],
                    ['value' => 'list', 'label' => 'List'],
                    ['value' => 'masonry', 'label' => 'Masonry'],
                ],
            ]);

            $stack->field('posts_per_page', [
                'type' => 'number',
                'label' => 'Posts Per Page',
                'default' => 12,
                'description' => 'Override default posts per page for this category.',
                'attributes' => ['min' => 1, 'max' => 100],
            ]);

            $stack->field('featured', [
                'type' => 'toggle',
                'label' => 'Featured Category',
                'default' => false,
                'description' => 'Show this category prominently on the homepage.',
            ]);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 5: User Profile Meta
    // =========================================================================
    OptStack::make('user_profile')
        ->forUser()
        ->label('Extended Profile')
        ->define(function ($stack) {
            // Group: Professional Info
            $stack->group('professional', function ($group) {
                $group->field('job_title', [
                    'type' => 'text',
                    'label' => 'Job Title',
                    'attributes' => ['placeholder' => 'e.g., Senior Developer'],
                ]);

                $group->field('company', [
                    'type' => 'text',
                    'label' => 'Company',
                ]);

                $group->field('bio', [
                    'type' => 'wysiwyg',
                    'label' => 'Biography',
                    'description' => 'A brief bio about yourself.',
                    'attributes' => ['rows' => 6, 'simple' => true],
                ]);

                $group->field('avatar', [
                    'type' => 'media',
                    'label' => 'Custom Avatar',
                    'description' => 'Override Gravatar with a custom image.',
                    'attributes' => [
                        'allowedTypes' => ['image'],
                        'buttonText' => 'Select Avatar',
                    ],
                ]);
            }, ['label' => 'Professional Information']);

            // Group: Social Profiles
            $stack->group('social', function ($group) {
                $group->field('twitter', [
                    'type' => 'url',
                    'label' => 'Twitter/X',
                    'attributes' => ['placeholder' => 'https://x.com/username'],
                ]);

                $group->field('linkedin', [
                    'type' => 'url',
                    'label' => 'LinkedIn',
                    'attributes' => ['placeholder' => 'https://linkedin.com/in/username'],
                ]);

                $group->field('github', [
                    'type' => 'url',
                    'label' => 'GitHub',
                    'attributes' => ['placeholder' => 'https://github.com/username'],
                ]);

                $group->field('website', [
                    'type' => 'url',
                    'label' => 'Personal Website',
                ]);
            }, ['label' => 'Social Profiles']);

            // Group: Preferences
            $stack->group('preferences', function ($group) {
                $group->field('email_notifications', [
                    'type' => 'checkbox-group',
                    'label' => 'Email Notifications',
                    'default' => ['comments', 'mentions'],
                    'options' => [
                        ['value' => 'comments', 'label' => 'New comments on my posts'],
                        ['value' => 'mentions', 'label' => 'When someone mentions me'],
                        ['value' => 'newsletter', 'label' => 'Weekly newsletter'],
                        ['value' => 'updates', 'label' => 'Product updates'],
                    ],
                ]);

                $group->field('timezone', [
                    'type' => 'select',
                    'label' => 'Timezone',
                    'default' => 'UTC',
                    'options' => [
                        ['value' => 'UTC', 'label' => 'UTC'],
                        ['value' => 'America/New_York', 'label' => 'Eastern Time'],
                        ['value' => 'America/Chicago', 'label' => 'Central Time'],
                        ['value' => 'America/Denver', 'label' => 'Mountain Time'],
                        ['value' => 'America/Los_Angeles', 'label' => 'Pacific Time'],
                        ['value' => 'Europe/London', 'label' => 'London'],
                        ['value' => 'Europe/Paris', 'label' => 'Paris'],
                        ['value' => 'Asia/Tokyo', 'label' => 'Tokyo'],
                    ],
                ]);

                $group->field('dark_mode', [
                    'type' => 'toggle',
                    'label' => 'Dark Mode',
                    'default' => false,
                    'description' => 'Enable dark mode for admin interface.',
                ]);
            }, ['label' => 'Preferences']);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 6: Group Layouts Demo
    // =========================================================================
    // This example demonstrates different group layout options:
    // - Inline layout (2-col: label | fields) - default
    // - Box layout (card style with header)
    // - Collapsible groups
    // - Nested groups
    // =========================================================================
    OptStack::make('group_layouts_demo')
        ->forOptions()
        ->menuParent('optstack')
        ->label('Group Layouts Demo')
        ->description('Demonstrates different group layout options')
        ->define(function ($stack) {
            // -----------------------------------------------------------------
            // Tab: Inline Groups (Default 2-Column Layout)
            // -----------------------------------------------------------------
            $stack->tab('inline_groups', function ($tab) {
                $tab->label('Inline Groups')
                    ->priority(10)
                    ->description('Groups with 2-column layout: Label | Fields');

                // Inline Group (default) - looks like a field row
                // Label on left, fields stacked on right
                $tab->group('contact_info', function ($group) {
                    $group->field('name', [
                        'type' => 'text',
                        'label' => 'Full Name',
                        'attributes' => ['placeholder' => 'John Doe'],
                    ]);

                    $group->field('email', [
                        'type' => 'email',
                        'label' => 'Email Address',
                        'attributes' => ['placeholder' => 'john@example.com'],
                    ]);

                    $group->field('phone', [
                        'type' => 'text',
                        'label' => 'Phone Number',
                        'attributes' => ['placeholder' => '+1 (555) 123-4567'],
                    ]);
                }, [
                    'label' => 'Contact Information',
                    'description' => 'Basic contact details displayed in 2-column layout.',
                    // 'layout' => 'inline' // This is the default, no need to specify
                ]);

                // Another inline group
                $tab->group('address', function ($group) {
                    $group->field('street', [
                        'type' => 'text',
                        'label' => 'Street Address',
                    ]);

                    $group->field('city', [
                        'type' => 'text',
                        'label' => 'City',
                    ]);

                    $group->field('state', [
                        'type' => 'text',
                        'label' => 'State/Province',
                    ]);

                    $group->field('zip', [
                        'type' => 'text',
                        'label' => 'ZIP/Postal Code',
                    ]);

                    $group->field('country', [
                        'type' => 'select',
                        'label' => 'Country',
                        'default' => 'US',
                        'options' => [
                            ['value' => 'US', 'label' => 'United States'],
                            ['value' => 'CA', 'label' => 'Canada'],
                            ['value' => 'UK', 'label' => 'United Kingdom'],
                            ['value' => 'AU', 'label' => 'Australia'],
                            ['value' => 'DE', 'label' => 'Germany'],
                            ['value' => 'FR', 'label' => 'France'],
                        ],
                    ]);
                }, [
                    'label' => 'Mailing Address',
                    'description' => 'Physical address for shipping or correspondence.',
                ]);

                // Inline group with collapsible option
                $tab->group('billing', function ($group) {
                    $group->field('card_holder', [
                        'type' => 'text',
                        'label' => 'Card Holder Name',
                    ]);

                    $group->field('card_type', [
                        'type' => 'select',
                        'label' => 'Card Type',
                        'options' => [
                            ['value' => 'visa', 'label' => 'Visa'],
                            ['value' => 'mastercard', 'label' => 'MasterCard'],
                            ['value' => 'amex', 'label' => 'American Express'],
                        ],
                    ]);

                    $group->field('notes', [
                        'type' => 'textarea',
                        'label' => 'Billing Notes',
                        'attributes' => ['rows' => 3],
                    ]);
                }, [
                    'label' => 'Billing Information',
                    'description' => 'Payment and billing details. Click chevron to collapse.',
                    'collapsible' => true,
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Box Groups (Card Style Layout)
            // -----------------------------------------------------------------
            $stack->tab('box_groups', function ($tab) {
                $tab->label('Box Groups')
                    ->priority(20)
                    ->description('Groups with card-style box layout');

                // Box layout group - card style with header
                $tab->group('company_info', function ($group) {
                    $group->field('company_name', [
                        'type' => 'text',
                        'label' => 'Company Name',
                        'attributes' => ['placeholder' => 'Acme Inc.'],
                    ]);

                    $group->field('company_logo', [
                        'type' => 'media',
                        'label' => 'Company Logo',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                            'buttonText' => 'Select Logo',
                        ],
                    ]);

                    $group->field('industry', [
                        'type' => 'select',
                        'label' => 'Industry',
                        'options' => [
                            ['value' => 'tech', 'label' => 'Technology'],
                            ['value' => 'finance', 'label' => 'Finance'],
                            ['value' => 'healthcare', 'label' => 'Healthcare'],
                            ['value' => 'retail', 'label' => 'Retail'],
                            ['value' => 'other', 'label' => 'Other'],
                        ],
                    ]);

                    $group->field('description', [
                        'type' => 'wysiwyg',
                        'label' => 'Company Description',
                        'attributes' => ['rows' => 4, 'simple' => true],
                    ]);
                }, [
                    'label' => 'Company Information',
                    'description' => 'Basic details about your company.',
                    'layout' => 'box', // Use box/card style layout
                ]);

                // Box layout with collapsible
                $tab->group('advanced_settings', function ($group) {
                    $group->field('api_key', [
                        'type' => 'text',
                        'label' => 'API Key',
                        'description' => 'Your secret API key for integrations.',
                    ]);

                    $group->field('webhook_url', [
                        'type' => 'url',
                        'label' => 'Webhook URL',
                        'attributes' => ['placeholder' => 'https://example.com/webhook'],
                    ]);

                    $group->field('debug_mode', [
                        'type' => 'toggle',
                        'label' => 'Debug Mode',
                        'default' => false,
                        'description' => 'Enable verbose logging for troubleshooting.',
                    ]);

                    $group->field('custom_css', [
                        'type' => 'code',
                        'label' => 'Custom CSS',
                        'attributes' => [
                            'language' => 'text/css',
                            'rows' => 8,
                        ],
                    ]);
                }, [
                    'label' => 'Advanced Settings',
                    'description' => 'Technical configuration options.',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Nested Groups
            // -----------------------------------------------------------------
            $stack->tab('nested_groups', function ($tab) {
                $tab->label('Nested Groups')
                    ->priority(30)
                    ->description('Groups containing other groups');

                // Parent group with nested groups inside
                $tab->group('organization', function ($group) {
                    $group->field('org_name', [
                        'type' => 'text',
                        'label' => 'Organization Name',
                    ]);

                    // Nested group: Headquarters
                    $group->group('headquarters', function ($nested) {
                        $nested->field('hq_address', [
                            'type' => 'text',
                            'label' => 'Address',
                        ]);

                        $nested->field('hq_city', [
                            'type' => 'text',
                            'label' => 'City',
                        ]);

                        $nested->field('hq_country', [
                            'type' => 'select',
                            'label' => 'Country',
                            'options' => [
                                ['value' => 'US', 'label' => 'United States'],
                                ['value' => 'UK', 'label' => 'United Kingdom'],
                                ['value' => 'DE', 'label' => 'Germany'],
                            ],
                        ]);
                    }, [
                        'label' => 'Headquarters',
                        'description' => 'Main office location.',
                    ]);

                    // Nested group: Primary Contact
                    $group->group('primary_contact', function ($nested) {
                        $nested->field('contact_name', [
                            'type' => 'text',
                            'label' => 'Contact Name',
                        ]);

                        $nested->field('contact_email', [
                            'type' => 'email',
                            'label' => 'Email',
                        ]);

                        $nested->field('contact_phone', [
                            'type' => 'text',
                            'label' => 'Phone',
                        ]);
                    }, [
                        'label' => 'Primary Contact',
                        'description' => 'Main point of contact.',
                    ]);
                }, [
                    'label' => 'Organization Details',
                    'description' => 'Company structure with nested information groups.',
                    'layout' => 'box',
                ]);
            });

            // -----------------------------------------------------------------
            // Tab: Repeatable Groups
            // -----------------------------------------------------------------
            $stack->tab('repeatable_groups', function ($tab) {
                $tab->label('Repeatable Groups')
                    ->priority(40)
                    ->description('Groups that can have multiple entries');

                // Inline repeatable group
                $tab->group('team_members', function ($group) {
                    $group->repeatable(0, 10); // Min 0, Max 10 items

                    $group->field('member_name', [
                        'type' => 'text',
                        'label' => 'Name',
                    ]);

                    $group->field('member_role', [
                        'type' => 'text',
                        'label' => 'Role/Title',
                    ]);

                    $group->field('member_email', [
                        'type' => 'email',
                        'label' => 'Email',
                    ]);

                    $group->field('member_photo', [
                        'type' => 'media',
                        'label' => 'Photo',
                        'attributes' => [
                            'allowedTypes' => ['image'],
                            'buttonText' => 'Select Photo',
                        ],
                    ]);
                }, [
                    'label' => 'Team Members',
                    'description' => 'Add team members (up to 10). Displays in inline layout.',
                    // 'layout' => 'inline' // default
                ]);

                // Box layout repeatable group
                $tab->group('locations', function ($group) {
                    $group->repeatable(1, 5); // Min 1, Max 5 items

                    $group->field('location_name', [
                        'type' => 'text',
                        'label' => 'Location Name',
                        'attributes' => ['placeholder' => 'e.g., Main Office'],
                    ]);

                    $group->field('location_address', [
                        'type' => 'textarea',
                        'label' => 'Address',
                        'attributes' => ['rows' => 2],
                    ]);

                    $group->field('location_phone', [
                        'type' => 'text',
                        'label' => 'Phone',
                    ]);

                    $group->field('location_hours', [
                        'type' => 'text',
                        'label' => 'Business Hours',
                        'attributes' => ['placeholder' => 'Mon-Fri 9am-5pm'],
                    ]);

                    $group->field('location_map', [
                        'type' => 'url',
                        'label' => 'Google Maps URL',
                    ]);
                }, [
                    'label' => 'Office Locations',
                    'description' => 'Add office locations (1-5 required). Uses box layout.',
                    'layout' => 'box',
                    'collapsible' => true,
                ]);
            });
        })
        ->build();

    // =========================================================================
    // EXAMPLE 7: Searchable Fields Demo
    // =========================================================================
    // This example demonstrates searchable/indexed fields.
    // Searchable fields are stored as separate meta keys for efficient WP_Query.
    //
    // Meta key format: _optstack_idx_{context}_{field_path}
    // Example: _optstack_idx_post_price, _optstack_idx_post_seo_title
    // =========================================================================
    $__stack = OptStack::make('searchable_demo')
        ->forPostType('post') // Works with any post type
        ->label('Searchable Fields Demo')
        ->description('Demonstrates searchable/indexed fields for efficient querying')
        ->define(function ($stack) {
            // Simple searchable field (stored as separate meta)
            $stack->field('price', [
                'type' => 'number',
                'label' => 'Price',
                'description' => 'Product price (searchable - can be queried with WP_Query)',
                'searchable' => true, // Enable indexing
                'attributes' => ['min' => 0, 'step' => 0.01],
            ]);

            $stack->field('sku', [
                'type' => 'text',
                'label' => 'SKU',
                'description' => 'Stock keeping unit (searchable)',
                'searchable' => true,
            ]);

            $stack->field('status', [
                'type' => 'select',
                'label' => 'Status',
                'description' => 'Product status (searchable)',
                'searchable' => true,
                'default' => 'draft',
                'options' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ],
            ]);

            $stack->field('featured', [
                'type' => 'toggle',
                'label' => 'Featured',
                'description' => 'Mark as featured (searchable)',
                'searchable' => true,
                'default' => false,
            ]);

            // Searchable fields inside groups
            $stack->group('seo', function ($group) {
                $group->field('title', [
                    'type' => 'text',
                    'label' => 'SEO Title',
                    'description' => 'Searchable field inside a group',
                    'searchable' => true, // Indexed as: _optstack_idx_post_seo_title
                ]);

                $group->field('keywords', [
                    'type' => 'text',
                    'label' => 'Keywords',
                    'description' => 'Searchable keywords',
                    'searchable' => true, // Indexed as: _optstack_idx_post_seo_keywords
                ]);

                // Non-searchable field in the same group
                $group->field('description', [
                    'type' => 'textarea',
                    'label' => 'Meta Description',
                    'description' => 'This field is NOT searchable (stored in main data only)',
                    // 'searchable' => false (default)
                ]);
            }, ['label' => 'SEO Settings']);

            // Nested group with searchable fields
            $stack->group('inventory', function ($group) {
                $group->field('quantity', [
                    'type' => 'number',
                    'label' => 'Stock Quantity',
                    'description' => 'Searchable nested field',
                    'searchable' => true, // Indexed as: _optstack_idx_post_inventory_quantity
                    'attributes' => ['min' => 0],
                ]);

                $group->field('warehouse', [
                    'type' => 'select',
                    'label' => 'Warehouse',
                    'searchable' => true, // Indexed as: _optstack_idx_post_inventory_warehouse
                    'options' => [
                        ['value' => 'main', 'label' => 'Main Warehouse'],
                        ['value' => 'secondary', 'label' => 'Secondary'],
                        ['value' => 'dropship', 'label' => 'Dropship'],
                    ],
                ]);
            }, ['label' => 'Inventory']);

            // Note: Repeatable groups CANNOT have searchable fields
            // because their values are arrays, not scalar values
            $stack->group('variants', function ($group) {
                $group->repeatable(0, 10);

                $group->field('name', [
                    'type' => 'text',
                    'label' => 'Variant Name',
                    // 'searchable' => true, // Would NOT work - repeater fields excluded
                ]);

                $group->field('price', [
                    'type' => 'number',
                    'label' => 'Variant Price',
                    // Searchable is ignored for fields in repeatable groups
                ]);
            }, ['label' => 'Product Variants', 'description' => 'Variants cannot have searchable fields']);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 8: Radio Image Field Demo
    // =========================================================================
    // This example demonstrates the Radio Image field type.
    // Radio Image works like a Radio field but displays image thumbnails
    // instead of text labels - perfect for theme/layout selection.
    // =========================================================================
    OptStack::make('radio_image_demo')
        ->forOptions()
        ->menuParent('optstack')
        ->label('Radio Image Demo')
        ->description('Demonstrates the Radio Image selection field')
        ->define(function ($stack) {
            // Basic Radio Image field
            // Options use 'label' as the image URL for backwards compatibility
            $stack->field('theme_style', [
                'type' => 'radio-image',
                'label' => 'Theme Style',
                'description' => 'Select a color theme for your site. Uses label as image URL.',
                'default' => 'light',
                'options' => [
                    [
                        'value' => 'light',
                        'label' => 'https://placehold.co/100x80/ffffff/333333?text=Light',
                        'description' => 'Light Theme',
                    ],
                    [
                        'value' => 'dark',
                        'label' => 'https://placehold.co/100x80/1a1a2e/ffffff?text=Dark',
                        'description' => 'Dark Theme',
                    ],
                    [
                        'value' => 'blue',
                        'label' => 'https://placehold.co/100x80/0066cc/ffffff?text=Blue',
                        'description' => 'Blue Theme',
                    ],
                    [
                        'value' => 'green',
                        'label' => 'https://placehold.co/100x80/28a745/ffffff?text=Green',
                        'description' => 'Green Theme',
                    ],
                ],
            ]);

            // Radio Image with explicit 'image' property and tooltips
            $stack->field('layout_preset', [
                'type' => 'radio-image',
                'label' => 'Layout Preset',
                'description' => 'Choose a page layout. Uses image property for URL and label for tooltip.',
                'default' => 'sidebar-right',
                'options' => [
                    [
                        'value' => 'full-width',
                        'label' => 'Full Width Layout',
                        'image' => 'https://placehold.co/120x90/e9ecef/495057?text=Full+Width',
                        'tooltip' => 'Content spans the entire width',
                    ],
                    [
                        'value' => 'sidebar-left',
                        'label' => 'Sidebar Left',
                        'image' => 'https://placehold.co/120x90/e9ecef/495057?text=◀+Content',
                        'tooltip' => 'Sidebar on the left side',
                    ],
                    [
                        'value' => 'sidebar-right',
                        'label' => 'Sidebar Right',
                        'image' => 'https://placehold.co/120x90/e9ecef/495057?text=Content+▶',
                        'tooltip' => 'Sidebar on the right side',
                    ],
                    [
                        'value' => 'dual-sidebar',
                        'label' => 'Dual Sidebar',
                        'image' => 'https://placehold.co/120x90/e9ecef/495057?text=◀+Content+▶',
                        'tooltip' => 'Sidebars on both sides',
                    ],
                ],
                'attributes' => [
                    'columns' => 4, // Fixed 4 columns
                    'imageWidth' => '120px',
                    'imageHeight' => '90px',
                ],
            ]);

            // Large images with custom dimensions
            $stack->field('hero_style', [
                'type' => 'radio-image',
                'label' => 'Hero Section Style',
                'description' => 'Select a hero section design. Larger preview images.',
                'default' => 'centered',
                'options' => [
                    [
                        'value' => 'centered',
                        'label' => 'Centered Hero',
                        'image' => 'https://placehold.co/200x120/6c5ce7/ffffff?text=Centered',
                    ],
                    [
                        'value' => 'split',
                        'label' => 'Split Layout',
                        'image' => 'https://placehold.co/200x120/00b894/ffffff?text=Split',
                    ],
                    [
                        'value' => 'video-bg',
                        'label' => 'Video Background',
                        'image' => 'https://placehold.co/200x120/e17055/ffffff?text=Video+BG',
                    ],
                ],
                'attributes' => [
                    'columns' => 3,
                    'imageWidth' => '200px',
                    'imageHeight' => '120px',
                    'objectFit' => 'cover',
                ],
            ]);

            // Compact icon/button selection
            $stack->field('icon_pack', [
                'type' => 'radio-image',
                'label' => 'Icon Style',
                'description' => 'Choose an icon style. Smaller square images work well for icons.',
                'default' => 'outlined',
                'options' => [
                    [
                        'value' => 'filled',
                        'label' => 'Filled Icons',
                        'image' => 'https://placehold.co/60x60/343a40/ffffff?text=●',
                    ],
                    [
                        'value' => 'outlined',
                        'label' => 'Outlined Icons',
                        'image' => 'https://placehold.co/60x60/ffffff/343a40?text=○',
                    ],
                    [
                        'value' => 'rounded',
                        'label' => 'Rounded Icons',
                        'image' => 'https://placehold.co/60x60/17a2b8/ffffff?text=◉',
                    ],
                    [
                        'value' => 'duotone',
                        'label' => 'Duotone Icons',
                        'image' => 'https://placehold.co/60x60/6f42c1/e9d5ff?text=◐',
                    ],
                ],
                'attributes' => [
                    'imageWidth' => '60px',
                    'imageHeight' => '60px',
                ],
            ]);
        })
        ->build();

    // =========================================================================
    // EXAMPLE 9: Deferred Group Demo
    // =========================================================================
    // This example demonstrates Deferred Groups.
    // Deferred groups show a trigger button instead of inline fields.
    // Clicking the button opens a modal with the group's fields.
    // This reduces UI clutter for complex/advanced settings.
    //
    // Key points:
    // - Data structure is identical to normal groups
    // - Validation works the same way
    // - Just a rendering strategy, not a data model change
    // =========================================================================
    OptStack::make('deferred_demo')
        ->forOptions()
        ->menuParent('optstack')
        ->label('Deferred Group Demo')
        ->description('Demonstrates deferred groups that open in modals')
        ->define(function ($stack) {
            // A simple field for context
            $stack->field('product_name', [
                'type' => 'text',
                'label' => 'Product Name',
                'description' => 'The name of your product.',
            ]);

            $stack->field('product_price', [
                'type' => 'number',
                'label' => 'Price',
                'description' => 'Base price for the product.',
                'attributes' => [
                    'min' => 0,
                    'step' => 0.01,
                    'prefix' => '$',
                ],
            ]);

            // Deferred group - opens in modal
            // Perfect for advanced settings that most users won't need
            $stack->group('pricing_options', function ($group) {
                $group->field('regular_price', [
                    'type' => 'number',
                    'label' => 'Regular Price',
                    'description' => 'The standard price without discounts.',
                    'attributes' => ['min' => 0, 'step' => 0.01, 'prefix' => '$'],
                ]);

                $group->field('sale_price', [
                    'type' => 'number',
                    'label' => 'Sale Price',
                    'description' => 'Discounted price (optional).',
                    'attributes' => ['min' => 0, 'step' => 0.01, 'prefix' => '$'],
                ]);

                $group->field('currency', [
                    'type' => 'select',
                    'label' => 'Currency',
                    'default' => 'USD',
                    'options' => [
                        ['value' => 'USD', 'label' => 'US Dollar ($)'],
                        ['value' => 'EUR', 'label' => 'Euro (€)'],
                        ['value' => 'GBP', 'label' => 'British Pound (£)'],
                        ['value' => 'JPY', 'label' => 'Japanese Yen (¥)'],
                    ],
                ]);

                $group->field('tax_class', [
                    'type' => 'select',
                    'label' => 'Tax Class',
                    'default' => 'standard',
                    'options' => [
                        ['value' => 'standard', 'label' => 'Standard Rate'],
                        ['value' => 'reduced', 'label' => 'Reduced Rate'],
                        ['value' => 'zero', 'label' => 'Zero Rate'],
                        ['value' => 'exempt', 'label' => 'Tax Exempt'],
                    ],
                ]);

                $group->field('price_includes_tax', [
                    'type' => 'toggle',
                    'label' => 'Price Includes Tax',
                    'default' => false,
                ]);
            }, [
                'label' => 'Pricing Options',
                'description' => 'Configure detailed pricing settings including currency, tax, and discounts.',
                'deferred' => true, // This makes it a deferred group!
                'ui' => [
                    'triggerLabel' => 'Configure Pricing',
                    'render' => 'modal', // Opens in modal (default)
                ],
            ]);

            // Another deferred group for SEO
            $stack->group('seo_settings', function ($group) {
                $group->field('meta_title', [
                    'type' => 'text',
                    'label' => 'Meta Title',
                    'description' => 'Title for search engines (60 chars max recommended).',
                    'attributes' => ['maxlength' => 70],
                ]);

                $group->field('meta_description', [
                    'type' => 'textarea',
                    'label' => 'Meta Description',
                    'description' => 'Description for search engines (160 chars max recommended).',
                    'attributes' => ['rows' => 3, 'maxlength' => 170],
                ]);

                $group->field('focus_keyword', [
                    'type' => 'text',
                    'label' => 'Focus Keyword',
                    'description' => 'Primary keyword for this content.',
                ]);

                $group->field('canonical_url', [
                    'type' => 'url',
                    'label' => 'Canonical URL',
                    'description' => 'The canonical URL for this page (leave empty to use default).',
                ]);

                $group->field('noindex', [
                    'type' => 'toggle',
                    'label' => 'No Index',
                    'description' => 'Prevent search engines from indexing this page.',
                    'default' => false,
                ]);

                $group->field('nofollow', [
                    'type' => 'toggle',
                    'label' => 'No Follow',
                    'description' => 'Prevent search engines from following links on this page.',
                    'default' => false,
                ]);
            }, [
                'label' => 'SEO Settings',
                'description' => 'Search engine optimization settings.',
                'deferred' => true,
                'ui' => [
                    'triggerLabel' => 'Configure SEO',
                    'render' => 'modal',
                ],
            ]);

            // Deferred group with drawer render mode
            $stack->group('advanced_options', function ($group) {
                $group->field('custom_css', [
                    'type' => 'code',
                    'label' => 'Custom CSS',
                    'description' => 'Add custom CSS styles.',
                    'attributes' => ['language' => 'text/css', 'rows' => 10],
                ]);

                $group->field('custom_js', [
                    'type' => 'code',
                    'label' => 'Custom JavaScript',
                    'description' => 'Add custom JavaScript code.',
                    'attributes' => ['language' => 'application/javascript', 'rows' => 10],
                ]);

                $group->field('tracking_code', [
                    'type' => 'textarea',
                    'label' => 'Tracking Code',
                    'description' => 'Analytics or tracking code (added to header).',
                    'attributes' => ['rows' => 4, 'placeholder' => '<!-- Paste your tracking code here -->'],
                ]);

                $group->field('schema_markup', [
                    'type' => 'textarea',
                    'label' => 'Schema Markup',
                    'description' => 'JSON-LD structured data markup.',
                    'attributes' => ['rows' => 6, 'placeholder' => '{"@context": "https://schema.org"...}'],
                ]);
            }, [
                'label' => 'Advanced Options',
                'description' => 'Custom code and advanced configuration (for developers).',
                'deferred' => true,
                'ui' => [
                    'triggerLabel' => 'Advanced Settings',
                    'render' => 'drawer', // Opens as drawer from right side
                ],
            ]);

            // Regular (non-deferred) group for comparison
            $stack->group('basic_info', function ($group) {
                $group->field('sku', [
                    'type' => 'text',
                    'label' => 'SKU',
                    'description' => 'Stock keeping unit.',
                ]);

                $group->field('barcode', [
                    'type' => 'text',
                    'label' => 'Barcode',
                    'description' => 'UPC, EAN, or ISBN.',
                ]);
            }, [
                'label' => 'Basic Info',
                'description' => 'This is a regular inline group (not deferred).',
                // No 'deferred' key = renders inline as usual
            ]);
        })
        ->build();

    // $bootstrap = \OptStack\WordPress\Bootstrap::getInstance();
    // $manager = $bootstrap->getIndexedMetaManager();
    // $keys = $manager->getIndexedMetaKeys($__stack);
    // echo '<pre>';
    // print_r($keys);
    // echo get_post_meta(1, '_optstack_idx_post_price', true);
    // echo '</pre>';
});

// =============================================================================
// HELPER FUNCTIONS FOR ACCESSING DATA
// =============================================================================

/**
 * Get all site settings.
 *
 * @return array
 */
function optstack_get_site_settings(): array
{
    return get_option('site_settings', []);
}

/**
 * Get a specific site setting.
 *
 * @param string $key     Setting key (supports dot notation: 'social_links.twitter')
 * @param mixed  $default Default value if not found
 * @return mixed
 */
function optstack_get_setting(string $key, mixed $default = null): mixed
{
    $settings = optstack_get_site_settings();
    
    // Support dot notation
    $keys = explode('.', $key);
    $value = $settings;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Get all theme options.
 *
 * @return array
 */
function optstack_get_theme_options(): array
{
    return get_option('theme_options', []);
}

/**
 * Get a specific theme option.
 *
 * @param string $key     Option key
 * @param mixed  $default Default value
 * @return mixed
 */
function optstack_get_theme_option(string $key, mixed $default = null): mixed
{
    $options = optstack_get_theme_options();
    
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
 * Get product data for a post.
 *
 * @param int $post_id Post ID
 * @return array
 */
function optstack_get_product_data(int $post_id): array
{
    return get_post_meta($post_id, 'product_data', true) ?: [];
}
// echo '<pre>';
// print_r(optstack_get_product_data(1));
// echo '</pre>';

/**
 * Get category settings for a term.
 *
 * @param int $term_id Term ID
 * @return array
 */
function optstack_get_category_settings(int $term_id): array
{
    return get_term_meta($term_id, 'category_settings', true) ?: [];
}

/**
 * Get user profile data.
 *
 * @param int|null $user_id User ID (defaults to current user)
 * @return array
 */
function optstack_get_user_profile(?int $user_id = null): array
{
    $user_id = $user_id ?? get_current_user_id();
    return get_user_meta($user_id, 'user_profile', true) ?: [];
}

/**
 * Generate CSS string from typography value.
 *
 * @param array $typography Typography settings array
 * @return string CSS properties
 */
function optstack_typography_css(array $typography): string
{
    $css = [];
    
    if (!empty($typography['fontFamily'])) {
        $css[] = 'font-family: ' . $typography['fontFamily'];
    }
    
    if (isset($typography['fontSize'])) {
        $unit = $typography['fontSizeUnit'] ?? 'px';
        $css[] = 'font-size: ' . $typography['fontSize'] . $unit;
    }
    
    if (!empty($typography['fontWeight'])) {
        $css[] = 'font-weight: ' . $typography['fontWeight'];
    }
    
    if (!empty($typography['fontStyle']) && $typography['fontStyle'] !== 'normal') {
        $css[] = 'font-style: ' . $typography['fontStyle'];
    }
    
    if (isset($typography['lineHeight'])) {
        $unit = $typography['lineHeightUnit'] ?? '';
        $css[] = 'line-height: ' . $typography['lineHeight'] . $unit;
    }
    
    if (isset($typography['letterSpacing']) && $typography['letterSpacing'] != 0) {
        $unit = $typography['letterSpacingUnit'] ?? 'px';
        $css[] = 'letter-spacing: ' . $typography['letterSpacing'] . $unit;
    }
    
    if (!empty($typography['textTransform']) && $typography['textTransform'] !== 'none') {
        $css[] = 'text-transform: ' . $typography['textTransform'];
    }
    
    if (!empty($typography['textDecoration']) && $typography['textDecoration'] !== 'none') {
        $css[] = 'text-decoration: ' . $typography['textDecoration'];
    }
    
    if (!empty($typography['color'])) {
        $css[] = 'color: ' . $typography['color'];
    }
    
    return implode('; ', $css);
}

/**
 * Get Google Font URL for typography settings.
 * Use this to enqueue Google Fonts on the frontend.
 *
 * @param array $typographies Array of typography settings
 * @return string|null Google Fonts URL or null if no Google fonts
 */
function optstack_get_google_fonts_url(array $typographies): ?string
{
    $fonts = [];
    
    foreach ($typographies as $typography) {
        if (empty($typography['fontFamily'])) continue;
        
        // Check if it's a Google Font (contains quotes and sans-serif/serif)
        if (preg_match('/"([^"]+)"/', $typography['fontFamily'], $matches)) {
            $fontName = $matches[1];
            $weight = $typography['fontWeight'] ?? '400';
            
            if (!isset($fonts[$fontName])) {
                $fonts[$fontName] = [];
            }
            
            if (!in_array($weight, $fonts[$fontName])) {
                $fonts[$fontName][] = $weight;
            }
        }
    }
    
    if (empty($fonts)) {
        return null;
    }
    
    $families = [];
    foreach ($fonts as $name => $weights) {
        sort($weights);
        $families[] = str_replace(' ', '+', $name) . ':wght@' . implode(';', $weights);
    }
    
    return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
}

// =============================================================================
// USAGE EXAMPLES IN TEMPLATES
// =============================================================================

/*
// Get brand color
$brand_color = optstack_get_setting('brand_color', '#2271b1');

// Get social links
$twitter = optstack_get_setting('social_links.twitter');
$facebook = optstack_get_setting('social_links.facebook');

// Get theme options
$primary_color = optstack_get_theme_option('primary_color', '#2271b1');
$container_width = optstack_get_theme_option('container_width', 1200);
$custom_css = optstack_get_theme_option('custom_css');

// Check if feature is enabled
$features = optstack_get_theme_option('enabled_features', []);
if (in_array('lazy_loading', $features)) {
    // Enable lazy loading
}

// Get product pricing
$product = optstack_get_product_data(get_the_ID());
$regular_price = $product['pricing']['regular_price'] ?? 0;
$sale_price = $product['pricing']['sale_price'] ?? null;
$currency = $product['pricing']['currency'] ?? 'USD';

$price = $sale_price ?: $regular_price;
echo sprintf('%s %.2f', $currency, $price);

// Get product features
$features = $product['features'] ?? [];
foreach ($features as $feature) {
    printf(
        '<div class="feature"><span class="%s"></span><h4>%s</h4><p>%s</p></div>',
        esc_attr($feature['icon'] ?? 'dashicons-yes'),
        esc_html($feature['title'] ?? ''),
        esc_html($feature['description'] ?? '')
    );
}

// Get category color
$category_settings = optstack_get_category_settings($term_id);
$category_color = $category_settings['color'] ?? '#333';

// Get user profile
$profile = optstack_get_user_profile();
$job_title = $profile['professional']['job_title'] ?? '';
$bio = $profile['professional']['bio'] ?? '';

// =============================================================================
// TYPOGRAPHY FIELD USAGE
// =============================================================================

// Get typography settings
$heading_typo = optstack_get_theme_option('heading_typography', []);
$body_typo = optstack_get_theme_option('body_typography', []);

// Generate inline CSS
$heading_css = optstack_typography_css($heading_typo);
// Output: font-family: "Montserrat", sans-serif; font-size: 32px; font-weight: 700; ...

// Use in HTML
echo '<h1 style="' . esc_attr($heading_css) . '">Page Title</h1>';

// Or generate CSS variables in <head>
$all_typography = [
    optstack_get_theme_option('heading_typography', []),
    optstack_get_theme_option('body_typography', []),
    optstack_get_theme_option('nav_typography', []),
];

// Enqueue Google Fonts
$google_fonts_url = optstack_get_google_fonts_url($all_typography);
if ($google_fonts_url) {
    wp_enqueue_style('theme-google-fonts', $google_fonts_url, [], null);
}

// Generate CSS stylesheet
function mytheme_generate_typography_css() {
    $heading = optstack_get_theme_option('heading_typography', []);
    $body = optstack_get_theme_option('body_typography', []);
    $button = optstack_get_theme_option('button_typography', []);
    $nav = optstack_get_theme_option('nav_typography', []);
    // Typography - Generated by OptStack
    $css = "
       
        h1, h2, h3, h4, h5, h6 {
            " . optstack_typography_css($heading) . ";
        }
        
        body, p {
            " . optstack_typography_css($body) . ";
        }
        
        .btn, button, input[type='submit'] {
            " . optstack_typography_css($button) . ";
        }
        
        .nav-menu a {
            " . optstack_typography_css($nav) . ";
        }
    ";
    
    return $css;
}

// Add to <head>
add_action('wp_head', function() {
    echo '<style id="theme-typography-css">' . mytheme_generate_typography_css() . '</style>';
});

// =============================================================================
// SEARCHABLE FIELDS USAGE
// =============================================================================
// Searchable fields are stored as separate meta keys for efficient WP_Query.
// Meta key format: _optstack_idx_{context}_{field_path}

// Query posts by searchable field (price)
$expensive_products = new WP_Query([
    'post_type' => 'post',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_price',
            'value' => 100,
            'compare' => '>=',
            'type' => 'NUMERIC',
        ],
    ],
]);

// Query by status (select field)
$active_products = new WP_Query([
    'post_type' => 'post',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
    ],
]);

// Query by featured toggle
$featured_products = new WP_Query([
    'post_type' => 'post',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_featured',
            'value' => '1', // Toggle values are stored as '1' or ''
        ],
    ],
]);

// Query by nested field (seo.title)
$posts_with_seo = new WP_Query([
    'post_type' => 'post',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_seo_title', // Dots become underscores
            'compare' => 'EXISTS',
        ],
    ],
]);

// Complex query with multiple searchable fields
$filtered_products = new WP_Query([
    'post_type' => 'post',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_price',
            'value' => [50, 200],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC',
        ],
        [
            'key' => '_optstack_idx_post_inventory_quantity',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ],
    ],
    'orderby' => 'meta_value_num',
    'meta_key' => '_optstack_idx_post_price',
    'order' => 'ASC',
]);

// Get all indexed meta keys for a stack (for debugging)
// $bootstrap = \OptStack\WordPress\Bootstrap::getInstance();
// $manager = $bootstrap->getIndexedMetaManager();
// $keys = $manager->getIndexedMetaKeys($stack);
// Returns: ['price' => '_optstack_idx_post_price', 'seo.title' => '_optstack_idx_post_seo_title', ...]
*/