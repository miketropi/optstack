# Text Field

A single-line text input field for short text values.

## Basic Usage

```php
$stack->field('site_title', [
    'type' => 'text',
    'label' => 'Site Title',
    'default' => 'My Website',
    'description' => 'Enter your site title',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'text'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default value |
| `attributes` | array | `[]` | Additional HTML attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `placeholder` | string | `''` | Placeholder text |
| `maxlength` | int | - | Maximum character length |
| `minlength` | int | - | Minimum character length |
| `readonly` | bool | `false` | Make field read-only |
| `disabled` | bool | `false` | Disable the field |
| `pattern` | string | - | Regex validation pattern |

## Examples

### With Placeholder

```php
$stack->field('company_name', [
    'type' => 'text',
    'label' => 'Company Name',
    'attributes' => [
        'placeholder' => 'Enter your company name',
    ],
]);
```

### With Character Limit

```php
$stack->field('meta_title', [
    'type' => 'text',
    'label' => 'Meta Title',
    'description' => 'Maximum 60 characters for SEO',
    'attributes' => [
        'maxlength' => 60,
    ],
]);
```

### Read-only Field

```php
$stack->field('license_key', [
    'type' => 'text',
    'label' => 'License Key',
    'default' => 'XXXX-XXXX-XXXX-XXXX',
    'attributes' => [
        'readonly' => true,
    ],
]);
```

### With Conditional Display

```php
$stack->field('custom_title', [
    'type' => 'text',
    'label' => 'Custom Title',
    'conditions' => [
        ['field' => 'use_custom_title', 'operator' => '==', 'value' => true],
    ],
]);
```

## Retrieving Value

```php
// For options
$title = my_option('site_title', 'Default Title');

// For post meta
$title = get_post_meta($post_id, 'site_title', true);
```

## Output Example

```php
<h1><?php echo esc_html(my_option('site_title', 'My Website')); ?></h1>
```

## Related Fields

- [textarea](./textarea.md) - For multi-line text
- [email](./email.md) - For email addresses
- [url](./url.md) - For URLs
