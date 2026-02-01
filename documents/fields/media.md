# Media Field

A media picker that integrates with the WordPress Media Library.

## Basic Usage

```php
$stack->field('site_logo', [
    'type' => 'media',
    'label' => 'Site Logo',
    'description' => 'Upload your site logo',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'media'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | int | `null` | Default attachment ID |
| `attributes` | array | `[]` | Additional attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `allowedTypes` | array | `['image']` | Allowed file types |
| `buttonText` | string | `'Select Media'` | Button label text |
| `multiple` | bool | `false` | Allow multiple selection |

### Allowed Types

- `'image'` - Images (jpg, png, gif, webp, svg)
- `'video'` - Videos (mp4, webm, mov)
- `'audio'` - Audio files (mp3, wav, ogg)
- `'application'` - Documents (pdf, doc, etc.)

## Examples

### Site Logo

```php
$stack->field('site_logo', [
    'type' => 'media',
    'label' => 'Site Logo',
    'description' => 'Upload your site logo (recommended: 200x50px)',
    'attributes' => [
        'allowedTypes' => ['image'],
        'buttonText' => 'Select Logo',
    ],
]);
```

### Favicon

```php
$stack->field('favicon', [
    'type' => 'media',
    'label' => 'Favicon',
    'description' => 'Site favicon (32x32px or 64x64px recommended)',
    'attributes' => [
        'allowedTypes' => ['image'],
        'buttonText' => 'Select Favicon',
    ],
]);
```

### Open Graph Image

```php
$stack->field('default_share_image', [
    'type' => 'media',
    'label' => 'Default Share Image',
    'description' => 'Used when no featured image (1200x630px recommended)',
    'attributes' => [
        'allowedTypes' => ['image'],
    ],
    'conditions' => [
        ['field' => 'enable_opengraph', 'operator' => '==', 'value' => true],
    ],
]);
```

### Background Image

```php
$stack->field('hero_background', [
    'type' => 'media',
    'label' => 'Hero Background Image',
    'description' => 'Large hero section background',
    'attributes' => [
        'allowedTypes' => ['image'],
        'buttonText' => 'Choose Background',
    ],
]);
```

### Video Upload

```php
$stack->field('intro_video', [
    'type' => 'media',
    'label' => 'Introduction Video',
    'description' => 'Upload an MP4 video',
    'attributes' => [
        'allowedTypes' => ['video'],
        'buttonText' => 'Select Video',
    ],
]);
```

### PDF Document

```php
$stack->field('brochure', [
    'type' => 'media',
    'label' => 'Brochure PDF',
    'description' => 'Upload a downloadable brochure',
    'attributes' => [
        'allowedTypes' => ['application'],
        'buttonText' => 'Select PDF',
    ],
]);
```

## Retrieving Value

The media field stores the **attachment ID** (not the URL):

```php
// Get attachment ID
$logo_id = my_option('identity.site_logo');

// Get attachment URL
$logo_url = wp_get_attachment_url($logo_id);

// Get attachment image with specific size
$logo_img = wp_get_attachment_image($logo_id, 'full');

// Get image URL for specific size
$logo_src = wp_get_attachment_image_url($logo_id, 'medium');
```

## Output Example

### Logo with Fallback

```php
<?php
$logo_id = my_option('identity.site_logo');
$logo_width = my_option('identity.logo_width', 180);

if ($logo_id):
?>
    <a href="<?php echo home_url('/'); ?>" class="site-logo">
        <?php echo wp_get_attachment_image($logo_id, 'full', false, [
            'style' => 'max-width: ' . esc_attr($logo_width) . 'px;',
            'alt' => get_bloginfo('name'),
        ]); ?>
    </a>
<?php else: ?>
    <a href="<?php echo home_url('/'); ?>" class="site-title">
        <?php bloginfo('name'); ?>
    </a>
<?php endif; ?>
```

### Favicon

```php
<?php
$favicon_id = my_option('identity.favicon');

if ($favicon_id):
    $favicon_url = wp_get_attachment_url($favicon_id);
?>
    <link rel="icon" href="<?php echo esc_url($favicon_url); ?>" type="image/x-icon">
<?php endif; ?>
```

### Background Image

```php
<?php
$bg_id = my_option('hero_background');
$bg_url = $bg_id ? wp_get_attachment_url($bg_id) : '';
?>

<section class="hero" <?php echo $bg_url ? 'style="background-image: url(' . esc_url($bg_url) . ');"' : ''; ?>>
    <!-- Hero content -->
</section>
```

### Open Graph Meta

```php
<?php
function output_og_image() {
    if (is_singular() && has_post_thumbnail()) {
        $image = get_the_post_thumbnail_url(null, 'large');
    } else {
        $default_id = my_option('opengraph.default_image');
        $image = $default_id ? wp_get_attachment_url($default_id) : '';
    }
    
    if ($image):
?>
    <meta property="og:image" content="<?php echo esc_url($image); ?>">
<?php
    endif;
}
add_action('wp_head', 'output_og_image');
```

## Related Fields

- [url](./url.md) - For external media URLs
- [text](./text.md) - For manual URL input
