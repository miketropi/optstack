# Radio Field

A radio button group for selecting a single option from visible choices.

## Basic Usage

```php
$stack->field('alignment', [
    'type' => 'radio',
    'label' => 'Alignment',
    'default' => 'left',
    'options' => [
        ['value' => 'left', 'label' => 'Left'],
        ['value' => 'center', 'label' => 'Center'],
        ['value' => 'right', 'label' => 'Right'],
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'radio'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default selected value |
| `options` | array | `[]` | **Required.** Array of options |
| `conditions` | array | `[]` | Conditional display rules |

## Options Format

```php
'options' => [
    ['value' => 'option_value', 'label' => 'Display Label'],
]
```

## Examples

### Post Display Mode

```php
$stack->field('display_mode', [
    'type' => 'radio',
    'label' => 'Display Mode',
    'default' => 'standard',
    'options' => [
        ['value' => 'standard', 'label' => 'Standard'],
        ['value' => 'compact', 'label' => 'Compact'],
        ['value' => 'expanded', 'label' => 'Expanded'],
    ],
]);
```

### Button Style

```php
$stack->field('button_style', [
    'type' => 'radio',
    'label' => 'Button Style',
    'default' => 'solid',
    'options' => [
        ['value' => 'solid', 'label' => 'Solid'],
        ['value' => 'outline', 'label' => 'Outline'],
        ['value' => 'ghost', 'label' => 'Ghost'],
    ],
]);
```

### Image Position

```php
$stack->field('image_position', [
    'type' => 'radio',
    'label' => 'Image Position',
    'default' => 'top',
    'options' => [
        ['value' => 'top', 'label' => 'Top'],
        ['value' => 'left', 'label' => 'Left'],
        ['value' => 'right', 'label' => 'Right'],
        ['value' => 'background', 'label' => 'Background'],
    ],
]);
```

### With Conditional Display

```php
$stack->field('nav_type', [
    'type' => 'radio',
    'label' => 'Navigation Type',
    'default' => 'horizontal',
    'options' => [
        ['value' => 'horizontal', 'label' => 'Horizontal Menu'],
        ['value' => 'vertical', 'label' => 'Vertical Sidebar'],
        ['value' => 'hamburger', 'label' => 'Hamburger Only'],
    ],
]);

$stack->field('nav_position', [
    'type' => 'radio',
    'label' => 'Navigation Position',
    'default' => 'left',
    'options' => [
        ['value' => 'left', 'label' => 'Left'],
        ['value' => 'right', 'label' => 'Right'],
    ],
    'conditions' => [
        ['field' => 'nav_type', 'operator' => '==', 'value' => 'vertical'],
    ],
]);
```

## Retrieving Value

```php
// For options
$alignment = my_option('alignment', 'left');

// For post meta
$display_mode = get_post_meta($post_id, 'display_mode', true);
```

## Output Example

```php
<?php $alignment = my_option('alignment', 'left'); ?>
<div class="content align-<?php echo esc_attr($alignment); ?>">
    <!-- Content -->
</div>
```

### Switch Statement

```php
$style = my_option('button_style', 'solid');

switch ($style) {
    case 'outline':
        $class = 'btn-outline';
        break;
    case 'ghost':
        $class = 'btn-ghost';
        break;
    default:
        $class = 'btn-solid';
}

echo '<button class="' . esc_attr($class) . '">Click</button>';
```

## When to Use Radio vs Select

| Use Radio When | Use Select When |
|----------------|-----------------|
| 3-5 options | More than 5 options |
| All options should be visible | Space is limited |
| Visual comparison is helpful | Simple selection needed |

## Related Fields

- [radio-image](./radio-image.md) - For visual selection with images
- [select](./select.md) - For dropdown selection
- [checkbox-group](./checkbox-group.md) - For multiple selections
