# Theme Options Example - Complete Guide

This example (`exam2.php`) demonstrates a **production-ready theme options page** using OptStack, covering all common WordPress theme customization needs.

---

## 🎯 **What's Included**

A complete theme options system with **10 organized tabs**:

1. **General Settings** - Site identity, layout, preloader
2. **Header & Navigation** - Header styles, sticky header, mobile menu, top bar
3. **Footer** - Footer layout, copyright, back-to-top button
4. **Typography** - Complete typography controls (body, headings, menu, buttons)
5. **Colors** - Comprehensive color scheme (primary, text, links, buttons, backgrounds)
6. **Social Media** - Social links and sharing options
7. **SEO & Meta** - SEO settings, Open Graph, Twitter Cards, Schema markup
8. **Custom Code** - Custom CSS/JS, header/footer code, Google Analytics
9. **Performance** - Optimization settings, asset loading, caching
10. **Advanced** - Maintenance mode, import/export, reset options

---

## 📦 **Installation**

### Option 1: Include in Theme

Add to your theme's `functions.php`:

```php
require_once WP_PLUGIN_DIR . '/optstack/examples/exam2.php';
```

### Option 2: Include in Plugin

Add to your plugin's main file:

```php
if (class_exists('OptStack\OptStack')) {
    require_once plugin_dir_path(__FILE__) . 'path/to/exam2.php';
}
```

---

## 🎨 **Features Showcase**

### **1. General Settings**

#### Site Identity
```php
// Get site logo
$logo_id = mytheme_option('general.identity.site_logo');
$logo_url = wp_get_attachment_url($logo_id);

// Get logo width
$logo_width = mytheme_option('general.identity.logo_width', 180);
```

#### Layout Options
```php
// Get container width
$container_width = mytheme_option('general.layout.container_width', 1200);

// Get sidebar layout
$sidebar = mytheme_option('general.layout.sidebar_layout', 'right');

// Check if boxed layout
$is_boxed = mytheme_option('general.layout.boxed_layout', false);
```

#### Preloader
```php
// Check if preloader enabled
if (mytheme_option('general.preloader.enable', false)) {
    $style = mytheme_option('general.preloader.style', 'spinner');
    $bg_color = mytheme_option('general.preloader.background_color', '#ffffff');
    // Display preloader...
}
```

### **2. Header & Navigation**

#### Header Layout
```php
// Get header style
$header_style = mytheme_option('header.header_layout.style', 'default');
// Options: default, centered, split, vertical

// Check sticky header
$is_sticky = mytheme_option('header.header_layout.sticky', true);

// Check transparent header
$is_transparent = mytheme_option('header.header_layout.transparent', false);

// Get header height
$height = mytheme_option('header.header_layout.height', 80);

// Get header background
$bg = mytheme_option('header.header_layout.background', '#ffffff');
```

#### Top Bar
```php
if (mytheme_option('header.top_bar.enable', false)) {
    $left = mytheme_option('header.top_bar.content_left', '');
    $right = mytheme_option('header.top_bar.content_right', '');
    $bg = mytheme_option('header.top_bar.background', '#f8f9fa');
    
    echo '<div class="top-bar" style="background: ' . esc_attr($bg) . '">';
    echo '<div class="top-bar-left">' . wp_kses_post($left) . '</div>';
    echo '<div class="top-bar-right">' . wp_kses_post($right) . '</div>';
    echo '</div>';
}
```

#### Mobile Menu
```php
// Get mobile breakpoint
$breakpoint = mytheme_option('header.mobile_menu.breakpoint', 992);

// Get mobile menu style
$mobile_style = mytheme_option('header.mobile_menu.style', 'slide');
// Options: slide, dropdown, fullscreen

// Get slide position
$position = mytheme_option('header.mobile_menu.position', 'left');
```

#### Header Elements
```php
// Check if search icon enabled
$show_search = mytheme_option('header.header_elements.search', true);

// Check if cart icon enabled
$show_cart = mytheme_option('header.header_elements.cart', false);

// Check CTA button
if (mytheme_option('header.header_elements.cta_button', false)) {
    $cta_text = mytheme_option('header.header_elements.cta_text', 'Get Started');
    $cta_url = mytheme_option('header.header_elements.cta_url', '');
    
    echo '<a href="' . esc_url($cta_url) . '" class="cta-button">' . esc_html($cta_text) . '</a>';
}
```

### **3. Footer**

