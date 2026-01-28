<?php
/**
 * Test updateField() Method
 * 
 * This file helps test and debug the updateField() functionality.
 * Add this to your theme's functions.php or create a custom plugin to test.
 */

declare(strict_types=1);

use OptStack\OptStack;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test updateField with searchable_demo stack
 */
add_action('init', function() {
    // Only run if requested via query param
    if (!isset($_GET['test_optstack_update'])) {
        return;
    }

    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    echo '<h1>Testing OptStack updateField()</h1>';
    
    // Test 1: Check if stack exists
    echo '<h2>Test 1: Stack Registration</h2>';
    $stack = OptStack::get('searchable_demo');
    if ($stack) {
        echo '✅ Stack "searchable_demo" is registered<br>';
        echo 'Context: ' . $stack->getContext() . '<br>';
        echo 'Post Type: ' . $stack->getPostType() . '<br>';
    } else {
        echo '❌ Stack "searchable_demo" not found<br>';
        echo 'Available stacks: ' . implode(', ', array_keys(OptStack::all())) . '<br>';
        return;
    }
    
    // Test 2: Check if post exists
    echo '<h2>Test 2: Post Verification</h2>';
    $post_id = 1;
    $post = get_post($post_id);
    if ($post) {
        echo "✅ Post #{$post_id} exists: {$post->post_title}<br>";
    } else {
        echo "❌ Post #{$post_id} does not exist<br>";
        return;
    }
    
    // Test 3: Get current value
    echo '<h2>Test 3: Current Value</h2>';
    $current_data = get_post_meta($post_id, 'searchable_demo', true);
    echo '<pre>';
    echo 'Current data: ';
    var_dump($current_data);
    echo '</pre>';
    
    // Test 4: Update field
    echo '<h2>Test 4: Update Field</h2>';
    $new_value = 'SKU__' . time();
    echo "Updating 'sku' field to: {$new_value}<br>";
    
    $success = OptStack::updateField('searchable_demo', 'sku', $new_value, $post_id);
    
    if ($success) {
        echo '✅ updateField() returned true<br>';
    } else {
        echo '❌ updateField() returned false<br>';
    }
    
    // Test 5: Verify update
    echo '<h2>Test 5: Verify Update</h2>';
    $updated_data = get_post_meta($post_id, 'searchable_demo', true);
    echo '<pre>';
    echo 'Updated data: ';
    var_dump($updated_data);
    echo '</pre>';
    
    if (isset($updated_data['sku']) && $updated_data['sku'] === $new_value) {
        echo '✅ Field updated successfully in main data<br>';
    } else {
        echo '❌ Field NOT updated in main data<br>';
    }
    
    // Test 6: Check indexed meta
    echo '<h2>Test 6: Check Indexed Meta (Searchable Field)</h2>';
    $indexed_value = get_post_meta($post_id, '_optstack_idx_post_sku', true);
    echo "Indexed meta key: _optstack_idx_post_sku<br>";
    echo "Indexed value: ";
    var_dump($indexed_value);
    echo '<br>';
    
    if ($indexed_value === $new_value) {
        echo '✅ Searchable field synced successfully<br>';
    } else {
        echo '❌ Searchable field NOT synced<br>';
    }
    
    // Test 7: Query by indexed field
    echo '<h2>Test 7: Query by Indexed Field</h2>';
    $query = new WP_Query([
        'post_type' => 'post',
        'meta_query' => [[
            'key' => '_optstack_idx_post_sku',
            'value' => $new_value,
            'compare' => '=',
        ]],
    ]);
    
    echo "Found {$query->found_posts} post(s) with SKU: {$new_value}<br>";
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo "✅ Found post: #" . get_the_ID() . " - " . get_the_title() . "<br>";
        }
        wp_reset_postdata();
    }
    
    // Test 8: Update nested field
    echo '<h2>Test 8: Update Nested Field</h2>';
    $seo_title = 'SEO Title ' . time();
    echo "Updating 'seo.title' field to: {$seo_title}<br>";
    
    $success2 = OptStack::updateField('searchable_demo', 'seo.title', $seo_title, $post_id);
    
    if ($success2) {
        echo '✅ Nested field updateField() returned true<br>';
        
        $updated_data2 = get_post_meta($post_id, 'searchable_demo', true);
        if (isset($updated_data2['seo']['title']) && $updated_data2['seo']['title'] === $seo_title) {
            echo '✅ Nested field updated successfully<br>';
        }
        
        // Check indexed meta for nested field
        $indexed_seo = get_post_meta($post_id, '_optstack_idx_post_seo_title', true);
        if ($indexed_seo === $seo_title) {
            echo '✅ Nested searchable field synced successfully<br>';
        } else {
            echo '❌ Nested searchable field NOT synced<br>';
            echo "Indexed value: ";
            var_dump($indexed_seo);
            echo '<br>';
        }
    } else {
        echo '❌ Nested field updateField() returned false<br>';
    }
    
    // Summary
    echo '<h2>Summary</h2>';
    echo '<pre>';
    echo 'Final data structure:' . "\n";
    print_r(get_post_meta($post_id, 'searchable_demo', true));
    echo "\n\nIndexed meta keys:\n";
    echo '_optstack_idx_post_sku = ' . get_post_meta($post_id, '_optstack_idx_post_sku', true) . "\n";
    echo '_optstack_idx_post_seo_title = ' . get_post_meta($post_id, '_optstack_idx_post_seo_title', true) . "\n";
    echo '</pre>';
    
    echo '<hr><p><a href="' . admin_url() . '">← Back to Dashboard</a></p>';
    
    exit;
});

/**
 * Add admin notice with test link
 */
add_action('admin_notices', function() {
    $test_url = add_query_arg('test_optstack_update', '1', home_url('/'));
    ?>
    <div class="notice notice-info">
        <p>
            <strong>OptStack updateField Test:</strong> 
            <a href="<?php echo esc_url($test_url); ?>" target="_blank">Run Test</a>
        </p>
    </div>
    <?php
});
