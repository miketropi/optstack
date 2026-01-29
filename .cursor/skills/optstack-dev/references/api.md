# API Reference

## Table of Contents

1. [OptStack Facade](#optstack-facade)
2. [Stack Methods](#stack-methods)
3. [StackBuilder Methods](#stackbuilder-methods)
4. [REST API Endpoints](#rest-api-endpoints)
5. [WordPress Hooks](#wordpress-hooks)
6. [React Hooks](#react-hooks)

---

## OptStack Facade

`src/OptStack.php` - Main entry point for all operations.

### Stack Management

```php
// Create a new stack builder
OptStack::make(string $id): StackBuilder

// Get a registered stack
OptStack::get(string $id): ?Stack

// Check if stack exists
OptStack::has(string $id): bool

// Get all registered stacks
OptStack::all(): array<string, Stack>

// Get stacks by context
OptStack::byContext(string $context): array

// Get stacks for post type
OptStack::forPostType(string $postType): array

// Get stacks for taxonomy
OptStack::forTaxonomy(string $taxonomy): array
```

### Data Operations

```php
// Get data from a stack
OptStack::getData(string $id, ?string $key = null, mixed $default = null): mixed

// Save data to a stack (bulk)
OptStack::saveData(string $id, array $data): bool

// Update a single field (auto-syncs searchable fields)
OptStack::updateField(string $id, string $key, mixed $value, ?int $objectId = null): bool
```

### Schema

```php
// Get stack schema (JSON)
OptStack::schema(string $id): ?array

// Get all schemas
OptStack::allSchemas(): array
```

---

## Stack Methods

`src/Core/Stack/Stack.php` - Core stack model.

### Definition

```php
$stack->field(string $key, array $config): self
$stack->group(string $key, ?callable $callback, array $config): self
$stack->tab(string $key, ?callable $callback): self
$stack->define(callable $callback): self
```

### Data Access

```php
$stack->getData(): array
$stack->saveData(array $data): bool
$stack->updateField(string $key, mixed $value, ?int $objectId = null): bool
$stack->getDefaults(): array
```

### Getters

```php
$stack->getId(): string
$stack->getContext(): string  // 'options', 'post_type', 'taxonomy', 'user'
$stack->getLabel(): string
$stack->getDescription(): string
$stack->getPostType(): ?string
$stack->getTaxonomy(): ?string
$stack->getFields(): FieldCollection
$stack->getGroups(): array
$stack->getTabs(): array
$stack->getStore(): ?StoreInterface
```

### Schema Export

```php
$stack->toArray(): array  // For JSON schema export
```

---

## StackBuilder Methods

`src/Core/Stack/StackBuilder.php` - Fluent API for building stacks.

```php
OptStack::make('stack_id')
    // Context (required, choose one)
    ->forOptions()                    // wp_options storage
    ->forPostType(string $postType)   // wp_postmeta storage
    ->forTaxonomy(string $taxonomy)   // wp_termmeta storage
    ->forUser()                       // wp_usermeta storage

    // Metadata
    ->label(string $label)
    ->description(string $description)

    // Menu (options context only)
    ->menuParent(string $parent)      // 'optstack', 'options-general.php', 'themes.php', etc.
    ->menuIcon(string $icon)          // dashicon or URL
    ->menuPosition(int $position)
    ->capability(string $capability)  // default: 'manage_options'

    // Definition
    ->define(callable $callback)

    // Build
    ->build(): Stack                  // Register and return stack
```

---

## REST API Endpoints

Base URL: `/wp-json/optstack/v1/`

### Get All Stacks

```
GET /stacks
Response: [{ id, label, context, ... }, ...]
```

### Get Single Stack Schema

```
GET /stacks/{id}
Response: { id, label, fields, groups, tabs, ... }
```

### Get Stack Data

```
GET /stacks/{id}/data
Query: ?object_id=123 (for post/term/user context)
Response: { schema: {...}, data: {...} }
```

### Save Stack Data

```
POST /stacks/{id}/data
Query: ?object_id=123
Body: { field1: value1, field2: value2, ... }
Response: { success: true, data: {...} }
```

---

## WordPress Hooks

### Actions

```php
// Define stacks here (runs on plugins_loaded priority 5)
add_action('optstack_init', function() {
    // Register stacks
});

// Stores bound, stacks ready to use
add_action('optstack_ready', function() {
    // Safe to access stack data
});

// After data saved via REST or form
add_action('optstack_data_saved', function($stack, $objectId, $objectType, $data) {
    // $stack - Stack instance
    // $objectId - Post/term/user ID (int)
    // $objectType - 'post', 'term', 'user'
    // $data - Saved data array
}, 10, 4);

// After searchable fields indexed (bulk save)
add_action('optstack_indexed_meta_synced', function($stack, $data, $objectId) {
    // Fires after syncIndexedMeta()
}, 10, 3);

// After single searchable field synced (updateField)
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    // $fieldPath - e.g., 'price' or 'seo.title'
    // Fires after updateField() syncs a searchable field
}, 10, 4);
```

### Debug Hook

```php
// Debug indexed meta operations
add_action('optstack_indexed_meta_debug', function($debugInfo) {
    error_log('OptStack Debug: ' . print_r($debugInfo, true));
});
```

---

## React Hooks

Located in `frontend/src/hooks/`.

### useStack

Fetch stack schema by ID.

```typescript
const { schema, loading, error } = useStack(stackId: string)
```

### useStackData

Manage stack data state.

```typescript
const {
  data,           // Record<string, unknown> - Current values
  loading,        // boolean - Initial fetch in progress
  error,          // string | null - Error message
  saving,         // boolean - Save in progress
  isDirty,        // boolean - Has unsaved changes
  updateField,    // (key: string, value: unknown) => void
  save,           // () => Promise<boolean>
  reset,          // () => void - Revert to original
} = useStackData(stackId: string, objectId?: number)
```

### useConditions

Evaluate conditional visibility rules.

```typescript
const { isVisible } = useConditions(data: Record<string, unknown>)

// Usage
if (!isVisible(field.conditions)) return null
```

### useStacks

Fetch all registered stacks.

```typescript
const { stacks, loading, error } = useStacks()
```

---

## IndexedMetaManager

`src/WordPress/Index/IndexedMetaManager.php` - Manages searchable field indexing.

```php
$manager = Bootstrap::getInstance()->getIndexedMetaManager();

// Sync all searchable fields for a stack
$manager->syncIndexedMeta(Stack $stack, array $data, int $objectId): void

// Sync single field (used by updateField)
$manager->syncSingleField(Stack $stack, string $fieldPath, mixed $value, int $objectId): bool

// Get indexed meta keys for a stack
$manager->getIndexedMetaKeys(Stack $stack): array
// Returns: ['price' => '_optstack_idx_post_price', 'seo.title' => '_optstack_idx_post_seo_title']

// Delete all indexed meta for a stack
$manager->deleteAllIndexedMeta(Stack $stack, int $objectId): void
```

### Meta Key Format

```
_optstack_idx_{context}_{field_path}
```

- `context`: post, term, user
- `field_path`: Dots replaced with underscores

Examples:
- `price` → `_optstack_idx_post_price`
- `seo.title` → `_optstack_idx_post_seo_title`
- `inventory.quantity` → `_optstack_idx_post_inventory_quantity`
