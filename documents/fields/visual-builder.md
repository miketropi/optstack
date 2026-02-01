# Visual Builder Field

A drag-and-drop visual content builder for creating complex layouts with blocks.

## Basic Usage

```php
$stack->field('page_sections', [
    'type' => 'visual_builder',
    'label' => 'Page Sections',
    'description' => 'Build your page layout',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'visual_builder'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | array | `[]` | Default blocks/content |
| `attributes` | array | `[]` | Builder configuration |
| `conditions` | array | `[]` | Conditional display rules |

## Features

- **Drag-and-drop** block ordering
- **Block types** for different content (text, images, galleries, etc.)
- **Nested blocks** for complex layouts
- **Visual preview** of content structure
- **Repeatable sections** with min/max limits

## Examples

### Page Builder

```php
$stack->field('page_builder', [
    'type' => 'visual_builder',
    'label' => 'Page Builder',
    'description' => 'Drag and drop sections to build your page',
]);
```

### Homepage Sections

```php
$stack->field('homepage_sections', [
    'type' => 'visual_builder',
    'label' => 'Homepage Sections',
    'description' => 'Configure homepage layout blocks',
]);
```

### Landing Page Builder

```php
$stack->tab('landing_page', function ($tab) {
    $tab->field('hero_builder', [
        'type' => 'visual_builder',
        'label' => 'Hero Section Builder',
    ]);
    
    $tab->field('content_builder', [
        'type' => 'visual_builder',
        'label' => 'Content Sections',
    ]);
}, ['label' => 'Landing Page']);
```

## Retrieving Value

```php
// Returns array of blocks
$sections = my_option('page_sections', []);

// Iterate through blocks
foreach ($sections as $section) {
    $type = $section['type'] ?? '';
    $content = $section['content'] ?? [];
    
    // Render based on block type
}
```

## Output Example

```php
<?php
function render_page_sections() {
    $sections = my_option('page_sections', []);
    
    foreach ($sections as $section) {
        $type = $section['type'] ?? 'default';
        $data = $section['data'] ?? [];
        
        switch ($type) {
            case 'hero':
                render_hero_section($data);
                break;
            case 'features':
                render_features_section($data);
                break;
            case 'testimonials':
                render_testimonials_section($data);
                break;
            case 'cta':
                render_cta_section($data);
                break;
            default:
                render_default_section($data);
        }
    }
}

function render_hero_section($data) {
    $title = $data['title'] ?? '';
    $subtitle = $data['subtitle'] ?? '';
    $image = $data['image'] ?? '';
    ?>
    <section class="hero">
        <?php if ($image): ?>
            <img src="<?php echo esc_url($image); ?>" alt="">
        <?php endif; ?>
        <h1><?php echo esc_html($title); ?></h1>
        <p><?php echo esc_html($subtitle); ?></p>
    </section>
    <?php
}
```

## Documentation

For detailed Visual Builder documentation, see:
- [VISUAL_BLOCK_BUILDER_FIELD.md](../VISUAL_BLOCK_BUILDER_FIELD.md)

## Related Fields

- [wysiwyg](./wysiwyg.md) - For rich text content
- [media](./media.md) - For image selection
- [checkbox-group](./checkbox-group.md) - For multiple selections
