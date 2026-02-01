# Checkbox Group Field

A group of checkboxes for selecting multiple options.

## Basic Usage

```php
$stack->field('features', [
    'type' => 'checkbox-group',
    'label' => 'Features',
    'default' => ['feature1', 'feature2'],
    'options' => [
        ['value' => 'feature1', 'label' => 'Feature 1'],
        ['value' => 'feature2', 'label' => 'Feature 2'],
        ['value' => 'feature3', 'label' => 'Feature 3'],
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'checkbox-group'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | array | `[]` | Default selected values (array) |
| `options` | array | `[]` | **Required.** Array of options |
| `conditions` | array | `[]` | Conditional display rules |

## Options Format

```php
'options' => [
    ['value' => 'option_value', 'label' => 'Display Label'],
]
```

## Examples

### Social Sharing Platforms

```php
$stack->field('sharing_platforms', [
    'type' => 'checkbox-group',
    'label' => 'Sharing Platforms',
    'default' => ['facebook', 'twitter', 'linkedin'],
    'options' => [
        ['value' => 'facebook', 'label' => 'Facebook'],
        ['value' => 'twitter', 'label' => 'Twitter/X'],
        ['value' => 'linkedin', 'label' => 'LinkedIn'],
        ['value' => 'pinterest', 'label' => 'Pinterest'],
        ['value' => 'whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'telegram', 'label' => 'Telegram'],
        ['value' => 'email', 'label' => 'Email'],
    ],
    'conditions' => [
        ['field' => 'enable_sharing', 'operator' => '==', 'value' => true],
    ],
]);
```

### Post Types Selection

```php
$stack->field('enabled_post_types', [
    'type' => 'checkbox-group',
    'label' => 'Enabled Post Types',
    'default' => ['post', 'page'],
    'options' => [
        ['value' => 'post', 'label' => 'Posts'],
        ['value' => 'page', 'label' => 'Pages'],
        ['value' => 'product', 'label' => 'Products'],
        ['value' => 'portfolio', 'label' => 'Portfolio'],
    ],
]);
```

### Header Elements

```php
$stack->field('header_elements', [
    'type' => 'checkbox-group',
    'label' => 'Header Elements',
    'default' => ['logo', 'menu', 'search'],
    'options' => [
        ['value' => 'logo', 'label' => 'Logo'],
        ['value' => 'menu', 'label' => 'Main Menu'],
        ['value' => 'search', 'label' => 'Search Icon'],
        ['value' => 'cart', 'label' => 'Cart Icon'],
        ['value' => 'account', 'label' => 'Account Icon'],
        ['value' => 'cta', 'label' => 'CTA Button'],
    ],
]);
```

### Optimization Options

```php
$stack->field('optimizations', [
    'type' => 'checkbox-group',
    'label' => 'Optimization Options',
    'default' => [],
    'options' => [
        ['value' => 'minify_css', 'label' => 'Minify CSS'],
        ['value' => 'minify_js', 'label' => 'Minify JavaScript'],
        ['value' => 'defer_js', 'label' => 'Defer JavaScript'],
        ['value' => 'lazy_images', 'label' => 'Lazy Load Images'],
        ['value' => 'preload_fonts', 'label' => 'Preload Fonts'],
    ],
]);
```

### Weekdays Selection

```php
$stack->field('business_days', [
    'type' => 'checkbox-group',
    'label' => 'Business Days',
    'default' => ['mon', 'tue', 'wed', 'thu', 'fri'],
    'options' => [
        ['value' => 'mon', 'label' => 'Monday'],
        ['value' => 'tue', 'label' => 'Tuesday'],
        ['value' => 'wed', 'label' => 'Wednesday'],
        ['value' => 'thu', 'label' => 'Thursday'],
        ['value' => 'fri', 'label' => 'Friday'],
        ['value' => 'sat', 'label' => 'Saturday'],
        ['value' => 'sun', 'label' => 'Sunday'],
    ],
]);
```

## Retrieving Value

```php
// Returns array of selected values
$platforms = my_option('sharing_platforms', ['facebook', 'twitter']);

// Check if specific value is selected
if (in_array('facebook', $platforms)) {
    // Facebook sharing is enabled
}

// For post meta
$post_types = get_post_meta($post_id, 'enabled_post_types', true);
if (!is_array($post_types)) {
    $post_types = [];
}
```

## Output Example

### Social Sharing Buttons

```php
<?php
$platforms = my_option('sharing_platforms', []);
$url = get_permalink();
$title = get_the_title();

foreach ($platforms as $platform):
    switch ($platform):
        case 'facebook':
            $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);
            break;
        case 'twitter':
            $share_url = 'https://twitter.com/intent/tweet?url=' . urlencode($url) . '&text=' . urlencode($title);
            break;
        case 'linkedin':
            $share_url = 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode($url);
            break;
        default:
            continue 2;
    endswitch;
?>
    <a href="<?php echo esc_url($share_url); ?>" class="share-btn share-<?php echo esc_attr($platform); ?>">
        Share on <?php echo esc_html(ucfirst($platform)); ?>
    </a>
<?php endforeach; ?>
```

### Conditional Classes

```php
<?php
$elements = my_option('header_elements', []);
$classes = ['header'];

if (in_array('search', $elements)) {
    $classes[] = 'has-search';
}
if (in_array('cart', $elements)) {
    $classes[] = 'has-cart';
}
?>

<header class="<?php echo esc_attr(implode(' ', $classes)); ?>">
    <?php if (in_array('logo', $elements)): ?>
        <div class="logo"><!-- Logo --></div>
    <?php endif; ?>
    
    <?php if (in_array('menu', $elements)): ?>
        <nav class="main-menu"><!-- Menu --></nav>
    <?php endif; ?>
    
    <?php if (in_array('search', $elements)): ?>
        <button class="search-toggle">Search</button>
    <?php endif; ?>
</header>
```

## Related Fields

- [toggle](./toggle.md) - For single boolean option
- [select](./select.md) - For single selection
- [radio](./radio.md) - For single visible selection
