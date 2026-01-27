# OptStack - System Patterns

## Architecture Pattern

### Layered Architecture

```
┌─────────────────────────────────────────┐
│           Renderers (UI/API)            │  ← React Admin, REST
├─────────────────────────────────────────┤
│         Store Adapters (WP)             │  ← OptionsStore, PostStore
├─────────────────────────────────────────┤
│           Core Framework                │  ← Pure PHP, no WP
└─────────────────────────────────────────┘
```

**Key Rule**: Dependencies flow DOWN only. Core never knows about WordPress.

## Context-Based Rendering

Each stack context renders in its appropriate WordPress location:

| Context | Renders In | WordPress Hook |
|---------|------------|----------------|
| `options` | Admin menu page | `admin_menu` |
| `post_type` | Meta box on edit screen | `add_meta_boxes` |
| `taxonomy` | Term edit form | `{taxonomy}_edit_form_fields` |
| `user` | User profile page | `show_user_profile` |

```php
// Options → Creates admin page under "Site Settings" menu
OptStack::make('site_settings')->forOptions();

// Post Type → Creates meta box on "product" edit screen
OptStack::make('product_data')->forPostType('product');

// Taxonomy → Adds fields to category term edit form
OptStack::make('category_settings')->forTaxonomy('category');

// User → Adds fields to user profile page
OptStack::make('user_preferences')->forUser();
```

## Core Design Patterns

### 1. Builder Pattern (Stack Definition)

```php
OptStack::make('site_settings')
    ->forOptions()
    ->define(function ($stack) {
        $stack->field('site_color', ['type' => 'text']);
    });
```

### 2. Registry Pattern (Stack Management)

```php
StackRegistry::register($stack);
StackRegistry::get('site_settings');
StackRegistry::all();
```

### 3. Adapter Pattern (Storage)

```php
interface StoreInterface {
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value): bool;
    public function delete(string $key): bool;
    public function all(): array;
}
```

Each WordPress context has its own adapter:
- `OptionsStore` → `get_option()`, `update_option()`
- `PostStore` → `get_post_meta()`, `update_post_meta()`
- `TermStore` → `get_term_meta()`, `update_term_meta()`

### 4. Facade Pattern (Entry Point)

`OptStack.php` provides a clean, static API hiding internal complexity.

## Data Flow Patterns

### Schema Export (PHP → Frontend)

```
Stack Definition (PHP)
        ↓
SchemaExporter
        ↓
JSON Schema
        ↓
REST Endpoint
        ↓
React Consumer
```

### Data Persistence

```
React Form State
        ↓
REST API POST
        ↓
SaveHandler (PHP)
        ↓
Store Adapter
        ↓
WordPress Database
```

## Field Schema Structure

```php
[
    'type'        => 'text',       // Required
    'default'     => null,         // Default value
    'sanitize'    => 'callback',   // Sanitization
    'validate'    => null,         // Validation rules
    'description' => '',           // Help text
    'conditions'  => [],           // Conditional logic
]
```

## Naming Conventions

### PHP
- Classes: `PascalCase`
- Methods: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Interfaces: `SomethingInterface`

### Field Keys
- Use `snake_case` for field keys
- Use descriptive, data-centric names
- Avoid UI-specific terms

### API Methods
✅ Preferred: `forOptions()`, `forPost()`, `forTaxonomy()`
❌ Avoid: `addMetabox()`, `addOptionPage()`

## Component Relationships

```
OptStack (Facade)
    │
    ├── StackRegistry
    │       │
    │       └── Stack[]
    │               │
    │               ├── FieldCollection
    │               │       └── Field[]
    │               │
    │               └── FieldGroup[]
    │                       └── Field[]
    │
    └── StoreInterface (Contract)
            │
            ├── OptionsStore
            ├── PostStore
            └── TermStore
```

## Critical Implementation Rules

1. **Core Isolation**: Core/ must never `use` any WordPress function
2. **Interface First**: Define contracts before implementations
3. **Schema is Truth**: Frontend renders from schema, never hardcodes fields
4. **Conditions are Metadata**: Backend defines, Frontend interprets
5. **Store Transparency**: Data accessible via native WP functions
