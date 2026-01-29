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

* `structure` (array of allowed Structure block types)
* `elements` (array of allowed Element block types)

### Optional Options

* `design` (array of allowed design controls)
* `default` (initial layout structure)

### Example Definition

```php
$group->field('layout', [
    'type' => 'visual_builder',
    'label' => 'Header Layout',
    'attributes' => [
        // Structure blocks (containers)
        'structure' => ['row', 'column', 'section'],
        
        // Element blocks (content)
        'elements' => ['logo', 'menu', 'button', 'search', 'spacer'],
        
        // Design controls for Structure blocks
        'design' => ['gap', 'padding', 'alignment', 'justify', 'background'],
    ],
    'default' => [
        'structure' => [
            [
                'id' => 'main_row',
                'type' => 'row',
                'blockCategory' => 'structure',
                'props' => ['gap' => 16],
                'elements' => [
                    ['id' => 'logo_1', 'type' => 'logo', 'blockCategory' => 'element', 'props' => []],
                    ['id' => 'menu_1', 'type' => 'menu', 'blockCategory' => 'element', 'props' => []],
                ],
            ],
        ],
        'settings' => [],
    ],
]);
```

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

## Block Types Architecture

Visual Builder blocks are organized into **two distinct categories**:

### 1. Structure Blocks (Containers)

**Purpose**: Container blocks that hold and organize Element blocks.

**Characteristics**:
* Provide layout structure (rows, columns, sections, containers)
* Can contain multiple Element blocks as children
* Define spacing, direction, and alignment for their children
* Cannot be nested inside Element blocks
* Can be nested inside other Structure blocks (e.g., columns inside sections)

**Common Structure Types**:
* `row` - Horizontal container (flex-direction: row)
* `column` - Vertical container (flex-direction: column)
* `columns` - Multi-column grid layout
* `section` - Full-width section container
* `container` - Generic wrapper with padding/margin controls

### 2. Element Blocks (Content)

**Purpose**: Content blocks that display actual content (text, images, buttons, etc.).

**Characteristics**:
* Provide visual content and functionality
* Cannot contain other blocks (leaf nodes)
* Must be placed inside Structure blocks
* Have specific props based on their type (text, URL, color, etc.)

**Common Element Types**:
* `logo` - Site logo with size/link
* `menu` - Navigation menu
* `button` - Call-to-action button
* `text` - Rich text content
* `search` - Search form
* `social` - Social media links
* `image` - Image with caption
* `icon` - Icon with optional link
* `spacer` - Flexible spacing element
* `divider` - Visual separator line

---

## Builder UI Model (Conceptual)

Frontend implementation (React) will expose three main areas:

### 1. Blocks Panel

* Lists allowed block types **organized by category** (Structure / Elements)
* Drag source only
* No state stored here
* Visual distinction between Structure and Element blocks

### 2. Canvas / Composition Area

* Visual preview of the layout
* Drag-and-drop interface
* Shows Structure blocks as containers
* Shows Element blocks as content within containers
* **Validation**: Elements must be dropped inside Structure blocks

### 3. Design / Inspector Panel

* Displays settings for selected block
* Maps controls to block `props`
* Uses schema-driven inputs
* Shows different controls for Structure vs Elements

OptStack Core MUST NOT implement this UI.

---

## Data Model (MANDATORY)

Visual Builder MUST store **pure structured data** with a clear hierarchy.

### Data Structure

```json
{
  "structure": [
    {
      "id": "struct_1",
      "type": "row",              // Structure block type
      "blockCategory": "structure",
      "props": {
        "gap": 16,
        "align": "center",
        "justify": "space-between",
        "padding": { "top": 16, "bottom": 16, "left": 32, "right": 32 }
      },
      "elements": [               // Elements contained in this structure
        {
          "id": "elem_1",
          "type": "logo",
          "blockCategory": "element",
          "props": {
            "size": "medium",
            "link": "/"
          }
        },
        {
          "id": "elem_2",
          "type": "spacer",
          "blockCategory": "element",
          "props": {
            "grow": true
          }
        },
        {
          "id": "elem_3",
          "type": "menu",
          "blockCategory": "element",
          "props": {
            "menu_id": "primary",
            "style": "horizontal"
          }
        },
        {
          "id": "elem_4",
          "type": "button",
          "blockCategory": "element",
          "props": {
            "text": "Get Started",
            "url": "/signup",
            "style": "primary"
          }
        }
      ]
    }
  ],
  "settings": {
    "background": "#ffffff",
    "minHeight": null
  }
}
```

