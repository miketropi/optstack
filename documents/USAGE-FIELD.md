# OptStack Field Usage Guide

This document provides a comprehensive guide on how to use fields in OptStack. Fields are the building blocks of your options pages, meta boxes, and custom data structures.

---

## Table of Contents

- [Basic Field Syntax](#basic-field-syntax)
- [Common Field Properties](#common-field-properties)
- [Available Field Types](#available-field-types)
- [Conditional Logic](#conditional-logic)
- [Field Attributes](#field-attributes)
- [Retrieving Field Values](#retrieving-field-values)
  - [Using OptStack::getField()](#using-optstackgetfield-recommended)
  - [Using OptStack::getData()](#using-optstackgetdata)
  - [Using OptStack::updateField()](#using-optstackupdatefield)
  - [Direct WordPress Functions](#direct-wordpress-functions-alternative)
- [Best Practices](#best-practices)
- [Related Documentation](#related-documentation)

---

## Basic Field Syntax

Fields are defined using the `field()` method on a stack, tab, or group:

```php
$stack->field('field_id', [
    'type' => 'text',
    'label' => 'Field Label',
    'default' => 'Default value',
    'description' => 'Help text for the field',
]);
```

### Field in a Tab

```php
$stack->tab('general', function ($tab) {
    $tab->field('site_title', [
        'type' => 'text',
        'label' => 'Site Title',
    ]);
});
```

### Field in a Group

```php
$stack->group('settings', function ($group) {
    $group->field('enable_feature', [
        'type' => 'toggle',
        'label' => 'Enable Feature',
    ]);
}, ['label' => 'Settings']);
```

---

## Common Field Properties

All fields share these common properties:

| Property | Type | Description |
|----------|------|-------------|
| `type` | string | **Required.** The field type (e.g., 'text', 'number', 'toggle') |
| `label` | string | The field label displayed to users |
| `description` | string | Help text shown below the field |
| `default` | mixed | Default value when no value is saved |
| `attributes` | array | Additional field-specific attributes |
| `conditions` | array | Conditional logic rules for showing/hiding the field |

### Example with All Common Properties

```php
$stack->field('example_field', [
    'type' => 'text',
    'label' => 'Example Field',
    'description' => 'This is a helpful description',
    'default' => 'Default text',
    'attributes' => [
        'placeholder' => 'Enter text...',
        'maxlength' => 100,
    ],
    'conditions' => [
        ['field' => 'enable_feature', 'operator' => '==', 'value' => true],
    ],
]);
```

---

## Available Field Types

OptStack supports the following field types. Click each link for detailed documentation:

### Input Fields
- **[text](./fields/text.md)** - Single-line text input
- **[textarea](./fields/textarea.md)** - Multi-line text input
- **[number](./fields/number.md)** - Numeric input with min/max/step
- **[email](./fields/email.md)** - Email address input
- **[url](./fields/url.md)** - URL input with validation

### Selection Fields
- **[select](./fields/select.md)** - Dropdown select menu
- **[radio](./fields/radio.md)** - Radio button group
- **[radio-image](./fields/radio-image.md)** - Visual radio buttons with images
- **[checkbox-group](./fields/checkbox-group.md)** - Multiple checkbox selection
- **[toggle](./fields/toggle.md)** - On/off switch

### Visual Fields
- **[color](./fields/color.md)** - Color picker with optional alpha
- **[media](./fields/media.md)** - WordPress media library picker
- **[range](./fields/range.md)** - Slider range input

### Rich Content Fields
- **[wysiwyg](./fields/wysiwyg.md)** - WordPress visual editor
- **[code](./fields/code.md)** - Code editor with syntax highlighting
- **[typography](./fields/typography.md)** - Complete typography controls

### Advanced Fields
- **[visual_builder](./fields/visual-builder.md)** - Drag-and-drop visual content builder

---

## Conditional Logic

Fields can be shown or hidden based on other field values using the `conditions` property:

### Basic Condition

```php
$stack->field('message', [
    'type' => 'textarea',
    'label' => 'Message',
    'conditions' => [
        ['field' => 'enable_message', 'operator' => '==', 'value' => true],
    ],
]);
```

### Available Operators

| Operator | Description |
|----------|-------------|
| `==` | Equal to |
| `!=` | Not equal to |
| `>` | Greater than |
| `<` | Less than |
| `>=` | Greater than or equal |
| `<=` | Less than or equal |
| `contains` | Contains value (for arrays) |
| `not_contains` | Does not contain value |

### Multiple Conditions (AND)

```php
'conditions' => [
    ['field' => 'enable_feature', 'operator' => '==', 'value' => true],
    ['field' => 'feature_type', 'operator' => '!=', 'value' => 'none'],
],
```

### Referencing Fields in Groups

When referencing a field inside a group from the same group, use just the field name:

```php
$stack->group('settings', function ($group) {
    $group->field('enable', [
        'type' => 'toggle',
        'label' => 'Enable',
    ]);
    
    $group->field('value', [
        'type' => 'text',
        'label' => 'Value',
        'conditions' => [
            ['field' => 'enable', 'operator' => '==', 'value' => true],
        ],
    ]);
});
```

---

## Field Attributes

The `attributes` property allows you to pass additional configuration specific to each field type. Common attributes include:

### Input Attributes

```php
'attributes' => [
    'placeholder' => 'Enter value...',
    'maxlength' => 100,
    'minlength' => 5,
    'readonly' => true,
    'disabled' => false,
]
```

### Number Attributes

```php
'attributes' => [
    'min' => 0,
    'max' => 100,
    'step' => 5,
    'suffix' => 'px',
    'prefix' => '$',
]
```

### Media Attributes

```php
'attributes' => [
    'allowedTypes' => ['image'],
    'buttonText' => 'Select Image',
    'multiple' => false,
]
```

See individual field documentation for field-specific attributes.

---

## Retrieving Field Values

### Using OptStack::getField() (Recommended)

The `OptStack::getField()` method is the recommended way to retrieve single field values. It supports dot notation for nested fields and automatically binds the store for post/term/user contexts.

```php
// Basic syntax
OptStack::getField(
    string $stackId,    // Stack identifier
    string $key,        // Field key (supports dot notation)
    mixed $default,     // Default value if not found
    ?int $objectId      // Object ID (required for post/term/user contexts)
): mixed
```

#### For Options Pages

```php
// Simple field
$site_name = OptStack::getField('site_settings', 'site_name', 'My Site');

// Nested field in a group (dot notation)
$logo = OptStack::getField('theme_options', 'identity.site_logo', '');
$primary_color = OptStack::getField('theme_options', 'colors.primary', '#3b82f6');
$sticky = OptStack::getField('theme_options', 'header_layout.sticky', true);

// Deeply nested field
$h1_size = OptStack::getField('theme_options', 'typography.heading_sizes.h1_size', 48);
```

#### For Post Meta

```php
// Simple field
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Nested field
$meta_title = OptStack::getField('product_data', 'seo.meta_title', '', $post_id);

// Multiple fields
$regular_price = OptStack::getField('product_data', 'pricing.regular_price', 0, $post_id);
$sale_price = OptStack::getField('product_data', 'pricing.sale_price', 0, $post_id);
```

#### For Term Meta

```php
// Simple field
$icon = OptStack::getField('category_settings', 'icon', '', $term_id);

// Nested field
$display_mode = OptStack::getField('category_settings', 'display.mode', 'grid', $term_id);
```

#### For User Meta

```php
// Simple field
$bio = OptStack::getField('user_profile', 'bio', '', $user_id);

// Nested field
$twitter = OptStack::getField('user_profile', 'social.twitter', '', $user_id);
```

---

### Using OptStack::getData()

Use `getData()` when you need multiple fields at once:

```php
// Get all data from a stack
$data = OptStack::getData('theme_options');

// Get specific key (simple, not dot notation)
$value = OptStack::getData('theme_options', 'colors', []);

// Access nested values from result
$primary = $data['colors']['primary'] ?? '#3b82f6';
$secondary = $data['colors']['secondary'] ?? '#8b5cf6';
```

---

### Using OptStack::updateField()

Update a single field value with automatic searchable field sync:

```php
// Basic syntax
OptStack::updateField(
    string $stackId,    // Stack identifier
    string $key,        // Field key (supports dot notation)
    mixed $value,       // New value
    ?int $objectId      // Object ID (required for post/term/user contexts)
): bool

// Examples
OptStack::updateField('product_data', 'price', 99.99, $post_id);
OptStack::updateField('product_data', 'seo.meta_title', 'New Title', $post_id);
OptStack::updateField('theme_options', 'colors.primary', '#3b82f6');
```

---

### Direct WordPress Functions (Alternative)

You can also use native WordPress functions directly:

#### For Options Pages

```php
// Get all options
$options = get_option('your_stack_id', []);

// Get specific value
$value = $options['field_id'] ?? 'default';

// For grouped fields
$grouped_value = $options['group_id']['field_id'] ?? 'default';
```

#### Custom Dot Notation Helper

```php
function my_option(string $key, mixed $default = null): mixed
{
    $options = get_option('my_options', []);
    
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

// Usage
$logo = my_option('identity.site_logo');
$sticky = my_option('header_layout.sticky', true);
```

#### For Post Meta

```php
// Get post meta
$value = get_post_meta($post_id, 'field_id', true);
```

#### For Term Meta

```php
// Get term meta
$value = get_term_meta($term_id, 'field_id', true);
```

#### For User Meta

```php
// Get user meta
$value = get_user_meta($user_id, 'field_id', true);
```

---

### Method Comparison

| Method | Best For | Dot Notation | Auto Store Binding |
|--------|----------|--------------|-------------------|
| `OptStack::getField()` | Single field retrieval | ✅ Yes | ✅ Yes |
| `OptStack::updateField()` | Single field update | ✅ Yes | ✅ Yes |
| `get_option()` / `get_post_meta()` | Direct WP access | ❌ No | N/A |

---

## Best Practices

1. **Use descriptive field IDs**: Use snake_case and be descriptive (e.g., `header_background_color` not `hbc`)

2. **Always provide defaults**: Set sensible default values to prevent empty states

3. **Group related fields**: Use groups to organize related fields together

4. **Use descriptions wisely**: Provide helpful context without being verbose

5. **Validate on retrieval**: Always validate field values when using them in templates

6. **Use conditional logic**: Hide irrelevant fields to simplify the UI

---

## Related Documentation

- [API-REFERENCE.md](./API-REFERENCE.md) - Complete OptStack API reference
- [GET_FIELD_FEATURE.md](./GET_FIELD_FEATURE.md) - Detailed getField() documentation
- [UPDATE_FIELD_FEATURE.md](./UPDATE_FIELD_FEATURE.md) - Detailed updateField() documentation
- [Field Types Reference](./fields/) - Individual field documentation
- [EXAM2_README.md](../examples/EXAM2_README.md) - Complete theme options example
- [basic-usage.php](../examples/basic-usage.php) - Code examples
