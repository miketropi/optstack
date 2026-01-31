<?php
/**
 * OptStack Example: Runtime Context Injection
 * 
 * This example demonstrates how to use OptStack as a pure Composer library
 * with Runtime Context Injection. This allows OptStack to be used by multiple
 * plugins/themes without conflicts.
 * 
 * Key Concepts:
 * - Plugin/Theme is the HOST (provides context)
 * - OptStack is the GUEST (receives context)
 * - No hardcoded paths or assumptions
 * - Multiple hosts can use OptStack simultaneously
 * 
 * @package OptStack
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// EXAMPLE 1: Using OptStack in a Plugin
// =============================================================================

/**
 * Plugin Entry Point (e.g., my-plugin/my-plugin.php)
 * 
 * This is how a plugin would initialize OptStack with Runtime Context Injection.
 */
function example_plugin_init(): void
{
    // Load OptStack via Composer
    $autoloader = plugin_dir_path(__FILE__) . '../vendor/autoload.php';
    
    if (!file_exists($autoloader)) {
        return;
    }
    
    require_once $autoloader;
    
    // Bootstrap OptStack with runtime context injection
    // Only version is required - OptStack auto-detects file/dir/url
    \OptStack\WordPress\Bootstrap::boot([
        'version' => '1.0.0',
    ]);
}

// Initialize when WordPress is ready
// add_action('plugins_loaded', 'example_plugin_init', 5);

// =============================================================================
// EXAMPLE 2: Using OptStack in a Theme
// =============================================================================

/**
 * Theme Entry Point (e.g., functions.php)
 * 
 * This is how a theme would initialize OptStack with Runtime Context Injection.
 */
function example_theme_init(): void
{
    // Load OptStack via Composer
    $autoloader = get_template_directory() . '/vendor/autoload.php';
    
    if (!file_exists($autoloader)) {
        return;
    }
    
    require_once $autoloader;
    
    // Bootstrap OptStack with runtime context injection
    // Only version is required - OptStack auto-detects file/dir/url
    \OptStack\WordPress\Bootstrap::boot([
        'version' => wp_get_theme()->get('Version'),
    ]);
}

// Initialize when WordPress is ready
// add_action('after_setup_theme', 'example_theme_init', 5);

// =============================================================================
// EXAMPLE 3: Multiple Plugins Using OptStack (No Conflicts!)
// =============================================================================

/**
 * Plugin A - E-commerce Plugin
 */
function example_plugin_a_init(): void
{
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
    
    // Plugin A bootstraps OptStack
    \OptStack\WordPress\Bootstrap::boot([
        'version' => '2.0.0',
    ]);
    
    // Define Plugin A's stacks
    add_action('optstack_init', function () {
        \OptStack\OptStack::make('ecommerce_settings')
            ->forOptions()
            ->label('E-commerce Settings')
            ->define(function ($stack) {
                $stack->field('currency', ['type' => 'select']);
                $stack->field('tax_rate', ['type' => 'number']);
            })
            ->build();
    });
}

/**
 * Plugin B - Portfolio Plugin
 */
function example_plugin_b_init(): void
{
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
    
    // Plugin B bootstraps OptStack
    \OptStack\WordPress\Bootstrap::boot([
        'version' => '1.5.0',
    ]);
    
    // Define Plugin B's stacks
    add_action('optstack_init', function () {
        \OptStack\OptStack::make('portfolio_settings')
            ->forOptions()
            ->label('Portfolio Settings')
            ->define(function ($stack) {
                $stack->field('columns', ['type' => 'number']);
                $stack->field('show_filters', ['type' => 'toggle']);
            })
            ->build();
    });
}

// No conflicts! Each plugin maintains its own context

// =============================================================================
// EXAMPLE 4: Accessing Runtime Context
// =============================================================================

/**
 * Get the current runtime context.
 */
function example_get_context(): void
{
    $context = \OptStack\WordPress\Bootstrap::context();
    
    if (!$context) {
        echo 'OptStack not initialized yet.';
        return;
    }
    
    // Access context properties
    echo 'Version: ' . $context->version . PHP_EOL;
    
    // Use helper methods
    echo 'Assets Dir: ' . $context->getAssetsDir() . PHP_EOL;
    echo 'Assets URL: ' . $context->getAssetsUrl() . PHP_EOL;
    
    // Build paths
    echo 'Config File: ' . $context->path('config.php') . PHP_EOL;
    echo 'Logo URL: ' . $context->url('assets/logo.png') . PHP_EOL;
    
    // Check file existence
    if ($context->fileExists('README.md')) {
        echo 'README.md exists!';
    }
    
    // Convert to array (for debugging)
    print_r($context->toArray());
}