#### Footer Layout
```php
// Get footer columns
$columns = mytheme_option('footer.footer_layout.columns', '4');

// Get footer colors
$bg = mytheme_option('footer.footer_layout.background', '#1f2937');
$text_color = mytheme_option('footer.footer_layout.text_color', '#9ca3af');
$heading_color = mytheme_option('footer.footer_layout.heading_color', '#ffffff');
```

#### Copyright Bar
```php
if (mytheme_option('footer.copyright.enable', true)) {
    $text = mytheme_option('footer.copyright.text', '© {year} {site_name}. All rights reserved.');
    $links = mytheme_option('footer.copyright.links', '');
    $bg = mytheme_option('footer.copyright.background', '#111827');
    
    // Replace tokens
    $text = str_replace(
        ['{year}', '{site_name}'],
        [date('Y'), get_bloginfo('name')],
        $text
    );
    
    echo '<div class="copyright-bar" style="background: ' . esc_attr($bg) . '">';
    echo '<p>' . esc_html($text) . '</p>';
    echo '<div class="footer-links">' . wp_kses_post($links) . '</div>';
    echo '</div>';
}
```

#### Back to Top Button
```php
if (mytheme_option('footer.back_to_top.enable', true)) {
    $position = mytheme_option('footer.back_to_top.position', 'right');
    $style = mytheme_option('footer.back_to_top.style', 'circle');
    
    echo '<button class="back-to-top back-to-top-' . esc_attr($position) . ' back-to-top-' . esc_attr($style) . '">';
    echo '<i class="icon-arrow-up"></i>';
    echo '</button>';
}
```

### **4. Typography**

#### Body Font
```php
$body_font = mytheme_option('typography.body_font', []);

// Apply body typography
$css = "
body {
    font-family: {$body_font['fontFamily']};
    font-size: {$body_font['fontSize']}{$body_font['fontSizeUnit']};
    font-weight: {$body_font['fontWeight']};
    line-height: {$body_font['lineHeight']};
    color: {$body_font['color']};
}
";
```

#### Heading Font
```php
$heading_font = mytheme_option('typography.heading_font', []);

// Individual heading sizes
$h1 = mytheme_option('typography.heading_sizes.h1_size', 48);
$h2 = mytheme_option('typography.heading_sizes.h2_size', 36);
$h3 = mytheme_option('typography.heading_sizes.h3_size', 28);
$h4 = mytheme_option('typography.heading_sizes.h4_size', 22);
$h5 = mytheme_option('typography.heading_sizes.h5_size', 18);
$h6 = mytheme_option('typography.heading_sizes.h6_size', 16);

$css = "
h1, h2, h3, h4, h5, h6 {
    font-family: {$heading_font['fontFamily']};
    font-weight: {$heading_font['fontWeight']};
    line-height: {$heading_font['lineHeight']};
    color: {$heading_font['color']};
}
h1 { font-size: {$h1}px; }
h2 { font-size: {$h2}px; }
h3 { font-size: {$h3}px; }
h4 { font-size: {$h4}px; }
h5 { font-size: {$h5}px; }
h6 { font-size: {$h6}px; }
";
```

### **5. Colors**

#### Primary Colors
```php
$brand = mytheme_option('colors.primary.brand', '#3b82f6');
$secondary = mytheme_option('colors.primary.secondary', '#8b5cf6');
$accent = mytheme_option('colors.primary.accent', '#10b981');
```

#### Text Colors
```php
$text_primary = mytheme_option('colors.text.primary', '#111827');
$text_secondary = mytheme_option('colors.text.secondary', '#6b7280');
$text_muted = mytheme_option('colors.text.muted', '#9ca3af');
```

#### Link Colors
```php
$link_color = mytheme_option('colors.links.default', '#3b82f6');
$link_hover = mytheme_option('colors.links.hover', '#2563eb');

$css = "
a {
    color: {$link_color};
}
a:hover {
    color: {$link_hover};
}
";
```

#### Button Colors
```php
$btn_primary_bg = mytheme_option('colors.buttons.primary_bg', '#3b82f6');
$btn_primary_text = mytheme_option('colors.buttons.primary_text', '#ffffff');
$btn_primary_hover = mytheme_option('colors.buttons.primary_hover', '#2563eb');

$css = "
.button-primary {
    background: {$btn_primary_bg};
    color: {$btn_primary_text};
}
.button-primary:hover {
    background: {$btn_primary_hover};
}
";
```

### **6. Social Media**

