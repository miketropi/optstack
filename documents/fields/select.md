# Select Field

A dropdown select menu for choosing from predefined options.

## Basic Usage

```php
$stack->field('layout', [
    'type' => 'select',
    'label' => 'Layout',
    'default' => 'default',
    'options' => [
        ['value' => 'default', 'label' => 'Default'],
        ['value' => 'wide', 'label' => 'Wide'],
        ['value' => 'narrow', 'label' => 'Narrow'],
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'select'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default selected value |
| `options` | array | `[]` | **Required.** Array of options |
| `attributes` | array | `[]` | Additional attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Options Format

Each option should have:

```php
'options' => [
    ['value' => 'option_value', 'label' => 'Display Label'],
]
```

## Examples

### Header Style

```php
$stack->field('header_style', [
    'type' => 'select',
    'label' => 'Header Style',
    'default' => 'default',
    'options' => [
        ['value' => 'default', 'label' => 'Default (Logo Left, Menu Right)'],
        ['value' => 'centered', 'label' => 'Centered Logo'],
        ['value' => 'split', 'label' => 'Split Menu (Logo Center)'],
        ['value' => 'vertical', 'label' => 'Vertical Sidebar'],
    ],
]);
```

### Footer Columns

```php
$stack->field('footer_columns', [
    'type' => 'select',
    'label' => 'Footer Widget Columns',
    'default' => '4',
    'options' => [
        ['value' => '1', 'label' => '1 Column'],
        ['value' => '2', 'label' => '2 Columns'],
        ['value' => '3', 'label' => '3 Columns'],
        ['value' => '4', 'label' => '4 Columns'],
    ],
]);
```

### Title Separator

```php
$stack->field('separator', [
    'type' => 'select',
    'label' => 'Title Separator',
    'default' => '|',
    'options' => [
        ['value' => '|', 'label' => '|'],
        ['value' => '-', 'label' => '-'],
        ['value' => '–', 'label' => '–'],
        ['value' => '—', 'label' => '—'],
        ['value' => '•', 'label' => '•'],
        ['value' => '/', 'label' => '/'],
    ],
]);
```

### Mobile Menu Style

```php
$stack->field('mobile_menu_style', [
    'type' => 'select',
    'label' => 'Mobile Menu Style',
    'default' => 'slide',
    'options' => [
        ['value' => 'slide', 'label' => 'Slide from Side'],
        ['value' => 'dropdown', 'label' => 'Dropdown'],
        ['value' => 'fullscreen', 'label' => 'Fullscreen Overlay'],
    ],
]);
```

### Font Display Strategy

```php
$stack->field('google_fonts_display', [
    'type' => 'select',
    'label' => 'Google Fonts Display',
    'default' => 'swap',
    'options' => [
        ['value' => 'auto', 'label' => 'Auto'],
        ['value' => 'block', 'label' => 'Block'],
        ['value' => 'swap', 'label' => 'Swap (Recommended)'],
        ['value' => 'fallback', 'label' => 'Fallback'],
        ['value' => 'optional', 'label' => 'Optional'],
    ],
    'description' => 'Font display strategy for performance',
]);
```

### With Conditional Display

```php
$stack->field('slide_position', [
    'type' => 'select',
    'label' => 'Slide Position',
    'default' => 'left',
    'options' => [
        ['value' => 'left', 'label' => 'Left'],
        ['value' => 'right', 'label' => 'Right'],
    ],
    'conditions' => [
        ['field' => 'mobile_menu_style', 'operator' => '==', 'value' => 'slide'],
    ],
]);
```

## Retrieving Value

```php
// For options
$style = my_option('header_style', 'default');

// For post meta
$layout = get_post_meta($post_id, 'layout', true);
```

## Output Example

```php
<?php
$style = my_option('header_style', 'default');
?>
<header class="header header-<?php echo esc_attr($style); ?>">
    <!-- Header content -->
</header>
```

### Switch Statement

```php
$columns = my_option('footer_columns', '4');

switch ($columns) {
    case '1':
        $class = 'footer-full';
        break;
    case '2':
        $class = 'footer-half';
        break;
    case '3':
        $class = 'footer-third';
        break;
    default:
        $class = 'footer-quarter';
}
```

## Related Fields

- [radio](./radio.md) - For visible radio buttons
- [radio-image](./radio-image.md) - For visual selection with images
- [checkbox-group](./checkbox-group.md) - For multiple selections
