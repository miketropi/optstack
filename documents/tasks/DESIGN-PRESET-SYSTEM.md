# Task: OptStack Design Preset System (DPS)

Build a flexible, clean, extensible **Design Preset Field System** for WordPress — a new `design_preset` field type that allows users to configure design tokens organized by semantic groups, edit them through a visual UI, and output them as CSS variables, `theme.json` tokens, or any pluggable format.

---

## Table of Contents

- [Goals](#goals)
- [Current state](#current-state)
- [Core principles](#core-principles)
- [What is a Design Preset?](#what-is-a-design-preset)
- [Field type definition](#field-type-definition)
- [Semantic Group system](#semantic-group-system)
- [Token specification](#token-specification)
- [Default presets](#default-presets)
- [Custom presets and overrides](#custom-presets-and-overrides)
- [Output layer (adapters)](#output-layer-adapters)
- [Storage model](#storage-model)
- [UI/UX specification](#uiux-specification)
- [Extensibility API](#extensibility-api)
- [Implementation tasks](#implementation-tasks)
- [Non-goals (v1)](#non-goals-v1)
- [Acceptance criteria](#acceptance-criteria)
- [Roadmap](#roadmap)
- [Related docs](#related-docs)

---

## Goals

1. **Design tokens as data** — Provide a structured, schema-driven way to define and manage design tokens in WordPress without writing CSS.
2. **Semantic grouping** — Organize tokens by UI purpose (heading, button, card) instead of by CSS property or HTML element.
3. **Editor-agnostic** — Presets are pure data. They work with the block editor, classic themes, page builders, or headless setups.
4. **Pluggable output** — Default output is CSS custom properties. Additional adapters (theme.json, inline styles, JS token map) are pluggable.
5. **Reuse OptStack infrastructure** — The preset system is a new field type (`design_preset`) that plugs into existing stacks, stores, REST API, and React field components.

---

## Current state

- **No design preset system** exists in OptStack. The closest features are:
  - `ColorField` supports `attributes.presets` for predefined color swatches.
  - `TypographyField` manages a single typography token set (font family, size, weight, etc.).
  - `radio-image` field is used in examples as a basic "layout preset" selector.
- **No semantic group concept** — Fields are defined individually; there is no mechanism to bundle related tokens into a named group.
- **No built-in output layer** — Themes/plugins manually read field values and generate CSS (see typography CSS example in `documents/fields/typography.md`).

See [USAGE-FIELD.md](../USAGE-FIELD.md) for current field definitions and [STORAGE-SYSTEM.md](../STORAGE-SYSTEM.md) for storage.

---

## Core principles

These principles are **mandatory**. Any implementation that violates them must be rejected.

| # | Principle | Rationale |
|---|-----------|-----------|
| 1 | **Preset = Data, not CSS** | A preset is a collection of typed token values. It never contains raw CSS, selectors, or media queries. |
| 2 | **Style semantic groups, not HTML tags** | Tokens target "heading", "button", "card" — not `<h1>`, `<button>`, `<div class="card">`. Mapping tokens to selectors is the output layer's job. |
| 3 | **Editor-agnostic** | Presets must not depend on Gutenberg, Elementor, or any specific editor. They are consumed by adapters. |
| 4 | **Output ≠ Storage** | How tokens are stored (OptStack store) is independent of how they are rendered (CSS vars, theme.json, etc.). |
| 5 | **Few groups, broad coverage** | Keep the number of semantic groups small but ensure each group covers multiple related UI elements. |
| 6 | **Schema is truth** | The group and token schema is the single source of truth. Frontend renders from schema; adapters read from schema. |

---

## What is a Design Preset?

A **Design Preset** is a named collection of design tokens organized by semantic groups.

```
Preset "modern"
├── heading     → { fontFamily: "Inter", fontWeight: 700, ... }
├── body_text   → { fontFamily: "Inter", fontSize: "16px", ... }
├── button      → [{ id: "primary", background: "#2563EB", ... }, ...]
├── card        → { background: "#FFFFFF", borderRadius: "8px", ... }
└── ...
```

A preset:

- **Does not** render HTML.
- **Does not** contain CSS selectors.
- **Does not** contain media queries.
- **Does not** contain layout rules (grid, flex).

### Use cases

| Use case | Description |
|----------|-------------|
| Global theme style | One active preset controls the entire site's design tokens |
| Style variants | Multiple presets (light, dark, playful) switchable by the user |
| Plugin UI consistency | A plugin registers its own groups and consumes tokens for consistent UI |
| Site templates | Starter content ships with a preset that matches its design |

---

## Field type definition

### Field type

`design_preset`

### Field config schema

```php
$stack->field('global_design', [
    'type'       => 'design_preset',
    'label'      => 'Design Presets',
    'attributes' => [
        'mode'           => 'single',       // "single" | "repeater"
        'default_preset' => 'modern',       // ID of the active preset on first load
        'allow_custom'   => true,           // allow users to create custom presets
        'allowed_groups' => [               // restrict which groups are editable
            'heading',
            'body_text',
            'button',
            'form_field',
            'card',
        ],
    ],
]);
```

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `mode` | `"single"` \| `"repeater"` | `"single"` | Single active preset or a list of named presets |
| `default_preset` | `string` | `"modern"` | ID of the built-in preset used as default |
| `allow_custom` | `bool` | `true` | Whether users can create/clone/edit custom presets |
| `allowed_groups` | `string[]` | all registered | Whitelist of semantic group IDs the field exposes |

### Stored value shape

When `mode = "single"`:

```json
{
  "active_preset": "modern",
  "overrides": {
    "button.primary.background": "#1D4ED8",
    "heading.fontWeight": 800
  }
}
```

When `mode = "repeater"`:

```json
{
  "active_preset": "my-dark",
  "presets": [
    {
      "id": "my-dark",
      "label": "My Dark Theme",
      "base": "dark",
      "tokens": {
        "heading": { "fontFamily": "Playfair Display", "fontWeight": 700 },
        "button": [
          { "id": "primary", "background": "#7C3AED" }
        ]
      }
    }
  ]
}
```

---

## Semantic Group system

### Why groups?

- **Fewer options** — One "heading" group covers h1–h6 instead of six separate controls.
- **Simpler UX** — Users think in terms of "buttons" and "cards", not CSS properties.
- **Reusable tokens** — Adapters can map one group's tokens to multiple selectors.
- **AI-friendly** — Agents reason about semantic intent, not CSS specifics.

### Group definition schema

Each group is described by a schema object used by both the PHP registry and the React editor:

```json
{
  "id": "button",
  "label": "Button",
  "applies_to": ["button", "cta", "icon_button"],
  "supports": ["typography", "spacing", "border", "color", "state"],
  "variant": true,
  "tokens": {
    "fontFamily":  { "type": "string",  "control": "font-family" },
    "fontSize":    { "type": "string",  "control": "size",   "units": ["px", "rem"] },
    "fontWeight":  { "type": "number",  "control": "select", "options": [300, 400, 500, 600, 700] },
    "padding":     { "type": "string",  "control": "spacing" },
    "borderRadius":{ "type": "string",  "control": "size",   "units": ["px", "rem", "%"] },
    "borderWidth": { "type": "string",  "control": "size",   "units": ["px"] },
    "borderColor": { "type": "string",  "control": "color" },
    "background":  { "type": "string",  "control": "color" },
    "color":       { "type": "string",  "control": "color" },
    "hoverBackground": { "type": "string", "control": "color" },
    "hoverColor":      { "type": "string", "control": "color" }
  }
}
```

| Property | Type | Description |
|----------|------|-------------|
| `id` | `string` | Unique snake_case group identifier |
| `label` | `string` | Human-readable name for the UI |
| `applies_to` | `string[]` | Semantic elements this group covers (documentation/intent only) |
| `supports` | `string[]` | Token categories: `typography`, `spacing`, `border`, `color`, `state` |
| `variant` | `bool` | Whether the group supports named variants (e.g. primary/secondary button) |
| `tokens` | `object` | Token definitions with type, control hint, and validation constraints |

### Supported Semantic Groups (v1)

#### Typography Groups

**`heading`** — Applies to: h1, h2, h3, h4, h5, h6

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `fontFamily` | string | font-family | Font stack |
| `fontWeight` | number | select | Weight (100–900) |
| `lineHeight` | number | range | Line height multiplier |
| `letterSpacing` | string | size | Letter spacing with unit |
| `color` | string | color | Text color |
| `sizeScale` | object | scale | Optional per-level sizes (`h1`–`h6`) |

Example token value:

```json
{
  "fontFamily": "Inter",
  "fontWeight": 700,
  "lineHeight": 1.2,
  "letterSpacing": "-0.02em",
  "color": "#111827",
  "sizeScale": {
    "h1": "3rem",
    "h2": "2.5rem",
    "h3": "2rem",
    "h4": "1.5rem",
    "h5": "1.25rem",
    "h6": "1rem"
  }
}
```

**`body_text`** — Applies to: paragraph, lead, small, muted

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `fontFamily` | string | font-family | Font stack |
| `fontSize` | string | size | Base font size |
| `fontWeight` | number | select | Weight |
| `lineHeight` | number | range | Line height multiplier |
| `color` | string | color | Text color |

**`inline_text`** — Applies to: link, inline code, mark, kbd

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `linkColor` | string | color | Default link color |
| `linkHoverColor` | string | color | Link hover color |
| `codeBackground` | string | color | Inline code background |
| `codeFontFamily` | string | font-family | Monospace font for code |
| `markBackground` | string | color | Highlight background |

#### Action Groups

**`button`** — Applies to: button, cta, icon_button — **supports variants**

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `fontFamily` | string | font-family | Button font |
| `fontSize` | string | size | Button font size |
| `fontWeight` | number | select | Button font weight |
| `padding` | string | spacing | Button padding |
| `borderRadius` | string | size | Corner radius |
| `borderWidth` | string | size | Border width |
| `borderColor` | string | color | Border color |
| `background` | string | color | Background color |
| `color` | string | color | Text color |
| `hoverBackground` | string | color | Hover background |
| `hoverColor` | string | color | Hover text color |

Variant example:

```json
[
  {
    "id": "primary",
    "label": "Primary",
    "background": "#2563EB",
    "color": "#FFFFFF",
    "borderRadius": "6px",
    "hoverBackground": "#1D4ED8"
  },
  {
    "id": "secondary",
    "label": "Secondary",
    "background": "#E5E7EB",
    "color": "#374151",
    "borderRadius": "6px",
    "hoverBackground": "#D1D5DB"
  }
]
```

**`link`** — Applies to: inline link, nav link

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `color` | string | color | Default link color |
| `hoverColor` | string | color | Hover color |
| `decoration` | string | select | Text decoration |
| `hoverDecoration` | string | select | Hover text decoration |

#### Form Groups

**`form_field`** — Applies to: input, textarea, select

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `background` | string | color | Field background |
| `borderColor` | string | color | Border color |
| `borderWidth` | string | size | Border width |
| `borderRadius` | string | size | Corner radius |
| `padding` | string | spacing | Inner padding |
| `fontSize` | string | size | Input font size |
| `color` | string | color | Input text color |
| `focusBorderColor` | string | color | Focus state border |
| `errorBorderColor` | string | color | Error state border |

**`form_choice`** — Applies to: checkbox, radio

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `size` | string | size | Control size |
| `borderColor` | string | color | Border color |
| `checkedBackground` | string | color | Checked state background |
| `borderRadius` | string | size | Corner radius |

**`form_meta`** — Applies to: label, help_text, error_text, success_text

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `labelFontSize` | string | size | Label font size |
| `labelFontWeight` | number | select | Label weight |
| `labelColor` | string | color | Label text color |
| `helpColor` | string | color | Help text color |
| `errorColor` | string | color | Error text color |
| `successColor` | string | color | Success text color |

#### Container Groups

**`container`** — Applies to: section, page container

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `maxWidth` | string | size | Max container width |
| `padding` | string | spacing | Horizontal padding |
| `background` | string | color | Background color |

**`card`** — Applies to: card, panel — **supports variants**

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `background` | string | color | Card background |
| `borderRadius` | string | size | Corner radius |
| `borderWidth` | string | size | Border width |
| `borderColor` | string | color | Border color |
| `padding` | string | spacing | Inner padding |
| `shadow` | string | shadow | Box shadow |

#### Navigation Group

**`navigation`** — Applies to: menu, breadcrumb, pagination, tabs

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `fontFamily` | string | font-family | Nav font |
| `fontSize` | string | size | Nav font size |
| `fontWeight` | number | select | Nav font weight |
| `color` | string | color | Default link color |
| `activeColor` | string | color | Active/current item color |
| `hoverColor` | string | color | Hover color |
| `padding` | string | spacing | Item padding |

#### Feedback Groups

**`alert`** — Applies to: info, success, warning, error — **supports variants**

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `background` | string | color | Alert background |
| `borderColor` | string | color | Alert border color |
| `color` | string | color | Alert text color |
| `borderRadius` | string | size | Corner radius |
| `padding` | string | spacing | Inner padding |
| `iconColor` | string | color | Icon color |

Built-in variants: `info`, `success`, `warning`, `error`.

**`loading`** — Applies to: loader, progress bar

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `color` | string | color | Spinner/bar color |
| `trackColor` | string | color | Track background |
| `size` | string | size | Default size |

#### Media Group

**`media`** — Applies to: image, video, gallery

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `borderRadius` | string | size | Media corner radius |
| `borderWidth` | string | size | Border width |
| `borderColor` | string | color | Border color |
| `shadow` | string | shadow | Box shadow |

#### Utility Group

**`utility`** — Applies to: badge, avatar, icon, divider

| Token | Type | Control | Description |
|-------|------|---------|-------------|
| `badgeBackground` | string | color | Badge background |
| `badgeColor` | string | color | Badge text color |
| `badgeBorderRadius` | string | size | Badge radius |
| `avatarSize` | string | size | Default avatar size |
| `avatarBorderRadius` | string | size | Avatar radius |
| `dividerColor` | string | color | Divider line color |
| `dividerWidth` | string | size | Divider thickness |

### Group summary

| Group | Supports | Variants | Token count |
|-------|----------|----------|-------------|
| `heading` | typography, color | no | 6 |
| `body_text` | typography, color | no | 5 |
| `inline_text` | color | no | 5 |
| `button` | typography, spacing, border, color, state | yes | 11 |
| `link` | color, state | no | 4 |
| `form_field` | color, border, spacing, typography, state | no | 9 |
| `form_choice` | color, border | no | 4 |
| `form_meta` | typography, color | no | 6 |
| `container` | spacing, color | no | 3 |
| `card` | color, border, spacing | yes | 6 |
| `navigation` | typography, color, spacing, state | no | 7 |
| `alert` | color, border, spacing | yes | 6 |
| `loading` | color | no | 3 |
| `media` | border, color | no | 4 |
| `utility` | color, border | no | 7 |

---

## Token specification

### Allowed token value types

| Type | Format | Examples |
|------|--------|---------|
| `string` | Typed value with unit | `"16px"`, `"1.5rem"`, `"#2563EB"`, `"Inter, sans-serif"` |
| `number` | Unitless numeric | `700`, `1.2`, `0` |
| `object` | Keyed map (for scales) | `{ "h1": "3rem", "h2": "2.5rem" }` |

### Strict rules

Tokens **must** contain only semantic, typed values.

| Allowed | Not allowed |
|---------|------------|
| Semantic token names (`fontSize`, `background`) | Raw CSS (`display: flex`) |
| Typed values (`16px`, `1.5rem`, `#hex`) | CSS selectors (`.btn`, `h1`) |
| Named variants (`primary`, `secondary`) | Media queries (`@media ...`) |
| State tokens (`hoverBackground`, `focusBorderColor`) | Layout rules (`grid-template-columns`) |

### TypeScript types

```typescript
interface DesignToken {
  [key: string]: string | number | Record<string, string>;
}

interface DesignGroupVariant extends DesignToken {
  id: string;
  label?: string;
}

type DesignGroupValue = DesignToken | DesignGroupVariant[];

interface DesignPreset {
  id: string;
  label: string;
  tokens: Record<string, DesignGroupValue>;
}

interface DesignPresetFieldValue {
  active_preset: string;
  overrides?: Record<string, string | number>;
  presets?: DesignPreset[];
}

interface DesignGroupSchema {
  id: string;
  label: string;
  applies_to: string[];
  supports: string[];
  variant: boolean;
  tokens: Record<string, {
    type: 'string' | 'number' | 'object';
    control: string;
    units?: string[];
    options?: (string | number)[];
  }>;
}
```

---

## Default presets

The following presets ship with OptStack and are available out of the box.

| Preset ID | Description |
|-----------|-------------|
| `modern` | Clean sans-serif, blue primary, generous spacing, soft shadows |
| `classic` | Serif headings, neutral palette, traditional proportions |
| `minimal` | System fonts, monochrome, tight spacing, no shadows |
| `playful` | Rounded everything, vibrant colors, large type |
| `elegant` | Thin weights, muted palette, wide letter-spacing |
| `dark` | Dark backgrounds, light text, accent colors |

Built-in presets are:

- **Immutable** — Users cannot modify built-in presets directly.
- **Cloneable** — Users can clone a built-in preset as a base for custom presets.

### Example: `modern` preset (abbreviated)

```json
{
  "id": "modern",
  "label": "Modern",
  "tokens": {
    "heading": {
      "fontFamily": "Inter, sans-serif",
      "fontWeight": 700,
      "lineHeight": 1.2,
      "letterSpacing": "-0.02em",
      "color": "#111827",
      "sizeScale": {
        "h1": "3rem", "h2": "2.5rem", "h3": "2rem",
        "h4": "1.5rem", "h5": "1.25rem", "h6": "1rem"
      }
    },
    "body_text": {
      "fontFamily": "Inter, sans-serif",
      "fontSize": "16px",
      "fontWeight": 400,
      "lineHeight": 1.6,
      "color": "#374151"
    },
    "button": [
      {
        "id": "primary",
        "label": "Primary",
        "fontFamily": "inherit",
        "fontSize": "14px",
        "fontWeight": 600,
        "padding": "10px 20px",
        "borderRadius": "6px",
        "borderWidth": "0",
        "background": "#2563EB",
        "color": "#FFFFFF",
        "hoverBackground": "#1D4ED8",
        "hoverColor": "#FFFFFF"
      },
      {
        "id": "secondary",
        "label": "Secondary",
        "fontFamily": "inherit",
        "fontSize": "14px",
        "fontWeight": 600,
        "padding": "10px 20px",
        "borderRadius": "6px",
        "borderWidth": "1px",
        "borderColor": "#D1D5DB",
        "background": "#FFFFFF",
        "color": "#374151",
        "hoverBackground": "#F3F4F6",
        "hoverColor": "#111827"
      }
    ],
    "card": {
      "background": "#FFFFFF",
      "borderRadius": "8px",
      "borderWidth": "1px",
      "borderColor": "#E5E7EB",
      "padding": "24px",
      "shadow": "0 1px 3px rgba(0,0,0,0.1)"
    }
  }
}
```

---

## Custom presets and overrides

### Override model

Users can override individual tokens from the active preset without creating a full custom preset.

```json
{
  "active_preset": "modern",
  "overrides": {
    "button.primary.borderRadius": "8px",
    "heading.fontFamily": "Poppins, sans-serif",
    "card.shadow": "none"
  }
}
```

Override keys use dot-notation: `{group}.{token}` or `{group}.{variant_id}.{token}`.

### Custom preset model

Custom presets reference a base preset and define their own token values. Tokens not explicitly set inherit from the base.

```json
{
  "id": "my-brand",
  "label": "My Brand",
  "base": "modern",
  "tokens": {
    "heading": {
      "fontFamily": "Playfair Display, serif",
      "color": "#1a1a2e"
    },
    "button": [
      {
        "id": "primary",
        "background": "#e94560",
        "hoverBackground": "#c81e45"
      }
    ]
  }
}
```

### Merge order

Token resolution follows this cascade:

```
built-in default → base preset → custom preset tokens → overrides
```

When resolving a token value:

1. Start with the built-in preset's full token set.
2. If a custom preset specifies `base`, take the base preset's tokens as foundation.
3. Deep-merge the custom preset's `tokens` on top.
4. Apply any per-token `overrides` last.

---

## Output layer (adapters)

The output layer converts resolved tokens into consumable formats. Adapters are pluggable — the system ships with a default CSS variables adapter and others can be registered.

### Default adapter: CSS custom properties

```css
:root {
  /* heading */
  --os-heading-font-family: Inter, sans-serif;
  --os-heading-font-weight: 700;
  --os-heading-line-height: 1.2;
  --os-heading-letter-spacing: -0.02em;
  --os-heading-color: #111827;
  --os-heading-size-h1: 3rem;
  --os-heading-size-h2: 2.5rem;
  --os-heading-size-h3: 2rem;

  /* body_text */
  --os-body-text-font-family: Inter, sans-serif;
  --os-body-text-font-size: 16px;
  --os-body-text-color: #374151;

  /* button.primary */
  --os-button-primary-background: #2563EB;
  --os-button-primary-color: #FFFFFF;
  --os-button-primary-border-radius: 6px;
  --os-button-primary-hover-background: #1D4ED8;

  /* button.secondary */
  --os-button-secondary-background: #FFFFFF;
  --os-button-secondary-color: #374151;

  /* card */
  --os-card-background: #FFFFFF;
  --os-card-border-radius: 8px;
  --os-card-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
```

Variable naming convention: `--os-{group}-{token}` or `--os-{group}-{variant}-{token}`, with camelCase token names converted to kebab-case.

### Optional adapters

| Adapter | Output | Use case |
|---------|--------|----------|
| `theme_json` | WordPress `theme.json` settings/styles | Block themes |
| `inline_style` | `<style>` tag with element selectors | Classic themes, legacy support |
| `js_token_map` | JavaScript object of resolved tokens | Headless, page builders, JS-driven UI |
| `scss_variables` | SCSS `$variable` declarations | Build-time consumption |

Adapters implement a common interface:

```php
interface DesignPresetAdapterInterface
{
    public function render(array $resolvedTokens): string;

    public function getType(): string;
}
```

---

## Storage model

Design preset data is stored using OptStack's existing store system (single serialized array per stack).

### Option keys

| Key | Type | Description |
|-----|------|-------------|
| Field value within stack | `array` | The `design_preset` field value containing `active_preset`, `overrides`, and optionally `presets` |

Because `design_preset` is a standard OptStack field type, its value lives inside the parent stack's data array — the same as any other field. No separate option keys are needed.

Example: if the stack ID is `theme_settings` and the field key is `global_design`:

```php
$data = get_option('theme_settings');
// $data['global_design'] = {
//   "active_preset": "modern",
//   "overrides": { "heading.fontFamily": "Poppins" }
// }
```

### Built-in presets storage

Built-in presets are defined in PHP (code, not database). They are registered via `optstack_register_design_preset()` and stored in memory at runtime. They are never written to the database.

### Custom presets storage

Custom presets (user-created) are stored as part of the field value in the stack's store. In repeater mode, the `presets` array holds all custom presets.

---

## UI/UX specification

### User flow

1. User opens the stack containing the `design_preset` field.
2. Field displays the current preset name and a thumbnail/preview strip.
3. User clicks the field to open the **Preset Editor popup**.
4. In the popup:
   - Left panel: list of semantic groups (filtered by `allowed_groups`).
   - Center panel: token controls for the selected group.
   - Right panel: live preview (isolated iframe preferred).
5. User selects a different preset from a dropdown or clones one.
6. User adjusts tokens — changes are tracked as overrides (single mode) or saved to the custom preset (repeater mode).
7. User clicks "Apply" — tokens are saved to the stack via REST API.
8. Output adapters re-render (CSS variables injected into `<head>`).

### Popup structure

```
┌──────────────────────────────────────────────────────────┐
│  Preset: [Modern ▾]  [Clone]  [Reset]          [Apply]  │
├────────────┬─────────────────────────┬───────────────────┤
│  Groups    │  Token Controls         │  Live Preview     │
│            │                         │                   │
│  ○ Heading │  Font Family: [Inter▾]  │  ┌─────────────┐  │
│  ○ Body    │  Font Weight: [700  ▾]  │  │             │  │
│  ● Button  │  Line Height: [===●]    │  │  (iframe)   │  │
│  ○ Card    │  Color:       [■ #111]  │  │             │  │
│  ○ Form    │                         │  │             │  │
│  ○ Nav     │  — Variant: Primary —   │  └─────────────┘  │
│            │  Background:  [■ #256]  │                   │
│            │  Radius:      [6px   ]  │                   │
│            │  Hover BG:    [■ #1D4]  │                   │
├────────────┴─────────────────────────┴───────────────────┤
│  Token changes: 3 overrides from "Modern" base           │
└──────────────────────────────────────────────────────────┘
```

### UI requirements

- Controls are lazy-loaded per group (only render active group's controls).
- Preview is isolated in an iframe to avoid style contamination.
- The popup uses OptStack's existing `DeferredGroupModal` pattern.
- Token controls reuse existing OptStack field components where possible (`ColorField`, `SelectField`, `RangeField`, `TypographyField` sub-controls).
- Preset selection shows a visual thumbnail grid (small preview of each preset's primary colors and typography).

---

## Extensibility API

### Register a semantic group

```php
add_action('optstack_init', function () {
    optstack_register_design_group('pricing_table', [
        'label'      => 'Pricing Table',
        'applies_to' => ['pricing_card', 'pricing_header', 'pricing_feature_list'],
        'supports'   => ['typography', 'spacing', 'border', 'color'],
        'variant'    => true,
        'tokens'     => [
            'headerBackground' => ['type' => 'string', 'control' => 'color'],
            'headerColor'      => ['type' => 'string', 'control' => 'color'],
            'priceSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
            'featureColor'     => ['type' => 'string', 'control' => 'color'],
            'borderRadius'     => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
        ],
    ]);
});
```

### Register a preset

```php
add_action('optstack_init', function () {
    optstack_register_design_preset([
        'id'     => 'brand_corporate',
        'label'  => 'Corporate',
        'tokens' => [
            'heading'   => ['fontFamily' => 'Merriweather, serif', 'fontWeight' => 700],
            'body_text' => ['fontFamily' => 'Open Sans, sans-serif', 'fontSize' => '16px'],
            'button'    => [
                ['id' => 'primary', 'background' => '#0F4C75', 'borderRadius' => '4px'],
            ],
        ],
    ]);
});
```

### Register an output adapter

```php
add_action('optstack_init', function () {
    optstack_register_design_adapter('theme_json', new ThemeJsonAdapter());
});
```

### Filter hooks

| Hook | Description |
|------|-------------|
| `optstack_design_groups` | Filter the registered groups array |
| `optstack_design_presets` | Filter the registered presets array |
| `optstack_design_resolved_tokens` | Filter final resolved tokens before output |
| `optstack_design_css_variables` | Filter generated CSS variable string |
| `optstack_design_adapters` | Filter registered adapters |

---

## Implementation tasks

### Phase 1: Core system

- [ ] **Group registry** — Create `DesignGroupRegistry` class (PHP) that stores group definitions and exposes them via `optstack_register_design_group()`. Include all v1 groups.
- [ ] **Preset registry** — Create `DesignPresetRegistry` class (PHP) for built-in and code-registered presets. Include all six default presets (`modern`, `classic`, `minimal`, `playful`, `elegant`, `dark`).
- [ ] **Token resolver** — Create `TokenResolver` class that merges base → preset → overrides and returns a flat resolved token map.
- [ ] **Field type backend** — Register `design_preset` as a new field type in OptStack's schema system. Define sanitization and validation rules for the field value shape.
- [ ] **REST schema** — Expose group definitions and preset data through the existing OptStack REST endpoints (extend stack schema export to include design groups and available presets).
- [ ] **CSS variables adapter** — Implement the default `CssVariablesAdapter` that converts resolved tokens to CSS custom properties and enqueues them via `wp_head`.

### Phase 2: Editor UI

- [ ] **React field component** — Create `DesignPresetField.tsx` following existing field component patterns. Includes preset selector, "Edit" button to open the popup.
- [ ] **Preset editor popup** — Build the three-panel popup (groups list, token controls, preview) using OptStack's existing modal infrastructure.
- [ ] **Token controls** — Implement controls for each token type, reusing existing OptStack field components (`ColorField`, `SelectField`, `RangeField`, font-family picker from `TypographyField`).
- [ ] **Preset switcher** — UI for selecting, cloning, and resetting presets.
- [ ] **Override tracking** — Track which tokens have been changed from the base preset; display override count and allow per-token reset.

### Phase 3: Preview and polish

- [ ] **Iframe preview** — Isolated preview panel that renders sample HTML with the current tokens applied as CSS variables.
- [ ] **State support** — Hover and focus state previews in the editor (show state tokens, interactive preview).
- [ ] **Import/export** — JSON import/export for custom presets (copy between sites).
- [ ] **Repeater mode** — Full repeater mode support for managing multiple named presets within one field.

### Phase 4: Advanced adapters

- [ ] **theme.json adapter** — Generate WordPress `theme.json` compatible output.
- [ ] **Block style mapping** — Map design tokens to core block styles.
- [ ] **Live iframe preview** — Full-page preview with live token updates (postMessage to iframe).
- [ ] **Preset marketplace** — Remote preset loading (optional, future).

---

## Non-goals (v1)

The following are explicitly out of scope for v1:

| Non-goal | Rationale |
|----------|-----------|
| CSS editor | Presets are data, not CSS. Raw CSS editing contradicts core principle #1. |
| Responsive controls | Tokens do not contain media queries. Responsive behavior is the theme's responsibility. |
| Layout system | No grid/flex/layout tokens. Layout is not a design token concern. |
| Animation system | Animations require keyframes and timings — outside token scope. |
| Selector mapping UI | Users do not configure which CSS selectors a group maps to. That is the adapter's job. |

---

## Acceptance criteria

- [ ] `design_preset` field type is registered and renders in the OptStack admin UI.
- [ ] All 15 v1 semantic groups are registered with complete token definitions.
- [ ] Six built-in presets ship with the plugin and are selectable in the UI.
- [ ] Users can clone a built-in preset and modify tokens via the popup editor.
- [ ] Overrides are stored correctly (dot-notation keys) and resolved via the merge cascade.
- [ ] CSS custom properties are output in `<head>` following the `--os-{group}-{token}` convention.
- [ ] Third-party code can register custom groups via `optstack_register_design_group()`.
- [ ] Third-party code can register custom presets via `optstack_register_design_preset()`.
- [ ] Third-party code can register custom adapters via `optstack_register_design_adapter()`.
- [ ] All filter hooks are documented and functional.
- [ ] The field value is stored and retrieved via OptStack's standard store system (no separate option keys).
- [ ] The system works across all OptStack contexts: options, customizer, post meta, term meta.

---

## Roadmap

```
Phase 1 — Core system
├── Group registry + 15 built-in groups
├── Preset registry + 6 default presets
├── Token resolver (merge cascade)
├── Field type (backend schema + validation)
├── REST schema extension
└── CSS variables adapter

Phase 2 — Editor UI
├── DesignPresetField component
├── Preset editor popup (3-panel)
├── Token controls (reuse existing fields)
├── Preset switcher (select, clone, reset)
└── Override tracking

Phase 3 — Preview and polish
├── Isolated iframe preview
├── State support (hover, focus)
├── Import/export JSON
└── Repeater mode

Phase 4 — Advanced
├── theme.json adapter
├── Block style mapping
├── Live iframe preview
└── Preset marketplace (future)
```

---

## Related docs

- [USAGE-FIELD.md](../USAGE-FIELD.md) — Field definition patterns
- [STORAGE-SYSTEM.md](../STORAGE-SYSTEM.md) — Store adapters and data persistence
- [CUSTOMIZER.md](../CUSTOMIZER.md) — Customizer integration
- [API-REFERENCE.md](../API-REFERENCE.md) — OptStack facade API
- [fields/typography.md](../fields/typography.md) — Existing typography field (token overlap)
- [fields/color.md](../fields/color.md) — Existing color field with preset support
- [DEFERRED_GROUP_FIELD_SPECIFICATION.md](../DEFERRED_GROUP_FIELD_SPECIFICATION.md) — Deferred modal pattern (reused by preset editor)
