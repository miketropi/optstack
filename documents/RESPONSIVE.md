# Responsive Fields

OptStack fields (text, select, number, and others) can be configured so that values are stored **per viewport**: desktop, tablet, and mobile. When `responsive` is enabled, the field UI includes a mode switcher so editors set a value for each breakpoint.

---

## Enabling responsive mode

Set `'responsive' => true` on the field (or in `attributes`):

```php
$stack->field('heading_size', [
    'type'       => 'number',
    'label'      => 'Heading size',
    'default'    => 24,
    'responsive' => true,
]);
```

Or via attributes:

```php
$stack->field('padding', [
    'type'    => 'number',
    'label'   => 'Padding',
    'default' => 16,
    'attributes' => [
        'responsive' => true,
    ],
]);
```

When `responsive` is `true`:

- The **stored value** is an object keyed by mode: `desktop`, `tablet`, `mobile`.
- The **field UI** shows a mode switcher (Desktop | Tablet | Mobile) and one input per mode (or a single input whose value is applied to the active mode).

---

## Value shape

For a non-responsive field, the value is a single value (string, number, etc.):

```json
"24"
```

For a responsive field, the value is an object with one key per mode:

```json
{
  "desktop": 24,
  "tablet": 20,
  "mobile": 18
}
```

- **desktop** – default/large screens (e.g. ≥ 1024px).
- **tablet** – medium screens (e.g. 768px–1023px).
- **mobile** – small screens (e.g. &lt; 768px).

If a mode is missing, the frontend can fall back to the next larger breakpoint (e.g. tablet → desktop, mobile → tablet → desktop).

---

## UI behavior

When `responsive` is enabled, the field renderer:

1. **Mode switcher** – Renders a small bar or tabs above (or beside) the field: **Desktop** | **Tablet** | **Mobile**. One mode is active at a time.
2. **Per-mode value** – The main control (text input, select, number, etc.) edits the value for the **active** mode only.
3. **Optional inheritance** – UI can offer “Use desktop value” for tablet/mobile so the user does not have to re-enter the same value. That is an implementation detail; stored value can still be explicit per mode.

The switcher can use icons (e.g. monitor, tablet, phone) and/or labels. Active mode should be clearly highlighted.

---

## Supported field types

Responsive mode can be supported for any field that stores a single primitive or simple value:

| Type        | Example use              | Value per mode   |
|------------|---------------------------|------------------|
| `text`     | Different taglines        | string           |
| `number`   | Font size, spacing        | number           |
| `range`    | Slider (e.g. size)        | number           |
| `select`   | Layout (full / narrow)     | string           |
| `toggle`   | Show/hide per breakpoint  | boolean          |
| `color`    | Accent per breakpoint     | string (hex)     |
| `url`      | Link per breakpoint       | string           |

Fields that already store an object (e.g. `media`, `typography`) can support responsive by nesting: e.g. `{ desktop: {...}, tablet: {...}, mobile: {...} }`, or by adding a top-level `responsive: true` and storing the same shape as above for each mode.

---

## PHP API summary

| Where       | Key          | Type    | Description                          |
|------------|--------------|---------|--------------------------------------|
| Field      | `responsive` | boolean | Enable desktop/tablet/mobile modes.  |
| Attributes | `responsive` | boolean | Same; can be used inside `attributes`. |

Default is `false`. When `true`, the schema and UI treat the field as responsive and use the three-mode value shape.

---

## Frontend (theme/block) usage

When reading the value in PHP or JS:

- **Non-responsive:** `$value` is the raw value (e.g. `24`).
- **Responsive:** `$value` is `['desktop' => 24, 'tablet' => 20, 'mobile' => 18]`.

Example (PHP) for a number field:

```php
$raw = get_option('my_stack')['heading_size'] ?? null;

if (is_array($raw) && isset($raw['desktop'])) {
    $desktop = (int) ($raw['desktop'] ?? 24);
    $tablet  = (int) ($raw['tablet'] ?? $desktop);
    $mobile  = (int) ($raw['mobile'] ?? $tablet);
    // Output CSS custom properties or class names per breakpoint
} else {
    $desktop = (int) $raw;
    $tablet  = $desktop;
    $mobile  = $desktop;
}
```

In CSS you can use media queries or custom properties:

```css
.heading {
  font-size: var(--heading-size-mobile, 18px);
}
@media (min-width: 768px) {
  .heading { font-size: var(--heading-size-tablet, 20px); }
}
@media (min-width: 1024px) {
  .heading { font-size: var(--heading-size-desktop, 24px); }
}
```

---

## Block attributes (Gutenberg)

For blocks, the attribute type for a responsive field should be `object` (with optional `default`):

```json
{
  "heading_size": {
    "type": "object",
    "default": {
      "desktop": 24,
      "tablet": 20,
      "mobile": 18
    }
  }
}
```

Schema-to-attributes mapping should detect `responsive => true` and emit `type: 'object'` and a default object with `desktop`, `tablet`, and `mobile` keys (using the field’s default for each, or a single default applied to all three).

---

## Summary

| Aspect        | Detail                                                                 |
|---------------|------------------------------------------------------------------------|
| **Enable**    | Set `'responsive' => true` on the field (or in `attributes`).         |
| **Value**     | Object: `{ desktop, tablet, mobile }`; each key holds the field value. |
| **UI**        | Mode switcher (Desktop | Tablet | Mobile) + one control per mode.       |
| **Fields**    | text, number, select, range, toggle, color, url, etc.                 |
| **Frontend**  | Read per-mode keys; use in CSS/JS with media queries or variables.     |
