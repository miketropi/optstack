# updateField() Method - Quick Single Field Updates

> **New Feature**: Update single field values with automatic searchable field synchronization

---

## Overview

The `updateField()` method provides a convenient way to update a single field value in a Stack without needing to fetch, modify, and save the entire data array. It automatically syncs searchable fields for efficient `WP_Query` operations.

## Problem It Solves

### Before (The Old Way)

```php
// Update a field - requires 3 steps
$data = get_post_meta($post_id, 'product_data', true);  // 1. Fetch
$data['price'] = 99.99;                                  // 2. Modify
update_post_meta($post_id, 'product_data', $data);      // 3. Save

// Problem: Searchable fields NOT synced automatically!
// You'd have to manually do:
update_post_meta($post_id, '_optstack_idx_post_price', 99.99);
```

**Issues:**
- ❌ Verbose (3+ lines of code)
- ❌ Race conditions (fetch-modify-save pattern)
- ❌ Searchable fields require manual sync
- ❌ Easy to forget indexed meta updates

### After (The New Way)

```php
// Update a field - single call
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// ✅ Updates main data
// ✅ Auto-syncs searchable fields
// ✅ Atomic operation
// ✅ Clean, simple code
```

---

## Usage

### Basic Syntax

```php
// Via OptStack facade
OptStack::updateField(
    string $stackId,    // Stack identifier
    string $key,        // Field key (supports dot notation)
    mixed $value,       // New value
    ?int $objectId      // Object ID (post/term/user) - REQUIRED for post_type/taxonomy/user contexts
): bool

// Via Stack instance
$stack = OptStack::get('stack_id');
$stack->updateField(
    string $key,        // Field key
    mixed $value,       // New value
    ?int $objectId      // Object ID
): bool
```

### Simple Field Update

```php
// Update a root-level field (object ID is REQUIRED)
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// IMPORTANT: For post_type, taxonomy, and user contexts, 
// the object ID (4th parameter) is REQUIRED
// The store will be automatically bound to this object ID

// Update boolean field
OptStack::updateField('product_data', 'featured', true, $post_id);

// Update select field
OptStack::updateField('product_data', 'status', 'active', $post_id);
```

### Nested Field Update (Dot Notation)

```php
// Update field inside a group
OptStack::updateField('product_data', 'pricing.regular_price', 149.99, $post_id);

// Update deeply nested field
OptStack::updateField('product_data', 'seo.meta.title', 'Product Title', $post_id);

// Multiple levels
OptStack::updateField('settings', 'social.links.twitter', 'https://x.com/...', $post_id);
```

---

## Searchable Field Auto-Sync

When a field is marked as `searchable: true`, the `updateField()` method automatically syncs the indexed meta key.

### How It Works

```php
// Define stack with searchable field
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->define(function($stack) {
            $stack->field('price', [
                'type' => 'number',
                'searchable' => true,  // Mark as searchable
            ]);
        })
        ->build();
});

// Update the field
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// What happens:
// 1. Updates: product_data['price'] = 99.99
// 2. Auto-syncs: _optstack_idx_post_price = 99.99
// 3. Fires: 'optstack_searchable_field_synced' action

// Now WP_Query works efficiently
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [[
        'key' => '_optstack_idx_post_price',
        'value' => 50,
        'compare' => '>=',
        'type' => 'NUMERIC',
    ]],
]);
```

### Searchable Field Requirements

For a field to be synced:
- ✅ Field must be marked `searchable: true`
- ✅ Field type must be scalar (text, number, select, toggle, etc.)
- ✅ Context must be post, term, or user (not options)
- ✅ Object ID must be provided

**Fields that CANNOT be searchable:**
- ❌ Fields in repeatable groups (values are arrays)
- ❌ Complex fields (wysiwyg, code, typography)
- ❌ Fields in options context (no `meta_query` support)

---

## Common Use Cases

### 1. Update After Purchase (E-commerce)