#### Social Links
```php
// Display social icons
mytheme_social_icons([
    'before' => '<ul class="social-icons">',
    'after' => '</ul>',
    'show_label' => false,
]);

// Get social links array
$social_links = mytheme_get_social_links();
foreach ($social_links as $link) {
    $platform = $link['platform'];  // facebook, twitter, instagram, etc.
    $url = $link['url'];
    $label = $link['label'] ?? ucfirst($platform);
}
```

#### Social Sharing
```php
// Check if sharing enabled on posts
$enable_posts = mytheme_option('social.sharing.enable_posts', true);
$enable_pages = mytheme_option('social.sharing.enable_pages', false);

// Get sharing platforms
$platforms = mytheme_option('social.sharing.platforms', ['facebook', 'twitter', 'linkedin']);

// Get sharing position
$position = mytheme_option('social.sharing.position', 'bottom');
// Options: top, bottom, both, float
```

### **7. SEO & Meta**

#### Basic SEO
```php
// Get site name for SEO
$site_name = mytheme_option('seo.basic.site_name', get_bloginfo('name'));
$separator = mytheme_option('seo.basic.separator', '|');

// Homepage SEO
$home_title = mytheme_option('seo.basic.home_title', '');
$home_description = mytheme_option('seo.basic.home_description', '');
$keywords = mytheme_option('seo.basic.keywords', '');
```

#### Open Graph
```php
if (mytheme_option('seo.opengraph.enable', true) && is_singular()) {
    $og_title = get_the_title();
    $og_description = get_the_excerpt();
    $og_image = get_the_post_thumbnail_url(null, 'large') 
        ?: wp_get_attachment_url(mytheme_option('seo.opengraph.default_image'));
    
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '">';
    echo '<meta property="og:description" content="' . esc_attr($og_description) . '">';
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">';
}
```

#### Twitter Cards
```php
if (mytheme_option('seo.twitter.enable', true)) {
    $card_type = mytheme_option('seo.twitter.card_type', 'summary_large_image');
    $username = mytheme_option('seo.twitter.username', '');
    
    echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">';
    if ($username) {
        echo '<meta name="twitter:site" content="@' . esc_attr($username) . '">';
    }
}
```

### **8. Custom Code**

All custom code is automatically output via action hooks:

```php
// Custom CSS (wp_head priority 999)
$custom_css = mytheme_option('custom_code.custom_css', '');

// Custom JS (wp_footer priority 999)
$custom_js = mytheme_option('custom_code.custom_js', '');

// Header code (wp_head priority 1)
$header_code = mytheme_option('custom_code.header_code', '');

// Footer code (wp_footer priority 999)
$footer_code = mytheme_option('custom_code.footer_code', '');

// Google Analytics (auto-injected if enabled)
$ga_enabled = mytheme_option('custom_code.analytics.enable', false);
$ga_id = mytheme_option('custom_code.analytics.tracking_id', '');
$anonymize_ip = mytheme_option('custom_code.analytics.anonymize_ip', true);
```

### **9. Performance**

#### Optimization Settings
```php
// Lazy load images
$lazy_load = mytheme_option('performance.optimization.lazy_load_images', true);

// Disable emojis
$disable_emojis = mytheme_option('performance.optimization.disable_emojis', false);

// Disable embeds
$disable_embeds = mytheme_option('performance.optimization.disable_embeds', false);

// Remove query strings
$remove_query_strings = mytheme_option('performance.optimization.remove_query_strings', false);
```

#### Asset Loading
```php
// Minification settings
$minify_css = mytheme_option('performance.assets.minify_css', false);
$minify_js = mytheme_option('performance.assets.minify_js', false);
$defer_js = mytheme_option('performance.assets.defer_js', false);
```

#### Font Loading
```php
// Google Fonts display
$font_display = mytheme_option('performance.fonts.google_fonts_display', 'swap');

// Preload fonts
$preload_fonts = mytheme_option('performance.fonts.preload_fonts', false);
```

#### Caching
```php
// Browser cache
$browser_cache = mytheme_option('performance.caching.browser_cache', true);
$cache_duration = mytheme_option('performance.caching.cache_duration', 7);
```

### **10. Advanced**

#### Maintenance Mode
```php
// Check if maintenance mode is active
if (mytheme_is_maintenance_mode()) {
    // Maintenance page is displayed automatically
}

// Get maintenance settings
$maintenance_title = mytheme_option('advanced.maintenance.title', 'Site Under Maintenance');
$maintenance_message = mytheme_option('advanced.maintenance.message', '');
```

