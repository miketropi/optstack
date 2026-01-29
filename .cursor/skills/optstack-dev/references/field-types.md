# Field Types Reference

## Table of Contents

1. [Text Types](#text-types)
2. [Number Types](#number-types)
3. [Selection Types](#selection-types)
4. [Boolean Types](#boolean-types)
5. [Rich Content Types](#rich-content-types)
6. [Specialized Types](#specialized-types)
7. [Field Configuration](#field-configuration)
8. [Group Configuration](#group-configuration)

---

## Text Types

### text / string / email / url / password / tel

```php
$stack->field('name', [
    'type' => 'text',  // or 'email', 'url', 'password', 'tel'
    'label' => 'Name',
    'default' => '',
    'description' => 'Help text',
    'searchable' => true,  // Enable WP_Query indexing
    'attributes' => [
        'placeholder' => 'Enter name...',
        'maxlength' => 100,
    ],
]);
```

### textarea

```php
$stack->field('bio', [
    'type' => 'textarea',
    'label' => 'Biography',
    'attributes' => [
        'rows' => 5,
        'placeholder' => 'Enter bio...',
    ],
]);
```

---

## Number Types

### number / integer / float

```php
$stack->field('price', [
    'type' => 'number',
    'label' => 'Price',
    'default' => 0,
    'searchable' => true,
    'attributes' => [
        'min' => 0,
        'max' => 10000,
        'step' => 0.01,
        'prefix' => '$',
        'suffix' => 'USD',
    ],
]);
```

### range / slider

```php
$stack->field('opacity', [
    'type' => 'range',
    'label' => 'Opacity',
    'default' => 100,
    'attributes' => [
        'min' => 0,
        'max' => 100,
        'step' => 5,
        'unit' => '%',
    ],
]);
```

---

## Selection Types

### select / dropdown

```php
$stack->field('status', [
    'type' => 'select',
    'label' => 'Status',
    'default' => 'draft',
    'searchable' => true,
    'options' => [
        ['value' => 'draft', 'label' => 'Draft'],
        ['value' => 'active', 'label' => 'Active'],
        ['value' => 'archived', 'label' => 'Archived'],
    ],
]);
```

### radio

```php
$stack->field('layout', [
    'type' => 'radio',
    'label' => 'Layout',
    'default' => 'grid',
    'options' => [
        ['value' => 'grid', 'label' => 'Grid', 'description' => '3 columns'],
        ['value' => 'list', 'label' => 'List', 'description' => 'Single column'],
    ],
    'attributes' => [
        'layout' => 'vertical',  // 'vertical' | 'horizontal' | 'cards'
    ],
]);
```

### radio-image / image-radio

Visual image selection (like radio but with images):

```php
$stack->field('theme', [
    'type' => 'radio-image',
    'label' => 'Theme Style',
    'default' => 'light',
    'options' => [
        [
            'value' => 'light',
            'image' => 'https://example.com/light.png',  // or use 'label' for image URL
            'label' => 'Light Theme',  // becomes tooltip when 'image' is set
            'description' => 'Light',  // text below image
        ],
        [
            'value' => 'dark',
            'image' => 'https://example.com/dark.png',
            'label' => 'Dark Theme',
        ],
    ],
    'attributes' => [
        'columns' => 3,           // grid columns (default: auto-fit)
        'imageWidth' => '120px',  // default: 100px
        'imageHeight' => '80px',  // default: 80px
        'objectFit' => 'cover',   // CSS object-fit
        'showTooltip' => true,    // show tooltips
    ],
]);
```

### checkbox-group / checkboxes

```php
$stack->field('features', [
    'type' => 'checkbox-group',
    'label' => 'Features',
    'default' => ['feature1'],
    'options' => [
        ['value' => 'feature1', 'label' => 'Feature 1'],
        ['value' => 'feature2', 'label' => 'Feature 2'],
    ],
    'attributes' => [
        'layout' => 'vertical',  // 'vertical' | 'horizontal' | 'cards'
    ],
]);
```

---

## Boolean Types

### toggle / boolean / checkbox / switch

```php
$stack->field('enabled', [
    'type' => 'toggle',
    'label' => 'Enable Feature',
    'default' => false,
    'searchable' => true,
    'description' => 'Turn this on to enable',
]);
```

---

## Rich Content Types

### wysiwyg / editor / richtext

```php
$stack->field('content', [
    'type' => 'wysiwyg',
    'label' => 'Content',
    'attributes' => [
        'rows' => 10,
    ],
]);
```

### code / code-editor

```php
$stack->field('custom_css', [
    'type' => 'code',
    'label' => 'Custom CSS',
    'attributes' => [
        'language' => 'text/css',  // 'text/css', 'text/html', 'application/javascript'
        'rows' => 15,
    ],
]);
```

---

## Specialized Types

### color / color-picker

```php
$stack->field('brand_color', [
    'type' => 'color',
    'label' => 'Brand Color',
    'default' => '#2271b1',
    'searchable' => true,
    'attributes' => [
        'alpha' => true,  // enable alpha/opacity
    ],
]);
```

### date / datetime / time

```php
$stack->field('event_date', [
    'type' => 'date',  // or 'datetime', 'time'
    'label' => 'Event Date',
    'searchable' => true,
]);
```

### media / image / file

```php
// Single file
$stack->field('logo', [
    'type' => 'media',
    'label' => 'Logo',
    'attributes' => [
        'allowedTypes' => ['image'],  // or ['image', 'video', 'audio']
        'buttonText' => 'Select Logo',
    ],
]);

// Multiple files
$stack->field('gallery', [
    'type' => 'media',
    'label' => 'Gallery',
    'attributes' => [
        'multiple' => true,
        'maxFiles' => 10,
        'allowedTypes' => ['image'],
    ],
]);
```

### typography

```php
$stack->field('heading_font', [
    'type' => 'typography',
    'label' => 'Heading Typography',
    'default' => [
        'fontFamily' => '"Montserrat", sans-serif',
        'fontSize' => 32,
        'fontSizeUnit' => 'px',
        'fontWeight' => '700',
        'fontStyle' => 'normal',
        'lineHeight' => 1.3,
        'lineHeightUnit' => '',
        'letterSpacing' => 0,
        'letterSpacingUnit' => 'px',
        'textTransform' => 'none',
        'textDecoration' => 'none',
        'color' => '#111827',
    ],
    'attributes' => [
        'disableGoogleFonts' => false,  // true = system fonts only
        'fonts' => [/* custom font list */],  // override default fonts
    ],
]);
```

---

## Field Configuration

### All Fields Support

```php
[
    'type' => 'text',           // Required
    'label' => 'Field Label',   // Display name
    'description' => '...',     // Help text
    'default' => null,          // Default value
    'searchable' => false,      // Index for WP_Query (scalar types only)
    'conditions' => [...],      // Conditional visibility
    'attributes' => [...],      // Type-specific attributes
]
```

### Conditional Visibility

```php
'conditions' => [
    [
        'field' => 'enable_advanced',  // field key (dot notation for nested)
        'operator' => '==',            // ==, !=, >, <, >=, <=, contains, not_contains, empty, not_empty, in, not_in
        'value' => true,
        'relation' => 'AND',           // AND | OR (for multiple conditions)
    ],
]
```

### Searchable Field Types

Only these types can be marked `searchable: true`:
- text, textarea, number, select, radio, radio-image, checkbox, toggle, boolean
- email, url, color, date, datetime, time

NOT searchable: wysiwyg, code, typography, media, fields in repeatable groups

---

## Group Configuration

```php
$stack->group('settings', function($group) {
    $group->field('option1', ['type' => 'text']);
    $group->field('option2', ['type' => 'toggle']);
    
    // Nested groups
    $group->group('advanced', function($nested) {
        $nested->field('debug', ['type' => 'toggle']);
    });
}, [
    'label' => 'Settings',
    'description' => 'Configure settings',
    'layout' => 'inline',      // 'inline' (2-col) | 'box' (card)
    'collapsible' => true,     // Allow collapse/expand
    'conditions' => [...],     // Conditional visibility
]);

// Repeatable group
$stack->group('items', function($group) {
    $group->repeatable(0, 10);  // min, max items
    
    $group->field('name', ['type' => 'text']);
    $group->field('value', ['type' => 'number']);
}, [
    'label' => 'Items',
    'layout' => 'box',
    'collapsible' => true,
]);

// Deferred group (opens in modal)
$stack->group('pricing', function($group) {
    $group->field('regular_price', ['type' => 'number']);
    $group->field('sale_price', ['type' => 'number']);
    $group->field('currency', ['type' => 'select', 'options' => [...]]);
}, [
    'label' => 'Pricing Options',
    'description' => 'Configure detailed pricing settings.',
    'deferred' => true,         // Render trigger button + modal
    'ui' => [
        'triggerLabel' => 'Configure Pricing',  // Button text
        'render' => 'modal',    // 'modal' | 'drawer' | 'panel'
    ],
]);
```

### Deferred Groups

Deferred groups show a trigger button instead of inline fields. Clicking opens a modal with the group's fields. Use for:
- Advanced settings most users won't need
- Complex configurations that would clutter the main form
- SEO, pricing, or technical settings

Key points:
- Data structure identical to normal groups
- Validation works the same way
- Just a rendering strategy, not a data model change
- Existing data preserved if modal never opened
