# updateField() Troubleshooting Guide

## Quick Fix Summary

**The issue was**: For `post_type`, `taxonomy`, and `user` context stacks, the store is not automatically bound during initialization. It needs the object ID to bind the store.

**The fix**: The `updateField()` method now automatically binds the store when you provide the object ID.

---

## ✅ Correct Usage

### For Post Type Stacks

```php
// Stack defined with forPostType()
OptStack::make('searchable_demo')
    ->forPostType('post')
    ->define(function($stack) {
        $stack->field('sku', [
            'type' => 'text',
            'searchable' => true,
        ]);
    })
    ->build();

// UPDATE: Object ID is REQUIRED (4th parameter)
OptStack::updateField('searchable_demo', 'sku', 'SKU__update', 1);
//                                                               ↑
//                                                           Post ID
```

### For Taxonomy Stacks

```php
// Stack defined with forTaxonomy()
OptStack::make('category_meta')
    ->forTaxonomy('category')
    ->define(...)
    ->build();

// UPDATE: Term ID is REQUIRED
OptStack::updateField('category_meta', 'color', '#FF5733', $term_id);
//                                                          ↑
//                                                      Term ID
```

### For User Stacks

```php
// Stack defined with forUser()
OptStack::make('user_settings')
    ->forUser()
    ->define(...)
    ->build();

// UPDATE: User ID is REQUIRED
OptStack::updateField('user_settings', 'preference', 'dark_mode', $user_id);
//                                                                 ↑
//                                                             User ID
```

### For Options Stacks (No Object ID Needed)

```php
// Stack defined with forOptions()
OptStack::make('site_settings')
    ->forOptions()
    ->define(...)
    ->build();

// UPDATE: No object ID needed for options
OptStack::updateField('site_settings', 'site_color', '#FF5733');
// Works without object ID because it's an options stack
```

---

## ❌ Common Mistakes

### Mistake 1: Missing Object ID

```php
// ❌ WRONG - Missing post ID for post_type stack
OptStack::updateField('searchable_demo', 'sku', 'SKU__update');
// Returns false - store cannot be bound without object ID

// ✅ CORRECT - Include post ID
OptStack::updateField('searchable_demo', 'sku', 'SKU__update', 1);
```

### Mistake 2: Using NULL as Object ID

```php
// ❌ WRONG - NULL object ID
OptStack::updateField('searchable_demo', 'sku', 'SKU__update', null);
// Returns false - same as not providing it

// ✅ CORRECT - Provide actual post ID
$post_id = 1; // or get_the_ID(), $post->ID, etc.
OptStack::updateField('searchable_demo', 'sku', 'SKU__update', $post_id);
```

### Mistake 3: Wrong Context Type

```php
// ❌ WRONG - Stack is for post_type, but using as options
$stack = OptStack::get('searchable_demo');
echo $stack->getContext(); // 'post_type'

// This won't work without object ID
OptStack::updateField('searchable_demo', 'sku', 'value');

// ✅ CORRECT - Provide object ID
OptStack::updateField('searchable_demo', 'sku', 'value', $post_id);
```

---

## 🔍 How to Debug

### Step 1: Check Stack Registration

```php
$stack = OptStack::get('searchable_demo');
if (!$stack) {
    echo "Stack not registered!";
    echo "Available: " . implode(', ', array_keys(OptStack::all()));
} else {
    echo "Stack found!";
    echo "Context: " . $stack->getContext();
    echo "Post Type: " . $stack->getPostType();
}
```

### Step 2: Check Object Exists

```php
$post_id = 1;
$post = get_post($post_id);
if (!$post) {
    echo "Post #{$post_id} does not exist!";
} else {
    echo "Post exists: {$post->post_title}";
}
```

### Step 3: Test Update

```php
$post_id = 1;
$success = OptStack::updateField('searchable_demo', 'sku', 'TEST_' . time(), $post_id);

if ($success) {
    echo "✅ Update successful!";
    
    // Verify in database
    $data = get_post_meta($post_id, 'searchable_demo', true);
    echo "Current value: " . $data['sku'];
    
    // Check indexed meta (if searchable)
    $indexed = get_post_meta($post_id, '_optstack_idx_post_sku', true);
    echo "Indexed value: {$indexed}";
} else {
    echo "❌ Update failed!";
}
```

### Step 4: Use Test Script

Copy `examples/test-updateField.php` to your theme and visit:
```
http://yoursite.local/?test_optstack_update=1
```

This runs comprehensive tests and shows detailed results.

---

## 🎯 Complete Working Example

