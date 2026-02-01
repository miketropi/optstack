# Range Field

A slider input for selecting numeric values within a range.

## Basic Usage

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

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'range'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | int\|float | `0` | Default value |
| `attributes` | array | `[]` | **Required.** Range configuration |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `min` | int\|float | `0` | Minimum value |
| `max` | int\|float | `100` | Maximum value |
| `step` | int\|float | `1` | Step increment |
| `unit` | string | `''` | Unit suffix (px, %, em, etc.) |

## Examples

### Container Width

```php
$stack->field('container_width', [
    'type' => 'range',
    'label' => 'Container Width',
    'default' => 1200,
    'attributes' => [
        'min' => 960,
        'max' => 1600,
        'step' => 20,
        'unit' => 'px',
    ],
]);
```

### Opacity/Transparency

```php
$stack->field('overlay_opacity', [
    'type' => 'range',
    'label' => 'Overlay Opacity',
    'default' => 50,
    'attributes' => [
        'min' => 0,
        'max' => 100,
        'step' => 5,
        'unit' => '%',
    ],
]);
```

### Border Radius

```php
$stack->field('border_radius', [
    'type' => 'range',
    'label' => 'Border Radius',
    'default' => 4,
    'attributes' => [
        'min' => 0,
        'max' => 50,
        'step' => 1,
        'unit' => 'px',
    ],
]);
```

### Spacing/Padding

```php
$stack->field('section_padding', [
    'type' => 'range',
    'label' => 'Section Padding',
    'default' => 60,
    'attributes' => [
        'min' => 20,
        'max' => 150,
        'step' => 10,
        'unit' => 'px',
    ],
]);
```

### Animation Duration

```php
$stack->field('animation_duration', [
    'type' => 'range',
    'label' => 'Animation Duration',
    'default' => 300,
    'attributes' => [
        'min' => 100,
        'max' => 1000,
        'step' => 50,
        'unit' => 'ms',
    ],
]);
```

### Font Size Scale

```php
$stack->field('font_scale', [
    'type' => 'range',
    'label' => 'Font Size Scale',
    'default' => 1,
    'description' => 'Adjust base font size multiplier',
    'attributes' => [
        'min' => 0.8,
        'max' => 1.5,
        'step' => 0.05,
        'unit' => 'x',
    ],
]);
```

### With Conditional Display

```php
$stack->field('blur_amount', [
    'type' => 'range',
    'label' => 'Blur Amount',
    'default' => 10,
    'attributes' => [
        'min' => 0,
        'max' => 50,
        'step' => 1,
        'unit' => 'px',
    ],
    'conditions' => [
        ['field' => 'enable_blur', 'operator' => '==', 'value' => true],
    ],
]);
```

## Retrieving Value

```php
// For options
$width = my_option('container_width', 1200);
$opacity = my_option('overlay_opacity', 50);

// Ensure numeric type
$width = (int) my_option('container_width', 1200);
$scale = (float) my_option('font_scale', 1);
```

## Output Example

### CSS Variables

```php
<?php
function output_range_css() {
    $container = my_option('container_width', 1200);
    $padding = my_option('section_padding', 60);
    $radius = my_option('border_radius', 4);
    ?>
    <style>
    :root {
        --container-width: <?php echo esc_attr($container); ?>px;
        --section-padding: <?php echo esc_attr($padding); ?>px;
        --border-radius: <?php echo esc_attr($radius); ?>px;
    }
    
    .container {
        max-width: var(--container-width);
    }
    
    .section {
        padding: var(--section-padding) 0;
    }
    
    .card {
        border-radius: var(--border-radius);
    }
    </style>
    <?php
}
add_action('wp_head', 'output_range_css');
```

### Inline Styles

```php
<?php
$opacity = my_option('overlay_opacity', 50);
$opacity_decimal = $opacity / 100;
?>
<div class="overlay" style="background-color: rgba(0, 0, 0, <?php echo esc_attr($opacity_decimal); ?>);">
    <!-- Overlay content -->
</div>
```

### Animation

```php
<?php $duration = my_option('animation_duration', 300); ?>
<style>
.animated {
    transition-duration: <?php echo esc_attr($duration); ?>ms;
}
</style>
```

## Range vs Number

| Use Range When | Use Number When |
|----------------|-----------------|
| Visual feedback is helpful | Precise values needed |
| Value is continuous | Specific increments matter |
| Quick adjustment desired | Manual input preferred |

## Related Fields

- [number](./number.md) - For direct numeric input
- [text](./text.md) - For manual value entry
