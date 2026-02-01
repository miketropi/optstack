# Radio Image Field

A visual radio button selection with image previews for each option.

## Basic Usage

```php
$stack->field('sidebar_position', [
    'type' => 'radio-image',
    'label' => 'Sidebar Position',
    'default' => 'right',
    'options' => [
        [
            'value' => 'left',
            'label' => 'Left Sidebar',
            'description' => 'Left',
        ],
        [
            'value' => 'right',
            'label' => 'Right Sidebar',
            'description' => 'Right',
        ],
        [
            'value' => 'none',
            'label' => 'No Sidebar',
            'description' => 'None',
        ],
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'radio-image'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default selected value |
| `options` | array | `[]` | **Required.** Array of options with images |
| `attributes` | array | `[]` | Layout attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Options Format

```php
'options' => [
    [
        'value' => 'option_value',
        'label' => 'Full Label',           // Tooltip/accessibility
        'description' => 'Short Label',    // Displayed below image
        'image' => 'https://...',          // Optional image URL
    ],
]
```

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `columns` | int | `3` | Number of columns in grid |
| `imageWidth` | string | `'100px'` | Image width (CSS value) |
| `imageHeight` | string | `'80px'` | Image height (CSS value) |

## Examples

### Sidebar Layout

```php
$stack->field('sidebar_layout', [
    'type' => 'radio-image',
    'label' => 'Default Sidebar Layout',
    'default' => 'right',
    'options' => [
        [
            'value' => 'left',
            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=◀+Content',
            'label' => 'Left Sidebar',
            'description' => 'Left',
        ],
        [
            'value' => 'right',
            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=Content+▶',
            'label' => 'Right Sidebar',
            'description' => 'Right',
        ],
        [
            'value' => 'none',
            'image' => 'https://placehold.co/120x80/e9ecef/495057?text=Full+Width',
            'label' => 'No Sidebar',
            'description' => 'Full Width',
        ],
    ],
    'attributes' => [
        'columns' => 3,
        'imageWidth' => '120px',
        'imageHeight' => '80px',
    ],
]);
```

### Header Style

```php
$stack->field('header_style', [
    'type' => 'radio-image',
    'label' => 'Header Style',
    'default' => 'standard',
    'options' => [
        [
            'value' => 'standard',
            'image' => 'https://placehold.co/140x60/1d2327/ffffff?text=Logo++++Menu',
            'label' => 'Standard Header',
            'description' => 'Standard',
        ],
        [
            'value' => 'centered',
            'image' => 'https://placehold.co/140x60/1d2327/ffffff?text=+++Logo+++',
            'label' => 'Centered Logo',
            'description' => 'Centered',
        ],
        [
            'value' => 'transparent',
            'image' => 'https://placehold.co/140x60/6c757d/ffffff?text=Transparent',
            'label' => 'Transparent Header',
            'description' => 'Transparent',
        ],
        [
            'value' => 'minimal',
            'image' => 'https://placehold.co/140x60/f8f9fa/1d2327?text=≡+Logo',
            'label' => 'Minimal (Hamburger)',
            'description' => 'Minimal',
        ],
    ],
    'attributes' => [
        'columns' => 4,
        'imageWidth' => '140px',
        'imageHeight' => '60px',
    ],
]);
```

### Footer Columns

```php
$stack->field('footer_columns', [
    'type' => 'radio-image',
    'label' => 'Footer Widget Layout',
    'default' => '4',
    'options' => [
        [
            'value' => '1',
            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━━━━━━━',
            'label' => '1 Column (Full Width)',
            'description' => '1 Column',
        ],
        [
            'value' => '2',
            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━━+━━━',
            'label' => '2 Columns',
            'description' => '2 Columns',
        ],
        [
            'value' => '3',
            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━━+━━+━━',
            'label' => '3 Columns',
            'description' => '3 Columns',
        ],
        [
            'value' => '4',
            'image' => 'https://placehold.co/100x50/343a40/ffffff?text=━+━+━+━',
            'label' => '4 Columns',
            'description' => '4 Columns',
        ],
    ],
    'attributes' => [
        'columns' => 4,
    ],
]);
```

### Blog Layout

```php
$stack->field('blog_layout', [
    'type' => 'radio-image',
    'label' => 'Blog Layout',
    'default' => 'grid',
    'options' => [
        [
            'value' => 'list',
            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=List',
            'label' => 'List Layout',
            'description' => 'List',
        ],
        [
            'value' => 'grid',
            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=Grid',
            'label' => 'Grid Layout',
            'description' => 'Grid',
        ],
        [
            'value' => 'masonry',
            'image' => 'https://placehold.co/100x80/dee2e6/495057?text=Masonry',
            'label' => 'Masonry Layout',
            'description' => 'Masonry',
        ],
    ],
    'attributes' => [
        'columns' => 3,
        'imageWidth' => '100px',
        'imageHeight' => '80px',
    ],
]);
```

### Without Images (Text Only)

```php
$stack->field('position', [
    'type' => 'radio-image',
    'label' => 'Button Position',
    'default' => 'right',
    'options' => [
        [
            'value' => 'left',
            'label' => 'Bottom Left',
            'description' => 'Left',
        ],
        [
            'value' => 'right',
            'label' => 'Bottom Right',
            'description' => 'Right',
        ],
    ],
]);
```

## Retrieving Value

```php
// For options
$layout = my_option('sidebar_layout', 'right');

// For post meta
$style = get_post_meta($post_id, 'header_style', true);
```

## Output Example

```php
<?php $sidebar = my_option('sidebar_layout', 'right'); ?>

<div class="page-wrapper sidebar-<?php echo esc_attr($sidebar); ?>">
    <?php if ($sidebar === 'left'): ?>
        <aside class="sidebar"><?php dynamic_sidebar('sidebar'); ?></aside>
    <?php endif; ?>
    
    <main class="content">
        <?php the_content(); ?>
    </main>
    
    <?php if ($sidebar === 'right'): ?>
        <aside class="sidebar"><?php dynamic_sidebar('sidebar'); ?></aside>
    <?php endif; ?>
</div>
```

## Related Fields

- [radio](./radio.md) - For simple radio buttons without images
- [select](./select.md) - For dropdown selection