### Nested Structure Example (Multi-column)

```json
{
  "structure": [
    {
      "id": "footer_row",
      "type": "row",
      "blockCategory": "structure",
      "props": {
        "gap": 32,
        "padding": { "top": 64, "bottom": 32, "left": 32, "right": 32 }
      },
      "elements": [
        {
          "id": "footer_columns",
          "type": "columns",
          "blockCategory": "structure",    // Structure can contain Structure
          "props": {
            "columns": 3,
            "gap": 24
          },
          "elements": [
            // Column 1
            [
              {
                "id": "col1_logo",
                "type": "logo",
                "blockCategory": "element",
                "props": { "size": "small" }
              },
              {
                "id": "col1_text",
                "type": "text",
                "blockCategory": "element",
                "props": { "content": "About us..." }
              }
            ],
            // Column 2
            [
              {
                "id": "col2_menu",
                "type": "menu",
                "blockCategory": "element",
                "props": { "menu_id": "footer" }
              }
            ],
            // Column 3
            [
              {
                "id": "col3_social",
                "type": "social",
                "blockCategory": "element",
                "props": { "platforms": ["twitter", "facebook"] }
              }
            ]
          ]
        }
      ]
    }
  ],
  "settings": {
    "background": "#111827"
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

A future-compatible registry MAY exist with **separate registration for Structure and Element blocks**:

### Structure Block Registration

```php
OptStack::registerStructureBlock('row', [
    'label' => 'Row',
    'category' => 'structure',
    'icon' => 'dashicons-align-center',
    'canContain' => ['structure', 'element'],  // Can contain both
    'props' => [
        'gap' => [
            'type' => 'number',
            'label' => 'Gap',
            'default' => 16,
            'min' => 0,
            'max' => 100,
        ],
        'align' => [
            'type' => 'select',
            'label' => 'Vertical Alignment',
            'options' => ['start', 'center', 'end', 'stretch'],
        ],
        'justify' => [
            'type' => 'select',
            'label' => 'Horizontal Alignment',
            'options' => ['start', 'center', 'end', 'space-between', 'space-around'],
        ],
    ],
]);

OptStack::registerStructureBlock('columns', [
    'label' => 'Columns',
    'category' => 'structure',
    'icon' => 'dashicons-columns',
    'canContain' => ['element'],  // Can only contain elements
    'props' => [
        'columns' => [
            'type' => 'number',
            'label' => 'Number of Columns',
            'default' => 2,
            'min' => 1,
            'max' => 6,
        ],
        'gap' => [
            'type' => 'number',
            'label' => 'Gap Between Columns',
            'default' => 16,
        ],
    ],
]);
```

### Element Block Registration

```php
OptStack::registerElementBlock('logo', [
    'label' => 'Logo',
    'category' => 'element',
    'icon' => 'dashicons-format-image',
    'props' => [
        'size' => [
            'type' => 'select',
            'label' => 'Size',
            'options' => ['small', 'medium', 'large'],
            'default' => 'medium',
        ],
        'link' => [
            'type' => 'url',
            'label' => 'Link URL',
            'default' => '/',
        ],
    ],
]);

OptStack::registerElementBlock('button', [
    'label' => 'Button',
    'category' => 'element',
    'icon' => 'dashicons-button',
    'props' => [
        'text' => [
            'type' => 'text',
            'label' => 'Button Text',
            'default' => 'Click me',
        ],
        'url' => [
            'type' => 'url',
            'label' => 'Button URL',
        ],
        'style' => [
            'type' => 'select',
            'label' => 'Style',
            'options' => ['primary', 'secondary', 'outline'],
            'default' => 'primary',
        ],
    ],
]);
```

### Validation Rules

* **Structure blocks** can contain other blocks (elements or structure)
* **Element blocks** cannot contain other blocks (leaf nodes)
* Core should validate structure hierarchy, not behavior
* Frontend validates block nesting based on `canContain` rules

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
