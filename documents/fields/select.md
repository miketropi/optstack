# Select Field

A dropdown select menu for choosing from predefined options. Supports both single and multiple selection modes.

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
| `default` | string/array | `''` | Default selected value(s) |
| `options` | array | `[]` | **Required.** Array of options |
| `attributes` | array | `[]` | Additional attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `multiple` | boolean | `false` | Enable multiple selection |
| `searchable` | boolean | `true` | Allow searching/filtering options |
| `clearable` | boolean | `false` | Show clear button to reset selection |
| `placeholder` | string | `'Select...'` | Placeholder text when no selection |

## Options Format

Each option should have:

```php
'options' => [
    ['value' => 'option_value', 'label' => 'Display Label'],
]
```

## Multiple Selection

Enable multiple selection with the `multiple` attribute. The value will be stored as an array.

```php
$stack->field('categories', [
    'type' => 'select',
    'label' => 'Categories',
    'default' => ['news', 'featured'],
    'attributes' => [
        'multiple' => true,
        'placeholder' => 'Select categories...',
    ],
    'options' => [
        ['value' => 'news', 'label' => 'News'],
        ['value' => 'featured', 'label' => 'Featured'],
        ['value' => 'popular', 'label' => 'Popular'],
        ['value' => 'trending', 'label' => 'Trending'],
    ],
]);
```

### Social Platforms Example

```php
$stack->field('social_platforms', [
    'type' => 'select',
    'label' => 'Share Platforms',
    'description' => 'Select which social platforms to show share buttons for',
    'default' => ['facebook', 'twitter'],
    'attributes' => [
        'multiple' => true,
        'searchable' => true,
        'clearable' => true,
    ],
    'options' => [
        ['value' => 'facebook', 'label' => 'Facebook'],
        ['value' => 'twitter', 'label' => 'Twitter/X'],
        ['value' => 'linkedin', 'label' => 'LinkedIn'],
        ['value' => 'pinterest', 'label' => 'Pinterest'],
        ['value' => 'whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'email', 'label' => 'Email'],
    ],
]);
```

### Retrieving Multiple Values

```php
// Returns array: ['facebook', 'twitter']
$platforms = OptStack::getField('settings', 'social_platforms', [], $post_id);

// Loop through selected values
foreach ($platforms as $platform) {
    echo "Share on: {$platform}";
}

// Check if specific value is selected
if (in_array('facebook', $platforms)) {
    // Show Facebook share button
}
```

## Examples (Single Select)

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
