# getField() Method - Quick Single Field Retrieval

> **New Feature**: Retrieve single field values with automatic context binding and dot notation support

---

## Overview

The `getField()` method provides a convenient way to retrieve a single field value from a Stack without needing to fetch the entire data array. It pairs with `updateField()` for a symmetric API.

## Problem It Solves

### Before (The Old Way)

```php
// Get a field - requires multiple steps
$data = get_post_meta($post_id, 'product_data', true);  // 1. Fetch all data
$price = isset($data['price']) ? $data['price'] : 0;   // 2. Extract with fallback

// For nested fields - even more complex
$data = get_post_meta($post_id, 'product_data', true);
$regular_price = isset($data['pricing']['regular_price']) 
    ? $data['pricing']['regular_price'] 
    : 0;
```

**Issues:**
- Verbose code for simple retrieval
- Manual null/isset checks
- No dot notation for nested fields
- Loads entire data array into memory

### After (The New Way)

```php
// Get a field - single call
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Nested field with dot notation
$regular_price = OptStack::getField('product_data', 'pricing.regular_price', 0, $post_id);

// Clean, simple, efficient
```

---

## Usage

### Basic Syntax

```php
// Via OptStack facade
OptStack::getField(
    string $stackId,    // Stack identifier
    string $key,        // Field key (supports dot notation)
    mixed $default,     // Default value if field not found
    ?int $objectId      // Object ID (post/term/user) - REQUIRED for post_type/taxonomy/user contexts
): mixed

// Via Stack instance
$stack = OptStack::get('stack_id');
$stack->getField(
    string $key,        // Field key
    mixed $default,     // Default value
    ?int $objectId      // Object ID
): mixed
```

### Simple Field Retrieval

```php
// Get a root-level field (object ID is REQUIRED for post/term/user contexts)
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Get with string default
$status = OptStack::getField('product_data', 'status', 'draft', $post_id);

// Get boolean field
$featured = OptStack::getField('product_data', 'featured', false, $post_id);

// Get array field
$tags = OptStack::getField('product_data', 'tags', [], $post_id);
```

### Nested Field Retrieval (Dot Notation)

```php
// Get field inside a group
$regular_price = OptStack::getField('product_data', 'pricing.regular_price', 0, $post_id);

// Get deeply nested field
$meta_title = OptStack::getField('product_data', 'seo.meta.title', '', $post_id);

// Get nested with array default
$colors = OptStack::getField('settings', 'theme.colors.palette', [], $post_id);
```

### Options Context (No Object ID Needed)

```php
// For options stacks, object ID is not required
$site_name = OptStack::getField('site_settings', 'identity.site_name', 'My Site');

$primary_color = OptStack::getField('theme_options', 'colors.primary', '#3b82f6');
```

---

## Comparison: getField vs getData

| Feature | `getField()` | `getData()` |
|---------|--------------|-------------|
| Single field | ✅ Optimized | Works but fetches all |
| Nested fields | ✅ Dot notation | Manual array access |
| Object ID binding | ✅ Auto-binds store | Requires pre-bound store |
| Default value | ✅ Built-in | ✅ Built-in |
| Return type | Single value | Array or single value |

### When to Use Each

```php
// Use getField() for single field retrieval
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Use getData() for multiple fields or entire data
$data = OptStack::getData('product_data');
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$status = $data['status'] ?? 'draft';
```

---

## Common Use Cases

### 1. Display Product Information

```php
function display_product_price($post_id) {
    $price = OptStack::getField('product_data', 'price', 0, $post_id);
    $sale_price = OptStack::getField('product_data', 'sale_price', 0, $post_id);
    
    if ($sale_price > 0 && $sale_price < $price) {
        echo '<del>$' . number_format($price, 2) . '</del>';
        echo '<ins>$' . number_format($sale_price, 2) . '</ins>';
    } else {
        echo '$' . number_format($price, 2);
    }
}
```

### 2. Conditional Logic Based on Field Value

```php
function maybe_show_banner($post_id) {
    $show_banner = OptStack::getField('page_settings', 'header.show_banner', false, $post_id);
    
    if ($show_banner) {
        $banner_text = OptStack::getField('page_settings', 'header.banner_text', '', $post_id);
        $banner_color = OptStack::getField('page_settings', 'header.banner_color', '#3b82f6', $post_id);
        
        echo '<div class="banner" style="background: ' . esc_attr($banner_color) . '">';
        echo esc_html($banner_text);
        echo '</div>';
    }
}
```

### 3. Theme Customization

```php
function get_theme_colors() {
    return [
        'primary' => OptStack::getField('theme_options', 'colors.primary', '#3b82f6'),
        'secondary' => OptStack::getField('theme_options', 'colors.secondary', '#8b5cf6'),
        'accent' => OptStack::getField('theme_options', 'colors.accent', '#10b981'),
        'text' => OptStack::getField('theme_options', 'colors.text', '#111827'),
    ];
}
```

### 4. REST API Response

```php
add_action('rest_api_init', function() {
    register_rest_route('myapp/v1', '/product/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => function($request) {
            $post_id = $request['id'];
            
            return [
                'id' => $post_id,
                'price' => OptStack::getField('product_data', 'price', 0, $post_id),
                'stock' => OptStack::getField('product_data', 'stock', 0, $post_id),
                'status' => OptStack::getField('product_data', 'status', 'draft', $post_id),
                'seo' => [
                    'title' => OptStack::getField('product_data', 'seo.title', '', $post_id),
                    'description' => OptStack::getField('product_data', 'seo.description', '', $post_id),
                ],
            ];
        },
        'permission_callback' => '__return_true',
    ]);
});
```

