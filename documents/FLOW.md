# OptStack Developer Documentation

> **Quick Reference Guide** - Understanding the OptStack architecture, workflows, and implementation patterns

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture Layers](#architecture-layers)
3. [Core Concepts](#core-concepts)
4. [Data Flow](#data-flow)
5. [Development Workflow](#development-workflow)
6. [Key Files Reference](#key-files-reference)
7. [Common Patterns](#common-patterns)
8. [API Reference](#api-reference)
9. [Examples](#examples)

---

## Overview

**OptStack** is a WordPress Data Stack Framework that provides a modern, type-safe way to define and manage structured data across WordPress contexts (Options, Post Meta, Term Meta, User Meta).

### Philosophy

- **Data-First**: Define data structures, not UI components
- **Native WordPress**: Uses standard WP functions (`get_option()`, `get_post_meta()`)
- **Type-Safe**: PHP 8.1+ with strict types, TypeScript frontend
- **Composable**: Interface-driven architecture
- **Modern DX**: React UI, HMR, REST API

### Quick Start

```php
// 1. Define a stack on optstack_init hook
add_action('optstack_init', function() {
    OptStack::make('site_settings')
        ->forOptions()
        ->label('Site Settings')
        ->define(function ($stack) {
            $stack->field('site_color', [
                'type' => 'color',
                'label' => 'Primary Color',
                'default' => '#000000',
            ]);
        })
        ->build();
});

// 2. Access data using native WordPress
$settings = get_option('site_settings');
echo $settings['site_color']; // #000000
```

---

## Architecture Layers

OptStack is built with three distinct layers that maintain clean separation of concerns:

```
┌─────────────────────────────────────────────────────────┐
│                    React Frontend                        │
│         (TypeScript + React + TailwindCSS)              │
│                                                          │
│  • Field Renderers (TextField, SelectField, etc.)       │
│  • StackRenderer (orchestrates field rendering)         │
│  • Hooks (useStack, useStackData, useConditions)        │
│  • REST API Client                                      │
└─────────────────────────────────────────────────────────┘
                           ▲
                           │ REST API (JSON)
                           ▼
┌─────────────────────────────────────────────────────────┐
│               WordPress Integration                      │
│                  (WP-specific code)                      │
│                                                          │
│  • Store Adapters (OptionsStore, PostStore, etc.)       │
│  • Admin UI (menu pages, meta boxes, forms)             │
│  • REST API Endpoints                                   │
│  • Bootstrap & Hooks                                    │
│  • Indexed Meta Manager (searchable fields)             │
└─────────────────────────────────────────────────────────┘
                           ▲
                           │ Pure PHP Interfaces
                           ▼
┌─────────────────────────────────────────────────────────┐
│                    Core Framework                        │
│              (Pure PHP, no WP dependencies)             │
│                                                          │
│  • Stack (data structure definition)                    │
│  • Field, FieldGroup, FieldCollection                   │
│  • Conditions (visibility logic)                        │
│  • Schema Exporter (to JSON)                           │
│  • Contracts (StoreInterface, etc.)                     │
└─────────────────────────────────────────────────────────┘
```

### Layer 1: Core Framework

**Purpose**: Pure PHP business logic, no WordPress dependencies

**Key Files**:
- `src/Core/Stack/Stack.php` - Main stack model
- `src/Core/Field/Field.php` - Field definition
- `src/Core/Field/FieldGroup.php` - Grouping fields
- `src/Core/Condition/ConditionEvaluator.php` - Conditional logic
- `src/Core/Contract/` - Interfaces

**Benefits**:
- ✅ Fully unit testable (no WP mocks needed)
- ✅ Framework-agnostic
- ✅ Clean domain logic

### Layer 2: WordPress Integration

**Purpose**: Adapt core framework to WordPress ecosystem

**Key Files**:
- `src/WordPress/Bootstrap.php` - Initialization & hooks
- `src/WordPress/Admin.php` - Admin UI rendering
- `src/WordPress/Store/` - Storage adapters
- `src/WordPress/Index/IndexedMetaManager.php` - Searchable fields

**Responsibilities**:
- Store binding (connect stacks to WP storage)
- Admin page/meta box rendering
- REST API endpoints
- WordPress hooks integration

### Layer 3: React Frontend

**Purpose**: Modern, reactive admin UI

**Key Files**:
- `frontend/src/main.tsx` - Entry point, mounts React apps
- `frontend/src/StackApp.tsx` - Root component
- `frontend/src/components/StackRenderer.tsx` - Orchestrator
- `frontend/src/components/FieldRenderer.tsx` - Field dispatcher
- `frontend/src/components/fields/*.tsx` - Individual field components

**Technologies**:
- React 18 (createRoot, hooks)
- TypeScript (strict mode)
- TailwindCSS (with `os-` prefix)
- Vite (build + HMR)

---

## Core Concepts

### 1. Stack

A **Stack** is a logical container for structured data. It defines:
- **Context**: Where data is stored (options, post, term, user)
- **Fields**: Individual data points
- **Groups**: Nested field collections
- **Tabs**: UI organization

```php
OptStack::make('stack_id')
    ->forOptions()              // Context
    ->label('Stack Label')      // Display name
    ->define(function($stack) { // Definition callback
        // Add fields, groups, tabs
    })
    ->build();                  // Register
```

### 2. Field

A **Field** is a single data point with:
- **Key**: Unique identifier
- **Type**: Data type (text, number, select, etc.)
- **Default**: Default value
- **Attributes**: Field-specific options
- **Conditions**: Visibility rules

```php
$stack->field('price', [
    'type' => 'number',
    'label' => 'Price',
    'default' => 0,
    'attributes' => ['min' => 0, 'step' => 0.01],
    'conditions' => [
        ['field' => 'enable_pricing', 'operator' => '==', 'value' => true]
    ],
    'searchable' => true, // Enable WP_Query optimization
]);
```

### 3. Field Group

A **Group** organizes related fields and supports:
- **Nesting**: Groups within groups
- **Repeatable**: Dynamic array of field sets
- **Layouts**: Inline (2-col) or Box (card)
- **Collapsible**: Expand/collapse UI

```php
$stack->group('pricing', function($group) {
    $group->repeatable(1, 10); // Min 1, max 10 items
    
    $group->field('price', ['type' => 'number']);
    $group->field('currency', ['type' => 'select']);
}, [
    'label' => 'Pricing Tiers',
    'layout' => 'box',         // or 'inline' (default)
    'collapsible' => true,
]);
```

### 4. Tab

**Tabs** organize complex settings pages:

```php
$stack->tab('general', function($tab) {
    $tab->label('General Settings')
        ->priority(10)
        ->description('Basic configuration');
    
    $tab->field('site_name', ['type' => 'text']);
    $tab->group('social', function($group) {
        // Groups within tabs
    });
});
```

### 5. Store Interface

All storage adapters implement `StoreInterface`:

```php
interface StoreInterface {
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value): bool;
    public function all(): array;
    public function has(string $key): bool;
    public function delete(string $key): bool;
}
```

**Implementations**:
- `OptionsStore` → `wp_options` table
- `PostStore` → `wp_postmeta` table
- `TermStore` → `wp_termmeta` table
- `UserStore` → `wp_usermeta` table

### 6. Searchable Fields

Fields marked `searchable: true` get indexed separately for efficient querying:

```php
// Field definition
$stack->field('price', [
    'type' => 'number',
    'searchable' => true,
]);

// Creates indexed meta: _optstack_idx_post_price

// Query efficiently
$products = new WP_Query([
    'meta_query' => [[
        'key' => '_optstack_idx_post_price',
        'value' => 100,
        'compare' => '>=',
        'type' => 'NUMERIC',
    ]],
]);
```

**Indexed Meta Key Format**: `_optstack_idx_{context}_{field_path}`
- `context`: post, term, user
- `field_path`: Dot-notation path with dots replaced by underscores

**Examples**:
- `price` → `_optstack_idx_post_price`
- `seo.title` → `_optstack_idx_post_seo_title`
- `inventory.quantity` → `_optstack_idx_post_inventory_quantity`

---

## Data Flow

### Complete Request-Response Cycle

```
┌──────────────────────────────────────────────────────────────┐
│                    1. INITIALIZATION                          │
└──────────────────────────────────────────────────────────────┘

plugins_loaded (priority 5)
    └─> optstack_init() in optstack.php
            └─> Bootstrap::boot()
                    └─> Fires 'optstack_init' hook
                            └─> Developer defines stacks
                                    └─> Stack registered in StackRegistry
                                            └─> bindStores() - attach storage
                                                    └─> Fires 'optstack_ready' hook
                                                            └─> Admin::registerRenderers()

┌──────────────────────────────────────────────────────────────┐
│                  2. ADMIN PAGE REQUEST                        │
└──────────────────────────────────────────────────────────────┘

User visits admin page (e.g., /wp-admin/admin.php?page=optstack-site_settings)
    │
    ├─> admin_menu hook
    │       └─> Admin::registerOptionsPage()
    │               └─> add_menu_page() or add_submenu_page()
    │
    ├─> admin_enqueue_scripts hook
    │       └─> Admin::enqueueAssets()
    │               ├─> wp_enqueue_script('optstack-admin')
    │               ├─> wp_enqueue_style('optstack-admin')
    │               └─> wp_localize_script() - pass config to JS
    │
    └─> Admin page renders
            └─> Admin::renderOptionsPage()
                    └─> Admin::renderMountPoint()
                            └─> Outputs: <div class="optstack-mount" data-stack="site_settings">

┌──────────────────────────────────────────────────────────────┐
│                  3. REACT APP INITIALIZATION                  │
└──────────────────────────────────────────────────────────────┘

main.tsx loads
    └─> mountOptStack()
            └─> Find all .optstack-mount elements
                    └─> For each mount point:
                            └─> ReactDOM.createRoot().render(<StackApp />)

StackApp component renders
    └─> useStack(stackId)
            └─> Fetches: GET /wp-json/optstack/v1/stacks/{stackId}
                    └─> Returns: { id, label, fields, groups, tabs, ... }
                            └─> Schema stored in state

StackRenderer component renders
    └─> useStackData(stackId, objectId)
            └─> Fetches: GET /wp-json/optstack/v1/stacks/{stackId}/data
                    └─> Returns: { schema, data }
                            └─> Data stored in state
                                    └─> Renders fields based on schema
                                            └─> Each field → FieldRenderer → Specific field component

┌──────────────────────────────────────────────────────────────┐
│                  4. USER EDITS & SAVES DATA                   │
└──────────────────────────────────────────────────────────────┘

User changes field value
    └─> Field component calls onChange()
            └─> useStackData hook updates local state
                    └─> isDirty = true
                            └─> Save button enabled

User clicks "Save Changes"
    └─> StackRenderer::handleSave()
            └─> useStackData::save()
                    └─> POST /wp-json/optstack/v1/stacks/{stackId}/data
                            └─> Body: { field_key: value, ... }

REST API Handler: Bootstrap::restSaveStackData()
    │
    ├─> Get stack from StackRegistry
    ├─> Bind appropriate store (Options/Post/Term/User)
    ├─> Validate & sanitize data (TODO: implement)
    └─> stack->saveData($data)
            └─> Store->set() for each field
                    └─> update_option() / update_post_meta() / etc.
                            └─> IndexedMetaManager::syncIndexedMeta()
                                    └─> For each searchable field:
                                            └─> update_*_meta(_optstack_idx_*)
                                                    └─> Fire 'optstack_data_saved' hook

Response: { success: true, data: {...} }
    └─> React updates state
            └─> isDirty = false
                    └─> Show success notification

┌──────────────────────────────────────────────────────────────┐
│                  5. META BOX (POST CONTEXT)                   │
└──────────────────────────────────────────────────────────────┘

Post edit screen loads
    │
    ├─> add_meta_boxes hook
    │       └─> Admin::registerMetaBox()
    │               └─> add_meta_box()
    │
    ├─> admin_enqueue_scripts hook
    │       └─> Admin::enqueueAssets() with post_id
    │
    └─> Meta box renders
            └─> Admin::renderMetaBox()
                    ├─> wp_nonce_field()
                    └─> renderMountPoint() with object_id

React loads, but for meta boxes:
    └─> StackRenderer renders with isEmbedded = true
            └─> Creates hidden input: <input name="optstack_data[stack_id]" />
                    └─> Updates on every change

WordPress post save
    └─> save_post hook
            └─> Admin::saveMetaBoxData()
                    ├─> Verify nonce
                    ├─> Check permissions
                    ├─> Get data from $_POST['optstack_data'][stack_id]
                    ├─> update_post_meta($post_id, $stack_id, $data)
                    └─> IndexedMetaManager::syncIndexedMeta()

┌──────────────────────────────────────────────────────────────┐
│                  6. DATA RETRIEVAL                            │
└──────────────────────────────────────────────────────────────┘

Frontend/Template Code:
    └─> get_option('site_settings')
            └─> Returns: ['site_color' => '#000', 'social' => [...]]
                    └─> Direct array access

Via OptStack API:
    └─> OptStack::getData('site_settings', 'site_color')
            └─> Stack->getData()
                    └─> Store->get('site_color')
                            └─> get_option('site_settings')['site_color']

Query by searchable field:
    └─> new WP_Query([
            'meta_query' => [[
                'key' => '_optstack_idx_post_price',
                'value' => 100,
                'compare' => '>=',
            ]]
        ])
```

### Key Hooks in Lifecycle

1. **`optstack_init`** - Define your stacks here
2. **`optstack_ready`** - Stores bound, stacks ready to use
3. **`optstack_before_save_post`** - Before post meta save
4. **`optstack_data_saved`** - After data saved (post/term/user)
5. **`optstack_indexed_meta_synced`** - After searchable fields indexed

---

## Development Workflow

### Setting Up Development Environment

#### 1. Install Dependencies

```bash
# Backend (if using Composer dependencies)
composer install

# Frontend
cd frontend
npm install
```

#### 2. Enable Dev Mode

**Add to `wp-config.php`**:
```php
// Enable OptStack dev mode with HMR
define('OPTSTACK_DEV_MODE', true);

// Optional: custom dev server URL
define('OPTSTACK_DEV_SERVER', 'http://localhost:5173');
```

#### 3. Start Dev Server

```bash
cd frontend
npm run dev
```

This starts Vite dev server at `http://localhost:5173` with:
- ⚡ Hot Module Replacement (HMR)
- 🔄 Instant CSS updates
- 🚀 Fast refresh for React components

#### 4. Build for Production

```bash
cd frontend
npm run build
```

Outputs to `frontend/dist/`:
- `optstack-admin.js`
- `optstack-main.css`

### Directory Structure

```
optstack/
├── src/                          # PHP Backend
│   ├── Core/                     # Pure PHP (no WP deps)
│   │   ├── Stack/
│   │   │   ├── Stack.php         # Main stack model
│   │   │   ├── StackBuilder.php  # Fluent API builder
│   │   │   └── StackRegistry.php # Global registry
│   │   ├── Field/
│   │   │   ├── Field.php         # Field definition
│   │   │   ├── FieldGroup.php    # Group container
│   │   │   └── FieldCollection.php
│   │   ├── Condition/
│   │   │   ├── Condition.php
│   │   │   └── ConditionEvaluator.php
│   │   ├── Container/
│   │   │   └── Tab.php
│   │   └── Contract/             # Interfaces
│   │       ├── StoreInterface.php
│   │       ├── SanitizableInterface.php
│   │       └── ValidatableInterface.php
│   ├── WordPress/                # WP Integration
│   │   ├── Bootstrap.php         # Init & REST API
│   │   ├── Admin.php             # Admin UI rendering
│   │   ├── Store/
│   │   │   ├── OptionsStore.php
│   │   │   ├── PostStore.php
│   │   │   ├── TermStore.php
│   │   │   └── UserStore.php
│   │   └── Index/
│   │       └── IndexedMetaManager.php
│   ├── Schema/
│   │   ├── SchemaExporter.php    # Stack → JSON
│   │   └── SchemaNormalizer.php
│   └── OptStack.php              # Facade

├── frontend/                     # React Frontend
│   ├── src/
│   │   ├── components/
│   │   │   ├── StackRenderer.tsx      # Main orchestrator
│   │   │   ├── FieldRenderer.tsx      # Field dispatcher
│   │   │   ├── GroupRenderer.tsx      # Group handler
│   │   │   ├── TabContainer.tsx       # Tabs UI
│   │   │   └── fields/                # Field components
│   │   │       ├── TextField.tsx
│   │   │       ├── NumberField.tsx
│   │   │       ├── SelectField.tsx
│   │   │       ├── ToggleField.tsx
│   │   │       ├── ColorField.tsx
│   │   │       ├── DateField.tsx
│   │   │       ├── MediaField.tsx
│   │   │       ├── WysiwygField.tsx
│   │   │       ├── CodeField.tsx
│   │   │       ├── TypographyField.tsx
│   │   │       ├── RadioImageField.tsx
│   │   │       ├── Repeater.tsx
│   │   │       └── ... (more fields)
│   │   ├── hooks/
│   │   │   ├── useStack.ts            # Fetch schema
│   │   │   ├── useStackData.ts        # Manage data state
│   │   │   ├── useStacks.ts           # List all stacks
│   │   │   └── useConditions.ts       # Visibility logic
│   │   ├── schema/
│   │   │   └── types.ts               # TypeScript definitions
│   │   ├── utils/
│   │   │   └── config.ts
│   │   ├── styles/
│   │   │   └── main.css               # TailwindCSS
│   │   ├── main.tsx                   # Entry point
│   │   └── StackApp.tsx               # Root component
│   ├── dist/                     # Build output
│   ├── package.json
│   ├── tsconfig.json
│   ├── vite.config.ts
│   └── tailwind.config.js

├── examples/
│   └── basic-usage.php           # Comprehensive examples

├── documents/
│   └── FLOW.md                   # This file!

├── optstack.php                  # Plugin bootstrap
├── composer.json
└── README.md
```

---

## Key Files Reference

### PHP Backend

#### `src/OptStack.php` - Facade/Entry Point

Main API surface. All public static methods for stack management.

**Key Methods**:
```php
OptStack::make(string $id): StackBuilder
OptStack::get(string $id): ?Stack
OptStack::all(): array
OptStack::getData(string $id, ?string $key, mixed $default): mixed
OptStack::saveData(string $id, array $data): bool
OptStack::schema(string $id): ?array
```

#### `src/Core/Stack/Stack.php` - Core Model

The heart of the framework. Defines structure, stores fields/groups/tabs.

**Key Methods**:
```php
$stack->field(string $key, array $config): self
$stack->group(string $key, callable $callback, array $config): self
$stack->tab(string $key, callable $callback, array $config): self
$stack->getData(): array
$stack->saveData(array $data): bool
$stack->getDefaults(): array
$stack->toArray(): array  // For schema export
```

#### `src/Core/Stack/StackBuilder.php` - Fluent API

Builds stacks with chainable methods.

**Pattern**:
```php
StackBuilder::make('id')
    ->forOptions() / forPostType() / forTaxonomy() / forUser()
    ->label()
    ->description()
    ->menuParent()
    ->define(callable)
    ->build()
```

#### `src/Core/Stack/StackRegistry.php` - Global Registry

Singleton that manages all registered stacks.

**Key Methods**:
```php
StackRegistry::register(Stack $stack): void
StackRegistry::get(string $id): ?Stack
StackRegistry::all(): array
StackRegistry::forPostType(string $postType): array
StackRegistry::forTaxonomy(string $taxonomy): array
StackRegistry::byContext(string $context): array
```

#### `src/WordPress/Bootstrap.php` - Initialization

Bootstraps the framework, registers hooks, REST API, and binds stores.

**Lifecycle**:
1. `boot()` - Called on `plugins_loaded`
2. `onInit()` - Fires `optstack_init`, binds stores
3. `registerRestRoutes()` - Sets up REST endpoints
4. `bindStores()` - Attaches storage to stacks

**REST Endpoints**:
- `GET /optstack/v1/stacks` → `restGetStacks()`
- `GET /optstack/v1/stacks/{id}` → `restGetStack()`
- `GET /optstack/v1/stacks/{id}/data` → `restGetStackData()`
- `POST /optstack/v1/stacks/{id}/data` → `restSaveStackData()`

#### `src/WordPress/Admin.php` - UI Rendering

Handles all admin UI rendering (pages, meta boxes, forms).

**Key Methods**:
```php
registerRenderers(): void              // Main entry point
registerOptionsPage(Stack): void       // Admin menu pages
registerMetaBox(Stack): void          // Post meta boxes
registerTaxonomyFields(Stack): void   // Term forms
registerUserFields(Stack): void       // User profile
enqueueAssets(Stack, array): void     // Load JS/CSS
renderMountPoint(Stack, array): void  // React mount points
```

#### `src/WordPress/Index/IndexedMetaManager.php` - Searchable Fields

Manages indexed meta keys for efficient querying.

**Key Methods**:
```php
syncIndexedMeta(Stack, array $data, int $objectId): void
getIndexedMetaKeys(Stack): array
getSearchableFields(Stack): array
```

### React Frontend

#### `frontend/src/main.tsx` - Entry Point

Finds all `.optstack-mount` elements and mounts React apps.

**Flow**:
```typescript
mountOptStack()
  → querySelectorAll('.optstack-mount')
  → for each: ReactDOM.createRoot().render(<StackApp />)
```

#### `frontend/src/StackApp.tsx` - Root Component

Root component that fetches schema and renders stack.

**Props**:
```typescript
interface StackAppProps {
  stackId: string
  context: string
  objectId?: number
  objectType?: string
}
```

**Flow**:
1. `useStack(stackId)` - Fetch schema
2. Show loading/error states
3. Render `<StackRenderer />`

#### `frontend/src/components/StackRenderer.tsx` - Orchestrator

Main component that:
- Fetches data with `useStackData()`
- Evaluates conditions with `useConditions()`
- Renders fields, groups, tabs
- Handles save/reset

**Key Logic**:
```typescript
const { data, isDirty, updateField, save, reset } = useStackData(stackId, objectId)
const { isVisible } = useConditions(data)

// Renders:
// - Root fields
// - Groups (repeatable or regular)
// - Tabs
// - Save bar (for options) or hidden input (for meta boxes)
```

#### `frontend/src/components/FieldRenderer.tsx` - Field Dispatcher

Maps field type to specific component:

```typescript
const fieldComponents = {
  text: TextField,
  number: NumberField,
  select: SelectField,
  toggle: ToggleField,
  color: ColorField,
  // ... etc
}

const Component = fieldComponents[field.type] || TextField
return <Component field={field} value={value} onChange={onChange} />
```

#### `frontend/src/hooks/useStackData.ts` - Data Management

Core hook for managing stack data state.

**Returns**:
```typescript
{
  data: Record<string, unknown>      // Current values
  loading: boolean                    // Initial fetch
  error: string | null               // Fetch errors
  saving: boolean                     // Save in progress
  isDirty: boolean                   // Has unsaved changes
  updateField: (key, value) => void  // Update single field
  save: () => Promise<boolean>       // POST to REST API
  reset: () => void                  // Revert to initial
}
```

---

## Common Patterns

### Pattern 1: Simple Options Page

```php
add_action('optstack_init', function() {
    OptStack::make('site_settings')
        ->forOptions()
        ->menuParent('optstack')
        ->label('Site Settings')
        ->define(function($stack) {
            $stack->field('site_name', [
                'type' => 'text',
                'label' => 'Site Name',
            ]);
        })
        ->build();
});

// Access
$settings = get_option('site_settings');
echo $settings['site_name'];
```

### Pattern 2: Post Meta Box with Groups

```php
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->label('Product Information')
        ->define(function($stack) {
            $stack->group('pricing', function($group) {
                $group->field('price', ['type' => 'number']);
                $group->field('currency', ['type' => 'select', 'options' => [...]]);
            }, ['label' => 'Pricing']);
        })
        ->build();
});

// Access
$product = get_post_meta($post_id, 'product_data', true);
echo $product['pricing']['price'];
```

### Pattern 3: Repeatable Group

```php
$stack->group('team_members', function($group) {
    $group->repeatable(0, 10); // min, max
    
    $group->field('name', ['type' => 'text']);
    $group->field('role', ['type' => 'text']);
    $group->field('photo', ['type' => 'media']);
}, [
    'label' => 'Team Members',
    'layout' => 'box',
    'collapsible' => true,
]);

// Access (array of items)
$members = $data['team_members']; // [['name' => '...', 'role' => '...'], ...]
foreach ($members as $member) {
    echo $member['name'];
}
```

### Pattern 4: Conditional Fields

```php
$stack->field('enable_advanced', [
    'type' => 'toggle',
    'label' => 'Enable Advanced Mode',
]);

$stack->field('advanced_setting', [
    'type' => 'text',
    'label' => 'Advanced Setting',
    'conditions' => [
        ['field' => 'enable_advanced', 'operator' => '==', 'value' => true]
    ],
]);

// Nested condition
$stack->group('features', function($group) {
    $group->field('feature_name', ['type' => 'text']);
}, [
    'conditions' => [
        ['field' => 'enable_advanced', 'operator' => '==', 'value' => true]
    ]
]);
```

**Operators**: `==`, `!=`, `>`, `<`, `>=`, `<=`, `contains`, `not_contains`, `empty`, `not_empty`, `in`, `not_in`

### Pattern 5: Searchable Fields for WP_Query

```php
// Define searchable fields
$stack->field('price', [
    'type' => 'number',
    'searchable' => true, // Enables indexing
]);

$stack->group('seo', function($group) {
    $group->field('title', [
        'type' => 'text',
        'searchable' => true, // Path: seo.title
    ]);
});

// Query by indexed fields
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_price',
            'value' => 100,
            'compare' => '>=',
            'type' => 'NUMERIC',
        ],
        [
            'key' => '_optstack_idx_post_seo_title',
            'compare' => 'EXISTS',
        ],
    ],
    'orderby' => 'meta_value_num',
    'meta_key' => '_optstack_idx_post_price',
]);
```

### Pattern 6: Tabs for Organization

```php
$stack->tab('general', function($tab) {
    $tab->label('General')
        ->priority(10);
    
    $tab->field('site_name', ['type' => 'text']);
    $tab->group('contact', function($group) {
        // Groups within tabs
    });
});

$stack->tab('advanced', function($tab) {
    $tab->label('Advanced')
        ->priority(20);
    
    $tab->field('custom_css', ['type' => 'code']);
});
```

### Pattern 7: Typography with Google Fonts

```php
$stack->field('heading_typography', [
    'type' => 'typography',
    'label' => 'Heading Typography',
    'default' => [
        'fontFamily' => '"Montserrat", sans-serif',
        'fontSize' => 32,
        'fontWeight' => '700',
        'lineHeight' => 1.3,
        'color' => '#111827',
    ],
]);

// Access in template
$typo = optstack_get_theme_option('heading_typography');

// Generate CSS
$css = optstack_typography_css($typo);
// → font-family: "Montserrat", sans-serif; font-size: 32px; ...

// Enqueue Google Fonts
$fonts_url = optstack_get_google_fonts_url([$typo]);
wp_enqueue_style('theme-fonts', $fonts_url);
```

### Pattern 8: Quick Field Updates

```php
// Instead of this (the old way):
$data = get_post_meta($post_id, 'product_data', true);
$data['price'] = 99.99;
update_post_meta($post_id, 'product_data', $data);
// Problem: Searchable fields not synced!

// Do this (the new way):
OptStack::updateField('product_data', 'price', 99.99, $post_id);
// ✅ Updates data
// ✅ Auto-syncs searchable fields

// Works with nested fields
OptStack::updateField('product_data', 'pricing.regular_price', 149.99, $post_id);

// Common use cases:
// Update stock quantity after purchase
add_action('woocommerce_payment_complete', function($order_id) {
    $order = wc_get_order($order_id);
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $current_stock = OptStack::getData('product_data', 'inventory.quantity', 0);
        $new_stock = max(0, $current_stock - $item->get_quantity());
        
        OptStack::updateField('product_data', 'inventory.quantity', $new_stock, $product_id);
        // Searchable field auto-synced for queries
    }
});

// Update post status based on form submission
add_action('gform_after_submission', function($entry, $form) {
    $post_id = $entry['post_id'];
    OptStack::updateField('product_data', 'status', 'pending_review', $post_id);
}, 10, 2);

// Bulk update fields
$product_ids = [123, 456, 789];
foreach ($product_ids as $product_id) {
    OptStack::updateField('product_data', 'featured', true, $product_id);
}
```

### Pattern 9: Radio Image Field

```php
$stack->field('layout', [
    'type' => 'radio-image',
    'label' => 'Layout Style',
    'default' => 'grid',
    'options' => [
        [
            'value' => 'grid',
            'label' => 'Grid Layout',
            'image' => 'https://example.com/grid.png',
            'tooltip' => '3-column grid',
        ],
        [
            'value' => 'list',
            'label' => 'List Layout',
            'image' => 'https://example.com/list.png',
        ],
    ],
    'attributes' => [
        'columns' => 3,
        'imageWidth' => '120px',
        'imageHeight' => '80px',
    ],
]);
```

---

## API Reference

### OptStack Facade Methods

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

// Get data from a stack
OptStack::getData(string $id, ?string $key = null, mixed $default = null): mixed

// Save data to a stack
OptStack::saveData(string $id, array $data): bool

// Update a single field (with searchable field auto-sync)
OptStack::updateField(string $id, string $key, mixed $value, ?int $objectId = null): bool

// Get stack schema (JSON)
OptStack::schema(string $id): ?array

// Get all schemas
OptStack::allSchemas(): array
```

### StackBuilder Methods

```php
$builder = OptStack::make('stack_id')

// Context configuration
    ->forOptions()                    // wp_options storage
    ->forPostType(string $postType)   // wp_postmeta storage
    ->forTaxonomy(string $taxonomy)   // wp_termmeta storage
    ->forUser()                       // wp_usermeta storage

// Metadata
    ->label(string $label)
    ->description(string $description)

// Menu configuration (options only)
    ->menuParent(string $parent)      // 'optstack', 'options-general.php', etc.
    ->menuIcon(string $icon)          // 'dashicons-admin-generic', URL
    ->menuPosition(int $position)
    ->capability(string $capability)  // 'manage_options', etc.

// Definition
    ->define(callable $callback)      // Define fields, groups, tabs
    ->field(string $key, array $config)
    ->group(string $key, callable $callback, array $config)
    ->tab(string $key, callable $callback, array $config)

// Build
    ->build(): Stack                  // Register and return stack
```

### Stack Methods

```php
// Configuration
$stack->forOptions()
$stack->forPostType(string $postType)
$stack->forTaxonomy(string $taxonomy)
$stack->forUser(?int $userId)

// Metadata
$stack->label(string $label)
$stack->description(string $description)

// Definition
$stack->field(string $key, array $config)
$stack->group(string $key, ?callable $callback, array $config)
$stack->tab(string $key, ?callable $callback, array $config)
$stack->define(callable $callback)

// Data access
$stack->getData(): array
$stack->saveData(array $data): bool
$stack->updateField(string $key, mixed $value, ?int $objectId = null): bool
$stack->getDefaults(): array

// Schema
$stack->toArray(): array

// Getters
$stack->getId(): string
$stack->getContext(): string
$stack->getLabel(): string
$stack->getFields(): FieldCollection
$stack->getGroups(): array
$stack->getTabs(): array
$stack->getStore(): ?StoreInterface
```

### Field Configuration

```php
$stack->field('key', [
    // Required
    'type' => 'text',              // Field type
    
    // Display
    'label' => 'Field Label',      // UI label
    'description' => '...',        // Help text
    
    // Data
    'default' => 'value',          // Default value
    
    // Behavior
    'searchable' => false,         // Index for WP_Query
    
    // Conditional visibility
    'conditions' => [
        [
            'field' => 'other_field',
            'operator' => '==',
            'value' => true,
            'relation' => 'AND',   // AND | OR
        ],
    ],
    
    // Field-specific options
    'options' => [                 // For select, radio, checkbox-group
        ['value' => 'option1', 'label' => 'Option 1'],
    ],
    
    // Field attributes (passed to component)
    'attributes' => [
        'placeholder' => '...',
        'min' => 0,
        'max' => 100,
        'step' => 1,
        'rows' => 5,
        // ... field-specific attributes
    ],
]);
```

### Field Types

| Type | Description | Attributes |
|------|-------------|------------|
| `text` | Single-line text | `placeholder`, `maxlength` |
| `textarea` | Multi-line text | `rows`, `placeholder` |
| `wysiwyg` | Rich text editor | `rows`, `simple` (boolean) |
| `number` | Numeric input | `min`, `max`, `step` |
| `range` | Slider | `min`, `max`, `step`, `unit` |
| `email` | Email input | `placeholder` |
| `url` | URL input | `placeholder` |
| `select` | Dropdown | `options` (array) |
| `radio` | Radio buttons | `options` (array) |
| `radio-image` | Image selection | `options` (with `image`), `columns`, `imageWidth`, `imageHeight` |
| `checkbox-group` | Multiple checkboxes | `options` (array) |
| `toggle` | On/off switch | - |
| `boolean` | Same as toggle | - |
| `color` | Color picker | `alpha` (boolean) |
| `date` | Date picker | `format` |
| `datetime` | Date + time picker | `format` |
| `time` | Time picker | `format` |
| `media` | File/image upload | `allowedTypes`, `buttonText`, `multiple`, `maxFiles` |
| `code` | Code editor | `language` ('text/css', 'text/html', etc.), `rows` |
| `typography` | Font settings | `disableGoogleFonts`, `fonts` (custom list) |

### Group Configuration

```php
$stack->group('key', function($group) {
    // Define fields within group
    $group->field('field1', [...]);
    $group->field('field2', [...]);
    
    // Nested groups
    $group->group('nested', function($nested) {
        // ...
    });
}, [
    // Group config
    'label' => 'Group Label',
    'description' => '...',
    
    // Layout
    'layout' => 'inline',          // 'inline' | 'box'
    'collapsible' => false,        // Allow collapse/expand
    
    // Repeatable
    'repeatable' => false,         // or true
    'minItems' => 0,               // Min items (if repeatable)
    'maxItems' => 10,              // Max items (if repeatable)
    
    // Conditional
    'conditions' => [...],
]);
```

### Tab Configuration

```php
$stack->tab('key', function($tab) {
    $tab->label('Tab Label')
        ->priority(10)             // Lower = earlier
        ->description('...');
    
    // Fields within tab
    $tab->field('field1', [...]);
    
    // Groups within tab
    $tab->group('group1', function($group) {
        // ...
    });
});
```

### REST API Endpoints

```
GET  /wp-json/optstack/v1/stacks
     Returns: array of stack schemas

GET  /wp-json/optstack/v1/stacks/{id}
     Returns: single stack schema

GET  /wp-json/optstack/v1/stacks/{id}/data
     Params: ?object_id=123 (for post/term/user context)
     Returns: { schema: {...}, data: {...} }

POST /wp-json/optstack/v1/stacks/{id}/data
     Params: ?object_id=123
     Body: { field1: value1, field2: value2, ... }
     Returns: { success: true, data: {...} }
```

---

## Examples

### Example 1: Theme Options with Tabs

```php
add_action('optstack_init', function() {
    OptStack::make('theme_options')
        ->forOptions()
        ->menuParent('themes.php')
        ->label('Theme Options')
        ->define(function($stack) {
            // General tab
            $stack->tab('general', function($tab) {
                $tab->label('General')->priority(10);
                
                $tab->field('logo', [
                    'type' => 'media',
                    'label' => 'Logo',
                    'attributes' => ['allowedTypes' => ['image']],
                ]);
            });
            
            // Colors tab
            $stack->tab('colors', function($tab) {
                $tab->label('Colors')->priority(20);
                
                $tab->field('primary_color', [
                    'type' => 'color',
                    'label' => 'Primary Color',
                    'default' => '#2271b1',
                ]);
            });
        })
        ->build();
});
```

### Example 2: Product Meta Box

```php
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->label('Product Data')
        ->define(function($stack) {
            // Simple fields
            $stack->field('sku', ['type' => 'text', 'label' => 'SKU']);
            $stack->field('price', [
                'type' => 'number',
                'label' => 'Price',
                'searchable' => true, // Enable WP_Query
            ]);
            
            // Group
            $stack->group('inventory', function($group) {
                $group->field('stock', ['type' => 'number']);
                $group->field('warehouse', ['type' => 'select', 'options' => [...]]);
            }, ['label' => 'Inventory']);
            
            // Repeatable group
            $stack->group('variants', function($group) {
                $group->repeatable(0, 10);
                
                $group->field('name', ['type' => 'text']);
                $group->field('price', ['type' => 'number']);
            }, ['label' => 'Variants']);
        })
        ->build();
});
```

### Example 3: Conditional Fields

```php
add_action('optstack_init', function() {
    OptStack::make('advanced_settings')
        ->forOptions()
        ->menuParent('optstack')
        ->define(function($stack) {
            // Toggle field
            $stack->field('enable_caching', [
                'type' => 'toggle',
                'label' => 'Enable Caching',
                'default' => false,
            ]);
            
            // Shows only when enable_caching is true
            $stack->field('cache_duration', [
                'type' => 'number',
                'label' => 'Cache Duration (seconds)',
                'default' => 3600,
                'conditions' => [
                    ['field' => 'enable_caching', 'operator' => '==', 'value' => true]
                ],
            ]);
            
            // Select field
            $stack->field('cache_driver', [
                'type' => 'select',
                'label' => 'Cache Driver',
                'options' => [
                    ['value' => 'redis', 'label' => 'Redis'],
                    ['value' => 'memcached', 'label' => 'Memcached'],
                    ['value' => 'file', 'label' => 'File'],
                ],
                'conditions' => [
                    ['field' => 'enable_caching', 'operator' => '==', 'value' => true]
                ],
            ]);
            
            // Shows only when Redis is selected
            $stack->field('redis_host', [
                'type' => 'text',
                'label' => 'Redis Host',
                'default' => 'localhost',
                'conditions' => [
                    ['field' => 'enable_caching', 'operator' => '==', 'value' => true],
                    ['field' => 'cache_driver', 'operator' => '==', 'value' => 'redis'],
                ],
            ]);
        })
        ->build();
});
```

### Example 4: Searchable Fields

```php
add_action('optstack_init', function() {
    OptStack::make('product_filters')
        ->forPostType('product')
        ->define(function($stack) {
            // All searchable for efficient queries
            $stack->field('price', [
                'type' => 'number',
                'searchable' => true,
            ]);
            
            $stack->field('status', [
                'type' => 'select',
                'searchable' => true,
                'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'draft', 'label' => 'Draft'],
                ],
            ]);
            
            $stack->field('featured', [
                'type' => 'toggle',
                'searchable' => true,
            ]);
            
            $stack->group('seo', function($group) {
                $group->field('title', [
                    'type' => 'text',
                    'searchable' => true, // Path: seo.title
                ]);
            });
        })
        ->build();
});

// Query products
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_price',
            'value' => [50, 200],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC',
        ],
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_featured',
            'value' => '1',
        ],
    ],
]);
```

### Example 5: Quick Field Updates

```php
// Define a stack with searchable fields
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->define(function($stack) {
            $stack->field('price', [
                'type' => 'number',
                'searchable' => true,
            ]);
            
            $stack->field('status', [
                'type' => 'select',
                'searchable' => true,
                'options' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'active', 'label' => 'Active'],
                ],
            ]);
            
            $stack->group('seo', function($group) {
                $group->field('title', [
                    'type' => 'text',
                    'searchable' => true,
                ]);
            });
        })
        ->build();
});