```php
<?php
// Define stack on optstack_init hook
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->label('Product Data')
        ->define(function($stack) {
            $stack->field('sku', [
                'type' => 'text',
                'label' => 'SKU',
                'searchable' => true, // Enable WP_Query
            ]);
            
            $stack->field('price', [
                'type' => 'number',
                'label' => 'Price',
                'searchable' => true,
            ]);
            
            $stack->group('seo', function($group) {
                $group->field('title', [
                    'type' => 'text',
                    'searchable' => true,
                ]);
            });
        })
        ->build();
});

// Use it after init
add_action('wp_loaded', function() {
    $post_id = 1; // Your post ID
    
    // Update simple field
    $success1 = OptStack::updateField('product_data', 'sku', 'SKU-12345', $post_id);
    if ($success1) {
        echo "✅ SKU updated<br>";
    }
    
    // Update price
    $success2 = OptStack::updateField('product_data', 'price', 99.99, $post_id);
    if ($success2) {
        echo "✅ Price updated<br>";
    }
    
    // Update nested field
    $success3 = OptStack::updateField('product_data', 'seo.title', 'Product SEO Title', $post_id);
    if ($success3) {
        echo "✅ SEO title updated<br>";
    }
    
    // Verify all updates
    $data = get_post_meta($post_id, 'product_data', true);
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    
    // Check searchable fields
    echo "Indexed SKU: " . get_post_meta($post_id, '_optstack_idx_post_sku', true) . "<br>";
    echo "Indexed Price: " . get_post_meta($post_id, '_optstack_idx_post_price', true) . "<br>";
    echo "Indexed SEO: " . get_post_meta($post_id, '_optstack_idx_post_seo_title', true) . "<br>";
});
```

---

## 📋 Checklist for Working updateField()

Before calling `updateField()`, ensure:

- [ ] Stack is registered (use `optstack_init` hook)
- [ ] Stack context matches your use case (post_type/taxonomy/user/options)
- [ ] For post_type/taxonomy/user: Object ID is provided as 4th parameter
- [ ] Object (post/term/user) exists in database
- [ ] Field key is correct (check stack definition)
- [ ] Code runs after `optstack_ready` hook (or later)

---

## 🔧 Advanced: Hook into Updates

Monitor when fields are updated:

```php
// Log all searchable field updates
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    error_log(sprintf(
        "Updated %s::%s = %s for object #%d",
        $stack->getId(),
        $fieldPath,
        $value,
        $objectId
    ));
}, 10, 4);

// Send notification on specific field update
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    if ($stack->getId() === 'product_data' && $fieldPath === 'price') {
        // Price changed, clear cache
        wp_cache_delete("product_price_{$objectId}");
        
        // Log to custom table
        global $wpdb;
        $wpdb->insert('price_history', [
            'product_id' => $objectId,
            'new_price' => $value,
            'changed_at' => current_time('mysql'),
        ]);
    }
}, 10, 4);
```

---

## 💡 Tips

1. **Always provide object ID for post/term/user stacks** - It's required!

2. **Use dot notation for nested fields**:
   ```php
   OptStack::updateField('stack', 'group.field', 'value', $id);
   ```

3. **Check return value**:
   ```php
   if (!OptStack::updateField(...)) {
       error_log('Update failed!');
   }
   ```

4. **Searchable fields sync automatically** - No manual `update_post_meta()` needed!

5. **For multiple updates, use `saveData()` instead**:
   ```php
   // Don't do this:
   OptStack::updateField('stack', 'field1', 'val1', $id);
   OptStack::updateField('stack', 'field2', 'val2', $id);
   OptStack::updateField('stack', 'field3', 'val3', $id);
   
   // Do this instead:
   OptStack::saveData('stack', [
       'field1' => 'val1',
       'field2' => 'val2',
       'field3' => 'val3',
   ]);
   ```

---

## 🆘 Still Not Working?

1. **Enable WordPress debug mode**:
   ```php
   // wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Check debug.log**:
   ```
   wp-content/debug.log
   ```

3. **Run the test script**:
   ```
   examples/test-updateField.php
   ```

4. **Check database directly**:
   ```sql
   SELECT * FROM wp_postmeta WHERE post_id = 1 AND meta_key = 'searchable_demo';
   SELECT * FROM wp_postmeta WHERE post_id = 1 AND meta_key LIKE '_optstack_idx_%';
   ```

5. **Verify PHP version**:
   - Required: PHP 8.1+
   - Check: `php -v`

---

## 📝 Summary

**The Fix**: `updateField()` now automatically binds the store when you provide the object ID.

**What Changed**:
- ✅ No more "store not bound" errors
- ✅ Object ID automatically binds the correct store
- ✅ Works seamlessly with post_type/taxonomy/user stacks

**Key Requirement**: 
- **Always provide object ID** for post_type/taxonomy/user context stacks
- **Only options** stacks don't need object ID

**Example**:
```php
// ✅ CORRECT
OptStack::updateField('searchable_demo', 'sku', 'SKU__update', 1);
//                                                              ↑
//                                                         Required!
```
