# Textarea Field

A multi-line text input field for longer text content.

## Basic Usage

```php
$stack->field('description', [
    'type' => 'textarea',
    'label' => 'Description',
    'default' => '',
    'description' => 'Enter a detailed description',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'textarea'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default value |
| `attributes` | array | `[]` | Additional HTML attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `rows` | int | `5` | Number of visible text rows |
| `cols` | int | - | Number of visible text columns |
| `placeholder` | string | `''` | Placeholder text |
| `maxlength` | int | - | Maximum character length |
| `readonly` | bool | `false` | Make field read-only |
| `disabled` | bool | `false` | Disable the field |

## Examples

### Basic Textarea

```php
$stack->field('bio', [
    'type' => 'textarea',
    'label' => 'Biography',
    'description' => 'Write a short biography',
    'attributes' => [
        'rows' => 4,
        'placeholder' => 'Tell us about yourself...',
    ],
]);
```

### With Character Limit

```php
$stack->field('meta_description', [
    'type' => 'textarea',
    'label' => 'Meta Description',
    'description' => 'Maximum 160 characters for SEO',
    'attributes' => [
        'rows' => 2,
        'maxlength' => 160,
    ],
]);
```

### Large Text Area

```php
$stack->field('terms_content', [
    'type' => 'textarea',
    'label' => 'Terms & Conditions',
    'attributes' => [
        'rows' => 15,
    ],
]);
```

### Copyright Text with Tokens

```php
$stack->field('copyright_text', [
    'type' => 'textarea',
    'label' => 'Copyright Text',
    'default' => '© {year} {site_name}. All rights reserved.',
    'description' => 'Use {year} for current year, {site_name} for site name',
    'attributes' => [
        'rows' => 2,
    ],
]);
```

## Retrieving Value

```php
// For options
$description = my_option('description', '');

// Process tokens
$copyright = my_option('copyright_text', '');
$copyright = str_replace(
    ['{year}', '{site_name}'],
    [date('Y'), get_bloginfo('name')],
    $copyright
);
```

## Output Example

```php
<div class="description">
    <?php echo nl2br(esc_html(my_option('description', ''))); ?>
</div>

// Or with HTML allowed
<div class="content">
    <?php echo wp_kses_post(my_option('content', '')); ?>
</div>
```

## Related Fields

- [text](./text.md) - For single-line text
- [wysiwyg](./wysiwyg.md) - For rich text with formatting
- [code](./code.md) - For code snippets