```php
add_action('woocommerce_payment_complete', function($order_id) {
    $order = wc_get_order($order_id);
    
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $current_stock = OptStack::getData('product_data', 'stock', 0);
        $new_stock = max(0, $current_stock - $item->get_quantity());
        
        OptStack::updateField('product_data', 'stock', $new_stock, $product_id);
        // Searchable field synced for inventory queries
    }
});
```

### 2. Auto-Update Timestamps

```php
add_action('post_updated', function($post_id) {
    if (get_post_type($post_id) === 'product') {
        OptStack::updateField('product_data', 'last_modified', time(), $post_id);
    }
});
```

### 3. Bulk Status Updates

```php
// Mark all draft products as pending review
$products = get_posts([
    'post_type' => 'product',
    'post_status' => 'draft',
    'posts_per_page' => -1,
]);

foreach ($products as $product) {
    OptStack::updateField('product_data', 'status', 'pending_review', $product->ID);
}
```

### 4. Conditional Updates

```php
$post_id = 123;

// Get current price
$price = OptStack::getData('product_data', 'price', 0);

// Apply discount if over $100
if ($price > 100) {
    $discounted = $price * 0.8;
    OptStack::updateField('product_data', 'sale_price', $discounted, $post_id);
}
```

### 5. REST API Integration

```php
add_action('rest_api_init', function() {
    register_rest_route('myapp/v1', '/product/(?P<id>\d+)/price', [
        'methods' => 'POST',
        'callback' => function($request) {
            $post_id = $request['id'];
            $price = floatval($request->get_param('price'));
            
            $success = OptStack::updateField('product_data', 'price', $price, $post_id);
            
            return ['success' => $success];
        },
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ]);
});
```

---

## Hooks & Events

### `optstack_searchable_field_synced`

Fires after a searchable field has been synced.

```php
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    // $stack - Stack instance
    // $fieldPath - Field path (e.g., 'price' or 'seo.title')
    // $value - New value
    // $objectId - Post/term/user ID
    
    error_log("Field {$fieldPath} updated to {$value} for #{$objectId}");
    
    // Send notifications, clear cache, trigger webhooks, etc.
}, 10, 4);
```

### Example: Send Alert on Low Stock

```php
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    if ($stack->getId() === 'product_data' && $fieldPath === 'stock') {
        if ($value < 5) {
            wp_mail(
                get_option('admin_email'),
                'Low Stock Alert',
                "Product #{$objectId} has only {$value} items left"
            );
        }
    }
}, 10, 4);
```

---

## Performance Benefits

### Atomic Operations

```php
// OLD WAY: Race condition possible
$data = get_post_meta($post_id, 'stack', true);  // Read
$data['count'] = $data['count'] + 1;             // Modify
update_post_meta($post_id, 'stack', $data);      // Write
// Problem: Another process might update between read and write

// NEW WAY: Atomic update at store level
OptStack::updateField('stack', 'count', $new_value, $post_id);
// Safer for concurrent updates
```

### Reduced Memory Usage

```php
// OLD WAY: Load entire data array (could be large)
$data = get_post_meta($post_id, 'product_data', true);
// If product_data has 50+ fields with images, text, etc., this loads everything

// NEW WAY: Only updates what's needed
OptStack::updateField('product_data', 'price', 99.99, $post_id);
// More efficient memory usage
```

---

## Error Handling

### Return Value

The method returns `bool`:
- `true` - Update successful
- `false` - Update failed (stack not found, store unavailable, etc.)

```php
$success = OptStack::updateField('product_data', 'price', 99.99, $post_id);

if (!$success) {
    error_log("Failed to update price");
    // Handle error
}
```

### Common Failure Reasons

1. **Stack not found**
   ```php
   OptStack::updateField('nonexistent_stack', 'field', 'value', 123);
   // Returns false
   ```

2. **No store bound**
   ```php
   // Stack defined but store not initialized
   // Returns false
   ```

3. **Invalid object ID**
   ```php
   // For post/term/user contexts without object ID
   OptStack::updateField('product_data', 'price', 99.99, null);
   // Returns false (needs object ID)
   ```

---

## API Reference

### OptStack Facade

