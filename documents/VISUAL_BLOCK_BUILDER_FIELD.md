# AI Implementation Prompt — Visual Block Builder Field (OptStack)

## Project Context

You are contributing to **OptStack**, a PHP framework designed as a **WordPress Data Stack Framework**.

OptStack is:
- data-first
- WordPress-native
- UI-agnostic

OptStack uses a **closure-based API** for defining stacks and groups:

```php
$stack->group('key', function ($group) {
    // define fields
}, $args);
```

Your task is to design and implement a **new field type** called:

> **Visual Block Builder Field**

---

## Problem Statement

In many WordPress use cases (header, footer, layout configuration), users need to visually compose **structured layouts** made of predefined blocks (logo, menu, button, columns).

Using plain form fields for these cases leads to:

* poor UX
* unclear layout structure
* hard-to-maintain configurations

However, building a full page builder is:

* overly complex
* not data-centric
* outside the scope of OptStack

---

## Goal

Introduce a **Visual Block Builder Field** that allows users to compose layout structures visually, while:

* remaining **data-driven**
* storing **pure structured data (JSON)**
* avoiding HTML, CSS, or JSX storage
* delegating all rendering to frontend

---

## Core Concept

A **Visual Block Builder Field**:

* is a single OptStack field type
* operates inside a **Deferred Group (modal)**
* allows drag-and-drop composition of predefined blocks
* outputs a structured layout schema (JSON)

It is **NOT**:

* a page builder
* a WYSIWYG editor
* a frontend renderer

---

## Placement Rules (MANDATORY)

* Visual Block Builder MUST be used inside a **Deferred Group**
* It MUST NOT be rendered inline
* It MUST rely on Deferred Group metadata for modal behavior

Example:

```php
$stack->group('header', function ($group) {

    $group->field('layout', [
        'type' => 'visual_builder',
        'label' => 'Header Layout',
        'blocks' => ['logo', 'menu', 'button'],
        'design' => ['alignment', 'spacing'],
    ]);

}, [
    'label'    => 'Header',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Edit Header Layout',
        'render' => 'modal',
    ],
]);
```

---

## Field Definition (PHP)

### Field Type

```php
'type' => 'visual_builder'
```

### Required Options

* `blocks` (array of allowed block types)

### Optional Options

* `design` (array of allowed design controls)
* `default` (initial layout structure)

---

## Normalized Field Schema

After normalization, the field schema MUST resemble:

```php
[
  'type' => 'visual_builder',
  'key' => 'layout',
  'label' => 'Header Layout',
  'blocks' => ['logo', 'menu', 'button'],
  'design' => ['alignment', 'spacing'],
  'default' => [
    'blocks' => [],
    'layout' => []
  ]
]
```

OptStack Core MUST treat this as a **single field**.

---

## Builder UI Model (Conceptual)

Frontend implementation (React) will expose two main areas:

### 1. Blocks Panel

* Lists allowed block types
* Drag source only
* No state stored here

### 2. Design / Inspector Panel

* Displays settings for selected block
* Maps controls to block `props`
* Uses schema-driven inputs

OptStack Core MUST NOT implement this UI.

---

## Data Model (MANDATORY)

Visual Builder MUST store **pure structured data**.

### Example Stored Value

```json
{
  "blocks": [
    {
      "id": "block_1",
      "type": "logo",
      "props": {
        "align": "left"
      }
    },
    {
      "id": "block_2",
      "type": "menu",
      "props": {
        "menu_id": 12
      }
    },
    {
      "id": "block_3",
      "type": "button",
      "props": {
        "text": "Donate",
        "url": "/donate",
        "style": "primary"
      }
    }
  ],
  "layout": {
    "direction": "row",
    "gap": 16
  }
}
```

### Storage Rules

* No HTML
* No CSS strings
* No JSX
* No inline styles
* No frontend-specific tokens

---

## Validation Rules

The Visual Block Builder field MUST:

* Validate block `type` against allowed `blocks`
* Validate block `props` shape if schema exists
* Reject unknown block types
* Allow empty layout unless explicitly required

Validation MUST occur during normal OptStack validation flow.

---

## Search & Indexing Constraints

* Visual Builder field MUST NOT be searchable
* No indexed meta
* No meta flattening

This field represents layout configuration, not queryable data.

---

## Compatibility Requirements

Visual Block Builder MUST remain compatible with:

* Deferred Groups
* Conditional logic
* Import / Export
* Revisions
* Multisite

---

## Block Registry (Future-ready Design)

OptStack Core MUST NOT hardcode block definitions.

A future-compatible registry MAY exist:

```php
OptStack::registerBlock('logo', [
    'label' => 'Logo',
    'props' => [
        'align' => [
            'type' => 'select',
            'options' => ['left', 'center', 'right']
        ]
    ]
]);
```

Core should validate structure, not behavior.

---

## Frontend Responsibilities (Reference)

Frontend (React + TypeScript) is responsible for:

* Drag & drop behavior
* Visual preview
* Modal rendering
* Form state binding
* JSON generation

Core MUST only expose schema and validate data.

---

## Architecture Constraints

### MUST NOT

* Implement UI logic in PHP
* Render preview HTML
* Introduce frontend dependencies
* Act as a page builder

### MUST

* Remain data-driven
* Keep schema explicit
* Preserve backward compatibility
* Follow OptStack conventions

---

## Design Mindset

Think like a **framework engineer**.

Your job is to:

* define clear data contracts
* enable expressive layouts
* avoid UI assumptions
* keep OptStack extensible

Do not optimize for a single UI implementation.

---

## Success Criteria

The implementation is correct if:

* Visual layouts can be composed and stored as JSON
* Existing OptStack features remain unaffected
* Frontend can build rich UI without backend changes
* No HTML or presentation logic leaks into storage

---

## End of Task