// Quick update a single field (the old way - fetch, modify, save)
$data = get_post_meta($post_id, 'product_data', true);
$data['price'] = 99.99;
update_post_meta($post_id, 'product_data', $data);
// Problem: searchable field NOT synced automatically!

// The new way - update field with auto-sync
OptStack::updateField('product_data', 'price', 99.99, $post_id);
// ✅ Updates main data
// ✅ Automatically syncs _optstack_idx_post_price

// Update nested field in group
OptStack::updateField('product_data', 'seo.title', 'New SEO Title', $post_id);
// ✅ Updates product_data['seo']['title']
// ✅ Automatically syncs _optstack_idx_post_seo_title

// Update status
OptStack::updateField('product_data', 'status', 'active', $post_id);
// ✅ Searchable field synced to _optstack_idx_post_status

// Now queries work efficiently
$active_products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [[
        'key' => '_optstack_idx_post_status',
        'value' => 'active',
    ]],
]);

// You can also use the stack instance directly
$stack = OptStack::get('product_data');
$stack->updateField('price', 149.99, $post_id);

// Hook into field update
add_action('optstack_searchable_field_synced', function($stack, $fieldPath, $value, $objectId) {
    error_log("Field {$fieldPath} updated to {$value} for #{$objectId}");
}, 10, 4);
```

**Benefits of `updateField()`:**
- ✅ **Simpler**: No need to fetch, merge, save
- ✅ **Safer**: Atomic update, no race conditions
- ✅ **Auto-sync**: Searchable fields synced automatically
- ✅ **Nested support**: Dot notation for nested fields
- ✅ **Performance**: Only updates what changed

### Example 6: Typography Field

```php
add_action('optstack_init', function() {
    OptStack::make('typography_settings')
        ->forOptions()
        ->menuParent('optstack')
        ->define(function($stack) {
            $stack->field('heading_font', [
                'type' => 'typography',
                'label' => 'Heading Typography',
                'default' => [
                    'fontFamily' => '"Montserrat", sans-serif',
                    'fontSize' => 32,
                    'fontSizeUnit' => 'px',
                    'fontWeight' => '700',
                    'lineHeight' => 1.3,
                    'color' => '#111827',
                ],
            ]);
            
            $stack->field('body_font', [
                'type' => 'typography',
                'label' => 'Body Typography',
                'default' => [
                    'fontFamily' => '"Inter", sans-serif',
                    'fontSize' => 16,
                    'fontWeight' => '400',
                    'lineHeight' => 1.6,
                ],
            ]);
        })
        ->build();
});

