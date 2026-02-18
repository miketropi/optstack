# Register Gutenberg Blocks with OptStack

A developer guide to create Gutenberg blocks powered by OptStack—define the schema, OptStack handles the block UI and attributes.

---

## 1. Load Your Block File

Ensure your block definition file is loaded by OptStack:

```php
// In your theme's functions.php or plugin bootstrap
require_once get_template_directory() . '/path/to/your-blocks.php';
```

Or add it in `optstack.php`:

```php
require_once OPTSTACK_DIR . 'examples/block-example.php';
```

---

## 2. Define the Stack (Block Registration)

Inside an `optstack_init` callback, define your stack with `forBlockType()`:

```php
add_action('optstack_init', function (): void {
    OptStack::make('my_hero_block')
        ->forBlockType('mytheme/hero')
        ->label('Hero Block')
        ->blockTitle('Hero')
        ->blockCategory('theme')
        ->blockIcon('cover-image')
        ->description('Hero section with title and background')
        ->define(function ($stack) {
            $stack->field('title', [
                'type'    => 'text',
                'label'   => 'Title',
                'default' => 'Welcome',
            ]);
            $stack->field('subtitle', [
                'type'    => 'textarea',
                'label'   => 'Subtitle',
                'default' => '',
            ]);
            $stack->field('background_color', [
                'type'    => 'color',
                'label'   => 'Background Color',
                'default' => '#2271b1',
            ]);
            $stack->field('image', [
                'type'  => 'media',
                'label' => 'Background Image',
            ]);
        })
        ->build();
});
```

| Method | Required | Description |
|--------|----------|-------------|
| `OptStack::make($stackId)` | Yes | Unique stack ID |
| `->forBlockType($name)` | Yes | Block name (e.g. `mytheme/hero`) |
| `->label($label)` | Yes | Admin label |
| `->blockTitle($title)` | Yes | Block title in inserter |
| `->blockCategory($category)` | Yes | `theme`, `design`, `widgets`, etc. |
| `->blockIcon($icon)` | Yes | [Dashicon](https://developer.wordpress.org/resource/dashicons/) name |
| `->description($desc)` | No | Block description |
| `->define(callable)` | Yes | Field definitions |
| `->build()` | Yes | Registers the block |

---

## 3. Add the Render Callback

Provide frontend HTML via the `optstack_render_block` filter:

```php
add_filter('optstack_render_block', function (string $html, string $stackId, array $attributes, $block): string {
    if ($stackId !== 'my_hero_block') {
        return $html;
    }

    $title = $attributes['title'] ?? 'Welcome';
    $subtitle = $attributes['subtitle'] ?? '';
    $bgColor = $attributes['background_color'] ?? '#2271b1';
    $image = $attributes['image'] ?? [];
    $imageUrl = is_array($image) && !empty($image['url']) ? $image['url'] : '';

    ob_start();
    ?>
    <div class="my-hero" style="background-color: <?php echo esc_attr($bgColor); ?>;">
        <h2><?php echo esc_html($title); ?></h2>
        <?php if ($subtitle): ?>
            <p><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        <?php if ($imageUrl): ?>
            <img src="<?php echo esc_url($imageUrl); ?>" alt="" />
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}, 10, 4);
```

**Filter signature:** `($html, $stackId, $attributes, $block)` — return HTML for your `$stackId`, otherwise return `$html`.

---

## 4. Supported Field Types

| Type | Block attribute | Notes |
|------|-----------------|-------|
| `text` | `string` | Single line |
| `textarea` | `string` | Multi-line |
| `url` | `string` | URL field |
| `email` | `string` | Email |
| `color` | `string` | Hex color |
| `date`, `datetime`, `time` | `string` | Date/time |
| `number` | `number` | Numeric |
| `range` | `number` | Slider with `attributes.min/max/step` |
| `toggle`, `boolean`, `checkbox` | `boolean` | On/off |
| `select`, `radio` | `string` | Use `options` array |
| `media` | `object` | `{ id, url, alt }` |
| `wysiwyg`, `code` | `string` | Rich text / code |

---

## 5. Groups & Repeatable Items

### Non-repeatable group

```php
$stack->group('settings', function ($group) {
    $group->field('layout', ['type' => 'select', 'label' => 'Layout', 'options' => [...]]);
    $group->field('padding', ['type' => 'number', 'label' => 'Padding', 'default' => 24]);
});
// In render: $attributes['settings']['layout'], $attributes['settings']['padding']
```

### Repeatable group (repeater)

```php
$stack->group('items', function ($group) {
    $group->repeatable(0, 10);
    $group->field('title', ['type' => 'text', 'label' => 'Title']);
    $group->field('description', ['type' => 'textarea', 'label' => 'Description']);
});
// In render: $attributes['items'] is array of objects [{title, description}, ...]
```

### Nested: group with repeatable inside (modal UI)

```php
$stack->group('testimonials', function ($group) {
    $group->group('items', function ($inner) {
        $inner->repeatable(0, 5);
        $inner->field('quote', ['type' => 'textarea', 'label' => 'Quote']);
        $inner->field('name', ['type' => 'text', 'label' => 'Name']);
    });
}, [
    'label'   => 'Testimonials',
    'deferred' => true,
    'ui'      => ['triggerLabel' => 'Configure', 'render' => 'modal'],
]);
// In render: $attributes['testimonials']['items'] = [{quote, name}, ...]
```

---

## 6. Quick Reference

### Minimal block

```php
add_action('optstack_init', function (): void {
    OptStack::make('simple_block')
        ->forBlockType('mytheme/simple')
        ->label('Simple Block')
        ->blockTitle('Simple')
        ->blockCategory('theme')
        ->blockIcon('block-default')
        ->define(fn ($s) => $s->field('text', ['type' => 'text', 'label' => 'Text']))
        ->build();
});

add_filter('optstack_render_block', function ($html, $stackId, $attrs, $block) {
    if ($stackId !== 'simple_block') return $html;
    return '<div>' . esc_html($attrs['text'] ?? '') . '</div>';
}, 10, 4);
```

### Common block icons (Dashicons)

`block-default`, `cover-image`, `megaphone`, `format-quote`, `grid-view`, `chart-bar`, `admin-generic`

### Block categories

`theme`, `design`, `widgets`, `embed`, `text`, `media`, `layout`

---

## 7. Checklist

- [ ] Load block file before `optstack_init`
- [ ] Use unique `stackId` and `forBlockType()` name
- [ ] Call `->build()`
- [ ] Add `optstack_render_block` filter for your `$stackId`
- [ ] Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
