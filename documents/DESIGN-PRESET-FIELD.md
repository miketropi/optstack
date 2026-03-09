# Design Preset Field

> A complete field type for managing design systems through semantic groups, presets, responsive tokens, and CSS custom properties.

---

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Field Registration](#field-registration)
  - [Options Page](#options-page)
  - [Customizer](#customizer)
  - [Post Meta Box](#post-meta-box)
  - [Field Attributes](#field-attributes)
- [Stored Value Structure](#stored-value-structure)
- [Built-in Groups](#built-in-groups)
- [Built-in Presets](#built-in-presets)
- [Responsive Tokens](#responsive-tokens)
  - [How Responsive Values Work](#how-responsive-values-work)
  - [Defining Responsive Tokens](#defining-responsive-tokens)
  - [Responsive Values in Presets](#responsive-values-in-presets)
  - [Breakpoint Switcher UI](#breakpoint-switcher-ui)
  - [Sync / Unsync Per Token](#sync--unsync-per-token)
- [Custom Groups](#custom-groups)
  - [Registering a Group](#registering-a-group)
  - [Token Definition Reference](#token-definition-reference)
  - [Variant Groups](#variant-groups)
- [Custom Presets](#custom-presets)
- [Custom Group Specimens](#custom-group-specimens)
  - [Registering a Specimen (JavaScript)](#registering-a-specimen-javascript)
  - [GroupSpecimenProps Reference](#groupspecimenprops-reference)
  - [Specimen Registry API](#specimen-registry-api)
- [Google Fonts Integration](#google-fonts-integration)
  - [Font Picker UI](#font-picker-ui)
  - [Frontend Font Enqueuing](#frontend-font-enqueuing)
  - [Filtering Fonts](#filtering-fonts)
- [Reading Tokens in PHP](#reading-tokens-in-php)
- [CSS Output](#css-output)
  - [CSS Variable Naming](#css-variable-naming)
  - [Responsive CSS Variables](#responsive-css-variables)
  - [Using Variables in Stylesheets](#using-variables-in-stylesheets)
- [Custom Output Adapters](#custom-output-adapters)
- [REST API](#rest-api)

---

## Overview

The `design_preset` field type provides a visual, specimen-based editor for managing design tokens organized into semantic groups (Heading, Body Text, Button, Link, Table, etc.). Users select a preset as a starting point and can override individual tokens. Tokens can be configured per breakpoint (desktop, tablet, mobile) for responsive design. The resolved tokens are output as CSS custom properties — including responsive `@media` overrides — on the frontend. Google Fonts used in the design system are automatically enqueued.

**Key concepts:**

- **Design Group** — A semantic category of design tokens (e.g. `heading`, `button`, `table`).
- **Design Token** — A single design value within a group (e.g. `fontFamily`, `color`, `borderRadius`).
- **Preset** — A named collection of token values across groups (e.g. "Modern", "Classic", "Dark").
- **Override** — A per-field token override on top of the active preset.
- **Responsive Token** — A token whose value can vary by breakpoint (`desktop`, `tablet`, `mobile`).
- **Variant Group** — A group (e.g. Button) that stores an array of named sub-styles (e.g. `primary`, `secondary`).

---

## Quick Start

```php
add_action('optstack_init', function (): void {
    \OptStack\OptStack::make('my_design')
        ->asOptionsPage('Design System', 'design-system', [
            'parent' => 'themes.php',
        ])
        ->define(function ($stack) {
            $stack->field('design_tokens', [
                'type'  => 'design_preset',
                'label' => 'Design Tokens',
            ]);
        })
        ->build();
});
```

This creates an options page under **Appearance > Design System** with a design preset field that includes all 9 built-in groups and 4 built-in presets.

---

## Field Registration

### Options Page

```php
$stack->field('design_tokens', [
    'type'        => 'design_preset',
    'label'       => 'Design Tokens',
    'description' => 'Select and customize your design system.',
    'attributes'  => [
        'default_preset'  => 'modern',
        'allowed_presets' => ['modern', 'classic'],
        'allowed_groups'  => ['heading', 'body_text', 'button', 'link'],
        'allow_custom'    => true,
    ],
]);
```

### Customizer

```php
\OptStack\OptStack::make('theme_design')
    ->forCustomizer('Design', ['priority' => 30])
    ->define(function ($stack) {
        $stack->field('design_tokens', [
            'type'  => 'design_preset',
            'label' => 'Design Tokens',
            'attributes' => [
                'default_preset' => 'elegant',
            ],
        ]);
    })
    ->build();
```

### Post Meta Box

```php
\OptStack\OptStack::make('page_design')
    ->forPostType('page')
    ->label('Page Design')
    ->define(function ($stack) {
        $stack->field('page_tokens', [
            'type'  => 'design_preset',
            'label' => 'Page Design Overrides',
            'attributes' => [
                'allowed_groups' => ['heading', 'body_text', 'container'],
            ],
        ]);
    })
    ->build();
```

### Field Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `default_preset` | `string` | `'modern'` | ID of the preset selected by default |
| `allowed_presets` | `string[]` | `[]` (all) | Limit which built-in presets are available. Empty = all presets. Also includes custom-registered presets by ID. |
| `allowed_groups` | `string[]` | `[]` (all) | Limit which groups appear in the editor. Empty = all groups. |
| `allow_custom` | `bool` | `true` | Allow users to clone presets and create custom ones |

---

## Stored Value Structure

The field stores a JSON object:

```json
{
  "active_preset": "modern",
  "overrides": {
    "heading.fontFamily": "Playfair Display, serif",
    "heading.sizeScale.h1": "4rem",
    "button.primary.background": "#10B981",
    "body_text.fontSize.tablet": "15px"
  },
  "presets": [
    {
      "id": "custom-1709312345678",
      "label": "My Custom Preset",
      "base": "modern",
      "tokens": {
        "heading": {
          "fontFamily": "Playfair Display, serif",
          "fontWeight": 700,
          "lineHeight": { "desktop": 1.2, "tablet": 1.25, "mobile": 1.3 },
          "color": "#111827",
          "sizeScale": {
            "desktop": { "h1": "3rem", "h2": "2.5rem" },
            "tablet": { "h1": "2.5rem", "h2": "2rem" },
            "mobile": { "h1": "2rem", "h2": "1.75rem" }
          }
        }
      }
    }
  ]
}
```

| Key | Type | Description |
|-----|------|-------------|
| `active_preset` | `string` | ID of the currently selected preset |
| `overrides` | `object` | Dot-notation token path → value overrides on top of the active preset. For responsive tokens, the breakpoint is appended: `group.token.breakpoint`. |
| `presets` | `array` | User-created custom presets (cloned from built-in ones). Each clone deep-copies all tokens from the source preset. |

**Override path formats:**

| Path | Example | Description |
|------|---------|-------------|
| `group.token` | `heading.fontFamily` | Flat token override |
| `group.token.breakpoint` | `body_text.fontSize.tablet` | Responsive token override for a specific breakpoint |
| `group.variant.token` | `button.primary.background` | Variant token override |
| `group.variant.token.breakpoint` | `button.primary.fontSize.mobile` | Variant responsive token override |

---

## Built-in Groups

The following 9 semantic groups are registered by default:

| Group ID | Label | Variant | Applies To |
|----------|-------|---------|------------|
| `heading` | Heading | No | h1–h6 |
| `body_text` | Body Text | No | paragraph, lead, small, muted |
| `button` | Button | **Yes** | button, cta, icon_button |
| `link` | Link | No | inline_link, nav_link |
| `form_field` | Form Field | No | input, textarea, select |
| `form_meta` | Form Meta | No | label, help_text, error_text, success_text |
| `container` | Container | No | section, page_container, wrapper |
| `table` | Table | No | table, thead, tbody, tr, th, td |
| `list` | List | No | ul, ol, li |

Groups marked as **Variant** store an array of named variants (e.g. Button has `primary`, `secondary`).

Each group defines tokens, some of which support **responsive values** (`responsive: true`). See [Responsive Tokens](#responsive-tokens) for details.

### Token Summary by Group

<details>
<summary><strong>heading</strong> — Typography tokens for headings</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `fontFamily` | `font-family` | No |
| `fontWeight` | `select` | No |
| `lineHeight` | `range` | **Yes** |
| `letterSpacing` | `size` | **Yes** |
| `color` | `color` | No |
| `sizeScale` | `scale` (h1–h6) | **Yes** |

</details>

<details>
<summary><strong>body_text</strong> — Typography tokens for body copy</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `fontFamily` | `font-family` | No |
| `fontSize` | `size` | **Yes** |
| `fontWeight` | `select` | No |
| `lineHeight` | `range` | **Yes** |
| `color` | `color` | No |

</details>

<details>
<summary><strong>button</strong> — Variant group for button styles</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `fontFamily` | `font-family` | No |
| `fontSize` | `size` | **Yes** |
| `fontWeight` | `select` | No |
| `padding` | `spacing` | **Yes** |
| `borderRadius` | `size` | **Yes** |
| `borderWidth` | `size` | No |
| `borderColor` | `color` | No |
| `background` | `color` | No |
| `color` | `color` | No |
| `hoverBackground` | `color` | No |
| `hoverColor` | `color` | No |

</details>

<details>
<summary><strong>link</strong> — Color and decoration tokens for links</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `color` | `color` | No |
| `hoverColor` | `color` | No |
| `decoration` | `select` | No |
| `hoverDecoration` | `select` | No |

</details>

<details>
<summary><strong>form_field</strong> — Tokens for input/textarea/select elements</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `background` | `color` | No |
| `borderColor` | `color` | No |
| `borderWidth` | `size` | No |
| `borderRadius` | `size` | No |
| `padding` | `spacing` | **Yes** |
| `fontSize` | `size` | **Yes** |
| `color` | `color` | No |
| `focusBorderColor` | `color` | No |
| `errorBorderColor` | `color` | No |

</details>

<details>
<summary><strong>form_meta</strong> — Tokens for labels and validation text</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `labelFontSize` | `size` | **Yes** |
| `labelFontWeight` | `select` | No |
| `labelColor` | `color` | No |
| `helpColor` | `color` | No |
| `errorColor` | `color` | No |
| `successColor` | `color` | No |

</details>

<details>
<summary><strong>container</strong> — Layout tokens for wrappers and sections</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `maxWidth` | `size` | **Yes** |
| `padding` | `spacing` | **Yes** |
| `background` | `color` | No |
| `borderRadius` | `size` | No |
| `borderWidth` | `size` | No |
| `borderColor` | `color` | No |

</details>

<details>
<summary><strong>table</strong> — Tokens for table styling</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `headerBackground` | `color` | No |
| `headerColor` | `color` | No |
| `headerFontWeight` | `select` | No |
| `cellPadding` | `spacing` | **Yes** |
| `cellFontSize` | `size` | **Yes** |
| `cellColor` | `color` | No |
| `borderColor` | `color` | No |
| `borderWidth` | `size` | No |
| `stripedBackground` | `color` | No |
| `hoverBackground` | `color` | No |

</details>

<details>
<summary><strong>list</strong> — Tokens for unordered and ordered lists</summary>

| Token | Control | Responsive |
|-------|---------|------------|
| `fontSize` | `size` | **Yes** |
| `lineHeight` | `range` | **Yes** |
| `color` | `color` | No |
| `markerColor` | `color` | No |
| `markerSize` | `size` | No |
| `itemSpacing` | `size` | **Yes** |
| `indentSize` | `size` | **Yes** |

</details>

---

## Built-in Presets

4 built-in presets are included:

| ID | Label | Description |
|----|-------|-------------|
| `modern` | Modern | Clean, sans-serif design with Inter. Blue accent (#2563EB). |
| `classic` | Classic | Serif-based design with Georgia. Dark accents, traditional spacing. |
| `elegant` | Elegant | Playfair Display headings, Lato body. Gold accent (#c9b99a), generous whitespace. |
| `dark` | Dark | Dark theme variant of Modern. Indigo accent (#6366F1) on dark backgrounds. |

All built-in presets include full responsive values for applicable tokens (different values for desktop, tablet, and mobile breakpoints).

---

## Responsive Tokens

### How Responsive Values Work

Tokens flagged as `responsive: true` can store per-breakpoint values instead of a single scalar. The three supported breakpoints are:

| Breakpoint | Target | Default Media Query |
|------------|--------|-------------------|
| `desktop` | Large screens | Base styles (no media query) |
| `tablet` | Medium screens | `@media (max-width: 1024px)` |
| `mobile` | Small screens | `@media (max-width: 767px)` |

A responsive token value is an object with breakpoint keys:

```json
{
  "fontSize": {
    "desktop": "16px",
    "tablet": "15px",
    "mobile": "14px"
  }
}
```

Non-responsive tokens remain scalar values and are identical across all breakpoints.

### Defining Responsive Tokens

Add `'responsive' => true` to a token definition:

```php
'tokens' => [
    'fontSize' => [
        'type'       => 'string',
        'control'    => 'size',
        'units'      => ['px', 'rem'],
        'responsive' => true,
    ],
    'color' => [
        'type'    => 'string',
        'control' => 'color',
        // No 'responsive' key = not responsive
    ],
],
```

### Responsive Values in Presets

When registering preset tokens for responsive fields, provide an object with breakpoint keys:

```php
'tokens' => [
    'body_text' => [
        'fontFamily' => 'Inter, sans-serif',     // scalar (not responsive)
        'fontSize'   => [                          // responsive
            'desktop' => '16px',
            'tablet'  => '15px',
            'mobile'  => '14px',
        ],
        'lineHeight' => [                          // responsive
            'desktop' => 1.6,
            'tablet'  => 1.6,
            'mobile'  => 1.5,
        ],
        'color'      => '#374151',                // scalar (not responsive)
    ],
],
```

For `scale` tokens (like `sizeScale`), the responsive object nests the scale keys under each breakpoint:

```php
'sizeScale' => [
    'desktop' => ['h1' => '3rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
    'tablet'  => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
    'mobile'  => ['h1' => '2rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
],
```

### Breakpoint Switcher UI

The editor header contains a breakpoint switcher (Desktop / Tablet / Mobile icons). Switching breakpoints:

1. Updates the specimen preview to show token values resolved for that breakpoint.
2. Applies a simulated viewport width to the preview area.
3. Shows the current breakpoint value in the property editor for responsive tokens.

### Sync / Unsync Per Token

Each responsive token displays a toggle button beside its control:

- **Synced** (link icon) — The token uses a single value across all breakpoints. Click to **unsync** and enter per-breakpoint values.
- **Per-breakpoint** (device icon) — The token has individual values for each breakpoint. Click to **sync** back to a single value (uses the desktop value).

Non-responsive tokens show a **lock icon** when viewing tablet or mobile breakpoints, indicating they cannot be configured per-breakpoint.

---

## Custom Groups

### Registering a Group

Register custom groups in the `optstack_init` hook:

```php
add_action('optstack_init', function (): void {

    optstack_register_design_group('pricing_table', [
        'label'      => 'Pricing Table',
        'applies_to' => ['pricing_card', 'pricing_header', 'pricing_feature'],
        'supports'   => ['typography', 'spacing', 'border', 'color'],
        'variant'    => false,
        'tokens'     => [
            'headerBackground' => ['type' => 'string', 'control' => 'color'],
            'headerColor'      => ['type' => 'string', 'control' => 'color'],
            'priceSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
            'priceFontWeight'  => ['type' => 'number', 'control' => 'select', 'options' => [400, 600, 700, 800]],
            'borderRadius'     => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
            'shadow'           => ['type' => 'string', 'control' => 'shadow'],
        ],
    ]);
});
```

Then reference it in `allowed_groups`:

```php
$stack->field('design_tokens', [
    'type' => 'design_preset',
    'label' => 'Design Tokens',
    'attributes' => [
        'allowed_groups' => ['heading', 'body_text', 'button', 'pricing_table'],
    ],
]);
```

### Token Definition Reference

Each token in the `tokens` array is defined as:

```php
'tokenName' => [
    'type'       => 'string',   // 'string' | 'number' | 'object'
    'control'    => 'color',    // UI control type (see table below)
    'responsive' => true,       // Optional: enable per-breakpoint values
    // Additional properties depend on the control type
],
```

**Available controls:**

| Control | Type | Extra Properties | Description |
|---------|------|-----------------|-------------|
| `color` | `string` | — | Color picker (SketchPicker) with hex input |
| `font-family` | `string` | — | Google Fonts picker with search, live preview, and system font fallbacks |
| `size` | `string` | `units: string[]` | Size input with unit hint (e.g. `['px', 'rem']`) |
| `spacing` | `string` | — | Spacing input (e.g. `10px 20px`) |
| `shadow` | `string` | — | Box shadow text input |
| `range` | `number` | `min`, `max`, `step` | Slider with numeric output |
| `select` | `string\|number` | `options: array` | Dropdown select (react-select) |
| `scale` | `object` | `keys: string[]` | Multi-value grid (e.g. h1–h6 size scale) |

**Token definition properties:**

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `type` | `string` | Yes | Value type: `'string'`, `'number'`, or `'object'` |
| `control` | `string` | Yes | UI control type (see table above) |
| `responsive` | `bool` | No | If `true`, the token supports per-breakpoint values |
| `units` | `string[]` | No | Available unit options for `size` controls |
| `options` | `array` | No | Selectable options for `select` controls |
| `min` | `number` | No | Minimum value for `range` controls |
| `max` | `number` | No | Maximum value for `range` controls |
| `step` | `number` | No | Step increment for `range` controls |
| `keys` | `string[]` | No | Sub-keys for `scale` controls (e.g. `['h1','h2','h3']`) |

### Variant Groups

When `'variant' => true`, the group expects tokens stored as an array of named variants:

```php
optstack_register_design_group('badge', [
    'label'      => 'Badge',
    'applies_to' => ['badge', 'tag'],
    'supports'   => ['color', 'border', 'typography'],
    'variant'    => true,
    'tokens'     => [
        'background'   => ['type' => 'string', 'control' => 'color'],
        'color'        => ['type' => 'string', 'control' => 'color'],
        'borderRadius' => ['type' => 'string', 'control' => 'size', 'units' => ['px']],
        'fontSize'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem'], 'responsive' => true],
    ],
]);
```

The preset defines each variant:

```php
optstack_register_design_preset([
    'id'    => 'my_preset',
    'label' => 'My Preset',
    'tokens' => [
        'badge' => [
            ['id' => 'default', 'label' => 'Default', 'background' => '#E5E7EB', 'color' => '#374151', 'borderRadius' => '9999px', 'fontSize' => '12px'],
            ['id' => 'success', 'label' => 'Success', 'background' => '#D1FAE5', 'color' => '#065F46', 'borderRadius' => '9999px', 'fontSize' => '12px'],
            ['id' => 'danger',  'label' => 'Danger',  'background' => '#FEE2E2', 'color' => '#991B1B', 'borderRadius' => '9999px', 'fontSize' => '12px'],
        ],
    ],
]);
```

Variant tokens that support responsive values use the same object format:

```php
['id' => 'primary', 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'], ...]
```

---

## Custom Presets

Register presets that include tokens for your custom groups:

```php
add_action('optstack_init', function (): void {

    optstack_register_design_preset([
        'id'    => 'brand_preset',
        'label' => 'Brand Style',
        'tokens' => [
            'heading' => [
                'fontFamily' => 'Montserrat, sans-serif',
                'fontWeight' => 700,
                'lineHeight' => ['desktop' => 1.2, 'tablet' => 1.25, 'mobile' => 1.3],
                'color'      => '#1A1A2E',
                'sizeScale'  => [
                    'desktop' => ['h1' => '3.5rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                    'tablet'  => ['h1' => '2.75rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                    'mobile'  => ['h1' => '2.25rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                ],
            ],
            'body_text' => [
                'fontFamily' => 'Open Sans, sans-serif',
                'fontSize'   => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                'fontWeight' => 400,
                'lineHeight' => ['desktop' => 1.7, 'tablet' => 1.65, 'mobile' => 1.6],
                'color'      => '#333333',
            ],
            'pricing_table' => [
                'headerBackground' => '#1A1A2E',
                'headerColor'      => '#FFFFFF',
                'priceSize'        => ['desktop' => '2.5rem', 'tablet' => '2rem', 'mobile' => '1.75rem'],
                'priceFontWeight'  => 700,
                'borderRadius'     => '12px',
                'shadow'           => '0 4px 12px rgba(0,0,0,0.1)',
            ],
        ],
    ]);
});
```

---

## Custom Group Specimens

By default, custom groups display a generic property grid in the editor. You can register a custom **specimen component** to provide a rich visual preview — like the built-in heading specimen showing H1–H6 type scale, or button specimens showing rendered buttons.

### Registering a Specimen (JavaScript)

The specimen registry is exposed on `window.optstack`. Register specimens **after** OptStack's admin script has loaded (use the `optstack-admin` script handle as a dependency):

```php
// In your plugin or theme:
add_action('admin_enqueue_scripts', function (): void {
    wp_enqueue_script(
        'my-custom-specimens',
        plugins_url('assets/specimens.js', __FILE__),
        ['optstack-admin'], // depend on OptStack so it loads first
        '1.0.0',
        true
    );
});
```

Then in `assets/specimens.js`:

```javascript
(function () {
  if (!window.optstack || !window.optstack.registerGroupSpecimen) return;

  window.optstack.registerGroupSpecimen('pricing_table', function (props) {
    var tokens = props.tokens;
    var onTokenChange = props.onTokenChange;
    var group = props.group;
    var activeBreakpoint = props.activeBreakpoint || 'desktop';

    var headerBg = String(tokens.headerBackground || '#1A1A2E');
    var priceSize = String(tokens.priceSize || '2.5rem');
    var radius = String(tokens.borderRadius || '12px');
    var shadow = String(tokens.shadow || 'none');

    return React.createElement('div', { className: 'os-dp-specimen' },
      React.createElement('div', { className: 'os-dp-inline-specimens' },
        React.createElement('div', { className: 'os-dp-inline-card' },
          React.createElement('div', { className: 'os-dp-inline-label' }, 'Pricing Card'),
          React.createElement('div', { className: 'os-dp-inline-preview' },
            React.createElement('div', {
              style: { borderRadius: radius, boxShadow: shadow, overflow: 'hidden', border: '1px solid #e5e7eb' }
            },
              React.createElement('div', {
                style: { background: headerBg, color: '#fff', padding: '24px', textAlign: 'center' }
              },
                React.createElement('div', { style: { fontSize: priceSize, fontWeight: 700 } }, '$29/mo')
              )
            )
          )
        )
      )
    );
  });
})();
```

### GroupSpecimenProps Reference

Every specimen component receives these props:

| Prop | Type | Description |
|------|------|-------------|
| `group` | `DesignGroupSchema` | The group schema with `id`, `label`, `applies_to`, `supports`, `variant`, `tokens`. |
| `tokens` | `DesignGroupValue` | Resolved token values for the active breakpoint. For non-variant groups: `Record<string, unknown>`. For variant groups: `DesignPresetVariant[]`. |
| `rawTokens` | `DesignGroupValue` | *(Optional)* The raw (un-resolved) token values, which may contain responsive objects. Useful for determining if a token is currently in responsive mode. |
| `activeBreakpoint` | `Breakpoint` | *(Optional)* Currently active breakpoint: `'desktop'`, `'tablet'`, or `'mobile'`. |
| `onTokenChange` | `(tokenKey: string, value: unknown, variantId?: string) => void` | Call this to update a token value. For variant groups, pass the `variantId` as the third argument. For responsive tokens, append the breakpoint to the key: `onTokenChange('fontSize.tablet', '15px')`. |
| `onBatchTokenChange` | `(changes: Record<string, unknown>, variantId?: string) => void` | *(Optional)* Apply multiple token changes atomically. Values set to `undefined` are deleted. Used for sync/unsync operations. |

### Specimen Registry API

Available on `window.optstack` after the admin script loads:

| Method | Signature | Description |
|--------|-----------|-------------|
| `registerGroupSpecimen` | `(groupId: string, component: React.ComponentType) => void` | Register a React component as the specimen for a group ID. |
| `unregisterGroupSpecimen` | `(groupId: string) => boolean` | Remove a registered specimen. Returns `true` if it existed. |
| `hasGroupSpecimen` | `(groupId: string) => boolean` | Check if a specimen is registered for a group ID. |

Registration order:
1. External specimens registered via `registerGroupSpecimen()` take priority.
2. Built-in specimens (heading, body_text, button, link, form_field, form_meta, container, table, list) are used next.
3. The generic property grid is the final fallback.

---

## Google Fonts Integration

### Font Picker UI

The `font-family` token control uses a searchable dropdown powered by `react-select` that integrates with the Google Fonts API:

- **System Fonts** — A curated list of system/web-safe fonts is always available (Inherit, System UI, Arial, Helvetica, Georgia, Times New Roman, Verdana, Monospace).
- **Google Fonts** — The top 150 fonts (sorted by popularity) are loaded from the Google Fonts API on first open.
- **Search** — Typing in the search box queries the full Google Fonts catalog (1500+ fonts), not just the initial 150.
- **Live Preview** — Each font option renders a small "Aa" preview in the actual font face.
- **Dynamic Loading** — Selected Google Fonts are loaded via `<link>` tags so they render immediately in the editor.

To enable Google Fonts in the admin, set the `googleFontsApiKey` in the OptStack config:

```php
add_filter('optstack_admin_config', function ($config) {
    $config['googleFontsApiKey'] = 'YOUR_GOOGLE_FONTS_API_KEY';
    return $config;
});
```

### Frontend Font Enqueuing

Google Fonts used in the active design preset are **automatically enqueued** on the frontend. The `DesignPresetManager` hooks into `wp_head` (priority 4, before CSS variables at priority 5) and:

1. Scans all `design_preset` fields across all registered stacks.
2. Resolves tokens and extracts all `fontFamily` values (including responsive variants).
3. Filters out known system fonts (Arial, Georgia, Helvetica, etc.).
4. Outputs `<link rel="preconnect">` tags for Google Fonts CDN.
5. Outputs a single `<link rel="stylesheet">` tag loading all required font families with weights 100–900.

This means themes and plugins do **not** need to manually enqueue Google Fonts — the design system handles it automatically.

### Filtering Fonts

Use the `optstack_design_google_fonts` filter to modify which Google Fonts are enqueued:

```php
add_filter('optstack_design_google_fonts', function (array $families): array {
    // Add an additional font
    $families[] = 'Roboto';
    // Remove a font
    $families = array_filter($families, fn($f) => $f !== 'Comic Sans MS');
    return $families;
});
```

---

## Reading Tokens in PHP

Use the `DesignPresetManager` to resolve tokens from the stored value:

```php
use OptStack\WordPress\DesignPreset\DesignPresetManager;

$fieldValue = \OptStack\OptStack::getField('my_design', 'design_tokens');
$resolved   = DesignPresetManager::resolveTokens($fieldValue);

// Access heading tokens
$headingFont  = $resolved['heading']['fontFamily'] ?? 'Inter, sans-serif';
$headingColor = $resolved['heading']['color'] ?? '#111827';

// Access responsive token (returns the responsive object)
$headingLineHeight = $resolved['heading']['lineHeight'];
// => ['desktop' => 1.2, 'tablet' => 1.25, 'mobile' => 1.3]

// Access button variant tokens
$primaryBg = '';
if (isset($resolved['button']) && is_array($resolved['button'])) {
    foreach ($resolved['button'] as $variant) {
        if ($variant['id'] === 'primary') {
            $primaryBg = $variant['background'] ?? '#2563EB';
        }
    }
}
```

### Flattening Tokens

Use `TokenResolver::flatten()` to convert the nested token map into dot-notation paths:

```php
use OptStack\Core\DesignPreset\TokenResolver;

$flat = TokenResolver::flatten($resolved);
// [
//   'heading.fontFamily'        => 'Inter, sans-serif',
//   'heading.lineHeight.desktop' => 1.2,
//   'heading.lineHeight.tablet'  => 1.25,
//   'heading.lineHeight.mobile'  => 1.3,
//   'heading.sizeScale.desktop.h1' => '3rem',
//   'button.primary.background' => '#2563EB',
//   ...
// ]
```

---

## CSS Output

The Design Preset System automatically outputs CSS custom properties in `<head>` via the `wp_head` hook.

### CSS Variable Naming

Tokens are converted to CSS custom properties using the convention:

```
--os-{group}-{token}
```

CamelCase token names become kebab-case:

| Token Path | CSS Variable |
|------------|-------------|
| `heading.fontFamily` | `--os-heading-font-family` |
| `heading.sizeScale.h1` | `--os-heading-size-scale-h1` |
| `button.primary.background` | `--os-button-primary-background` |
| `body_text.fontSize` | `--os-body-text-font-size` |
| `container.maxWidth` | `--os-container-max-width` |

### Responsive CSS Variables

Responsive tokens produce a **desktop default** in `:root` plus **media-query overrides** for tablet and mobile:

```css
:root {
  --os-heading-font-family: Inter, sans-serif;
  --os-heading-font-weight: 700;
  --os-heading-line-height: 1.2;
  --os-heading-size-scale-h1: 3rem;
  --os-heading-size-scale-h2: 2.5rem;
  --os-body-text-font-size: 16px;
  --os-container-max-width: 1200px;
  --os-container-padding: 0 24px;
  --os-button-primary-background: #2563EB;
  /* ... all tokens ... */
}

@media (max-width: 1024px) {
  :root {
    --os-heading-line-height: 1.25;
    --os-heading-size-scale-h1: 2.5rem;
    --os-heading-size-scale-h2: 2rem;
    --os-body-text-font-size: 15px;
    --os-container-max-width: 768px;
    --os-container-padding: 0 20px;
    /* ... only tokens with tablet values ... */
  }
}

@media (max-width: 767px) {
  :root {
    --os-heading-line-height: 1.3;
    --os-heading-size-scale-h1: 2rem;
    --os-heading-size-scale-h2: 1.75rem;
    --os-body-text-font-size: 14px;
    --os-container-max-width: 100%;
    --os-container-padding: 0 16px;
    /* ... only tokens with mobile values ... */
  }
}
```

Non-responsive tokens (like `fontFamily` or `color`) only appear in the base `:root` block.

### Using Variables in Stylesheets

```css
h1, h2, h3, h4, h5, h6 {
  font-family: var(--os-heading-font-family, inherit);
  font-weight: var(--os-heading-font-weight, 600);
  line-height: var(--os-heading-line-height, 1.25);
  color: var(--os-heading-color, inherit);
}

h1 { font-size: var(--os-heading-size-scale-h1, 3rem); }
h2 { font-size: var(--os-heading-size-scale-h2, 2.25rem); }
h3 { font-size: var(--os-heading-size-scale-h3, 1.875rem); }

body {
  font-family: var(--os-body-text-font-family, sans-serif);
  font-size: var(--os-body-text-font-size, 16px);
  line-height: var(--os-body-text-line-height, 1.6);
  color: var(--os-body-text-color, #333);
}

.btn-primary {
  background: var(--os-button-primary-background);
  color: var(--os-button-primary-color);
  padding: var(--os-button-primary-padding);
  border-radius: var(--os-button-primary-border-radius);
}

.container {
  max-width: var(--os-container-max-width, 1200px);
  padding: var(--os-container-padding, 0 24px);
  margin: 0 auto;
}
```

Because responsive tokens are output as `@media` overrides, your stylesheet **does not need separate media queries** — the CSS variables automatically adjust at each breakpoint.

---

## Custom Output Adapters

By default, tokens are output as CSS variables via `CssVariablesAdapter`. You can customize its behavior or add entirely new adapters.

### Customizing the CSS Variables Adapter

The `CssVariablesAdapter` constructor accepts:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$prefix` | `string` | `'os'` | CSS variable prefix (`--{prefix}-...`) |
| `$selector` | `string` | `':root'` | CSS selector to scope variables |
| `$breakpoints` | `array` | `['tablet' => '1024px', 'mobile' => '767px']` | Media query breakpoint widths |

```php
use OptStack\WordPress\DesignPreset\CssVariablesAdapter;

add_action('optstack_init', function (): void {
    // Custom prefix and breakpoints
    $adapter = new CssVariablesAdapter(
        prefix: 'theme',
        selector: ':root',
        breakpoints: ['tablet' => '992px', 'mobile' => '576px'],
    );
    \OptStack\WordPress\DesignPreset\DesignPresetManager::registerAdapter('css_variables', $adapter);
});
```

### Creating a Custom Adapter

Implement `DesignPresetAdapterInterface`:

```php
use OptStack\Core\Contract\DesignPresetAdapterInterface;

class ScssTokenAdapter implements DesignPresetAdapterInterface
{
    public function render(array $resolvedTokens): string
    {
        $flat = \OptStack\Core\DesignPreset\TokenResolver::flatten($resolvedTokens);
        $lines = [];
        foreach ($flat as $path => $value) {
            $varName = '$os-' . str_replace('.', '-', $path);
            $lines[] = "{$varName}: {$value};";
        }
        return implode("\n", $lines);
    }

    public function getType(): string
    {
        return 'scss';
    }
}

// Register it
add_action('optstack_init', function (): void {
    optstack_register_design_adapter('scss', new ScssTokenAdapter());
});
```

---

## REST API

The design preset system exposes a REST endpoint:

**`GET /wp-json/optstack/v1/design-presets`**

Returns the complete groups schema (including responsive flags) and all registered presets:

```json
{
  "groups": {
    "heading": {
      "id": "heading",
      "label": "Heading",
      "applies_to": ["h1", "h2", "h3", "h4", "h5", "h6"],
      "supports": ["typography", "color"],
      "variant": false,
      "tokens": {
        "fontFamily": { "type": "string", "control": "font-family" },
        "fontWeight": { "type": "number", "control": "select", "options": [100, 200, 300, 400, 500, 600, 700, 800, 900] },
        "lineHeight": { "type": "number", "control": "range", "min": 0.8, "max": 3, "step": 0.05, "responsive": true },
        "letterSpacing": { "type": "string", "control": "size", "units": ["em", "px"], "responsive": true },
        "color": { "type": "string", "control": "color" },
        "sizeScale": { "type": "object", "control": "scale", "keys": ["h1", "h2", "h3", "h4", "h5", "h6"], "responsive": true }
      }
    },
    "body_text": { "..." : "..." },
    "button": { "variant": true, "..." : "..." }
  },
  "presets": [
    {
      "id": "modern",
      "label": "Modern",
      "builtin": true,
      "tokens": {
        "heading": {
          "fontFamily": "Inter, sans-serif",
          "fontWeight": 700,
          "lineHeight": { "desktop": 1.2, "tablet": 1.25, "mobile": 1.3 },
          "sizeScale": {
            "desktop": { "h1": "3rem", "h2": "2.5rem" },
            "tablet": { "h1": "2.5rem", "h2": "2rem" },
            "mobile": { "h1": "2rem", "h2": "1.75rem" }
          }
        }
      }
    }
  ]
}
```

Authentication: Requires `manage_options` capability (WordPress nonce authentication).

### WordPress Filters

| Filter | Arguments | Description |
|--------|-----------|-------------|
| `optstack_design_resolved_tokens` | `$resolved`, `$fieldValue` | Modify resolved tokens before output |
| `optstack_design_css_variables` | `$css` | Modify the final CSS string |
| `optstack_design_google_fonts` | `$families` | Modify the list of Google Font families to enqueue |
| `optstack_design_groups` | `$groups` | Modify group schemas exposed via REST |
| `optstack_design_presets` | `$presets` | Modify presets exposed via REST |