// In template
$heading = optstack_get_theme_option('heading_font');
$body = optstack_get_theme_option('body_font');

// Generate CSS
echo '<style>';
echo 'h1, h2, h3 { ' . optstack_typography_css($heading) . ' }';
echo 'body, p { ' . optstack_typography_css($body) . ' }';
echo '</style>';

// Enqueue Google Fonts
$fonts_url = optstack_get_google_fonts_url([$heading, $body]);
if ($fonts_url) {
    wp_enqueue_style('theme-fonts', $fonts_url);
}
```

---

## Debugging & Troubleshooting

### Enable Debug Mode

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('OPTSTACK_DEV_MODE', true);
```

### Common Issues

#### 1. **Stacks not appearing in admin**

**Check**:
- Is plugin activated?
- Are you using `optstack_init` hook?
- Is `build()` called?
- Check PHP errors in `wp-content/debug.log`

**Debug**:
```php
add_action('optstack_ready', function() {
    error_log('Registered stacks: ' . print_r(array_keys(OptStack::all()), true));
});
```

#### 2. **React UI not loading**

**Check**:
- Run `npm run build` in `frontend/`
- Check browser console for errors
- Verify `frontend/dist/optstack-admin.js` exists
- Check Network tab for 404s

**Debug**:
```php
add_action('admin_footer', function() {
    ?>
    <script>
    console.log('OptStack config:', window.optstack);
    </script>
    <?php
});
```

