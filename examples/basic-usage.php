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
                    ->priority(30);

                $tab->field('heading_font', [
                    'type' => 'select',
                    'label' => 'Heading Font',
                    'default' => 'system',
                    'options' => [
                        ['value' => 'system', 'label' => 'System Default'],
                        ['value' => 'inter', 'label' => 'Inter'],
                        ['value' => 'roboto', 'label' => 'Roboto'],
                        ['value' => 'open-sans', 'label' => 'Open Sans'],
                        ['value' => 'playfair', 'label' => 'Playfair Display'],
                        ['value' => 'montserrat', 'label' => 'Montserrat'],
                    ],
                ]);

                $tab->field('body_font', [
                    'type' => 'select',
                    'label' => 'Body Font',
                    'default' => 'system',
                    'options' => [
                        ['value' => 'system', 'label' => 'System Default'],
                        ['value' => 'inter', 'label' => 'Inter'],
                        ['value' => 'roboto', 'label' => 'Roboto'],
                        ['value' => 'open-sans', 'label' => 'Open Sans'],
                        ['value' => 'lato', 'label' => 'Lato'],
                        ['value' => 'source-sans', 'label' => 'Source Sans Pro'],
                    ],
                ]);

                $tab->field('base_font_size', [
                    'type' => 'range',
                    'label' => 'Base Font Size',
                    'default' => 16,
                    'attributes' => [
                        'min' => 14,
                        'max' => 20,
                        'step' => 1,
                        'unit' => 'px',
                    ],
                ]);

                $tab->field('line_height', [
                    'type' => 'range',
                    'label' => 'Line Height',
                    'default' => 1.6,
                    'attributes' => [
                        'min' => 1.2,
                        'max' => 2.0,
                        'step' => 0.1,
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

                $tab->field('sidebar_position', [
                    'type' => 'radio',
                    'label' => 'Default Sidebar Position',
                    'default' => 'right',
                    'options' => [
                        ['value' => 'left', 'label' => 'Left Sidebar'],
                        ['value' => 'right', 'label' => 'Right Sidebar'],
                        ['value' => 'none', 'label' => 'No Sidebar (Full Width)'],
                    ],
                ]);

                $tab->field('header_style', [
                    'type' => 'radio',
                    'label' => 'Header Style',
                    'default' => 'standard',
                    'options' => [
                        ['value' => 'standard', 'label' => 'Standard'],
                        ['value' => 'centered', 'label' => 'Centered Logo'],
                        ['value' => 'transparent', 'label' => 'Transparent (Hero)'],
                    ],
                ]);

                $tab->field('sticky_header', [
                    'type' => 'toggle',
                    'label' => 'Sticky Header',
                    'default' => true,
                    'description' => 'Keep header fixed at top when scrolling.',
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

                $tab->field('footer_columns', [
                    'type' => 'select',
                    'label' => 'Footer Widget Columns',
                    'default' => '4',
                    'options' => [
                        ['value' => '2', 'label' => '2 Columns'],
                        ['value' => '3', 'label' => '3 Columns'],
                        ['value' => '4', 'label' => '4 Columns'],
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
                        'allowedTypes' => ['image'],
                        'buttonText' => 'Add Image',
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
*/
