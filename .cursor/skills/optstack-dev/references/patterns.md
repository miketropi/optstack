# Common Patterns

## Table of Contents

1. [Stack Patterns](#stack-patterns)
2. [Data Access Patterns](#data-access-patterns)
3. [Query Patterns](#query-patterns)
4. [React Component Patterns](#react-component-patterns)
5. [Development Patterns](#development-patterns)

---

## Stack Patterns

### Options Page Under Custom Menu

```php
OptStack::make('site_settings')
    ->forOptions()
    ->menuParent('optstack')  // Under OptStack menu
    ->label('Site Settings')
    ->define(function($stack) {
        $stack->field('site_name', ['type' => 'text']);
    })
    ->build();
```

### Options Page Under Appearance

```php
OptStack::make('theme_options')
    ->forOptions()
    ->menuParent('themes.php')
    ->label('Theme Options')
    ->define(fn($stack) => /* ... */)
    ->build();
```

### Post Type Meta Box

```php
OptStack::make('product_data')
    ->forPostType('product')  // or 'post', 'page', etc.
    ->label('Product Data')
    ->define(function($stack) {
        $stack->field('price', ['type' => 'number', 'searchable' => true]);
    })
    ->build();
```

### Taxonomy Term Meta

```php
OptStack::make('category_settings')
    ->forTaxonomy('category')
    ->label('Category Settings')
    ->define(fn($stack) => /* ... */)
    ->build();
```

### User Profile Fields

```php
OptStack::make('user_preferences')
    ->forUser()
    ->label('Preferences')
    ->define(fn($stack) => /* ... */)
    ->build();
```

### Tabs for Organization

```php
$stack->tab('general', function($tab) {
    $tab->label('General')->priority(10);
    $tab->field('logo', ['type' => 'media']);
});

$stack->tab('advanced', function($tab) {
    $tab->label('Advanced')->priority(20);
    $tab->field('custom_css', ['type' => 'code']);
});
```

### Conditional Fields

```php
$stack->field('enable_caching', ['type' => 'toggle']);

$stack->field('cache_duration', [
    'type' => 'number',
    'conditions' => [
        ['field' => 'enable_caching', 'operator' => '==', 'value' => true]
    ],
]);

// Nested condition
$stack->field('redis_host', [
    'type' => 'text',
    'conditions' => [
        ['field' => 'enable_caching', 'operator' => '==', 'value' => true],
        ['field' => 'cache_driver', 'operator' => '==', 'value' => 'redis'],
    ],
]);
```

### Repeatable Group

```php
$stack->group('team', function($group) {
    $group->repeatable(1, 10);  // 1-10 items
    
    $group->field('name', ['type' => 'text']);
    $group->field('role', ['type' => 'text']);
    $group->field('photo', ['type' => 'media']);
}, ['label' => 'Team Members', 'layout' => 'box', 'collapsible' => true]);
```

### Deferred Group (Modal)

Use deferred groups to reduce UI clutter. Fields open in a modal on click.

```php
// SEO settings in a modal
$stack->group('seo', function($group) {
    $group->field('meta_title', ['type' => 'text']);
    $group->field('meta_description', ['type' => 'textarea']);
    $group->field('noindex', ['type' => 'toggle', 'default' => false]);
}, [
    'label' => 'SEO Settings',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Configure SEO',
        'render' => 'modal',  // or 'drawer', 'panel'
    ],
]);

// Advanced settings in a drawer (slides from right)
$stack->group('advanced', function($group) {
    $group->field('custom_css', ['type' => 'code']);
    $group->field('tracking_code', ['type' => 'textarea']);
}, [
    'label' => 'Advanced',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Advanced Settings',
        'render' => 'drawer',
    ],
]);
```

---

## Data Access Patterns

### Native WordPress (Recommended for Templates)

```php
// Options
$settings = get_option('site_settings');
echo $settings['site_name'];

// Post meta
$product = get_post_meta($post_id, 'product_data', true);
echo $product['price'];
echo $product['seo']['title'];  // nested

// Term meta
$cat = get_term_meta($term_id, 'category_settings', true);

// User meta
$prefs = get_user_meta($user_id, 'user_preferences', true);
```

### Via OptStack Facade

```php
// Get entire stack data
$data = OptStack::getData('product_data');

// Get specific field with default
$price = OptStack::getData('product_data', 'price', 0);
```

### Single Field Update (Auto-syncs Searchable)

```php
// REQUIRED: object_id for post/term/user contexts
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// Nested field (dot notation)
OptStack::updateField('product_data', 'seo.title', 'New Title', $post_id);

// Options (no object_id needed)
OptStack::updateField('site_settings', 'site_color', '#FF5733');
```

### Bulk Update

```php
OptStack::saveData('product_data', [
    'price' => 99.99,
    'status' => 'active',
    'seo' => ['title' => 'Product', 'description' => '...'],
]);
```

---

## Query Patterns

### WP_Query with Searchable Fields

```php
// Simple query
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [[
        'key' => '_optstack_idx_post_price',
        'value' => 100,
        'compare' => '>=',
        'type' => 'NUMERIC',
    ]],
]);

// Multiple conditions
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_price',
            'value' => [50, 200],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC',
        ],
    ],
]);

// Order by indexed field
$products = new WP_Query([
    'post_type' => 'product',
    'orderby' => 'meta_value_num',
    'meta_key' => '_optstack_idx_post_price',
    'order' => 'ASC',
]);
```

### Get Indexed Meta Keys

```php
$bootstrap = \OptStack\WordPress\Bootstrap::getInstance();
$manager = $bootstrap->getIndexedMetaManager();
$stack = OptStack::get('product_data');
$keys = $manager->getIndexedMetaKeys($stack);
// ['price' => '_optstack_idx_post_price', ...]
```

---

## React Component Patterns

### Field Component Structure

```typescript
// frontend/src/components/fields/MyField.tsx
import type { FieldRendererProps } from '../../schema/types'

export function MyField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const currentValue = value ?? field.default
  
  return (
    <div className={`os-field os-field-myfield ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        {/* Field implementation */}
        <input
          type="text"
          className="os-input"
          value={currentValue || ''}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
        />
        
        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
```

### Register Field Component

```typescript
// frontend/src/components/FieldRenderer.tsx
import { MyField } from './fields/MyField'

const fieldComponents = {
  // ...existing
  'my-field': MyField,
  'myfield': MyField,  // alias
}
```

### CSS Naming Convention

Use `os-` prefix for all classes:

```css
/* frontend/src/styles/main.css */
.os-field-myfield { }
.os-myfield-wrapper { }
.os-myfield-input { }
.os-myfield-btn { }
```

---

## Development Patterns

### Enable Dev Mode

```php
// wp-config.php
define('OPTSTACK_DEV_MODE', true);
define('OPTSTACK_DEV_SERVER', 'http://localhost:5173');  // optional
```

### Frontend Commands

```bash
cd frontend

# Development with HMR
npm run dev

# Production build
npm run build

# Type check
npm run type-check
```

### Debug Hooks

```php
// Log all stack registrations
add_action('optstack_ready', function() {
    error_log('Stacks: ' . print_r(array_keys(OptStack::all()), true));
});

// Log data saves
add_action('optstack_data_saved', function($stack, $objectId, $type, $data) {
    error_log("Saved {$stack->getId()} for {$type} #{$objectId}");
}, 10, 4);

// Log searchable field syncs
add_action('optstack_searchable_field_synced', function($stack, $path, $value, $id) {
    error_log("Synced {$stack->getId()}::{$path} = {$value} for #{$id}");
}, 10, 4);
```

### PHP Syntax Check

```bash
php -l src/Core/Stack/Stack.php
php -l examples/basic-usage.php
```

### Verify Store Binding

```php
$stack = OptStack::get('my_stack');
$store = $stack->getStore();
if ($store === null) {
    error_log('Store not bound!');
}
```

### Check Searchable Fields Resolution

```php
$stack = OptStack::get('product_data');
$resolver = new \OptStack\Core\Index\SearchableFieldResolver();
$fields = $resolver->resolve($stack);
error_log('Searchable: ' . print_r(array_keys($fields), true));
```