#### 3. **Data not saving**

**Check**:
- Browser Network tab - check POST request
- WordPress REST API enabled?
- User has `manage_options` capability?
- Check nonce field (for meta boxes)

**Debug**:
```php
add_action('optstack_data_saved', function($stack, $objectId, $objectType, $data) {
    error_log("Saved {$stack->getId()} for $objectType #$objectId: " . print_r($data, true));
}, 10, 4);
```

#### 4. **Searchable fields not working**

**Check**:
- Field has `searchable: true`
- Field type is scalar (not array/object)
- Not in repeatable group
- Run query with correct meta key format

**Debug**:
```php
$bootstrap = \OptStack\WordPress\Bootstrap::getInstance();
$manager = $bootstrap->getIndexedMetaManager();
$keys = $manager->getIndexedMetaKeys($stack);
print_r($keys); // Shows all indexed meta keys
```

#### 5. **Conditional fields not hiding**

**Check**:
- Field path correct (use dot notation for nested)
- Operator matches value type
- Browser console for JS errors

**Debug in React**:
```typescript
// In useConditions hook
console.log('Evaluating conditions:', field.conditions, 'with data:', data);
```

### Logging

```php
// Log all OptStack events
add_action('optstack_init', function() {
    error_log('OptStack init');
});

add_action('optstack_ready', function() {
    error_log('OptStack ready, stacks: ' . OptStack::count());
});

add_action('optstack_data_saved', function($stack, $objectId, $type, $data) {
    error_log("Data saved: {$stack->getId()}");
}, 10, 4);

add_action('optstack_indexed_meta_synced', function($stack, $data, $objectId) {
    error_log("Indexed meta synced for #{$objectId}");
}, 10, 3);
```

