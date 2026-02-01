# Code Field

A code editor with syntax highlighting for CSS, JavaScript, HTML, and other languages.

## Basic Usage

```php
$stack->field('custom_css', [
    'type' => 'code',
    'label' => 'Custom CSS',
    'description' => 'Add your custom CSS here',
    'attributes' => [
        'language' => 'text/css',
        'rows' => 15,
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'code'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default code content |
| `attributes` | array | `[]` | Editor configuration |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `language` | string | `'text/plain'` | Syntax highlighting language |
| `rows` | int | `10` | Number of visible rows |

### Supported Languages

| Language | MIME Type |
|----------|-----------|
| CSS | `text/css` |
| JavaScript | `application/javascript` |
| HTML | `text/html` |
| PHP | `application/x-httpd-php` |
| JSON | `application/json` |
| Plain Text | `text/plain` |

## Examples

### Custom CSS

```php
$stack->field('custom_css', [
    'type' => 'code',
    'label' => 'Custom CSS',
    'description' => 'Add your custom CSS styles here',
    'attributes' => [
        'language' => 'text/css',
        'rows' => 20,
    ],
]);
```

### Custom JavaScript

```php
$stack->field('custom_js', [
    'type' => 'code',
    'label' => 'Custom JavaScript',
    'description' => 'Add your custom JS here (without <script> tags)',
    'attributes' => [
        'language' => 'application/javascript',
        'rows' => 15,
    ],
]);
```

### Header Code

```php
$stack->field('header_code', [
    'type' => 'code',
    'label' => 'Header Code',
    'description' => 'Code to insert in <head> section (analytics, meta tags, etc.)',
    'attributes' => [
        'language' => 'text/html',
        'rows' => 10,
    ],
]);
```

### Footer Code

```php
$stack->field('footer_code', [
    'type' => 'code',
    'label' => 'Footer Code',
    'description' => 'Code to insert before </body> tag',
    'attributes' => [
        'language' => 'text/html',
        'rows' => 10,
    ],
]);
```

### Schema Markup

```php
$stack->field('schema_json', [
    'type' => 'code',
    'label' => 'Custom Schema Markup',
    'description' => 'JSON-LD structured data',
    'attributes' => [
        'language' => 'application/json',
        'rows' => 15,
    ],
]);
```

### Custom Code Tab

```php
$stack->tab('custom_code', function ($tab) {
    $tab->field('custom_css', [
        'type' => 'code',
        'label' => 'Custom CSS',
        'attributes' => [
            'language' => 'text/css',
            'rows' => 20,
        ],
    ]);
    
    $tab->field('custom_js', [
        'type' => 'code',
        'label' => 'Custom JavaScript',
        'attributes' => [
            'language' => 'application/javascript',
            'rows' => 15,
        ],
    ]);
    
    $tab->field('header_code', [
        'type' => 'code',
        'label' => 'Header Code',
        'description' => 'Code to insert in <head> section',
        'attributes' => [
            'language' => 'text/html',
            'rows' => 10,
        ],
    ]);
    
    $tab->field('footer_code', [
        'type' => 'code',
        'label' => 'Footer Code',
        'description' => 'Code to insert before </body> tag',
        'attributes' => [
            'language' => 'text/html',
            'rows' => 10,
        ],
    ]);
}, ['label' => 'Custom Code']);
```

## Retrieving Value

```php
// For options
$css = my_option('custom_css', '');
$js = my_option('custom_js', '');
$header_code = my_option('header_code', '');
```

## Output Example

### Custom CSS

```php
/**
 * Output custom CSS from theme options.
 */
function output_custom_css(): void
{
    $custom_css = my_option('custom_css', '');
    
    if (!empty($custom_css)) {
        echo '<style id="theme-custom-css">' . wp_strip_all_tags($custom_css) . '</style>';
    }
}
add_action('wp_head', 'output_custom_css', 999);
```

### Custom JavaScript

```php
/**
 * Output custom JavaScript from theme options.
 */
function output_custom_js(): void
{
    $custom_js = my_option('custom_js', '');
    
    if (!empty($custom_js)) {
        echo '<script id="theme-custom-js">' . $custom_js . '</script>';
    }
}
add_action('wp_footer', 'output_custom_js', 999);
```

### Header Code

```php
/**
 * Output header code from theme options.
 */
function output_header_code(): void
{
    $header_code = my_option('header_code', '');
    
    if (!empty($header_code)) {
        echo $header_code;
    }
}
add_action('wp_head', 'output_header_code', 1);
```

### Footer Code

```php
/**
 * Output footer code from theme options.
 */
function output_footer_code(): void
{
    $footer_code = my_option('footer_code', '');
    
    if (!empty($footer_code)) {
        echo $footer_code;
    }
}
add_action('wp_footer', 'output_footer_code', 999);
```

## Security Considerations

- **CSS**: Use `wp_strip_all_tags()` to prevent XSS
- **JavaScript**: Be cautious - only allow trusted admins to edit
- **HTML**: May contain scripts - limit who can edit
- **Consider capabilities**: Check user permissions before outputting

```php
// Only output if user has appropriate permissions
if (current_user_can('unfiltered_html')) {
    echo $header_code;
}
```

## Related Fields

- [textarea](./textarea.md) - For plain text without highlighting
- [wysiwyg](./wysiwyg.md) - For rich text content
- [text](./text.md) - For single-line code snippets
