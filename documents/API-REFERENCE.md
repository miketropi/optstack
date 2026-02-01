# OptStack API Reference

> Complete reference for the OptStack facade and core classes

---

## Table of Contents

- [OptStack Facade](#optstack-facade)
  - [Stack Creation](#stack-creation)
  - [Stack Retrieval](#stack-retrieval)
  - [Data Operations](#data-operations)
  - [Field Operations](#field-operations)
  - [Schema Export](#schema-export)
  - [Stack Management](#stack-management)
  - [Shorthand Methods](#shorthand-methods)
- [Stack Instance](#stack-instance)
- [Quick Reference](#quick-reference)

---

## OptStack Facade

The `OptStack` class provides a clean, static API for defining and managing data stacks.

```php
use OptStack\OptStack;
```

### Stack Creation

#### `make(string $id): StackBuilder`

Create a new stack builder for fluent configuration.

```php
$builder = OptStack::make('my_stack');

// Full example with chaining
OptStack::make('product_data')
    ->forPostType('product')
    ->label('Product Data')
    ->define(function($stack) {
        $stack->field('price', ['type' => 'number']);
        $stack->field('stock', ['type' => 'number']);
    })
    ->build();
```

**Parameters:**
- `$id` (string) - Unique stack identifier

**Returns:** `StackBuilder` instance for fluent configuration

---

### Stack Retrieval

#### `get(string $id): ?Stack`

Get a registered stack by ID.

```php
$stack = OptStack::get('product_data');

if ($stack !== null) {
    // Stack exists
    $data = $stack->getData();
}
```

**Parameters:**
- `$id` (string) - Stack identifier

**Returns:** `Stack` instance or `null` if not found

---

#### `has(string $id): bool`

Check if a stack is registered.

```php
if (OptStack::has('product_data')) {
    // Stack is registered
}
```

**Parameters:**
- `$id` (string) - Stack identifier

**Returns:** `bool`

---

#### `all(): array`

Get all registered stacks.

```php
$stacks = OptStack::all();

foreach ($stacks as $id => $stack) {
    echo "Stack: {$id} - {$stack->getLabel()}\n";
}
```

**Returns:** `array<string, Stack>` - Map of stack ID to Stack instance

---

#### `byContext(string $context): array`

Get stacks filtered by context type.

```php
// Get all post meta stacks
$postStacks = OptStack::byContext('post_type');

// Get all options stacks
$optionsStacks = OptStack::byContext('options');

// Get all term meta stacks
$termStacks = OptStack::byContext('taxonomy');
```

**Parameters:**
- `$context` (string) - Context type: `options`, `post`, `post_type`, `term`, `taxonomy`, `user`

**Returns:** `array<string, Stack>`

---

#### `forPostType(string $postType): array`

Get all stacks for a specific post type.

```php
$productStacks = OptStack::forPostType('product');
$pageStacks = OptStack::forPostType('page');
```

**Parameters:**
- `$postType` (string) - Post type slug

**Returns:** `array<string, Stack>`

---

#### `forTaxonomy(string $taxonomy): array`

Get all stacks for a specific taxonomy.

```php
$categoryStacks = OptStack::forTaxonomy('category');
$tagStacks = OptStack::forTaxonomy('post_tag');
```

**Parameters:**
- `$taxonomy` (string) - Taxonomy slug

**Returns:** `array<string, Stack>`

---

### Data Operations

#### `getData(string $id, ?string $key = null, mixed $default = null): mixed`

Get data from a stack. Can retrieve all data or a specific key.

```php
// Get all data
$data = OptStack::getData('product_data');

// Get specific key
$price = OptStack::getData('product_data', 'price', 0);

// Get nested key (simple, not dot notation)
$data = OptStack::getData('site_settings');
$primaryColor = $data['colors']['primary'] ?? '#3b82f6';
```

**Parameters:**
- `$id` (string) - Stack identifier
- `$key` (string|null) - Optional specific key to retrieve
- `$default` (mixed) - Default value if key not found

**Returns:** All data array, specific value, or default

**Note:** For nested fields with dot notation, use `getField()` instead.

---

#### `saveData(string $id, array $data): bool`

Save data to a stack. Merges with existing data.

```php
$success = OptStack::saveData('product_data', [
    'price' => 99.99,
    'stock' => 50,
    'status' => 'active',
]);

if ($success) {
    echo 'Data saved successfully';
}
```

**Parameters:**
- `$id` (string) - Stack identifier
- `$data` (array) - Data to save (key-value pairs)

**Returns:** `bool` - Success status

---

### Field Operations

#### `getField(string $id, string $key, mixed $default = null, ?int $objectId = null): mixed`

Get a single field value with dot notation support.

```php
// Simple field
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// Nested field (dot notation)
$metaTitle = OptStack::getField('product_data', 'seo.meta.title', '', $post_id);

// Options context (no object ID needed)
$siteName = OptStack::getField('site_settings', 'identity.site_name', 'My Site');
```

**Parameters:**
- `$id` (string) - Stack identifier
- `$key` (string) - Field key (supports dot notation for nested fields)
- `$default` (mixed) - Default value if field not found
- `$objectId` (int|null) - Object ID for post/term/user contexts

**Returns:** Field value or default

**See also:** [GET_FIELD_FEATURE.md](GET_FIELD_FEATURE.md)

---

#### `updateField(string $id, string $key, mixed $value, ?int $objectId = null): bool`

Update a single field value with automatic searchable field sync.

```php
// Simple field update
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// Nested field update
OptStack::updateField('product_data', 'pricing.regular_price', 149.99, $post_id);

// Searchable field (auto-syncs index)
OptStack::updateField('product_data', 'status', 'active', $post_id);
// Also updates: _optstack_idx_post_status
```

**Parameters:**
- `$id` (string) - Stack identifier
- `$key` (string) - Field key (supports dot notation)
- `$value` (mixed) - New value
- `$objectId` (int|null) - Object ID for post/term/user contexts

**Returns:** `bool` - Success status

**See also:** [UPDATE_FIELD_FEATURE.md](UPDATE_FIELD_FEATURE.md)

---

### Schema Export

#### `schema(string $id): ?array`

Export a single stack's schema as an array.

```php
$schema = OptStack::schema('product_data');

// Schema structure
[
    'id' => 'product_data',
    'context' => 'post_type',
    'postType' => 'product',
    'label' => 'Product Data',
    'fields' => [...],
    'groups' => [...],
    'tabs' => [...],
]
```

**Parameters:**
- `$id` (string) - Stack identifier

**Returns:** Schema array or `null` if stack not found

---

#### `allSchemas(): array`

Export all registered stacks' schemas.

```php
$schemas = OptStack::allSchemas();

foreach ($schemas as $id => $schema) {
    echo "Stack: {$id}\n";
    echo "Context: {$schema['context']}\n";
}
```

**Returns:** `array<string, array>` - Map of stack ID to schema

---

### Stack Management

#### `register(Stack $stack): void`

Register a stack directly (without using the builder).

```php
$stack = new Stack('custom_stack');
$stack->forOptions();
$stack->field('option', ['type' => 'text']);

OptStack::register($stack);
```

**Parameters:**
- `$stack` (Stack) - Stack instance to register

---

#### `unregister(string $id): bool`

Remove a registered stack.

```php
$removed = OptStack::unregister('old_stack');

if ($removed) {
    echo 'Stack unregistered';
}
```

**Parameters:**
- `$id` (string) - Stack identifier

**Returns:** `bool` - `true` if stack was unregistered, `false` if not found

---

#### `count(): int`

Get the count of registered stacks.

```php
$total = OptStack::count();
echo "Total stacks: {$total}";
```

**Returns:** `int` - Number of registered stacks

---

#### `version(): string`

Get the OptStack framework version.

```php
$version = OptStack::version();
echo "OptStack v{$version}";
```

**Returns:** `string` - Version number (e.g., "0.1.2")

---

### Shorthand Methods

These methods provide quick stack definition without manual builder calls.

#### `options(string $id, callable $callback): Stack`

Define and register an options stack.

```php
OptStack::options('site_settings', function($stack) {
    $stack->field('site_name', ['type' => 'text']);
    $stack->field('tagline', ['type' => 'text']);
    
    $stack->group('colors', function($group) {
        $group->field('primary', ['type' => 'color']);
        $group->field('secondary', ['type' => 'color']);
    });
});
```

**Equivalent to:**
```php
OptStack::make('site_settings')
    ->forOptions()
    ->define($callback)
    ->build();
```

---

#### `postType(string $id, string $postType, callable $callback): Stack`

Define and register a post type meta stack.

```php
OptStack::postType('product_data', 'product', function($stack) {
    $stack->field('price', ['type' => 'number', 'searchable' => true]);
    $stack->field('stock', ['type' => 'number']);
    $stack->field('sku', ['type' => 'text', 'searchable' => true]);
});
```

**Equivalent to:**
```php
OptStack::make('product_data')
    ->forPostType('product')
    ->define($callback)
    ->build();
```

---

#### `taxonomy(string $id, string $taxonomy, callable $callback): Stack`

Define and register a taxonomy term meta stack.

```php
OptStack::taxonomy('category_settings', 'category', function($stack) {
    $stack->field('icon', ['type' => 'media']);
    $stack->field('color', ['type' => 'color']);
    $stack->field('featured', ['type' => 'toggle']);
});
```

**Equivalent to:**
```php
OptStack::make('category_settings')
    ->forTaxonomy('category')
    ->define($callback)
    ->build();
```

---

## Stack Instance

When you have a `Stack` instance, these methods are available:

### Data Methods

```php
$stack = OptStack::get('product_data');

// Get all data
$data = $stack->getData();

// Get single field
$price = $stack->getField('price', 0, $post_id);
$title = $stack->getField('seo.title', '', $post_id);

// Save data
$stack->saveData(['price' => 99.99, 'stock' => 50]);

// Update single field
$stack->updateField('price', 99.99, $post_id);
$stack->updateField('seo.title', 'New Title', $post_id);
```

### Metadata Methods

```php
$stack = OptStack::get('product_data');

// Get stack info
$id = $stack->getId();              // 'product_data'
$context = $stack->getContext();    // 'post_type'
$postType = $stack->getPostType();  // 'product'
$label = $stack->getLabel();        // 'Product Data'
$description = $stack->getDescription();

// Get store
$store = $stack->getStore();        // StoreInterface
```

### Schema Methods

```php
$stack = OptStack::get('product_data');

// Get fields
$fields = $stack->getFields();      // FieldCollection

// Get groups
$groups = $stack->getGroups();      // array<string, FieldGroup>
$group = $stack->getGroup('seo');   // FieldGroup|null

// Get tabs
$tabs = $stack->getTabs();          // array<string, Tab>
$tab = $stack->getTab('general');   // Tab|null
$hasTabs = $stack->hasTabs();       // bool

// Get defaults
$defaults = $stack->getDefaults();  // array

// Export schema
$schema = $stack->toArray();        // array
```

---

## Quick Reference

### Data Flow

```
Define Stack → Register → Bind Store → Get/Update Data
```

### Common Patterns

```php
// 1. Define stack (in plugin/theme init)
add_action('optstack_init', function() {
    OptStack::postType('product_data', 'product', function($stack) {
        $stack->field('price', ['type' => 'number', 'searchable' => true]);
    });
});

// 2. Get field value
$price = OptStack::getField('product_data', 'price', 0, $post_id);

// 3. Update field value
OptStack::updateField('product_data', 'price', 99.99, $post_id);

// 4. Get all data
$data = OptStack::getData('product_data');

// 5. Save multiple fields
OptStack::saveData('product_data', ['price' => 99.99, 'stock' => 50]);
```

### Context Requirements

| Context | Object ID Required | Searchable Support |
|---------|-------------------|-------------------|
| `options` | No | No |
| `post_type` | Yes | Yes |
| `taxonomy` | Yes | Yes |
| `user` | Yes | Yes |

### Method Summary

| Method | Purpose | Returns |
|--------|---------|---------|
| `make($id)` | Create stack builder | `StackBuilder` |
| `get($id)` | Get stack by ID | `Stack\|null` |
| `has($id)` | Check if stack exists | `bool` |
| `all()` | Get all stacks | `array` |
| `getData($id, $key, $default)` | Get data from stack | `mixed` |
| `saveData($id, $data)` | Save data to stack | `bool` |
| `getField($id, $key, $default, $objectId)` | Get single field | `mixed` |
| `updateField($id, $key, $value, $objectId)` | Update single field | `bool` |
| `schema($id)` | Export stack schema | `array\|null` |
| `options($id, $callback)` | Define options stack | `Stack` |
| `postType($id, $postType, $callback)` | Define post type stack | `Stack` |
| `taxonomy($id, $taxonomy, $callback)` | Define taxonomy stack | `Stack` |

---

## Related Documentation

- [FLOW.md](FLOW.md) - Complete framework documentation
- [GET_FIELD_FEATURE.md](GET_FIELD_FEATURE.md) - getField() detailed guide
- [UPDATE_FIELD_FEATURE.md](UPDATE_FIELD_FEATURE.md) - updateField() detailed guide
- [USAGE-FIELD.md](USAGE-FIELD.md) - Field definition guide
- [OPTSTACK_SEARCHABLE_FIELDS.md](OPTSTACK_SEARCHABLE_FIELDS.md) - Searchable fields guide