---

## Best Practices

### 1. **Use Namespaced Keys**

```php
// Good - clear namespace
OptStack::make('mytheme_settings')

// Bad - might conflict
OptStack::make('settings')
```

### 2. **Group Related Fields**

```php
// Good - organized
$stack->group('social', function($group) {
    $group->field('twitter', [...]);
    $group->field('facebook', [...]);
});

// Bad - flat structure
$stack->field('social_twitter', [...]);
$stack->field('social_facebook', [...]);
```

### 3. **Use Tabs for Complex Settings**

If you have more than 10-15 fields, use tabs to organize them.

### 4. **Mark Queryable Fields as Searchable**

```php
// If you'll query by this field
$stack->field('price', [
    'type' => 'number',
    'searchable' => true, // Do this!
]);
```

### 5. **Provide Defaults**

Always provide sensible defaults to avoid null/undefined issues.

### 6. **Use Conditions for Progressive Disclosure**

Hide advanced options behind toggles to keep UI simple.

### 7. **Add Descriptions**

Help users understand what each field does.

### 8. **Validate Searchable Field Types**

Only scalar fields can be searchable. Arrays/objects won't work with `meta_query`.

### 9. **Use Layout Options**

- `inline` for simple groups (2-3 fields)
- `box` for complex groups (5+ fields)
- `collapsible: true` for optional sections

