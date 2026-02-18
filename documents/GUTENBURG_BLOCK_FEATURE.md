# OptStack Gutenberg Block Feature

> **Feature:** Allow OptStack to register Gutenberg blocks with schema-driven fields. Block attributes are powered by OptStack stacks; the UI reuses the existing OptStack React renderer; data is stored in `post_content` as block attributes.

---

## Overview

This document specifies how OptStack integrates with the WordPress Block Editor (Gutenberg) so that developers can define blocks whose configuration UI is driven by OptStack schemas. Instead of manually building `InspectorControls` and managing `attributes`, developers register a block that references an OptStack stack—the framework handles the rest.

### Data Flow

```
┌─────────────────────┐
│  OptStack Schema    │  ← Stack definition (fields, groups, types)
│  (PHP Stack/Field)  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ OptStack UI         │  ← StackRenderer (React) - reused
│ Renderer            │     Renders in block InspectorControls
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Gutenberg Block     │  ← Block sidebar / Inspector panel
│ Panel               │     Uses @wordpress/block-editor
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ post_content JSON   │  ← Block attributes in comment delimiter
│ (block attributes)  │     <!-- wp:namespace/block {"field":"value"} -->
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ PHP render_block()  │  ← Server-side render_callback
│ (render_callback)   │     Receives attributes, outputs HTML
└─────────────────────┘
```

---

## Key Design Decisions

### 1. Storage: Block Attributes vs Post Meta

| Approach | Storage | Pros | Cons |
|----------|---------|------|------|
| **Block attributes** | `post_content` (block comment) | Native block model; block is self-contained; supports block patterns/templates | Only available within block context |
| **Post meta** | `wp_postmeta` | Reusable with existing OptStack PostStore; shared across blocks | Not block-specific; different data model |

**Decision:** Use **block attributes** for block configuration. This aligns with Gutenberg's architecture—each block instance owns its settings. OptStack provides the schema and UI; the block owns the data.

### 2. Schema Reuse

OptStack schemas (fields, groups, types, conditions) are **reused as-is**. No duplicate schema format. The block registration layer maps schema → `block.json` attributes and wires the UI.

### 3. UI Reuse

The existing **StackRenderer** (and `FieldRenderer`, `GroupRenderer`, etc.) is reused inside the block's `edit` function. A new **data adapter** passes block attributes instead of REST-fetched data, and calls `setAttributes` instead of REST save.

---

## Architecture

### Components

```
optstack/
├── src/
│   ├── WordPress/
│   │   ├── Block/
│   │   │   ├── BlockRegistry.php       # Register OptStack blocks
│   │   │   ├── SchemaToAttributes.php  # Map stack schema → block attributes
│   │   │   └── BlockRenderer.php       # PHP render_callback helper
│   │   └── Bootstrap.php               # Register block assets, init BlockRegistry
│   └── Core/Stack/
│       └── Stack.php                   # New: forBlockType() context
├── frontend/src/
│   ├── blocks/
│   │   ├── OptStackBlock.tsx           # Block edit wrapper
│   │   ├── BlockStackRenderer.tsx      # StackRenderer + block attributes adapter
│   │   └── useBlockStackData.ts        # Hook: attributes ↔ setAttributes
│   └── main.tsx                        # Or separate blocks entry point
└── blocks/                             # Built block JS (or bundled in frontend)
    └── optstack-block.js
```

### New Stack Context: `block`

A stack can be configured for block usage:

```php
OptStack::make('hero_block_settings')
    ->forBlockType('mytheme/hero')  // Registers for this block
    ->label('Hero Block Settings')
    ->define(function ($stack) {
        $stack->field('title', ['type' => 'text', 'label' => 'Title']);
        $stack->field('subtitle', ['type' => 'textarea', 'label' => 'Subtitle']);
        $stack->field('background_color', ['type' => 'color', 'label' => 'Background', 'default' => '#ffffff']);
        $stack->field('image', ['type' => 'media', 'label' => 'Background Image']);
    })
    ->build();
```

**Alternative API:** `forBlock()` as a dedicated block registration helper that creates both the stack and the block in one call (see API section).

---

## Implementation Phases

### Phase 1: Schema → Block Attributes Mapping

**PHP: `SchemaToAttributes`**

