# OptStack

> **OptStack – WordPress Data Stack Framework**
> A PHP framework for defining, storing, and managing structured data in WordPress using a unified, extensible stack-based model.

```text
optstack/
├── composer.json
├── README.md
├── CONTRIBUTING.md
├── ARCHITECTURE.md
├── LICENSE

├── src/                    # PHP SOURCE (Composer autoload)
│   ├── Core/               # 1️⃣ Pure PHP – KHÔNG WordPress
│   │   ├── Stack/
│   │   │   ├── Stack.php
│   │   │   ├── StackRegistry.php
│   │   │   └── StackBuilder.php
│   │   │
│   │   ├── Field/
│   │   │   ├── Field.php
│   │   │   ├── FieldGroup.php
│   │   │   └── FieldCollection.php
│   │   │
│   │   ├── Condition/
│   │   │   ├── Condition.php
│   │   │   └── ConditionEvaluatorInterface.php
│   │   │
│   │   ├── Path/
│   │   │   └── PathResolver.php
│   │   │
│   │   ├── Contract/
│   │   │   ├── StoreInterface.php
│   │   │   └── RendererInterface.php
│   │   │
│   │   └── Support/
│   │       ├── Arr.php
│   │       └── Validator.php
│
│   ├── WordPress/          # 2️⃣ WP Integration Layer
│   │   ├── Store/
│   │   │   ├── OptionsStore.php
│   │   │   ├── PostStore.php
│   │   │   └── TermStore.php
│   │   │
│   │   ├── Renderer/
│   │   │   ├── AdminRenderer.php
│   │   │   └── RestRenderer.php
│   │   │
│   │   ├── Hook/
│   │   │   ├── RegisterStacks.php
│   │   │   └── SaveHandler.php
│   │   │
│   │   └── Bootstrap.php
│
│   ├── Schema/             # 3️⃣ Schema export (PHP → JSON)
│   │   ├── SchemaExporter.php
│   │   └── SchemaNormalizer.php
│
│   └── OptStack.php        # Facade / entry class
│
├── plugin/                 # 4️⃣ WordPress Plugin Wrapper
│   ├── optstack.php        # Plugin bootstrap file
│   └── composer.json       # optional (if plugin-level deps)
│
├── frontend/               # 5️⃣ React Admin UI
│   ├── package.json
│   ├── tsconfig.json
│   ├── vite.config.ts
│   ├── src/
│   │   ├── components/
│   │   │   ├── fields/
│   │   │   │   ├── TextField.tsx
│   │   │   │   ├── SelectField.tsx
│   │   │   │   └── Repeater.tsx
│   │   │   └── FieldRenderer.tsx
│   │   │
│   │   ├── hooks/
│   │   ├── schema/
│   │   │   └── types.ts
│   │   ├── styles/
│   │   │   └── main.css
│   │   ├── wp-externals/   # Dev mode shims for WP globals
│   │   │   ├── react.ts
│   │   │   └── react-dom-client.ts
│   │   │
│   │   └── main.tsx
│   └── dist/               # Built assets
│
├── tests/                  # 6️⃣ Tests
│   ├── Core/
│   └── WordPress/
│
└── tools/
    └── ai/
        ├── backend-agent.md
        └── frontend-agent.md

```

---

## 1. Vision & Philosophy

OptStack is **not** just an options framework or a meta box helper.

It is designed as a **Data Stack Framework** that sits **below UI concerns**, focusing on **data modeling, storage, and retrieval**.

### Core principles

* **Data-first, UI-agnostic**
* **Native WordPress compatibility** (`get_option`, `get_post_meta`, `get_term_meta`)
* **Unified data model** across Options, Posts, and Terms
* **Composable & extensible** architecture
* **Future-proof** for Headless WordPress and REST-based workflows

---

## 2. What is a “Data Stack”?

A **Data Stack** represents a logical root of structured data stored in WordPress.

Each stack:

* Has a **storage backend** (options, post meta, term meta, etc.)
* Contains **groups** and **fields**
* Supports **nested**, **repeatable**, and **conditional** data

Think of it as a **schema + storage adapter**, not a UI screen.

---

## 3. Supported Data Contexts

OptStack supports multiple WordPress data sources via adapters:

| Context          | Storage       | WP API            |
| ---------------- | ------------- | ----------------- |
| Options          | `wp_options`  | `get_option()`    |
| Post             | `wp_postmeta` | `get_post_meta()` |
| Post Type        | `wp_postmeta` | `get_post_meta()` |
| Term / Taxonomy  | `wp_termmeta` | `get_term_meta()` |
| User *(planned)* | `wp_usermeta` | `get_user_meta()` |

All contexts share the **same field definition syntax**.

---

## 4. Architecture Overview

OptStack is split into **three strict layers**:

### 4.1 Core (Framework Layer)

Pure PHP. No WordPress dependencies.

Responsibilities:

* Stack definition
* Field schema
* Grouping & nesting
* Conditional metadata
* Path resolution
* Sanitization rules

This layer must be **unit-testable without WordPress**.

---

### 4.2 Store Adapters (WordPress Integration)

Bridges OptStack with WordPress storage.

Responsibilities:

* Read/write data
* Data normalization
* Mapping stack root to WP storage

Examples:

* `OptionsStore`
* `PostStore`
* `TermStore`

---

### 4.3 Renderers (UI / Admin / API)

Optional and replaceable.

Responsibilities:

* Render admin UI
* Handle save hooks
* Integrate with Settings API, Meta Boxes, REST, Gutenberg, etc.

Renderers **must never own business logic**.

---

## 5. Installation

```bash
composer require optstack/optstack
```

---

## 6. Basic Usage

### 6.1 Options Stack

```php
use OptStack\OptStack;

OptStack::make('site_settings')
    ->forOptions()
    ->define(function ($stack) {
        $stack->field('site_color', [
            'type' => 'text',
            'default' => '#000000',
        ]);
    });
```

Data is stored as a **single option array** and accessible via:

```php
get_option('site_settings');
```

---

### 6.2 Post Type Stack

```php
OptStack::make('product_data')
    ->forPostType('product')
    ->define(function ($stack) {
        $stack->group('pricing', function ($group) {
            $group->field('price', ['type' => 'number']);
            $group->field('currency', ['type' => 'select']);
        });
    });
```

---

### 6.3 Term / Taxonomy Stack

```php
OptStack::make('category_settings')
    ->forTaxonomy('category')
    ->define(function ($stack) {
        $stack->field('icon');
        $stack->field('color');
    });
```

---

## 7. Field Definition Schema

Each field is defined via an associative array.

```php
$field = [
    'type'        => 'text',
    'default'     => null,
    'sanitize'    => 'sanitize_text_field',
    'validate'    => null,
    'description' => '',
    'conditions'  => [],
];
```

Fields are **data descriptors**, not UI components.

---

## 8. Groups, Nesting & Repeatables

```php
$stack->group('features', function ($group) {
    $group->repeatable()->fields(function ($item) {
        $item->field('title');
        $item->field('enabled', ['type' => 'boolean']);
    });
});
```

Data output:

```php
[
  'features' => [
    ['title' => 'A', 'enabled' => true],
    ['title' => 'B', 'enabled' => false],
  ]
]
```

---

## 9. Conditional Fields

Conditions are **metadata**, not UI logic.

```php
$stack->field('advanced_option', [
    'conditions' => [
        ['field' => 'enable_advanced', 'operator' => '==', 'value' => true]
    ]
]);
```

Renderers decide how to interpret conditions.

---

## 10. Naming & API Conventions

* Use **data-centric naming**
* Avoid UI-specific terms

### Preferred

* `forOptions()`
* `forPost()`
* `forPostType()`
* `forTerm()`
* `forTaxonomy()`

### Avoid

* `add_metabox()`
* `add_option_page()`

---

## 11. Frontend (UI) Development Guide

OptStack is **UI-agnostic**, but provides a clear contract for building modern frontend interfaces.

This section describes how to build an **Admin UI layer** using **React + TypeScript + TailwindCSS**.

---

## 11.1 Frontend Architecture

Frontend is treated as a **Renderer**.

```text
OptStack Core (PHP)
   ↓ schema export
REST / PHP Bridge
   ↓ JSON schema
React Renderer (Admin UI)
```

Key rule:

> **Frontend never defines fields. It only consumes schemas.**

---

## 11.2 Schema Contract (Backend → Frontend)

OptStack must expose stack definitions as **JSON Schema-like structures**.

