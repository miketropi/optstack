---
name: optstack-dev
description: Development guide for OptStack WordPress Data Stack Framework. Use when working with OptStack plugin code - creating/modifying stacks, fields, groups, tabs, stores, or React field components. Covers PHP backend (Core/WordPress layers), React frontend (TypeScript), field types, searchable fields, conditional visibility, and the complete save/load data flow.
---

# OptStack Development Guide

OptStack is a WordPress Data Stack Framework for defining and managing structured data across WordPress contexts (Options, Post Meta, Term Meta, User Meta).

## Architecture Overview

```
┌─────────────────────────────────────┐
│         React Frontend              │  TypeScript + React + CSS
│   (fields/, hooks/, StackRenderer)  │  frontend/src/
└─────────────────────────────────────┘
                 ▲ REST API
┌─────────────────────────────────────┐
│       WordPress Integration         │  Store adapters, Admin UI
│   (Bootstrap, Admin, Store/)        │  src/WordPress/
└─────────────────────────────────────┘
                 ▲ Interfaces
┌─────────────────────────────────────┐
│          Core Framework             │  Pure PHP, no WP deps
│   (Stack, Field, Conditions)        │  src/Core/
└─────────────────────────────────────┘
```

## Key Files

| Purpose | Location |
|---------|----------|
| Plugin entry | `optstack.php` |
| OptStack facade | `src/OptStack.php` |
| Stack model | `src/Core/Stack/Stack.php` |
| Field definition | `src/Core/Field/Field.php` |
| Bootstrap/REST | `src/WordPress/Bootstrap.php` |
| Admin rendering | `src/WordPress/Admin.php` |
| Indexed meta | `src/WordPress/Index/IndexedMetaManager.php` |
| React entry | `frontend/src/main.tsx` |
| Field dispatcher | `frontend/src/components/FieldRenderer.tsx` |
| Data hook | `frontend/src/hooks/useStackData.ts` |
| Styles | `frontend/src/styles/main.css` |
| Examples | `examples/basic-usage.php` |

## Development Workflows

### Adding a New Field Type

1. **Create React component** in `frontend/src/components/fields/MyField.tsx`:
   ```typescript
   import type { FieldRendererProps } from '../../schema/types'
   
   export function MyField({ field, value, onChange, disabled }: FieldRendererProps) {
     return (
       <div className="os-field">
         <label className="os-label">{field.label}</label>
         <div className="os-field-body">
           {/* Field implementation */}
         </div>
       </div>
     )
   }
   ```

2. **Register in FieldRenderer.tsx**:
   ```typescript
   import { MyField } from './fields/MyField'
   
   const fieldComponents = {
     // ... existing
     'my-field': MyField,
   }
   ```

3. **Add CSS** in `frontend/src/styles/main.css` using `os-` prefix

4. **Add to SEARCHABLE_TYPES** in `src/Core/Field/Field.php` if scalar type

### Modifying Save/Load Flow

Data flow: React → REST API → Bootstrap → Store → WordPress meta/options

- **REST endpoints**: `src/WordPress/Bootstrap.php` (`restSaveStackData`, `restGetStackData`)
- **Store adapters**: `src/WordPress/Store/` (OptionsStore, PostStore, TermStore, UserStore)
- **Indexed meta**: `src/WordPress/Index/IndexedMetaManager.php`

### Adding Stack Features

1. **Add property** to `src/Core/Stack/Stack.php`
2. **Add builder method** to `src/Core/Stack/StackBuilder.php`
3. **Export in schema** via `toArray()` method
4. **Handle in React** via schema types

## Quick Reference

### Define a Stack

```php
add_action('optstack_init', function() {
    OptStack::make('my_stack')
        ->forPostType('post')  // or forOptions(), forTaxonomy(), forUser()
        ->label('My Stack')
        ->define(function($stack) {
            $stack->field('title', ['type' => 'text', 'label' => 'Title']);
            $stack->group('settings', function($group) {
                $group->field('enabled', ['type' => 'toggle']);
            }, ['label' => 'Settings']);
        })
        ->build();
});
```

### Programmatic Data Access

```php
// Read
$data = get_post_meta($post_id, 'my_stack', true);
$value = OptStack::getData('my_stack', 'field_key', $default);

// Write (auto-syncs searchable fields)
OptStack::updateField('my_stack', 'field_key', $value, $post_id);
OptStack::saveData('my_stack', ['field1' => 'val1', 'field2' => 'val2']);
```

### Searchable Fields

Mark fields `'searchable' => true` to enable efficient `WP_Query`:

```php
$stack->field('price', ['type' => 'number', 'searchable' => true]);
// Creates: _optstack_idx_post_price
```

## Detailed References

- **Field types & configuration**: See [references/field-types.md](references/field-types.md)
- **API methods & hooks**: See [references/api.md](references/api.md)
- **Common patterns**: See [references/patterns.md](references/patterns.md)

## Dev Mode

Enable HMR for frontend development:

```php
// wp-config.php
define('OPTSTACK_DEV_MODE', true);
```

```bash
cd frontend && npm run dev
```

Build for production: `cd frontend && npm run build`

## Key Hooks

| Hook | When |
|------|------|
| `optstack_init` | Define stacks here |
| `optstack_ready` | Stores bound, safe to use |
| `optstack_data_saved` | After data saved |
| `optstack_indexed_meta_synced` | After searchable fields synced |
| `optstack_searchable_field_synced` | After single field synced (updateField) |
