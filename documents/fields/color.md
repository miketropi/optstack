# Color Field

A color picker with optional alpha (transparency) support.

## Basic Usage

```php
$stack->field('brand_color', [
    'type' => 'color',
    'label' => 'Brand Color',
    'default' => '#3b82f6',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'color'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default color (hex format) |
| `attributes` | array | `[]` | Additional attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `alpha` | bool | `false` | Enable alpha/transparency support |

## Examples

### Primary Colors

```php
$stack->group('colors', function ($group) {
    $group->field('brand', [
        'type' => 'color',
        'label' => 'Primary Brand Color',
        'default' => '#3b82f6',
    ]);
    
    $group->field('secondary', [
        'type' => 'color',
        'label' => 'Secondary Color',
        'default' => '#8b5cf6',
    ]);
    
    $group->field('accent', [
        'type' => 'color',
        'label' => 'Accent Color',
        'default' => '#10b981',
    ]);
}, ['label' => 'Primary Colors']);
```

### Text Colors

```php
$stack->group('text_colors', function ($group) {
    $group->field('primary', [
        'type' => 'color',
        'label' => 'Primary Text',
        'default' => '#111827',
    ]);
    
    $group->field('secondary', [
        'type' => 'color',
        'label' => 'Secondary Text',
        'default' => '#6b7280',
    ]);
    
    $group->field('muted', [
        'type' => 'color',
        'label' => 'Muted Text',
        'default' => '#9ca3af',
    ]);
}, ['label' => 'Text Colors']);
```

### With Alpha (Transparency)

```php
$stack->field('header_background', [
    'type' => 'color',
    'label' => 'Header Background',
    'default' => '#ffffff',
    'attributes' => [
        'alpha' => true,
    ],
]);

$stack->field('overlay_color', [
    'type' => 'color',
    'label' => 'Overlay Color',
    'default' => 'rgba(0, 0, 0, 0.5)',
    'attributes' => [
        'alpha' => true,
    ],
]);
```

### Button Colors

```php
$stack->group('button_colors', function ($group) {
    $group->field('primary_bg', [
        'type' => 'color',
        'label' => 'Primary Button Background',
        'default' => '#3b82f6',
    ]);
    
    $group->field('primary_text', [
        'type' => 'color',
        'label' => 'Primary Button Text',
        'default' => '#ffffff',
    ]);
    
    $group->field('primary_hover', [
        'type' => 'color',
        'label' => 'Primary Button Hover',
        'default' => '#2563eb',
    ]);
}, ['label' => 'Button Colors']);
```

### Background Colors

```php
$stack->group('backgrounds', function ($group) {
    $group->field('body', [
        'type' => 'color',
        'label' => 'Body Background',
        'default' => '#ffffff',
    ]);
    
    $group->field('header', [
        'type' => 'color',
        'label' => 'Header Background',
        'default' => '#1f2937',
    ]);
    
    $group->field('footer', [
        'type' => 'color',
        'label' => 'Footer Background',
        'default' => '#111827',
    ]);
}, ['label' => 'Background Colors']);
```

## Retrieving Value

```php
// For options
$brand_color = my_option('primary.brand', '#3b82f6');

// For alpha colors
$overlay = my_option('overlay_color', 'rgba(0, 0, 0, 0.5)');
```

## Output Example

### CSS Variables

```php
<?php
function output_color_css() {
    $brand = my_option('primary.brand', '#3b82f6');
    $secondary = my_option('primary.secondary', '#8b5cf6');
    $text = my_option('text_colors.primary', '#111827');
    ?>
    <style>
    :root {
        --color-brand: <?php echo esc_attr($brand); ?>;
        --color-secondary: <?php echo esc_attr($secondary); ?>;
        --color-text: <?php echo esc_attr($text); ?>;
    }
    </style>
    <?php
}
add_action('wp_head', 'output_color_css');
```

### Inline Styles

```php
<?php $bg = my_option('header_background', '#ffffff'); ?>
<header style="background-color: <?php echo esc_attr($bg); ?>">
    <!-- Header content -->
</header>
```

### Dynamic Stylesheet

```php
function generate_theme_css() {
    $css = '';
    
    // Brand color
    $brand = my_option('primary.brand', '#3b82f6');
    $css .= "a { color: {$brand}; }";
    $css .= ".btn-primary { background-color: {$brand}; }";
    
    // Button colors
    $btn_bg = my_option('button_colors.primary_bg', '#3b82f6');
    $btn_text = my_option('button_colors.primary_text', '#ffffff');
    $btn_hover = my_option('button_colors.primary_hover', '#2563eb');
    
    $css .= ".button-primary {
        background-color: {$btn_bg};
        color: {$btn_text};
    }";
    $css .= ".button-primary:hover {
        background-color: {$btn_hover};
    }";
    
    return $css;
}
```

## Color Formats

The color field supports:

- **Hex colors**: `#3b82f6`, `#fff`
- **RGB colors**: `rgb(59, 130, 246)`
- **RGBA colors** (with alpha): `rgba(59, 130, 246, 0.5)`

## Related Fields

- [text](./text.md) - For manual color input
- [select](./select.md) - For predefined color choices