---

## 🛠️ **Helper Functions**

### Core Functions

```php
// Get all theme options
$options = mytheme_get_options();

// Get specific option (supports dot notation)
$value = mytheme_option('header.header_layout.sticky', true);

// Get social links
$social = mytheme_get_social_links();

// Display social icons
mytheme_social_icons([
    'before' => '<div class="social">',
    'after' => '</div>',
    'show_label' => true,
]);

// Check maintenance mode
if (mytheme_is_maintenance_mode()) {
    // Logged-out users see maintenance page
}
```

### Automatic Hooks

These functions run automatically:

```php
// Output custom CSS (wp_head)
add_action('wp_head', 'mytheme_output_custom_css', 999);

// Output custom JS (wp_footer)
add_action('wp_footer', 'mytheme_output_custom_js', 999);

// Output header code (wp_head)
add_action('wp_head', 'mytheme_output_header_code', 1);

// Output footer code (wp_footer)
add_action('wp_footer', 'mytheme_output_footer_code', 999);

// Output Google Analytics (wp_head)
add_action('wp_head', 'mytheme_output_google_analytics', 1);

// Add favicon (wp_head)
add_action('wp_head', 'mytheme_add_favicon');

// Show maintenance page (template_redirect)
add_action('template_redirect', 'mytheme_show_maintenance_page', 1);
```

---

## 📋 **Template Usage Examples**

### In header.php

```php
<?php
// Get header settings
$header_style = mytheme_option('header.header_layout.style', 'default');
$is_sticky = mytheme_option('header.header_layout.sticky', true);
$is_transparent = mytheme_option('header.header_layout.transparent', false);
$header_height = mytheme_option('header.header_layout.height', 80);
$header_bg = mytheme_option('header.header_layout.background', '#ffffff');

// Get logo
$logo_id = mytheme_option('general.identity.site_logo');
$logo_width = mytheme_option('general.identity.logo_width', 180);

// Get header elements
$show_search = mytheme_option('header.header_elements.search', true);
$show_cart = mytheme_option('header.header_elements.cart', false);
$show_cta = mytheme_option('header.header_elements.cta_button', false);
?>

<header 
    class="site-header header-<?php echo esc_attr($header_style); ?> <?php echo $is_sticky ? 'sticky' : ''; ?> <?php echo $is_transparent ? 'transparent' : ''; ?>" 
    style="height: <?php echo esc_attr($header_height); ?>px; background: <?php echo esc_attr($header_bg); ?>;"
>
    <div class="container">
        <!-- Logo -->
        <?php if ($logo_id): ?>
            <a href="<?php echo home_url('/'); ?>" class="site-logo">
                <?php echo wp_get_attachment_image($logo_id, 'full', false, [
                    'style' => 'max-width: ' . $logo_width . 'px;'
                ]); ?>
            </a>
        <?php else: ?>
            <a href="<?php echo home_url('/'); ?>" class="site-title">
                <?php bloginfo('name'); ?>
            </a>
        <?php endif; ?>
        
        <!-- Navigation -->
        <?php wp_nav_menu(['theme_location' => 'primary']); ?>
        
        <!-- Header Elements -->
        <?php if ($show_search): ?>
            <button class="search-toggle"><i class="icon-search"></i></button>
        <?php endif; ?>
        
        <?php if ($show_cart && function_exists('WC')): ?>
            <a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon">
                <i class="icon-cart"></i>
                <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
            </a>
        <?php endif; ?>
        
        <?php if ($show_cta): ?>
            <a href="<?php echo esc_url(mytheme_option('header.header_elements.cta_url')); ?>" class="cta-button">
                <?php echo esc_html(mytheme_option('header.header_elements.cta_text', 'Get Started')); ?>
            </a>
        <?php endif; ?>
    </div>
</header>
```

### In footer.php

