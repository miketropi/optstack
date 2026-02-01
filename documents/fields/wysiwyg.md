# WYSIWYG Field

A rich text editor (What You See Is What You Get) based on the WordPress visual editor.

## Basic Usage

```php
$stack->field('content', [
    'type' => 'wysiwyg',
    'label' => 'Content',
    'description' => 'Enter rich text content',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'wysiwyg'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default HTML content |
| `attributes` | array | `[]` | Editor configuration |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `rows` | int | `10` | Number of visible rows |
| `simple` | bool | `false` | Use simplified toolbar |
| `media_buttons` | bool | `true` | Show media upload button |
| `teeny` | bool | `false` | Use minimal editor |

## Examples

### Full Editor

```php
$stack->field('page_content', [
    'type' => 'wysiwyg',
    'label' => 'Page Content',
    'description' => 'Main content area with full formatting options',
    'attributes' => [
        'rows' => 15,
        'media_buttons' => true,
    ],
]);
```

### Simple Editor

```php
$stack->field('excerpt', [
    'type' => 'wysiwyg',
    'label' => 'Excerpt',
    'description' => 'Short description with basic formatting',
    'attributes' => [
        'rows' => 5,
        'simple' => true,
    ],
]);
```

### Maintenance Message

```php
$stack->field('maintenance_message', [
    'type' => 'wysiwyg',
    'label' => 'Maintenance Message',
    'default' => '<p>We are currently performing scheduled maintenance. Please check back soon.</p>',
    'attributes' => [
        'rows' => 5,
        'simple' => true,
    ],
    'conditions' => [
        ['field' => 'enable_maintenance', 'operator' => '==', 'value' => true],
    ],
]);
```

### Top Bar Content

```php
$stack->group('top_bar', function ($group) {
    $group->field('enable', [
        'type' => 'toggle',
        'label' => 'Enable Top Bar',
        'default' => false,
    ]);
    
    $group->field('content_left', [
        'type' => 'wysiwyg',
        'label' => 'Left Content',
        'description' => 'Contact info, opening hours, etc.',
        'attributes' => [
            'rows' => 3,
            'simple' => true,
        ],
        'conditions' => [
            ['field' => 'enable', 'operator' => '==', 'value' => true],
        ],
    ]);
    
    $group->field('content_right', [
        'type' => 'wysiwyg',
        'label' => 'Right Content',
        'description' => 'Social icons, quick links, etc.',
        'attributes' => [
            'rows' => 3,
            'simple' => true,
        ],
        'conditions' => [
            ['field' => 'enable', 'operator' => '==', 'value' => true],
        ],
    ]);
}, ['label' => 'Top Bar']);
```

### About Section

```php
$stack->field('about_content', [
    'type' => 'wysiwyg',
    'label' => 'About Section',
    'default' => '<h2>About Us</h2><p>Tell your story here...</p>',
    'attributes' => [
        'rows' => 10,
    ],
]);
```

### Footer Text

```php
$stack->field('footer_about', [
    'type' => 'wysiwyg',
    'label' => 'Footer About Text',
    'description' => 'Brief company description for footer',
    'attributes' => [
        'rows' => 4,
        'simple' => true,
        'media_buttons' => false,
    ],
]);
```

## Retrieving Value

```php
// For options (returns HTML string)
$content = my_option('content', '');

// With shortcode processing
$content = do_shortcode(my_option('content', ''));
```

## Output Example

### Safe HTML Output

```php
<?php
$content = my_option('page_content', '');
?>
<div class="content">
    <?php echo wp_kses_post($content); ?>
</div>
```

### With Shortcode Support

```php
<?php
$content = my_option('about_content', '');
?>
<section class="about">
    <?php echo do_shortcode(wp_kses_post($content)); ?>
</section>
```

### Maintenance Page

```php
<?php
if (my_option('maintenance.enable', false) && !current_user_can('administrator')):
    $title = my_option('maintenance.title', 'Site Under Maintenance');
    $message = my_option('maintenance.message', '');
    
    wp_die(
        wp_kses_post($message),
        esc_html($title),
        ['response' => 503]
    );
endif;
```

### Top Bar

```php
<?php if (my_option('top_bar.enable', false)): ?>
    <div class="top-bar">
        <div class="top-bar-left">
            <?php echo wp_kses_post(my_option('top_bar.content_left', '')); ?>
        </div>
        <div class="top-bar-right">
            <?php echo wp_kses_post(my_option('top_bar.content_right', '')); ?>
        </div>
    </div>
<?php endif; ?>
```

## Security

Always sanitize WYSIWYG output:

```php
// For post content (allows safe HTML)
echo wp_kses_post($content);

// For more restrictive output
echo wp_kses($content, [
    'p' => [],
    'a' => ['href' => [], 'target' => []],
    'strong' => [],
    'em' => [],
]);

// Plain text only
echo wp_strip_all_tags($content);
```

## Related Fields

- [textarea](./textarea.md) - For plain text without formatting
- [code](./code.md) - For code snippets
- [text](./text.md) - For single-line text