```php
/**
 * Update a single field value in a stack.
 *
 * @param string $id Stack identifier
 * @param string $key Field key (supports dot notation)
 * @param mixed $value New value
 * @param int|null $objectId Object ID (post/term/user)
 * @return bool Success status
 */
OptStack::updateField(string $id, string $key, mixed $value, ?int $objectId = null): bool
```

### Stack Instance

```php
/**
 * Update a single field value.
 *
 * @param string $key Field key (supports dot notation)
 * @param mixed $value New value
 * @param int|null $objectId Object ID (post/term/user)
 * @return bool Success status
 */
$stack->updateField(string $key, mixed $value, ?int $objectId = null): bool
```

### IndexedMetaManager

```php
/**
 * Sync indexed meta for a single field.
 *
 * @param Stack $stack Stack definition
 * @param string $fieldPath Field path (e.g., 'price' or 'seo.title')
 * @param mixed $value Field value
 * @param int $objectId Object ID
 * @return bool Whether the field was synced
 */
$manager->syncSingleField(Stack $stack, string $fieldPath, mixed $value, int $objectId): bool
```

---

## Migration Guide

### Updating Existing Code

**Before:**
```php
// Get all data
$data = get_post_meta($post_id, 'product_data', true);

// Update field
$data['price'] = 99.99;
$data['status'] = 'active';

// Save back
update_post_meta($post_id, 'product_data', $data);

// Manually sync searchable fields (if you remember!)
update_post_meta($post_id, '_optstack_idx_post_price', 99.99);
update_post_meta($post_id, '_optstack_idx_post_status', 'active');
```

**After:**
```php
// Update fields (auto-sync)
OptStack::updateField('product_data', 'price', 99.99, $post_id);
OptStack::updateField('product_data', 'status', 'active', $post_id);
```

---

## Best Practices

### ✅ DO

1. **Use for single field updates**
   ```php
   OptStack::updateField('stack', 'field', 'value', $id);
   ```

2. **Use dot notation for nested fields**
   ```php
   OptStack::updateField('stack', 'group.field', 'value', $id);
   ```

3. **Provide object ID for post/term/user contexts**
   ```php
   OptStack::updateField('product_data', 'price', 99.99, $post_id);
   ```

4. **Check return value**
   ```php
   if (!OptStack::updateField(...)) {
       // Handle error
   }
   ```

### ❌ DON'T

1. **Don't use for multiple field updates**
   ```php
   // Bad - inefficient
   OptStack::updateField('stack', 'field1', 'value1', $id);
   OptStack::updateField('stack', 'field2', 'value2', $id);
   OptStack::updateField('stack', 'field3', 'value3', $id);
   
   // Good - use saveData instead
   OptStack::saveData('stack', [
       'field1' => 'value1',
       'field2' => 'value2',
       'field3' => 'value3',
   ]);
   ```

2. **Don't forget object ID for post/term/user contexts**
   ```php
   // Bad - won't sync searchable fields
   OptStack::updateField('product_data', 'price', 99.99, null);
   
   // Good
   OptStack::updateField('product_data', 'price', 99.99, $post_id);
   ```

---

## Summary

The `updateField()` method provides a **simpler, safer, and more efficient** way to update single field values with automatic searchable field synchronization.

**Key Benefits:**
- ✅ **Simpler**: One-line updates
- ✅ **Safer**: Atomic operations, no race conditions
- ✅ **Auto-sync**: Searchable fields synced automatically
- ✅ **Nested**: Dot notation support
- ✅ **Efficient**: Only updates what changed
- ✅ **Cleaner**: More readable code

**When to Use:**
- Updating a single field value
- Triggered by WordPress hooks/events
- REST API endpoints
- Bulk operations on single fields
- Conditional field updates

**When NOT to Use:**
- Updating multiple fields at once (use `saveData()` instead)
- Complex transformations requiring all data
- Initial data population (use `saveData()` instead)

---

For more examples, see:
- `examples/updateField-usage.php`
- `documents/FLOW.md` - Complete documentation
- `README.md` - Quick start guide