// =============================================================================
// EXAMPLE 5: Custom Host with Custom Context
// =============================================================================

/**
 * Advanced: Custom initialization for a headless WordPress setup.
 */
function example_headless_init(): void
{
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Custom context for headless environment
    \OptStack\WordPress\Bootstrap::boot([
        'version' => 'headless-v1',
    ]);
}

// =============================================================================
// EXAMPLE 6: Testing Without WordPress
// =============================================================================

/**
 * OptStack can be tested without WordPress because it doesn't assume
 * WordPress is always available.
 */
function example_standalone_test(): void
{
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Bootstrap will fail silently if WordPress is not available
    \OptStack\WordPress\Bootstrap::boot([
        'version' => 'test',
    ]);
    
    // Core OptStack functionality still works
    $stack = \OptStack\OptStack::make('test_stack')
        ->forOptions()
        ->define(function ($s) {
            $s->field('name', ['type' => 'text']);
        })
        ->build();
    
    echo 'Stack created: ' . $stack->getId();
}

// =============================================================================
// BENEFITS OF SIMPLIFIED BOOTSTRAP
// =============================================================================

/*
 * ✅ Minimal Configuration
 *    - Only 'version' parameter required
 *    - OptStack auto-detects file/dir/url paths
 *    - No manual path configuration needed
 * 
 * ✅ Multiple Hosts Support
 *    - Multiple plugins can use OptStack simultaneously
 *    - No conflicts between different versions
 * 
 * ✅ Flexible Deployment
 *    - Works as WordPress plugin
 *    - Works via Composer in themes
 *    - Works via Composer in plugins
 *    - Works in custom environments
 * 
 * ✅ Easy Testing
 *    - No WordPress dependency in core
 *    - Can be tested standalone
 *    - Mock contexts for unit tests
 * 
 * ✅ Clean Architecture
 *    - Clear separation of concerns
 *    - Host provides version info
 *    - OptStack handles the rest
 * 
 * ✅ AI-Friendly
 *    - Minimal boilerplate
 *    - Easy to understand and extend
 */

// =============================================================================
// VALIDATION CHECKLIST
// =============================================================================

/*
 * Bootstrap is correctly implemented if:
 * 
 * ✓ OptStack works when installed via Composer
 * ✓ No constants are defined inside src/ directory
 * ✓ OptStack can be used by multiple plugins simultaneously
 * ✓ Frontend assets load with correct URLs automatically
 * ✓ No fatal errors when running outside WordPress
 * ✓ Bootstrap::context() returns the context
 * ✓ Only 'version' parameter is required
 */

// =============================================================================
// MIGRATION GUIDE (For Existing Code)
// =============================================================================

/*
 * Before (Old Way - Using Constants):
 * 
 * define('OPTSTACK_DIR', plugin_dir_path(__FILE__));
 * define('OPTSTACK_URL', plugin_dir_url(__FILE__));
 * $assetUrl = OPTSTACK_URL . 'assets/logo.png';
 * 
 * 
 * After (New Way - Using Context):
 * 
 * \OptStack\WordPress\Bootstrap::boot([
 *     'version' => '1.0.0',
 * ]);
 * 
 * $context = \OptStack\WordPress\Bootstrap::context();
 * $assetUrl = $context->url('assets/logo.png');
 */

// =============================================================================
// DEBUGGING RUNTIME CONTEXT
// =============================================================================

/**
 * Debug helper to inspect the current context.
 */
function optstack_debug_context(): void
{
    $context = \OptStack\WordPress\Bootstrap::context();
    
    if (!$context) {
        echo '<pre>OptStack context not initialized</pre>';
        return;
    }
    
    echo '<pre>';
    echo '<h3>OptStack Runtime Context</h3>';
    echo '<strong>Version:</strong> ' . esc_html($context->version) . PHP_EOL;
    echo PHP_EOL;
    echo '<strong>Assets Dir:</strong> ' . esc_html($context->getAssetsDir()) . PHP_EOL;
    echo '<strong>Assets URL:</strong> ' . esc_url($context->getAssetsUrl()) . PHP_EOL;
    echo '</pre>';
}

// Add admin notice to show context (for debugging)
// add_action('admin_notices', 'optstack_debug_context');