### 10. **Test with Dev Mode**

Develop with HMR for faster iteration:
```php
define('OPTSTACK_DEV_MODE', true);
```

---

## Performance Considerations

### 1. **Autoload Options**

By default, options are autoloaded. For large datasets:
```php
// In OptionsStore constructor
new OptionsStore($stackId, $autoload = false);
```

### 2. **Searchable Fields**

Use searchable fields for queries, but don't over-index:
- ✅ Fields you'll query by (price, status, category)
- ❌ Large text fields
- ❌ Fields in repeatable groups

### 3. **Lazy Load Media**

```php
$stack->field('images', [
    'type' => 'media',
    'attributes' => [
        'multiple' => true,
        'maxFiles' => 10, // Limit to prevent performance issues
    ],
]);
```

### 4. **Minimize Conditionals**

Each condition is evaluated on every render. Keep them simple.

### 5. **Cache Heavy Queries**

```php
function mytheme_get_featured_products() {
    $cached = get_transient('featured_products');
    if ($cached !== false) {
        return $cached;
    }
    
    $products = new WP_Query([
        'meta_key' => '_optstack_idx_post_featured',
        'meta_value' => '1',
    ]);
    
    set_transient('featured_products', $products, HOUR_IN_SECONDS);
    return $products;
}
```