### 5. Shortcode with Field Value

```php
add_shortcode('product_price', function($atts) {
    $atts = shortcode_atts([
        'id' => get_the_ID(),
        'format' => 'currency',
    ], $atts);
    
    $price = OptStack::getField('product_data', 'price', 0, intval($atts['id']));
    
    if ($atts['format'] === 'currency') {
        return '$' . number_format($price, 2);
    }
    
    return $price;
});

// Usage: [product_price id="123"]
```

### 6. Check Feature Availability

```php
function is_feature_enabled($feature) {
    return OptStack::getField('site_settings', "features.{$feature}", false);
}

// Usage
if (is_feature_enabled('comments')) {
    comments_template();
}

if (is_feature_enabled('social_sharing')) {
    display_social_buttons();
}
```

---

## Symmetric API with updateField

The `getField()` and `updateField()` methods provide a symmetric API:

```php
// Read a field
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Update a field
OptStack::updateField('product_data', 'price', $price * 1.1, $post_id);

// Read nested field
$title = OptStack::getField('product_data', 'seo.title', '', $post_id);

// Update nested field
OptStack::updateField('product_data', 'seo.title', 'New Title', $post_id);
```

---

## Error Handling

### Return Value

The method returns:
- The field value if found
- The default value if field not found or stack unavailable

```php
// Field exists
$price = OptStack::getField('product_data', 'price', 0, $post_id);
// Returns: 99.99

// Field doesn't exist
$missing = OptStack::getField('product_data', 'nonexistent', 'default', $post_id);
// Returns: 'default'

// Stack doesn't exist
$invalid = OptStack::getField('nonexistent_stack', 'field', 'default', $post_id);
// Returns: 'default'
```

### Null vs Default

```php
// If field is explicitly null, returns null (not default)
// Example: field value is null
$value = OptStack::getField('stack', 'nullable_field', 'default', $id);
// Returns: null (the actual value), not 'default'

// If field doesn't exist, returns default
$value = OptStack::getField('stack', 'missing_field', 'default', $id);
// Returns: 'default'
```

---

## API Reference

### OptStack Facade

```php
/**
 * Get a single field value from a stack.
 *
 * @param string $id Stack identifier
 * @param string $key Field key (supports dot notation)
 * @param mixed $default Default value if field not found
 * @param int|null $objectId Object ID (post/term/user)
 * @return mixed Field value or default
 */
OptStack::getField(string $id, string $key, mixed $default = null, ?int $objectId = null): mixed
```

### Stack Instance

```php
/**
 * Get a single field value.
 *
 * @param string $key Field key (supports dot notation)
 * @param mixed $default Default value if not found
 * @param int|null $objectId Object ID (post/term/user)
 * @return mixed Field value or default
 */
$stack->getField(string $key, mixed $default = null, ?int $objectId = null): mixed
```

---

## Best Practices

### DO

1. **Use for single field retrieval**
   ```php
   $price = OptStack::getField('stack', 'price', 0, $post_id);
   ```

2. **Always provide meaningful defaults**
   ```php
   $status = OptStack::getField('stack', 'status', 'draft', $post_id);
   $enabled = OptStack::getField('stack', 'enabled', false, $post_id);
   $items = OptStack::getField('stack', 'items', [], $post_id);
   ```

3. **Use dot notation for nested fields**
   ```php
   $title = OptStack::getField('stack', 'seo.meta.title', '', $post_id);
   ```

4. **Provide object ID for post/term/user contexts**
   ```php
   $price = OptStack::getField('product_data', 'price', 0, $post_id);
   ```

### DON'T

1. **Don't use for multiple field retrieval**
   ```php
   // Bad - multiple calls
   $price = OptStack::getField('stack', 'price', 0, $id);
   $stock = OptStack::getField('stack', 'stock', 0, $id);
   $status = OptStack::getField('stack', 'status', '', $id);
   
   // Good - use getData for multiple fields
   $data = OptStack::getData('stack');
   $price = $data['price'] ?? 0;
   $stock = $data['stock'] ?? 0;
   $status = $data['status'] ?? '';
   ```

2. **Don't forget object ID for post/term/user contexts**
   ```php
   // Bad - no object ID
   $price = OptStack::getField('product_data', 'price', 0);
   
   // Good
   $price = OptStack::getField('product_data', 'price', 0, $post_id);
   ```

3. **Don't use without a default for critical values**
   ```php
   // Bad - no default
   $price = OptStack::getField('stack', 'price', null, $id);
   echo '$' . $price; // Could be null!
   
   // Good
   $price = OptStack::getField('stack', 'price', 0, $id);
   echo '$' . number_format($price, 2);
   ```

---

## Summary

The `getField()` method provides a **simple, clean, and efficient** way to retrieve single field values with automatic context binding and dot notation support.

**Key Benefits:**
- **Simpler**: One-line retrieval with default value
- **Nested**: Dot notation for nested fields
- **Symmetric**: Pairs with `updateField()` for consistent API
- **Context-aware**: Auto-binds store for post/term/user contexts
- **Type-safe**: Returns proper types, not always arrays

**When to Use:**
- Retrieving a single field value
- Conditional logic based on field values
- Templates and shortcodes
- REST API responses
- Theme customization

**When NOT to Use:**
- Retrieving multiple fields at once (use `getData()` instead)
- Need entire data structure (use `getData()` instead)

---

## Related Documentation

- `UPDATE_FIELD_FEATURE.md` - Companion method for updating fields
- `API-REFERENCE.md` - Complete OptStack API reference
- `USAGE-FIELD.md` - Field definition guide
- `FLOW.md` - Complete documentation