- Input: `Stack` (or schema array)
- Output: `attributes` definition for `block.json`
- Mapping rules:
  - `text`, `textarea`, `url`, `email` → `type: 'string'`
  - `number`, `range` → `type: 'number'`
  - `toggle`, `boolean`, `checkbox` → `type: 'boolean'`
  - `select`, `radio` → `type: 'string'` (or `enum` if options fixed)
  - `color` → `type: 'string'`
  - `media` → `type: 'object'` (id, url, alt, etc.)
  - `group` (non-repeatable) → `type: 'object'`
  - `group` (repeatable) → `type: 'array'`
- Default values from schema become `default` in attributes

### Phase 2: Block Registration (PHP)

**`BlockRegistry`**

- On `optstack_ready`, find all stacks with `context === 'block'` (or `forBlockType` config)
- For each stack:
  - Generate `block.json` (or equivalent) with:
    - `name`: from stack config (e.g. `optstack/hero` or custom namespace)
    - `attributes`: from `SchemaToAttributes`
    - `render_callback`: `BlockRenderer::render($attributes, $content, $block)`
  - `register_block_type()` with server-side registration
  - Enqueue block script (React) that uses OptStack UI

**`BlockRenderer` (PHP)**

- Receives `$attributes` (same shape as schema)
- Option A: Generic template (e.g. Twig/latte) if stack defines one
- Option B: Call `do_action('optstack_render_block_{block_name}', $attributes)` and let theme/plugin provide template
- Option C: Stack defines `render` callback in PHP
- Output HTML (no inner blocks unless specified)

### Phase 3: Block Edit (React)

**`BlockStackRenderer`**

- Props: `stackId`, `attributes`, `setAttributes`
- Fetches schema via `useStack(stackId)` (REST: `/stacks/{id}`)
- Does **not** call `/stacks/{id}/data` (no REST for block data)
- Uses `attributes` as data source; `setAttributes` as save
- Passes to `StackRenderer` with a **block adapter**:
  - `data` = `attributes`
  - `updateField(key, value)` → `setAttributes({ [key]: value })`
  - `save` = no-op (attributes auto-saved by Gutenberg)
  - `loading` = false (schema load only)

**`useBlockStackData` hook**

```ts
function useBlockStackData(stackId: string, attributes: object, setAttributes: (a: object) => void) {
  const { schema } = useStack(stackId)
  const data = attributes
  const updateField = (key: string, value: unknown) => {
    setAttributes({ ...attributes, [key]: value })
  }
  return { schema, data, updateField, save: async () => true }
}
```

**Block `edit` component**

```tsx
function OptStackBlockEdit(props) {
  const { attributes, setAttributes } = props
  return (
    <>
      <BlockControls>...</BlockControls>
      <InspectorControls>
        <BlockStackRenderer
          stackId="hero_block_settings"
          attributes={attributes}
          setAttributes={setAttributes}
        />
      </InspectorControls>
      <div {...useBlockProps()}>
        {/* Block preview in editor */}
      </div>
    </>
  )
}
```

### Phase 4: Block Build & Enqueue

- Add block script to Vite (or separate webpack) build
- Output: `blocks/optstack-block.js` (or per-block bundles)
- Enqueue in PHP only when `block_editor_settings` / `enqueue_block_editor_assets`
- Block script depends on `wp-blocks`, `wp-element`, `wp-block-editor`, `optstack-admin` (shared StackRenderer)

---

## API Proposal

### PHP: Register a block with OptStack

**Option A: Stack + Block separate**

```php
// Define stack for block
OptStack::make('hero_block_settings')
    ->forBlockType('mytheme/hero')
    ->label('Hero Block')
    ->define(function ($stack) { ... })
    ->build();

// Register block separately (theme/plugin)
register_block_type('mytheme/hero', [
    'render_callback' => [OptStack\WordPress\Block\BlockRenderer::class, 'render'],
]);
```

**Option B: Single call (OptStack owns block registration)**

```php
OptStack::block('mytheme/hero', function ($stack) {
    $stack->field('title', ['type' => 'text', 'label' => 'Title']);
    $stack->field('image', ['type' => 'media', 'label' => 'Image']);
})
->label('Hero Block')
->renderWith(function ($attributes) {
    return sprintf(
        '<div class="hero"><h1>%s</h1><img src="%s" /></div>',
        esc_html($attributes['title'] ?? ''),
        esc_url($attributes['image']['url'] ?? '')
    );
});
```

