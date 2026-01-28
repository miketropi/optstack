<?php
/**
 * OptStack updateField() Method - Usage Examples
 *
 * This file demonstrates how to use the updateField() method to quickly
 * update single field values with automatic searchable field syncing.
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
 * Example 1: Simple Field Update
 * 
 * Update a single field value without fetching the entire data array.
 */
function example_simple_update() {
    $post_id = 123;
    
    // Old way (requires fetch, modify, save)
    $data = get_post_meta($post_id, 'product_data', true);
    $data['price'] = 99.99;
    update_post_meta($post_id, 'product_data', $data);
    
    // New way (single call, auto-sync searchable fields)
    OptStack::updateField('product_data', 'price', 99.99, $post_id);
    
    // If the 'price' field is marked as searchable, the indexed meta
    // '_optstack_idx_post_price' will be automatically synced!
}

/**
 * Example 2: Nested Field Update
 * 
 * Update fields within groups using dot notation.
 */
function example_nested_update() {
    $post_id = 123;
    
    // Update a field inside a group
    OptStack::updateField('product_data', 'pricing.regular_price', 149.99, $post_id);
    OptStack::updateField('product_data', 'pricing.sale_price', 99.99, $post_id);
    
    // Deeply nested fields
    OptStack::updateField('product_data', 'seo.meta.title', 'Product Title', $post_id);
    
    // All searchable fields in these paths will be synced automatically
}

/**
 * Example 3: Update in WordPress Hooks
 * 
 * Common use case: Update fields when other WordPress events occur.
 */
function example_hook_integration() {
    // Update product status when published
    add_action('publish_product', function($post_id) {
        OptStack::updateField('product_data', 'status', 'active', $post_id);
    });
    
    // Update inventory after purchase
    add_action('woocommerce_payment_complete', function($order_id) {
        $order = wc_get_order($order_id);
        
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            // Get current stock
            $current_stock = OptStack::getData('product_data', 'inventory.quantity', 0);
            
            // Calculate new stock
            $new_stock = max(0, $current_stock - $item->get_quantity());
            
            // Update with auto-sync
            OptStack::updateField('product_data', 'inventory.quantity', $new_stock, $product_id);
            
            // If quantity is searchable, WP_Query for low-stock products works instantly!
        }
    });
    
    // Auto-update last_modified timestamp
    add_action('post_updated', function($post_id) {
        if (get_post_type($post_id) === 'product') {
            OptStack::updateField('product_data', 'last_modified', time(), $post_id);
        }
    });
}

/**
 * Example 4: Bulk Updates
 * 
 * Efficiently update the same field across multiple posts.
 */
function example_bulk_updates() {
    // Mark all products in a category as featured
    $products = get_posts([
        'post_type' => 'product',
        'category' => 'summer-sale',
        'posts_per_page' => -1,
    ]);
    
    foreach ($products as $product) {
        OptStack::updateField('product_data', 'featured', true, $product->ID);
    }
    
    // Update prices for a specific vendor
    $vendor_products = [123, 456, 789, 1011];
    foreach ($vendor_products as $product_id) {
        OptStack::updateField('product_data', 'vendor_id', 42, $product_id);
    }
}

/**
 * Example 5: Using Stack Instance
 * 
 * You can also use the stack instance directly for better performance
 * if you're making multiple updates to the same stack.
 */
function example_stack_instance() {
    $post_id = 123;
    
    // Get stack instance once
    $stack = OptStack::get('product_data');
    
    if ($stack) {
        // Make multiple updates
        $stack->updateField('price', 99.99, $post_id);
        $stack->updateField('status', 'active', $post_id);
        $stack->updateField('featured', true, $post_id);
        
        // Each update syncs searchable fields automatically
    }
}

/**
 * Example 6: Conditional Updates
 * 
 * Update fields based on conditions.
 */
function example_conditional_updates() {
    $post_id = 123;
    
    // Get current price
    $current_price = OptStack::getData('product_data', 'price', 0);
    
    // Apply 20% discount if over $100
    if ($current_price > 100) {
        $discounted_price = $current_price * 0.8;
        OptStack::updateField('product_data', 'sale_price', $discounted_price, $post_id);
    }
    
    // Update status based on stock
    $stock = OptStack::getData('product_data', 'inventory.quantity', 0);
    $status = $stock > 0 ? 'in_stock' : 'out_of_stock';
    OptStack::updateField('product_data', 'inventory.status', $status, $post_id);
}

/**
 * Example 7: Searchable Field Auto-Sync
 * 
 * Demonstrate automatic searchable field synchronization.
 */