---

## Contributing

### Adding a New Field Type

1. **Define field in PHP** (optional, automatic)
2. **Create React component**:

```typescript
// frontend/src/components/fields/MyField.tsx
import { FieldRendererProps } from '../../schema/types'

export function MyField({ field, value, onChange, disabled }: FieldRendererProps) {
    return (
        <div className="os-field">
            <label className="os-field-label">{field.label}</label>
            <input
                type="text"
                value={value || ''}
                onChange={(e) => onChange(e.target.value)}
                disabled={disabled}
            />
        </div>
    )
}
```

3. **Register in FieldRenderer**:

```typescript
// frontend/src/components/FieldRenderer.tsx
import { MyField } from './fields/MyField'

const fieldComponents = {
    // ...
    myfield: MyField,
}
```

### Testing

```bash
# Backend tests (if you add PHPUnit)
composer test

# Frontend tests
cd frontend
npm run test

# Type checking
npm run type-check

# Build check
npm run build
```

---

## Resources

- **README.md** - Installation & quick start
- **examples/basic-usage.php** - Comprehensive examples
- **frontend/documents/OPTSTACK_SEARCHABLE_FIELDS.md** - Searchable fields guide
- **frontend/DEV-MODE.md** - Development setup

---

## Summary

OptStack provides a **clean, modern, type-safe** way to manage structured data in WordPress:

✅ **Define once** - Use everywhere (Options, Posts, Terms, Users)  
✅ **Native storage** - Standard WP functions  
✅ **Modern UI** - React + TypeScript + TailwindCSS  
✅ **Type-safe** - PHP 8.1+ strict types, TypeScript  
✅ **Efficient queries** - Searchable fields for `WP_Query`  
✅ **Great DX** - HMR, clear APIs, comprehensive examples  
✅ **Extensible** - Interface-driven, add custom fields easily  

**Key Takeaways**:

1. Stacks are registered on `optstack_init` hook
2. Data stored as single array in WordPress meta/options
3. React UI communicates via REST API
4. Searchable fields enable efficient WP_Query
5. Three-layer architecture keeps code clean and testable

---

**Happy coding! 🚀**