Example payload:

```json
{
  "stack": "product_data",
  "context": "post",
  "groups": {
    "pricing": {
      "repeatable": false,
      "fields": {
        "price": {
          "type": "number",
          "default": 0
        },
        "currency": {
          "type": "select",
          "options": ["USD", "EUR"]
        }
      }
    }
  }
}
```

This payload is the **single source of truth** for UI rendering.

---

## 11.3 Field Type Mapping (FE Responsibility)

Frontend maps `type` → React component.

| Field type | React component   |
| ---------- | ----------------- |
| text       | `<TextField />`   |
| number     | `<NumberField />` |
| select     | `<SelectField />` |
| boolean    | `<ToggleField />` |
| group      | `<FieldGroup />`  |
| repeatable | `<Repeater />`    |

Backend **must not** assume how UI is rendered.

---

## 11.4 Conditional Logic Handling

Conditions are passed as metadata:

```json
"conditions": [
  { "field": "enable_advanced", "operator": "==", "value": true }
]
```

Frontend responsibilities:

* Evaluate conditions at runtime
* Show/hide fields
* Disable inputs if needed

Backend **does not execute conditions**.

---

## 11.5 State Management

Recommended FE state shape:

```ts
interface StackState {
  [group: string]: any;
}
```

Rules:

* Keep state normalized
* Avoid coupling UI state with WordPress specifics
* Treat stack as a controlled form

---

## 11.6 Saving & Persistence

Frontend submits **entire stack payload**:

```json
{
  "stack": "product_data",
  "data": {
    "pricing": {
      "price": 100,
      "currency": "USD"
    }
  }
}
```

Backend handles:

* Sanitization
* Validation
* Persistence via Store Adapter

---

## 11.7 Tech Stack Recommendation

* **React** – UI rendering
* **TypeScript** – schema safety
* **TailwindCSS** – fast, consistent styling
* **Vite** – build tooling with HMR support
* **Zod** *(optional)* – runtime validation

---

## 11.8 Development Workflow

OptStack supports two frontend development modes:

### Production Mode (Default)

```bash
cd frontend
npm run build
```

Assets are built to `frontend/dist/` and loaded by WordPress.

### Development Mode (Hot Reload)

For live reloading during development:

1. Enable in `wp-config.php`:
   ```php
   define('OPTSTACK_DEV_MODE', true);
   ```

2. Start Vite dev server:
   ```bash
   cd frontend
   npm run dev
   ```

3. WordPress loads assets from `http://localhost:5173`

**How it works:**

```text
┌─────────────────────┐     ┌─────────────────────┐
│   WordPress Admin   │ ←── │   Vite Dev Server   │
│                     │     │   localhost:5173    │
│  OPTSTACK_DEV_MODE  │     │                     │
│       = true        │     │  - HMR for CSS      │
└─────────────────────┘     │  - Page reload JS   │
                            └─────────────────────┘
```

**Configuration:**

| Constant | Default | Description |
|----------|---------|-------------|
| `OPTSTACK_DEV_MODE` | `false` | Enable dev server loading |
| `OPTSTACK_DEV_SERVER` | `http://localhost:5173` | Vite server URL |

**Notes:**
- React is provided by WordPress globally (not bundled)
- Fast Refresh is disabled (uses WordPress's React)
- CSS changes apply instantly via HMR
- JS/TSX changes trigger full page reload

---

## 11.9 UI Design Rules

* No hard-coded field logic
* No WordPress-specific UI assumptions
* Components must be composable
* UI must tolerate unknown field types

---

## 12. AI Agent Development Guide

This project is designed to be developed collaboratively with AI agents.

### Rules for AI contributors

* Do **not** mix UI logic into Core
* Do **not** introduce WordPress dependencies into Core
* Always prefer extending via interfaces
* Follow existing naming conventions
* Add tests for Core changes

### Stable APIs

* Stack definition DSL
* Store interfaces
* Field schema structure

These APIs should be considered **contractual**.

---

## 12. Roadmap

### v0.x (Foundation)

* Core stack engine
* Options / Post / Term stores
* Field schema & validation

### v1.0

* Admin renderer
* Conditional engine
* Migration helpers

### Future

* REST adapter
* Headless UI
* Schema export/import
* Gutenberg sidebar integration

---

## 13. License

MIT License
