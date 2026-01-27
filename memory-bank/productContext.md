# OptStack - Product Context

## Why This Project Exists

WordPress lacks a unified, modern approach to structured data management. Existing solutions are:

- **UI-coupled** - Meta box libraries that mix data and presentation
- **Fragmented** - Different APIs for options vs post meta vs term meta
- **Legacy-bound** - Not designed for modern React-based admin UIs
- **Headless-unfriendly** - Difficult to expose via REST

## Problems It Solves

### 1. Data Definition Fragmentation
**Problem**: Defining fields for options requires different code than post meta or term meta.
**Solution**: Unified `field()` and `group()` syntax works across all contexts.

### 2. UI Lock-in
**Problem**: Traditional frameworks assume WordPress admin UI.
**Solution**: OptStack is UI-agnostic. React admin is just one renderer.

### 3. Schema Opacity
**Problem**: Field definitions live in PHP, making headless/JS consumption difficult.
**Solution**: Schema export provides JSON contracts for any consumer.

### 4. Testing Difficulty
**Problem**: Meta box code is hard to unit test without WordPress.
**Solution**: Core layer has zero WP dependencies, fully unit testable.

### 5. Nested/Repeatable Data
**Problem**: Complex data structures are awkward in WordPress.
**Solution**: First-class support for groups, nesting, and repeatables.

## How It Should Work

### Developer Experience
```php
// Define once, use everywhere
OptStack::make('site_settings')
    ->forOptions()
    ->define(function ($stack) {
        $stack->field('primary_color', ['type' => 'text']);
        $stack->group('social', function ($group) {
            $group->field('twitter');
            $group->field('facebook');
        });
    });

// Data is native WordPress
$settings = get_option('site_settings');
// ['primary_color' => '#000', 'social' => ['twitter' => '...', 'facebook' => '...']]
```

### Admin Experience
- Clean, modern React-based interface
- Conditional fields show/hide automatically
- Repeatable groups with drag-and-drop
- Real-time validation feedback

### Data Consumer Experience
```php
// Native WP functions work
get_option('site_settings');
get_post_meta($id, 'product_data', true);
get_term_meta($term_id, 'category_settings', true);
```

## User Experience Goals

1. **Intuitive API** - Fluent, chainable, readable
2. **Zero Surprise** - Data stored as expected in WP
3. **Modern Admin** - React UI that feels native
4. **Type Safety** - TypeScript schema contracts
5. **Extensible** - Easy to add custom field types
