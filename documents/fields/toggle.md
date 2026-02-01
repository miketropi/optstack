# Toggle Field

A switch/toggle field for boolean on/off values.

## Basic Usage

```php
$stack->field('enable_feature', [
    'type' => 'toggle',
    'label' => 'Enable Feature',
    'default' => false,
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'toggle'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | bool | `false` | Default state (true/false) |
| `conditions` | array | `[]` | Conditional display rules |

## Examples

### Maintenance Mode

```php
$stack->field('maintenance_mode', [
    'type' => 'toggle',
    'label' => 'Enable Maintenance Mode',
    'default' => false,
    'description' => 'Show maintenance page to visitors (admins can still access)',
]);
```

### Sticky Header

```php
$stack->field('sticky_header', [
    'type' => 'toggle',
    'label' => 'Sticky Header',
    'default' => true,
    'description' => 'Keep header visible when scrolling',
]);
```

### Feature Toggles

```php
$stack->group('features', function ($group) {
    $group->field('lazy_load_images', [
        'type' => 'toggle',
        'label' => 'Lazy Load Images',
        'default' => true,
        'description' => 'Load images as they come into viewport',
    ]);
    
    $group->field('disable_emojis', [
        'type' => 'toggle',
        'label' => 'Disable WordPress Emojis',
        'default' => false,
        'description' => 'Remove emoji scripts if not needed',
    ]);
    
    $group->field('disable_embeds', [
        'type' => 'toggle',
        'label' => 'Disable Embeds',
        'default' => false,
        'description' => 'Remove oEmbed scripts',
    ]);
}, ['label' => 'Performance Features']);
```

### With Conditional Fields

Toggle fields are commonly used to control visibility of related fields:

```php
$stack->field('enable_analytics', [
    'type' => 'toggle',
    'label' => 'Enable Google Analytics',
    'default' => false,
]);

$stack->field('tracking_id', [
    'type' => 'text',
    'label' => 'Tracking ID',
    'description' => 'GA4 Measurement ID (G-XXXXXXXXXX)',
    'attributes' => [
        'placeholder' => 'G-XXXXXXXXXX',
    ],
    'conditions' => [
        ['field' => 'enable_analytics', 'operator' => '==', 'value' => true],
    ],
]);

$stack->field('anonymize_ip', [
    'type' => 'toggle',
    'label' => 'Anonymize IP',
    'default' => true,
    'description' => 'GDPR compliance',
    'conditions' => [
        ['field' => 'enable_analytics', 'operator' => '==', 'value' => true],
    ],
]);
```

### Social Sharing

```php
$stack->group('sharing', function ($group) {
    $group->field('enable_posts', [
        'type' => 'toggle',
        'label' => 'Enable Sharing on Posts',
        'default' => true,
    ]);
    
    $group->field('enable_pages', [
        'type' => 'toggle',
        'label' => 'Enable Sharing on Pages',
        'default' => false,
    ]);
}, ['label' => 'Social Sharing']);
```

## Retrieving Value

```php
// For options (returns boolean)
$is_enabled = my_option('enable_feature', false);

// Check value
if (my_option('sticky_header', true)) {
    // Sticky header is enabled
}

// For post meta
$is_featured = (bool) get_post_meta($post_id, 'is_featured', true);
```

## Output Example

```php
// Conditional class
<?php $is_sticky = my_option('sticky_header', true); ?>
<header class="header <?php echo $is_sticky ? 'sticky' : ''; ?>">

// Conditional output
<?php if (my_option('show_breadcrumbs', true)): ?>
    <nav class="breadcrumbs">
        <?php // breadcrumb code ?>
    </nav>
<?php endif; ?>
```

### Maintenance Mode Check

```php
function is_maintenance_mode(): bool
{
    if (current_user_can('administrator')) {
        return false;
    }
    
    return my_option('maintenance_mode', false);
}

if (is_maintenance_mode()) {
    // Show maintenance page
}
```

## Related Fields

- [checkbox-group](./checkbox-group.md) - For multiple boolean selections
- [select](./select.md) - For single selection from options
