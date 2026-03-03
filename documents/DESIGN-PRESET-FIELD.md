# Design Preset Field

> A complete field type for managing design systems through semantic groups, presets, and design tokens.

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
- [Custom Groups](#custom-groups)
  - [Registering a Group](#registering-a-group)
  - [Token Definition Reference](#token-definition-reference)
  - [Variant Groups](#variant-groups)
- [Custom Presets](#custom-presets)
- [Custom Group Specimens](#custom-group-specimens)
  - [Registering a Specimen (JavaScript)](#registering-a-specimen-javascript)
  - [GroupSpecimenProps Reference](#groupspecimenprops-reference)
  - [Specimen Registry API](#specimen-registry-api)
- [Reading Tokens in PHP](#reading-tokens-in-php)
- [CSS Output](#css-output)
  - [CSS Variable Naming](#css-variable-naming)
  - [Using Variables in Stylesheets](#using-variables-in-stylesheets)
- [Custom Output Adapters](#custom-output-adapters)
- [REST API](#rest-api)

---

## Overview

The `design_preset` field type provides a visual, specimen-based editor for managing design tokens organized into semantic groups (Heading, Body Text, Button, Card, etc.). Users select a preset as a starting point and can override individual tokens. The resolved tokens are output as CSS custom properties on the frontend.

**Key concepts:**

- **Design Group** — A semantic category of design tokens (e.g. `heading`, `button`, `card`).
- **Design Token** — A single design value within a group (e.g. `fontFamily`, `color`, `borderRadius`).
- **Preset** — A named collection of token values across groups (e.g. "Modern", "Classic", "Dark").
- **Override** — A per-field token override on top of the active preset.

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

This creates an options page under **Appearance > Design System** with a design preset field that includes all 15 built-in groups and 6 built-in presets.

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
        'allowed_presets' => ['modern', 'classic', 'minimal'],
        'allowed_groups'  => ['heading', 'body_text', 'button', 'card'],
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
                'allowed_groups' => ['heading', 'body_text', 'card'],
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
    "button.primary.background": "#10B981"
  },
  "presets": [
    {
      "id": "custom-1709312345678",
      "label": "My Custom Preset",
      "base": "modern",
      "tokens": {}
    }
  ]
}
```

| Key | Type | Description |
|-----|------|-------------|
| `active_preset` | `string` | ID of the currently selected preset |
| `overrides` | `object` | Dot-notation token path → value overrides on top of the active preset |
| `presets` | `array` | User-created custom presets (cloned from built-in ones) |

---

## Built-in Groups

The following 15 semantic groups are registered by default:

| Group ID | Label | Variant | Applies To |
|----------|-------|---------|------------|
| `heading` | Heading | No | h1–h6 |
| `body_text` | Body Text | No | paragraph, lead, small, muted |
| `inline_text` | Inline Text | No | link, inline_code, mark, kbd |
| `button` | Button | **Yes** | button, cta, icon_button |
| `link` | Link | No | inline_link, nav_link |
| `form_field` | Form Field | No | input, textarea, select |
| `form_choice` | Form Choice | No | checkbox, radio |
| `form_meta` | Form Meta | No | label, help_text, error_text, success_text |
| `container` | Container | No | section, page_container |
| `card` | Card | **Yes** | card, panel |
| `navigation` | Navigation | No | menu, breadcrumb, pagination, tabs |
| `alert` | Alert | **Yes** | info, success, warning, error |
| `loading` | Loading | No | loader, progress_bar |
| `media` | Media | No | image, video, gallery |
| `utility` | Utility | No | badge, avatar, icon, divider |

Groups marked as **Variant** store an array of named variants (e.g. Button has `primary`, `secondary`, `ghost`).

---

## Built-in Presets

6 built-in presets are included: `modern`, `classic`, `minimal`, `playful`, `elegant`, `dark`.

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
            'priceSize'        => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
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
    'type'    => 'string',   // 'string' | 'number' | 'object'
    'control' => 'color',    // UI control type (see table below)
    // Additional properties depend on the control type
],
```

**Available controls:**

| Control | Type | Extra Properties | Description |
|---------|------|-----------------|-------------|
| `color` | `string` | — | Color picker with hex input |
| `font-family` | `string` | — | Font family text input |
| `size` | `string` | `units: string[]` | Size input with unit hint (e.g. `['px', 'rem']`) |
| `spacing` | `string` | — | Spacing input (e.g. `10px 20px`) |
| `shadow` | `string` | — | Box shadow text input |
| `range` | `number` | `min`, `max`, `step` | Slider with numeric output |
| `select` | `string\|number` | `options: array` | Dropdown select |
| `scale` | `object` | `keys: string[]` | Multi-value grid (e.g. h1–h6 size scale) |

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
        'fontSize'     => ['type' => 'string', 'control' => 'size', 'units' => ['px', 'rem']],
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
            ['id' => 'default',  'label' => 'Default',  'background' => '#E5E7EB', 'color' => '#374151', 'borderRadius' => '9999px', 'fontSize' => '12px'],
            ['id' => 'success',  'label' => 'Success',  'background' => '#D1FAE5', 'color' => '#065F46', 'borderRadius' => '9999px', 'fontSize' => '12px'],
            ['id' => 'danger',   'label' => 'Danger',   'background' => '#FEE2E2', 'color' => '#991B1B', 'borderRadius' => '9999px', 'fontSize' => '12px'],
        ],
    ],
]);
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
                'lineHeight' => 1.2,
                'color'      => '#1A1A2E',
                'sizeScale'  => ['h1' => '3.5rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
            ],
            'body_text' => [
                'fontFamily' => 'Open Sans, sans-serif',
                'fontSize'   => '16px',
                'fontWeight' => 400,
                'lineHeight' => 1.7,
                'color'      => '#333333',
            ],
            'pricing_table' => [
                'headerBackground' => '#1A1A2E',
                'headerColor'      => '#FFFFFF',
                'priceSize'        => '2.5rem',
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

By default, custom groups display a generic property grid in the editor. You can register a custom **specimen component** to provide a rich visual preview — like the built-in heading specimens showing H1–H6 type scale, or button specimens showing rendered buttons.

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

    var headerBg = String(tokens.headerBackground || '#1A1A2E');
    var headerColor = String(tokens.headerColor || '#FFFFFF');
    var priceSize = String(tokens.priceSize || '2.5rem');
    var priceWeight = Number(tokens.priceFontWeight || 700);
    var radius = String(tokens.borderRadius || '12px');
    var shadow = String(tokens.shadow || 'none');

    return React.createElement('div', { className: 'os-dp-specimen' },
      // Visual specimen
      React.createElement('div', { className: 'os-dp-inline-specimens' },
        React.createElement('div', { className: 'os-dp-inline-card' },
          React.createElement('div', { className: 'os-dp-inline-label' }, 'Pricing Card Preview'),
          React.createElement('div', { className: 'os-dp-inline-preview' },
            React.createElement('div', {
              style: {
                borderRadius: radius,
                boxShadow: shadow,
                overflow: 'hidden',
                border: '1px solid #e5e7eb',
              }
            },
              React.createElement('div', {
                style: {
                  background: headerBg,
                  color: headerColor,
                  padding: '24px',
                  textAlign: 'center',
                }
              },
                React.createElement('div', { style: { fontSize: '14px', marginBottom: '8px' } }, 'Pro Plan'),
                React.createElement('div', { style: { fontSize: priceSize, fontWeight: priceWeight } }, '$29/mo')
              ),
              React.createElement('div', { style: { padding: '20px' } },
                React.createElement('div', { style: { fontSize: '14px', color: '#6B7280' } }, '✓ Feature one'),
                React.createElement('div', { style: { fontSize: '14px', color: '#6B7280', marginTop: '8px' } }, '✓ Feature two'),
                React.createElement('div', { style: { fontSize: '14px', color: '#6B7280', marginTop: '8px' } }, '✓ Feature three')
              )
            )
          ),
          // Metadata
          React.createElement('div', { className: 'os-dp-type-meta' },
            metaItem('Header BG', headerBg, true),
            metaItem('Price Size', priceSize),
            metaItem('Radius', radius)
          )
        )
      ),

      // Property editor (reuse the generic property pattern)
      React.createElement('div', { className: 'os-dp-divider' }),
      React.createElement(PropertyEditor, { group: group, tokens: tokens, onTokenChange: onTokenChange })
    );
  });

  // Helper: create a metadata item
  function metaItem(label, value, isColor) {
    var children = [];
    if (isColor) {
      children.push(React.createElement('span', {
        className: 'os-dp-meta-swatch',
        style: { background: value }
      }));
    }
    children.push(value);
    return React.createElement('span', { className: 'os-dp-meta-item' },
      React.createElement('span', { className: 'os-dp-meta-label' }, label),
      React.createElement('span', { className: 'os-dp-meta-value' }, children)
    );
  }

  // Minimal property editor that renders token controls
  function PropertyEditor(props) {
    var open = React.useState(false);
    var isOpen = open[0];
    var setOpen = open[1];

    return React.createElement('div', { className: 'os-dp-property-section' },
      React.createElement('button', {
        type: 'button',
        className: 'os-dp-property-toggle',
        onClick: function () { setOpen(!isOpen); }
      },
        React.createElement('span', null, isOpen ? '▾' : '▸'),
        React.createElement('span', null, 'Edit Properties')
      ),
      isOpen && React.createElement('div', { className: 'os-dp-property-grid' },
        Object.keys(props.group.tokens).map(function (key) {
          var val = props.tokens[key];
          return React.createElement('div', { key: key, className: 'os-dp-field' },
            React.createElement('label', { className: 'os-dp-field-label' }, key),
            React.createElement('input', {
              className: 'os-dp-input',
              value: String(val != null ? val : ''),
              onChange: function (e) { props.onTokenChange(key, e.target.value); }
            })
          );
        })
      )
    );
  }
})();
```

### GroupSpecimenProps Reference

Every specimen component receives these props:

| Prop | Type | Description |
|------|------|-------------|
| `group` | `DesignGroupSchema` | The group schema with `id`, `label`, `applies_to`, `supports`, `variant`, `tokens`. |
| `tokens` | `DesignGroupValue` | Resolved token values. For non-variant groups: `Record<string, unknown>`. For variant groups: `DesignPresetVariant[]`. |
| `onTokenChange` | `(tokenKey: string, value: unknown, variantId?: string) => void` | Call this to update a token value. For variant groups, pass the `variantId` as the third argument. |

### Specimen Registry API

Available on `window.optstack` after the admin script loads:

| Method | Signature | Description |
|--------|-----------|-------------|
| `registerGroupSpecimen` | `(groupId: string, component: React.ComponentType) => void` | Register a React component as the specimen for a group ID. |
| `unregisterGroupSpecimen` | `(groupId: string) => boolean` | Remove a registered specimen. Returns `true` if it existed. |
| `hasGroupSpecimen` | `(groupId: string) => boolean` | Check if a specimen is registered for a group ID. |

Registration order:
1. External specimens registered via `registerGroupSpecimen()` take priority.
2. Built-in specimens (heading, body_text, button, etc.) are used next.
3. The generic property grid is the final fallback.

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
| `card.default.borderRadius` | `--os-card-default-border-radius` |

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
h4 { font-size: var(--os-heading-size-scale-h4, 1.5rem); }
h5 { font-size: var(--os-heading-size-scale-h5, 1.25rem); }
h6 { font-size: var(--os-heading-size-scale-h6, 1rem); }

.btn-primary {
  background: var(--os-button-primary-background);
  color: var(--os-button-primary-color);
  padding: var(--os-button-primary-padding);
  border-radius: var(--os-button-primary-border-radius);
}

.card {
  background: var(--os-card-default-background, #fff);
  border-radius: var(--os-card-default-border-radius, 8px);
  box-shadow: var(--os-card-default-shadow, none);
  padding: var(--os-card-default-padding, 24px);
}
```

---

## Custom Output Adapters

By default, tokens are output as CSS variables. You can add custom adapters for other output formats:

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

Returns the complete groups schema and all registered presets:

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
        "fontWeight": { "type": "number", "control": "select", "options": [100, 200, 300, 400, 500, 600, 700, 800, 900] }
      }
    }
  },
  "presets": [
    {
      "id": "modern",
      "label": "Modern",
      "builtin": true,
      "tokens": { ... }
    }
  ]
}
```

Authentication: Requires `manage_options` capability (WordPress nonce authentication).
