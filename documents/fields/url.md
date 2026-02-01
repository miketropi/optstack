# URL Field

A URL input field with built-in URL format validation.

## Basic Usage

```php
$stack->field('website_url', [
    'type' => 'url',
    'label' => 'Website URL',
    'description' => 'Enter a valid URL',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'url'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default value |
| `attributes` | array | `[]` | Additional HTML attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `placeholder` | string | `''` | Placeholder text |
| `readonly` | bool | `false` | Make field read-only |
| `disabled` | bool | `false` | Disable the field |

## Examples

### Social Media Links

```php
$stack->group('social_links', function ($group) {
    $group->field('twitter', [
        'type' => 'url',
        'label' => 'Twitter/X',
        'attributes' => [
            'placeholder' => 'https://x.com/username',
        ],
    ]);
    
    $group->field('facebook', [
        'type' => 'url',
        'label' => 'Facebook',
        'attributes' => [
            'placeholder' => 'https://facebook.com/page',
        ],
    ]);
    
    $group->field('instagram', [
        'type' => 'url',
        'label' => 'Instagram',
        'attributes' => [
            'placeholder' => 'https://instagram.com/username',
        ],
    ]);
    
    $group->field('linkedin', [
        'type' => 'url',
        'label' => 'LinkedIn',
        'attributes' => [
            'placeholder' => 'https://linkedin.com/in/username',
        ],
    ]);
}, ['label' => 'Social Media Links']);
```

### CTA Button URL

```php
$stack->field('cta_url', [
    'type' => 'url',
    'label' => 'CTA Button URL',
    'description' => 'Link for the call-to-action button',
    'conditions' => [
        ['field' => 'show_cta', 'operator' => '==', 'value' => true],
    ],
]);
```

### External Link

```php
$stack->field('documentation_url', [
    'type' => 'url',
    'label' => 'Documentation URL',
    'default' => 'https://docs.example.com',
]);
```

### With Default Value

```php
$stack->field('portfolio_url', [
    'type' => 'url',
    'label' => 'Portfolio URL',
    'default' => 'https://portfolio.example.com',
    'attributes' => [
        'placeholder' => 'https://...',
    ],
]);
```

## Retrieving Value

```php
// For options
$url = my_option('website_url', '');

// Validate and escape
$url = my_option('cta_url', '');
if (!empty($url)) {
    echo esc_url($url);
}
```

## Output Example

```php
// Link output
<?php $url = my_option('website_url', ''); ?>
<?php if ($url): ?>
    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
        Visit Website
    </a>
<?php endif; ?>
```

### Social Icons

```php
<?php
$social = [
    'twitter' => my_option('social_links.twitter', ''),
    'facebook' => my_option('social_links.facebook', ''),
    'instagram' => my_option('social_links.instagram', ''),
];
?>

<ul class="social-icons">
    <?php foreach ($social as $platform => $url): ?>
        <?php if (!empty($url)): ?>
            <li>
                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                    <i class="icon-<?php echo esc_attr($platform); ?>"></i>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
```

## Validation

Always use `esc_url()` when outputting URLs:

```php
// Safe URL output
<a href="<?php echo esc_url($url); ?>">Link</a>

// For admin URLs
<a href="<?php echo esc_url(admin_url('options.php')); ?>">Settings</a>
```

## Related Fields

- [text](./text.md) - For general text input
- [email](./email.md) - For email addresses