```php
<?php
// Get footer settings
$footer_columns = mytheme_option('footer.footer_layout.columns', '4');
$footer_bg = mytheme_option('footer.footer_layout.background', '#1f2937');
$footer_text = mytheme_option('footer.footer_layout.text_color', '#9ca3af');
$heading_color = mytheme_option('footer.footer_layout.heading_color', '#ffffff');

// Copyright settings
$show_copyright = mytheme_option('footer.copyright.enable', true);
$copyright_text = mytheme_option('footer.copyright.text', '© {year} {site_name}. All rights reserved.');
$copyright_links = mytheme_option('footer.copyright.links', '');
$copyright_bg = mytheme_option('footer.copyright.background', '#111827');

// Back to top
$show_back_to_top = mytheme_option('footer.back_to_top.enable', true);
$back_to_top_position = mytheme_option('footer.back_to_top.position', 'right');
$back_to_top_style = mytheme_option('footer.back_to_top.style', 'circle');

// Replace tokens
$copyright_text = str_replace(
    ['{year}', '{site_name}'],
    [date('Y'), get_bloginfo('name')],
    $copyright_text
);
?>

<footer class="site-footer" style="background: <?php echo esc_attr($footer_bg); ?>; color: <?php echo esc_attr($footer_text); ?>;">
    <div class="container">
        <div class="footer-widgets footer-columns-<?php echo esc_attr($footer_columns); ?>">
            <?php
            for ($i = 1; $i <= $footer_columns; $i++) {
                if (is_active_sidebar('footer-' . $i)) {
                    echo '<div class="footer-widget-area">';
                    dynamic_sidebar('footer-' . $i);
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
    
    <?php if ($show_copyright): ?>
        <div class="copyright-bar" style="background: <?php echo esc_attr($copyright_bg); ?>;">
            <div class="container">
                <p><?php echo esc_html($copyright_text); ?></p>
                <?php if ($copyright_links): ?>
                    <div class="footer-links"><?php echo wp_kses_post($copyright_links); ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</footer>

<?php if ($show_back_to_top): ?>
    <button 
        class="back-to-top back-to-top-<?php echo esc_attr($back_to_top_position); ?> back-to-top-<?php echo esc_attr($back_to_top_style); ?>"
        aria-label="Back to top"
    >
        <i class="icon-arrow-up"></i>
    </button>
<?php endif; ?>
```

### In functions.php (Theme Customization)

```php
/**
 * Enqueue theme styles with color customization
 */
function mytheme_enqueue_styles() {
    wp_enqueue_style('mytheme-style', get_stylesheet_uri());
    
    // Generate dynamic CSS from theme options
    $custom_css = mytheme_generate_theme_css();
    wp_add_inline_style('mytheme-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_styles');

/**
 * Generate CSS from theme options
 */
function mytheme_generate_theme_css() {
    $css = '';
    
    // Container width
    $container_width = mytheme_option('general.layout.container_width', 1200);
    $css .= ".container { max-width: {$container_width}px; }";
    
    // Primary colors
    $brand_color = mytheme_option('colors.primary.brand', '#3b82f6');
    $css .= ":root { --color-brand: {$brand_color}; }";
    
    // Typography
    $body_font = mytheme_option('typography.body_font', []);
    if (!empty($body_font)) {
        $css .= "body { 
            font-family: {$body_font['fontFamily']}; 
            font-size: {$body_font['fontSize']}{$body_font['fontSizeUnit']}; 
            color: {$body_font['color']}; 
        }";
    }
    
    return $css;
}
```

---

## 🎨 **Customization Tips**

### 1. Rename Functions

Change `mytheme_` prefix to match your theme name:

```php
// Find: mytheme_
// Replace: yourtheme_
```

### 2. Modify Menu Location

Change menu parent in OptStack definition:

```php
->menuParent('themes.php')  // Under Appearance

// Other options:
->menuParent('options-general.php')  // Under Settings
->menuParent('optstack')  // Under OptStack
```

### 3. Add Custom Fields

Add new fields to any tab:

```php
$tab->field('your_field', [
    'type' => 'text',
    'label' => 'Your Field',
]);
```

### 4. Remove Unwanted Tabs

Comment out or remove tabs you don't need:

```php
// Remove this entire block if you don't need Performance tab
// $stack->tab('performance', function ($tab) { ... });
```

### 5. Change Default Values

Modify defaults in field definitions:

```php
'default' => 'your_default_value',
```

---

## 🚀 **Next Steps**

1. **Activate** - Include `exam2.php` in your theme
2. **Access** - Go to **Appearance → Theme Options** in WordPress admin
3. **Configure** - Customize all settings for your theme
4. **Apply** - Use helper functions in your theme templates
5. **Style** - Add CSS to match your theme's design

---

## 📚 **Related Documentation**

- [OptStack Documentation](../documents/FLOW.md)
- [Field Types Reference](../.cursor/skills/optstack-dev/references/field-types.md)
- [Common Patterns](../.cursor/skills/optstack-dev/references/patterns.md)
- [Visual Builder Example](./basic-usage.php)

---

**Happy Theming! 🎨**
