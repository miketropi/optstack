<?php
/**
 * Plugin Name: OptStack
 * Plugin URI: https://optstack-doc.beplus-agency.cloud/
 * Description: WordPress Data Stack Framework - A PHP framework for defining, storing, and managing structured data in WordPress using a unified, extensible stack-based model.
 * Version: 0.1.9
 * Author: Beplus
 * Author URI: https://optstack-doc.beplus-agency.cloud/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: optstack
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('OPTSTACK_VERSION', '0.1.9');
define('OPTSTACK_FILE', __FILE__);
define('OPTSTACK_DIR', plugin_dir_path(__FILE__));
define('OPTSTACK_URL', plugin_dir_url(__FILE__));

// Dev mode - set to true to load from Vite dev server (npm run dev)
if (!defined('OPTSTACK_DEV_MODE')) {
    define('OPTSTACK_DEV_MODE', false);
}

// Vite dev server URL
if (!defined('OPTSTACK_DEV_SERVER')) {
    define('OPTSTACK_DEV_SERVER', 'http://localhost:5173');
}

/**
 * Load Composer autoloader if available.
 */
$autoloader = OPTSTACK_DIR . 'vendor/autoload.php';

if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    // Fallback: Manual autoloading for development without Composer
    spl_autoload_register(function (string $class): void {
        // Only handle OptStack namespace
        if (!str_starts_with($class, 'OptStack\\')) {
            return;
        }

        // Convert namespace to path
        $relativePath = str_replace('OptStack\\', '', $class);
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
        $file = OPTSTACK_DIR . 'src' . DIRECTORY_SEPARATOR . $relativePath . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

/**
 * Initialize OptStack with Runtime Context Injection.
 */
function optstack_init(): void
{
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        add_action('admin_notices', function (): void {
            echo '<div class="error"><p>';
            echo esc_html__('OptStack requires PHP 8.1 or higher.', 'optstack');
            echo '</p></div>';
        });
        return;
    }

    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        add_action('admin_notices', function (): void {
            echo '<div class="error"><p>';
            echo esc_html__('OptStack requires WordPress 6.0 or higher.', 'optstack');
            echo '</p></div>';
        });
        return;
    }

    // Bootstrap the framework with runtime context injection
    // The host (this plugin) provides its own context
    \OptStack\WordPress\Bootstrap::boot([
        // 'file' => OPTSTACK_FILE,
        // 'dir' => OPTSTACK_DIR,
        // 'url' => OPTSTACK_URL,
        // 'version' => OPTSTACK_VERSION,
    ]);
}

// Initialize on plugins_loaded to ensure all dependencies are available
add_action('plugins_loaded', 'optstack_init', 5);

/**
 * Compatibility: Fix block editor warnings when using block themes.
 *
 * 1. Ensure get_block_templates() returns a numerically indexed array so
 *    $current_template[0] exists (core assumes key 0). Avoids "Undefined array key 0"
 *    and "Attempt to read property 'content' on null".
 *
 * 2. Ensure each template's content is a string before parse_blocks() runs.
 *    Avoids preg_match()/strlen() null deprecations in WP_Block_Parser.
 *
 * @see https://core.trac.wordpress.org/ticket/62407
 */
add_filter('get_block_templates', function ($templates, $query, $template_type) {
    if (!is_array($templates)) {
        return $templates;
    }
    $list = array_values($templates);
    foreach ($list as $template) {
        if (is_object($template) && property_exists($template, 'content') && $template->content === null) {
            $template->content = '';
        }
    }
    return $list;
}, 10, 3);

/**
 * Plugin activation hook.
 */
function optstack_activate(): void
{
    // Flush rewrite rules
    flush_rewrite_rules();

    // Set activation flag
    update_option('optstack_activated', true);

    // Fire activation action
    do_action('optstack_activated');
}
register_activation_hook(__FILE__, 'optstack_activate');

/**
 * Plugin deactivation hook.
 */
function optstack_deactivate(): void
{
    // Flush rewrite rules
    flush_rewrite_rules();

    // Fire deactivation action
    do_action('optstack_deactivated');
}
register_deactivation_hook(__FILE__, 'optstack_deactivate');

/**
 * Helper function to get OptStack instance.
 *
 * @return class-string<\OptStack\OptStack>
 */
function optstack(): string
{
    return \OptStack\OptStack::class;
}


/**
 * Register a custom design group.
 *
 * @param string $id Group identifier (snake_case)
 * @param array $config Group configuration
 */
function optstack_register_design_group(string $id, array $config): void
{
    \OptStack\Core\DesignPreset\DesignGroupRegistry::register($id, $config);
}

/**
 * Register a custom design preset.
 *
 * @param array $preset Preset with 'id', 'label', and 'tokens' keys
 */
function optstack_register_design_preset(array $preset): void
{
    \OptStack\Core\DesignPreset\DesignPresetRegistry::register($preset);
}

/**
 * Register a custom design preset output adapter.
 *
 * @param string $key Adapter identifier
 * @param \OptStack\Core\Contract\DesignPresetAdapterInterface $adapter Adapter instance
 */
function optstack_register_design_adapter(string $key, \OptStack\Core\Contract\DesignPresetAdapterInterface $adapter): void
{
    \OptStack\WordPress\DesignPreset\DesignPresetManager::registerAdapter($key, $adapter);
}

// Load example usage (for testing - remove in production).
// require_once OPTSTACK_DIR . 'examples/basic-usage.php';
// require_once OPTSTACK_DIR . 'examples/exam2.php';
// require_once OPTSTACK_DIR . 'examples/block-example.php';
// require_once OPTSTACK_DIR . 'examples/select-wp-query-example.php';
// require_once OPTSTACK_DIR . 'examples/customizer-example.php';
// require_once OPTSTACK_DIR . 'examples/design-preset-example.php';

// Debug listener for searchable fields (remove in production)
add_action('optstack_indexed_meta_debug', function($debugInfo) {
    error_log('OptStack Indexed Meta Debug: ' . print_r($debugInfo, true));
});