function example_searchable_sync() {
    $post_id = 123;
    
    // Define a stack with searchable fields
    add_action('optstack_init', function() {
        OptStack::make('product_data')
            ->forPostType('product')
            ->define(function($stack) {
                $stack->field('price', [
                    'type' => 'number',
                    'searchable' => true, // Will be indexed
                ]);
                
                $stack->field('status', [
                    'type' => 'select',
                    'searchable' => true, // Will be indexed
                ]);
                
                $stack->field('description', [
                    'type' => 'textarea',
                    // NOT searchable - won't be indexed
                ]);
            })
            ->build();
    });
    
    // Update searchable field
    OptStack::updateField('product_data', 'price', 99.99, $post_id);
    // ✅ Updates: product_data['price']
    // ✅ Syncs: _optstack_idx_post_price = 99.99
    
    OptStack::updateField('product_data', 'status', 'active', $post_id);
    // ✅ Updates: product_data['status']
    // ✅ Syncs: _optstack_idx_post_status = 'active'
    
    OptStack::updateField('product_data', 'description', 'Long text...', $post_id);
    // ✅ Updates: product_data['description']
    // ❌ NOT synced (not searchable)
    
    // Now you can query efficiently
    $expensive_products = new WP_Query([
        'post_type' => 'product',
        'meta_query' => [[
            'key' => '_optstack_idx_post_price',
            'value' => 50,
            'compare' => '>=',
            'type' => 'NUMERIC',
        ]],
    ]);
    
    $active_products = new WP_Query([
        'post_type' => 'product',
        'meta_query' => [[
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ]],
    ]);
}

/**
 * Example 8: Error Handling
 * 
 * Handle errors when updating fields.
 */
function example_error_handling() {
    $post_id = 123;
    
    // Check if update was successful
    $success = OptStack::updateField('product_data', 'price', 99.99, $post_id);
    
    if ($success) {
        // Update successful
        error_log("Price updated successfully");
    } else {
        // Update failed (stack not found, store not available, etc.)
        error_log("Failed to update price");
    }
    
    // Wrap in try-catch for safety
    try {
        OptStack::updateField('product_data', 'invalid.nested.path', 'value', $post_id);
    } catch (Exception $e) {
        error_log("Update error: " . $e->getMessage());
    }
}

/**
 * Example 9: Hook into Field Updates
 * 
 * Listen to field update events.
 */
function example_hook_listening() {
    // Hook into searchable field sync
    add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
        error_log(sprintf(
            "Stack '%s': Field '%s' updated to '%s' for object #%d",
            $stack->getId(),
            $fieldPath,
            $value,
            $objectId
        ));
        
        // Send notification, clear cache, trigger webhook, etc.
        if ($fieldPath === 'status' && $value === 'out_of_stock') {
            // Notify admin
            wp_mail(
                get_option('admin_email'),
                'Product Out of Stock',
                "Product #{$objectId} is now out of stock"
            );
        }
    }, 10, 4);
}

/**
 * Example 10: REST API Integration
 * 
 * Update fields via AJAX/REST API.
 */
function example_rest_integration() {
    // Register custom REST endpoint
    add_action('rest_api_init', function() {
        register_rest_route('myapp/v1', '/product/(?P<id>\d+)/price', [
            'methods' => 'POST',
            'callback' => function($request) {
                $post_id = $request['id'];
                $new_price = $request->get_param('price');
                
                // Validate
                if (!is_numeric($new_price) || $new_price < 0) {
                    return new WP_Error('invalid_price', 'Invalid price value', ['status' => 400]);
                }
                
                // Update field
                $success = OptStack::updateField('product_data', 'price', floatval($new_price), $post_id);
                
                if ($success) {
                    return [
                        'success' => true,
                        'message' => 'Price updated',
                        'price' => $new_price,
                    ];
                }
                
                return new WP_Error('update_failed', 'Failed to update price', ['status' => 500]);
            },
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            },
        ]);
    });
    
    // Frontend JavaScript
    /*
    fetch('/wp-json/myapp/v1/product/123/price', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify({ price: 99.99 })
    });
    */
}

/**
 * Performance Comparison
 */
function performance_comparison() {
    $post_id = 123;
    
    // OLD WAY (3 operations):
    // 1. Fetch entire data array
    $data = get_post_meta($post_id, 'product_data', true);
    // 2. Modify one field
    $data['price'] = 99.99;
    // 3. Save entire array back
    update_post_meta($post_id, 'product_data', $data);
    // 4. Manually sync searchable field (if you remember!)
    update_post_meta($post_id, '_optstack_idx_post_price', 99.99);
    
    // NEW WAY (1 operation):
    OptStack::updateField('product_data', 'price', 99.99, $post_id);
    // ✅ Updates data
    // ✅ Auto-syncs searchable fields
    // ✅ Atomic operation (no race conditions)
    // ✅ Cleaner code
}
