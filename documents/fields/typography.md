# Typography Field

A comprehensive typography control with font family, size, weight, line height, letter spacing, and more.

## Basic Usage

```php
$stack->field('body_typography', [
    'type' => 'typography',
    'label' => 'Body Typography',
    'default' => [
        'fontFamily' => 'Inter, sans-serif',
        'fontSize' => 16,
        'fontSizeUnit' => 'px',
        'fontWeight' => '400',
        'lineHeight' => 1.6,
        'color' => '#374151',
    ],
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'typography'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | array | `[]` | Default typography values |
| `attributes` | array | `[]` | Font configuration |
| `conditions` | array | `[]` | Conditional display rules |

## Default Value Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `fontFamily` | string | `'inherit'` | Font family |
| `fontSize` | int\|float | `16` | Font size value |
| `fontSizeUnit` | string | `'px'` | Font size unit (px, em, rem) |
| `fontWeight` | string | `'400'` | Font weight (100-900, normal, bold) |
| `fontStyle` | string | `'normal'` | Font style (normal, italic) |
| `lineHeight` | float | `1.5` | Line height value |
| `lineHeightUnit` | string | `''` | Line height unit (empty for unitless) |
| `letterSpacing` | float | `0` | Letter spacing value |
| `letterSpacingUnit` | string | `'px'` | Letter spacing unit |
| `textTransform` | string | `'none'` | Text transform (none, uppercase, lowercase, capitalize) |
| `textDecoration` | string | `'none'` | Text decoration (none, underline, line-through) |
| `color` | string | `''` | Text color (hex format) |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `disableGoogleFonts` | bool | `false` | Only show system fonts |
| `fonts` | array | `[]` | Custom font list |

## Examples

### Body Typography

```php
$stack->field('body_font', [
    'type' => 'typography',
    'label' => 'Body Font',
    'default' => [
        'fontFamily' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'fontSize' => 16,
        'fontSizeUnit' => 'px',
        'fontWeight' => '400',
        'lineHeight' => 1.6,
        'color' => '#374151',
    ],
]);
```

### Heading Typography

```php
$stack->field('heading_font', [
    'type' => 'typography',
    'label' => 'Heading Font (H1-H6)',
    'default' => [
        'fontFamily' => '"Montserrat", sans-serif',
        'fontWeight' => '700',
        'lineHeight' => 1.2,
        'textTransform' => 'none',
        'color' => '#111827',
    ],
]);
```

### Menu Typography

```php
$stack->field('menu_font', [
    'type' => 'typography',
    'label' => 'Menu Font',
    'default' => [
        'fontFamily' => 'inherit',
        'fontSize' => 15,
        'fontSizeUnit' => 'px',
        'fontWeight' => '500',
        'textTransform' => 'none',
        'letterSpacing' => 0.5,
        'letterSpacingUnit' => 'px',
    ],
]);
```

### Button Typography

```php
$stack->field('button_font', [
    'type' => 'typography',
    'label' => 'Button Font',
    'default' => [
        'fontFamily' => 'inherit',
        'fontSize' => 14,
        'fontSizeUnit' => 'px',
        'fontWeight' => '600',
        'textTransform' => 'uppercase',
        'letterSpacing' => 0.5,
        'letterSpacingUnit' => 'px',
    ],
    'attributes' => [
        'disableGoogleFonts' => true,
    ],
]);
```

### With Custom Font List

```php
$stack->field('nav_typography', [
    'type' => 'typography',
    'label' => 'Navigation Typography',
    'default' => [
        'fontFamily' => '"Poppins", sans-serif',
        'fontSize' => 14,
        'fontSizeUnit' => 'px',
        'fontWeight' => '500',
    ],
    'attributes' => [
        'fonts' => [
            ['value' => 'inherit', 'label' => 'Default (Inherit)', 'category' => 'system'],
            ['value' => 'system-ui, sans-serif', 'label' => 'System UI', 'category' => 'system'],
            ['value' => 'Inter', 'label' => 'Inter', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
            ['value' => 'Poppins', 'label' => 'Poppins', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
            ['value' => 'Montserrat', 'label' => 'Montserrat', 'category' => 'google', 'variants' => ['400', '500', '600', '700']],
        ],
    ],
]);
```

### Complete Typography Tab

```php
$stack->tab('typography', function ($tab) {
    $tab->field('body_font', [
        'type' => 'typography',
        'label' => 'Body Font',
        'default' => [
            'fontFamily' => '"Inter", sans-serif',
            'fontSize' => 16,
            'fontSizeUnit' => 'px',
            'fontWeight' => '400',
            'lineHeight' => 1.6,
            'color' => '#374151',
        ],
    ]);
    
    $tab->field('heading_font', [
        'type' => 'typography',
        'label' => 'Heading Font',
        'default' => [
            'fontFamily' => '"Montserrat", sans-serif',
            'fontWeight' => '700',
            'lineHeight' => 1.2,
            'color' => '#111827',
        ],
    ]);
    
    $tab->field('menu_font', [
        'type' => 'typography',
        'label' => 'Menu Font',
        'default' => [
            'fontFamily' => 'inherit',
            'fontSize' => 15,
            'fontSizeUnit' => 'px',
            'fontWeight' => '500',
        ],
    ]);
}, ['label' => 'Typography']);
```

## Retrieving Value

```php
// Returns array with all typography properties
$body_font = my_option('body_font', []);

// Access individual properties
$font_family = $body_font['fontFamily'] ?? 'inherit';
$font_size = $body_font['fontSize'] ?? 16;
$font_weight = $body_font['fontWeight'] ?? '400';
$line_height = $body_font['lineHeight'] ?? 1.6;
$color = $body_font['color'] ?? '#374151';
```

## Output Example

### Generate CSS

```php
function generate_typography_css() {
    $css = '';
    
    // Body typography
    $body = my_option('body_font', []);
    if (!empty($body)) {
        $css .= "body {\n";
        if (!empty($body['fontFamily'])) {
            $css .= "    font-family: {$body['fontFamily']};\n";
        }
        if (!empty($body['fontSize'])) {
            $unit = $body['fontSizeUnit'] ?? 'px';
            $css .= "    font-size: {$body['fontSize']}{$unit};\n";
        }
        if (!empty($body['fontWeight'])) {
            $css .= "    font-weight: {$body['fontWeight']};\n";
        }
        if (!empty($body['lineHeight'])) {
            $unit = $body['lineHeightUnit'] ?? '';
            $css .= "    line-height: {$body['lineHeight']}{$unit};\n";
        }
        if (!empty($body['color'])) {
            $css .= "    color: {$body['color']};\n";
        }
        $css .= "}\n\n";
    }
    
    // Heading typography
    $heading = my_option('heading_font', []);
    if (!empty($heading)) {
        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        if (!empty($heading['fontFamily'])) {
            $css .= "    font-family: {$heading['fontFamily']};\n";
        }
        if (!empty($heading['fontWeight'])) {
            $css .= "    font-weight: {$heading['fontWeight']};\n";
        }
        if (!empty($heading['lineHeight'])) {
            $css .= "    line-height: {$heading['lineHeight']};\n";
        }
        if (!empty($heading['color'])) {
            $css .= "    color: {$heading['color']};\n";
        }
        $css .= "}\n";
    }
    
    return $css;
}

function output_typography_styles() {
    $css = generate_typography_css();
    if (!empty($css)) {
        echo '<style id="theme-typography">' . $css . '</style>';
    }
}
add_action('wp_head', 'output_typography_styles', 50);
```

### CSS Variables

```php
function output_typography_vars() {
    $body = my_option('body_font', []);
    $heading = my_option('heading_font', []);
    ?>
    <style>
    :root {
        --font-body: <?php echo esc_attr($body['fontFamily'] ?? 'inherit'); ?>;
        --font-heading: <?php echo esc_attr($heading['fontFamily'] ?? 'inherit'); ?>;
        --font-size-base: <?php echo esc_attr($body['fontSize'] ?? 16); ?><?php echo esc_attr($body['fontSizeUnit'] ?? 'px'); ?>;
        --line-height-base: <?php echo esc_attr($body['lineHeight'] ?? 1.6); ?>;
        --color-text: <?php echo esc_attr($body['color'] ?? '#374151'); ?>;
    }
    </style>
    <?php
}
add_action('wp_head', 'output_typography_vars', 10);
```

## Related Fields

- [select](./select.md) - For simple font family selection
- [number](./number.md) - For individual size inputs
- [color](./color.md) - For text color only