**Option C: Hybrid** – Stack defines schema; block registration references stack ID:

```php
// Theme/plugin registers block, references OptStack stack
register_block_type('mytheme/hero', [
    'attributes' => OptStack\WordPress\Block\SchemaToAttributes::fromStack('hero_block_settings'),
    'render_callback' => [BlockRenderer::class, 'render'],
]);

// Stack defined elsewhere (e.g. theme-options)
OptStack::make('hero_block_settings')
    ->forBlockType('mytheme/hero')
    ->define(...)
    ->build();
```

### PHP: Render callback

```php
// BlockRenderer::render
public static function render(array $attributes, string $content, WP_Block $block): string {
    $stackId = $block->block_type->optstack_stack ?? null;
    if (!$stackId) {
        return '';
    }
    return (string) apply_filters(
        'optstack_render_block',
        '',
        $stackId,
        $attributes,
        $block
    );
}
```

Theme/plugin adds template:

```php
add_filter('optstack_render_block', function ($html, $stackId, $attributes, $block) {
    if ($stackId !== 'hero_block_settings') return $html;
    ob_start();
    get_template_part('blocks/hero', null, $attributes);
    return ob_get_clean();
}, 10, 4);
```

---

## Schema Considerations for Blocks

### Supported field types

All existing OptStack field types should work. Ensure:

- `media` → stored as object `{ id, url, alt, ... }` in attributes
- `group` (repeatable) → `type: 'array'` in block.json
- `conditions` → evaluated client-side; no change

### Attributes size

Block attributes are stored in `post_content`. Very large nested structures may bloat the post. Consider:

- Prefer scalar/compact values
- For media, store attachment ID + optional thumbnail URL; resolve full URL in PHP
- Document recommended max depth/size for block stacks

### Block-specific config

Stack config for blocks may include:

- `block_name`: `namespace/block-name`
- `block_category`: e.g. `theme`
- `block_icon`: Dashicon or SVG
- `block_keywords`: for inserter search
- `block_preview`: whether to show a live preview in the editor (or placeholder)

---

## File Structure (Proposed)

```
optstack/
├── src/WordPress/Block/
│   ├── BlockRegistry.php
│   ├── SchemaToAttributes.php
│   ├── BlockRenderer.php
│   └── BlockAssetEnqueue.php
├── frontend/src/
│   ├── blocks/
│   │   ├── OptStackBlockEdit.tsx
│   │   ├── BlockStackRenderer.tsx
│   │   └── useBlockStackData.ts
│   └── main.tsx (or blocks/block-editor.tsx entry)
├── blocks/                          # Built output
│   └── optstack-dynamic-block.js
└── documents/
    └── GUTENBURG_BLOCK_FEATURE.md
```

---

## Hooks & Filters

| Hook/Filter | Purpose |
|-------------|---------|
| `optstack_register_blocks` | Register additional block types or modify block config |
| `optstack_render_block` | Override or provide render output for a block |
| `optstack_block_attributes` | Modify generated attributes for a stack |
| `optstack_block_attributes_{stack_id}` | Stack-specific attribute modification |

---

## Out of Scope (v1)

- Inner blocks (block nesting) – blocks are leaf nodes
- Block variations – each stack = one block type
- Block patterns – can be added later using standard pattern API
- Server-side schema validation for block attributes – Gutenberg handles save

---

## Checklist for Implementation

- [x] `Stack::forBlockType()` / `StackBuilder::forBlockType()`
- [x] `SchemaToAttributes::fromStack()`
- [x] `BlockRegistry` – auto-register blocks from stacks
- [x] `BlockRenderer` – PHP render_callback
- [x] `BlockStackRenderer` – React component for InspectorControls
- [x] `useBlockStackData` – attributes ↔ setAttributes adapter
- [x] Build pipeline for block script (vite.block.config.ts)
- [x] Enqueue block script in editor
- [x] Documentation & example block

---

## References

- [Block Registration – Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/)
- [block.json – Block Editor Handbook](https://developer.wordpress.org/block-editor/getting-started/fundamentals/block-json)
- [Attributes – Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/)
- [Edit and Save – Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/)
