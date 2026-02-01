# Number Field

A numeric input field with optional min/max constraints and step increments.

## Basic Usage

```php
$stack->field('quantity', [
    'type' => 'number',
    'label' => 'Quantity',
    'default' => 1,
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'number'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | int\|float | `0` | Default value |
| `attributes` | array | `[]` | Additional attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `min` | int\|float | - | Minimum allowed value |
| `max` | int\|float | - | Maximum allowed value |
| `step` | int\|float | `1` | Increment step value |
| `suffix` | string | `''` | Unit suffix (e.g., 'px', '%') |
| `prefix` | string | `''` | Unit prefix (e.g., '$') |
| `placeholder` | string | `''` | Placeholder text |

## Examples

### Container Width

```php
$stack->field('container_width', [
    'type' => 'number',
    'label' => 'Container Width',
    'default' => 1200,
    'attributes' => [
        'min' => 960,
        'max' => 1920,
        'step' => 10,
        'suffix' => 'px',
    ],
]);
```

### Percentage Value

```php
$stack->field('content_width', [
    'type' => 'number',
    'label' => 'Content Width',
    'default' => 70,
    'attributes' => [
        'min' => 50,
        'max' => 100,
        'step' => 5,
        'suffix' => '%',
    ],
]);
```

### Price Field

```php
$stack->field('price', [
    'type' => 'number',
    'label' => 'Price',
    'default' => 0,
    'attributes' => [
        'min' => 0,
        'step' => 0.01,
        'prefix' => '$',
    ],
]);
```

### Header Height

```php
$stack->field('header_height', [
    'type' => 'number',
    'label' => 'Header Height',
    'default' => 80,
    'attributes' => [
        'min' => 60,
        'max' => 150,
        'step' => 5,
        'suffix' => 'px',
    ],
]);
```

### Font Size

```php
$stack->field('font_size', [
    'type' => 'number',
    'label' => 'Font Size',
    'default' => 16,
    'attributes' => [
        'min' => 12,
        'max' => 24,
        'step' => 1,
        'suffix' => 'px',
    ],
]);
```

### With Conditional Display

```php
$stack->field('cache_duration', [
    'type' => 'number',
    'label' => 'Cache Duration (days)',
    'default' => 7,
    'attributes' => [
        'min' => 1,
        'max' => 365,
        'step' => 1,
    ],
    'conditions' => [
        ['field' => 'enable_cache', 'operator' => '==', 'value' => true],
    ],
]);
```

## Retrieving Value

```php
// For options
$width = my_option('container_width', 1200);

// Ensure integer
$width = (int) my_option('container_width', 1200);

// For post meta
$quantity = (int) get_post_meta($post_id, 'quantity', true);
```

## Output Example

```php
// CSS output
.container {
    max-width: <?php echo esc_attr(my_option('container_width', 1200)); ?>px;
}

// Inline style
<div style="height: <?php echo esc_attr(my_option('header_height', 80)); ?>px;">
```

## Related Fields

- [range](./range.md) - For visual slider input
- [text](./text.md) - For text input
