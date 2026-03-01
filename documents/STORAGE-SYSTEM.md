# OptStack Storage System Guide

This document provides a comprehensive guide on how OptStack stores and retrieves data using WordPress native storage mechanisms.

---

## Table of Contents

- [Overview](#overview)
- [Store Types](#store-types)
  - [OptionsStore](#optionsstore)
  - [PostStore](#poststore)
  - [TermStore](#termstore)
  - [UserStore](#userstore)
- [StoreInterface](#storeinterface)
- [How Data is Stored](#how-data-is-stored)
- [Automatic Store Binding](#automatic-store-binding)
- [Direct Store Access](#direct-store-access)
- [Storage Patterns](#storage-patterns)
- [Performance Considerations](#performance-considerations)
- [Debugging Storage](#debugging-storage)
- [Best Practices](#best-practices)

---

## Overview

OptStack uses a **Store Adapter pattern** to persist data to WordPress. Each store type wraps a WordPress storage mechanism and provides a consistent API for reading and writing data.

### Key Principles

1. **Single Serialized Array**: All stack data is stored as a single serialized array under one key
2. **Caching**: Stores cache loaded data to minimize database queries
3. **Transparent**: You typically don't interact with stores directly - OptStack handles it
4. **Consistent API**: All stores implement the same `StoreInterface`

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     OptStack Facade                          │
│  (OptStack::getField, OptStack::updateField, etc.)          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         Stack                                │
│              (getData, saveData, getField, etc.)            │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     StoreInterface                           │
│        (get, set, delete, all, has, setMany, etc.)          │
└─────────────────────────────────────────────────────────────┘
          │              │              │              │              │
          ▼              ▼              ▼              ▼              ▼
   OptionsStore  ThemeModStore   PostStore     TermStore      UserStore
   (wp_options)  (theme_mod)   (wp_postmeta) (wp_termmeta)  (wp_usermeta)
```

---

## Store Types

### OptionsStore

Stores data in the `wp_options` table. Used for global site settings, theme options, and plugin settings.

**WordPress Table:** `wp_options`

**Use Case:** Options pages, theme settings, global configurations

**Stack Definition:**
```php
OptStack::make('theme_options')
    ->forOptions()
    ->define(function ($stack) {
        $stack->field('site_name', ['type' => 'text']);
        $stack->field('primary_color', ['type' => 'color']);
    })
    ->build();
```

**How Data is Stored:**
```sql
-- In wp_options table
option_name: theme_options
option_value: a:2:{s:9:"site_name";s:7:"My Site";s:13:"primary_color";s:7:"#3b82f6";}
```

**Constructor:**
```php
new OptionsStore(string $optionName, bool $autoload = true)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$optionName` | string | The option name (stack ID) |
| `$autoload` | bool | Whether WordPress should autoload this option (default: true) |

**Unique Methods:**
```php
// Get the option name
$store->getOptionName(): string
```

---

### ThemeModStore

Stores data via WordPress theme mods (`get_theme_mod` / `set_theme_mod`). Used for theme-specific options, typically when the stack is shown in the **WordPress Customizer** (Appearance → Customize).

**WordPress API:** `get_theme_mod()` / `set_theme_mod()`

**Use Case:** Customizer-backed theme options, theme-specific settings

**Stack Definition:**
```php
OptStack::make('theme_options')
    ->forCustomizer('theme_mod')  // or ->forCustomizer('option') for wp_options
    ->label('Theme Options')
    ->define(function ($stack) {
        $stack->field('primary_color', ['type' => 'color', 'default' => '#2271b1']);
        $stack->field('site_tagline', ['type' => 'text']);
    })
    ->build();
```

**How Data is Stored:** One theme_mod key (the stack ID) holds the full serialized array, same shape as OptionsStore.

**Constructor:**
```php
new ThemeModStore(string $key)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | string | The theme_mod key (e.g. stack ID) |

**Unique Methods:**
```php
// Get the theme_mod key
$store->getKey(): string
```

See [CUSTOMIZER.md](./CUSTOMIZER.md) for full Customizer usage.

---

### PostStore

Stores data in the `wp_postmeta` table. Used for custom fields on posts, pages, and custom post types.

**WordPress Table:** `wp_postmeta`

**Use Case:** Post meta boxes, product data, page settings

**Stack Definition:**
```php
OptStack::make('product_data')
    ->forPostType('product')
    ->define(function ($stack) {
        $stack->field('price', ['type' => 'number']);
        $stack->field('sku', ['type' => 'text']);
    })
    ->build();
```

**How Data is Stored:**
```sql
-- In wp_postmeta table
post_id: 123
meta_key: product_data
meta_value: a:2:{s:5:"price";d:99.99;s:3:"sku";s:8:"SKU-1234";}
```

**Constructor:**
```php
new PostStore(int $postId, string $metaKey)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$postId` | int | The WordPress post ID |
| `$metaKey` | string | The meta key (stack ID) |

**Unique Methods:**
```php
// Get/set the post ID
$store->getPostId(): int
$store->setPostId(int $postId): self

// Get the meta key
$store->getMetaKey(): string
```

---

### TermStore

Stores data in the `wp_termmeta` table. Used for custom fields on taxonomy terms (categories, tags, custom taxonomies).

**WordPress Table:** `wp_termmeta`

**Use Case:** Category settings, tag metadata, custom taxonomy fields

**Stack Definition:**
```php
OptStack::make('category_settings')
    ->forTaxonomy('category')
    ->define(function ($stack) {
        $stack->field('icon', ['type' => 'media']);
        $stack->field('color', ['type' => 'color']);
    })
    ->build();
```

**How Data is Stored:**
```sql
-- In wp_termmeta table
term_id: 5
meta_key: category_settings
meta_value: a:2:{s:4:"icon";i:456;s:5:"color";s:7:"#10b981";}
```

**Constructor:**
```php
new TermStore(int $termId, string $metaKey)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$termId` | int | The WordPress term ID |
| `$metaKey` | string | The meta key (stack ID) |

**Unique Methods:**
```php
// Get/set the term ID
$store->getTermId(): int
$store->setTermId(int $termId): self

// Get the meta key
$store->getMetaKey(): string
```

---

### UserStore

Stores data in the `wp_usermeta` table. Used for custom user profile fields.

**WordPress Table:** `wp_usermeta`

**Use Case:** User profile extensions, user preferences, author information

**Stack Definition:**
```php
OptStack::make('user_profile')
    ->forUser()
    ->define(function ($stack) {
        $stack->field('bio', ['type' => 'textarea']);
        $stack->group('social', function ($group) {
            $group->field('twitter', ['type' => 'url']);
            $group->field('linkedin', ['type' => 'url']);
        });
    })
    ->build();
```

**How Data is Stored:**
```sql
-- In wp_usermeta table
user_id: 1
meta_key: user_profile
meta_value: a:2:{s:3:"bio";s:20:"About the author...";s:6:"social";a:2:{s:7:"twitter";s:24:"https://twitter.com/...";s:8:"linkedin";s:25:"https://linkedin.com/...";}}
```

**Constructor:**
```php
new UserStore(int $userId, string $metaKey)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$userId` | int | The WordPress user ID |
| `$metaKey` | string | The meta key (stack ID) |

**Unique Methods:**
```php
// Get/set the user ID
$store->getUserId(): int
$store->setUserId(int $userId): self

// Get the meta key
$store->getMetaKey(): string
```

---

## StoreInterface

All stores implement the `StoreInterface` contract, providing a consistent API:

```php
interface StoreInterface
{
    // Get a single value
    public function get(string $key, mixed $default = null): mixed;
    
    // Set a single value
    public function set(string $key, mixed $value): bool;
    
    // Delete a single key
    public function delete(string $key): bool;
    
    // Get all data
    public function all(): array;
    
    // Check if key exists
    public function has(string $key): bool;
}
```

### Additional Methods (All Stores)

Beyond the interface, all stores provide these additional methods:

```php
// Set multiple values at once
$store->setMany(array $values): bool

// Replace all data (overwrites everything)
$store->replace(array $data): bool

// Delete all data for this stack
$store->deleteAll(): bool

// Clear the internal cache
$store->clearCache(): void
```

---

## How Data is Stored

### Single Serialized Array Pattern

OptStack stores all stack data as a **single serialized array** under one key. This approach:

- Reduces database queries (one query loads all data)
- Keeps related data together
- Simplifies data management

**Example:**

For a stack with this structure:
```php
$stack->field('site_name', ['type' => 'text']);
$stack->group('colors', function ($group) {
    $group->field('primary', ['type' => 'color']);
    $group->field('secondary', ['type' => 'color']);
});
```

Data is stored as:
```php
[
    'site_name' => 'My Site',
    'colors' => [
        'primary' => '#3b82f6',
        'secondary' => '#8b5cf6',
    ]
]
```

Which is serialized in the database as:
```
a:2:{s:9:"site_name";s:7:"My Site";s:6:"colors";a:2:{s:7:"primary";s:7:"#3b82f6";s:9:"secondary";s:7:"#8b5cf6";}}
```

### Where to Find Stored Data

| Context | WordPress Function | Database Table | Key Column |
|---------|-------------------|----------------|------------|
| Options | `get_option()` | `wp_options` | `option_name` |
| Customizer (theme_mod) | `get_theme_mod()` | theme mods | key (stack ID) |
| Customizer (option) | `get_option()` | `wp_options` | `option_name` |
| Post Meta | `get_post_meta()` | `wp_postmeta` | `meta_key` |
| Term Meta | `get_term_meta()` | `wp_termmeta` | `meta_key` |
| User Meta | `get_user_meta()` | `wp_usermeta` | `meta_key` |

---

## Automatic Store Binding

OptStack automatically binds the appropriate store based on how you define your stack:

### For Options

```php
OptStack::make('theme_options')
    ->forOptions()  // ← Triggers OptionsStore binding
    ->build();
```

Store is bound immediately during `optstack_init`.

### For Customizer

```php
OptStack::make('theme_options')
    ->forCustomizer('theme_mod')  // ← ThemeModStore (theme-specific)
    ->label('Theme Options')
    ->define(function ($stack) { ... })
    ->build();

// Or use wp_options in the Customizer:
OptStack::make('plugin_settings')
    ->forCustomizer('option')  // ← OptionsStore
    ->define(function ($stack) { ... })
    ->build();
```

Store is bound during `optstack_init`. The stack appears in **Appearance → Customize** as a panel. See [CUSTOMIZER.md](./CUSTOMIZER.md).

### For Post Types

```php
// Single post type
OptStack::make('product_data')
    ->forPostType('product')  // ← Triggers PostStore binding
    ->build();

// Multiple post types
OptStack::make('seo_settings')
    ->forPostType(['post', 'page', 'product'])  // ← Same stack for multiple post types
    ->build();
```

Store is bound:
- When accessing via REST API with `object_id` parameter
- When using `OptStack::getField()` with `$post_id`
- During `save_post` hook

### For Taxonomies

```php
// Single taxonomy
OptStack::make('category_settings')
    ->forTaxonomy('category')  // ← Triggers TermStore binding
    ->build();

// Multiple taxonomies
OptStack::make('term_settings')
    ->forTaxonomy(['category', 'post_tag', 'product_cat'])  // ← Same stack for multiple taxonomies
    ->build();
```

Store is bound:
- When accessing via REST API with `object_id` parameter
- When using `OptStack::getField()` with `$term_id`
- During `created_term` and `edited_term` hooks

### For Users

```php
OptStack::make('user_profile')
    ->forUser()  // ← Triggers UserStore binding
    ->build();
```

Store is bound:
- When accessing via REST API with `object_id` parameter
- When using `OptStack::getField()` with `$user_id`
- During `profile_update` and `user_register` hooks

---

## Direct Store Access

While you typically use the OptStack facade, you can access stores directly for advanced use cases:

### Get Store from Stack

```php
$stack = OptStack::get('theme_options');
$store = $stack->getStore();

// Direct store operations
$value = $store->get('site_name', 'Default');
$store->set('site_name', 'New Name');
$allData = $store->all();
```

### Create Store Manually

```php
use OptStack\WordPress\Store\OptionsStore;
use OptStack\WordPress\Store\PostStore;
use OptStack\WordPress\Store\TermStore;
use OptStack\WordPress\Store\UserStore;

// Options
$optionsStore = new OptionsStore('my_options');
$value = $optionsStore->get('key', 'default');

// Post meta
$postStore = new PostStore($postId, 'my_meta_key');
$value = $postStore->get('field_name');

// Term meta
$termStore = new TermStore($termId, 'term_settings');
$value = $termStore->get('color');

// User meta
$userStore = new UserStore($userId, 'user_preferences');
$value = $userStore->get('theme');
```

### Bind Custom Store

```php
$stack = OptStack::get('my_stack');
$stack->setStore(new PostStore($customPostId, 'custom_key'));
```

---

## Storage Patterns

### Pattern 1: Options with Autoload

For frequently accessed settings:
```php
// Autoload is TRUE by default - data loaded on every page
$store = new OptionsStore('theme_options', true);
```

### Pattern 2: Options without Autoload

For rarely accessed settings:
```php
// Autoload FALSE - only load when needed
$store = new OptionsStore('advanced_settings', false);
```

### Pattern 3: Bulk Updates

When updating multiple fields:
```php
// Inefficient - multiple database writes
$store->set('field1', 'value1');
$store->set('field2', 'value2');
$store->set('field3', 'value3');

// Efficient - single database write
$store->setMany([
    'field1' => 'value1',
    'field2' => 'value2',
    'field3' => 'value3',
]);
```

### Pattern 4: Replace All Data

When you want to completely replace stored data:
```php
// This replaces ALL data (be careful!)
$store->replace([
    'new_field' => 'new_value',
]);
// Previous fields are gone!
```

### Pattern 5: Conditional Data Access

```php
$store = $stack->getStore();

if ($store->has('premium_features')) {
    $features = $store->get('premium_features');
    // Process features
}
```

---

## Performance Considerations

### Caching

All stores implement internal caching:

```php
// First call - hits database
$value1 = $store->get('field1');

// Second call - uses cache (no database query)
$value2 = $store->get('field2');

// Clear cache if needed (forces database reload)
$store->clearCache();
```

### Autoload for Options

For `OptionsStore`, the `autoload` parameter affects performance:

```php
// Autoload TRUE (default)
// - Data loaded on EVERY page load
// - Best for: frequently accessed settings
// - Cost: memory on every request
new OptionsStore('theme_options', true);

// Autoload FALSE
// - Data loaded only when accessed
// - Best for: rarely used settings, large data
// - Cost: extra query when first accessed
new OptionsStore('import_export_history', false);
```

### Large Data Sets

For stacks with many fields or large values:

1. **Use autoload: false** for options
2. **Consider splitting** into multiple stacks
3. **Use deferred groups** to delay loading UI

---

## Debugging Storage

### View Stored Data

```php
// Get all data from a stack
$stack = OptStack::get('theme_options');
$data = $stack->getData();
error_log(print_r($data, true));

// Or using WordPress directly
$data = get_option('theme_options');
var_dump($data);
```

### Check Store Type

```php
$stack = OptStack::get('product_data');
$store = $stack->getStore();

if ($store instanceof OptionsStore) {
    echo "Using OptionsStore: " . $store->getOptionName();
} elseif ($store instanceof PostStore) {
    echo "Using PostStore for post: " . $store->getPostId();
} elseif ($store instanceof TermStore) {
    echo "Using TermStore for term: " . $store->getTermId();
} elseif ($store instanceof UserStore) {
    echo "Using UserStore for user: " . $store->getUserId();
}
```

### Database Query

Check stored data directly in the database:

```sql
-- Options
SELECT * FROM wp_options WHERE option_name = 'theme_options';

-- Post Meta
SELECT * FROM wp_postmeta WHERE post_id = 123 AND meta_key = 'product_data';

-- Term Meta
SELECT * FROM wp_termmeta WHERE term_id = 5 AND meta_key = 'category_settings';

-- User Meta
SELECT * FROM wp_usermeta WHERE user_id = 1 AND meta_key = 'user_profile';
```

### Clear Cache

If data seems stale:

```php
$store = $stack->getStore();
$store->clearCache();
$freshData = $store->all();
```

---

## Best Practices

### 1. Use Meaningful Stack IDs

Stack ID becomes the storage key:
```php
// Good - descriptive and namespaced
OptStack::make('mytheme_general_settings');
OptStack::make('myplugin_product_data');

// Bad - generic, may conflict
OptStack::make('settings');
OptStack::make('data');
```

### 2. Don't Mix Contexts

One stack = one storage context:
```php
// Good - separate stacks for different contexts
OptStack::make('theme_options')->forOptions();
OptStack::make('product_data')->forPostType('product');

// Bad - trying to share stack across contexts
// This will cause confusion
```

### 3. Use OptStack Facade

Prefer the facade over direct store access:
```php
// Good - uses OptStack API
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Avoid unless necessary
$store = new PostStore($post_id, 'product_data');
$price = $store->get('price', 0);
```

### 4. Handle Missing Data

Always provide defaults:
```php
// Good - always has a fallback
$color = OptStack::getField('theme_options', 'primary_color', '#3b82f6');

// Risky - may return null
$color = OptStack::getField('theme_options', 'primary_color');
```

### 5. Batch Updates When Possible

```php
// Good - single save operation
OptStack::saveData('theme_options', [
    'site_name' => 'My Site',
    'tagline' => 'Just another site',
    'primary_color' => '#3b82f6',
]);

// Less efficient - multiple save operations
OptStack::updateField('theme_options', 'site_name', 'My Site');
OptStack::updateField('theme_options', 'tagline', 'Just another site');
OptStack::updateField('theme_options', 'primary_color', '#3b82f6');
```

---

## Related Documentation

- [API-REFERENCE.md](./API-REFERENCE.md) - Complete OptStack API
- [GET_FIELD_FEATURE.md](./GET_FIELD_FEATURE.md) - Retrieving field values
- [UPDATE_FIELD_FEATURE.md](./UPDATE_FIELD_FEATURE.md) - Updating field values
- [OPTSTACK_SEARCHABLE_FIELDS.md](./OPTSTACK_SEARCHABLE_FIELDS.md) - Indexed meta for queries
- [USAGE-FIELD.md](./USAGE-FIELD.md) - Field definition guide
