# Task: OptStack options for WordPress Customizer (customize storage)

This document describes how to build **Customize storage** support so that OptStack stacks can be edited in the **WordPress Customizer** (Appearance → Customize) with configurable storage backends (theme mod or option) and optional live preview.

---

## Table of Contents

- [Goals](#goals)
- [Current state](#current-state)
- [Proposed behavior](#proposed-behavior)
- [Storage options](#storage-options)
- [Implementation tasks](#implementation-tasks)
- [API design](#api-design)
- [Technical notes](#technical-notes)
- [Acceptance criteria](#acceptance-criteria)
- [Related docs](#related-docs)

---

## Goals

1. **Customizer as UI** – Allow one or more OptStack stacks to be edited in the Customizer (Appearance → Customize) instead of (or in addition to) admin options pages.
2. **Configurable storage** – Support at least:
   - **Theme mod** – `get_theme_mod()` / `set_theme_mod()` (theme-specific, good for theme options).
   - **Option** – Existing `wp_options` (same as current options context), so the same stack can be used in Customizer with option storage.
3. **Reuse schema** – Use the same stack definition (tabs, groups, fields) so developers define once and choose where it appears (admin page and/or Customizer).
4. **Live preview (optional)** – Support live preview in the Customizer where feasible (e.g. refresh or `postMessage` for simple settings).

---

## Current state

- **Options context** – Stacks with `->forOptions()` use `OptionsStore` (single `wp_options` row keyed by stack ID). They are rendered only in admin (custom menu/submenu) and saved via REST API.
- **No Customizer** – There is no registration with `WP_Customize_Manager`; no panels, sections, or controls; no theme_mod-based store.
- **Store binding** – `Bootstrap::bindStore()` switches on `$stack->getContext()` and only knows `options`, `post`, `post_type`, `term`, `taxonomy`, `user`. No `customizer` or `theme_mod` context.

See [STORAGE-SYSTEM.md](../STORAGE-SYSTEM.md) and [optstack.php](../../optstack.php) for current storage and helpers.

---

## Proposed behavior

1. **New storage backend: Theme mod**  
   - Add a store that implements `StoreInterface` and uses `get_theme_mod()` / `set_theme_mod()` for a single key (e.g. stack ID). Same “single serialized array” shape as OptionsStore so one key holds the whole stack data.

2. **Customizer registration**  
   - When a stack is registered “for Customizer” (see [API design](#api-design)):
     - Add a Customizer **panel** (or use an existing one) for the stack.
     - For each tab → section; for each group/field → control/setting.
     - Settings are registered with the chosen storage (theme_mod or option). One Customizer “setting” can be the whole stack (single serialized value) or one setting per field/key; single-setting is simpler and matches current store shape.

3. **UI in Customizer**  
   - **Phase 1 (recommended):** Use a single Customizer section that loads the **existing OptStack React admin UI** (same as options page) in an iframe or panel content, and sync the single “stack data” setting on save. No need to map every field to a native Customizer control.
   - **Phase 2 (optional):** Map OptStack field types to native Customizer controls (e.g. `WP_Customize_Color_Control`, `WP_Customize_Image_Control`) for a subset of types and use `postMessage` for those for live preview.

4. **Preview**  
   - With Phase 1, preview can be “refresh” (default) when the user clicks “Publish” or “Save & Publish”.
   - With Phase 2, selected controls can use `postMessage` and JS to update the preview without full refresh.

5. **Reading data**  
   - Theme/plugin code continues to use `OptStack::getField($stackId, $key, $default)` (or equivalent). For Customizer-backed stacks, the store behind that call is either the new Theme Mod store or the existing Options store, so no change at the call site.

---

## Storage options

| Storage   | WordPress API           | Use case                          | Autoload (options only) |
|----------|--------------------------|-----------------------------------|--------------------------|
| **Option**   | `get_option` / `update_option` | Same as current options; shared with admin UI | Configurable (default true) |
| **Theme mod**| `get_theme_mod` / `set_theme_mod` | Theme-specific; survives theme switch if desired; standard for Customizer | N/A |

- **Theme mod** – One theme_mod key per stack (e.g. `theme_options`). Value is the same serialized array as in OptionsStore. Per-theme; switching theme gives a different set of values unless migrated.
- **Option** – Same as today: one option name = stack ID. Can be used from both admin options page and Customizer if we allow the same stack to be registered in both places with the same option name.

---

## Implementation tasks

### 1. Theme Mod store

- [x] Add `OptStack\WordPress\Store\ThemeModStore` implementing `StoreInterface`.
  - Constructor: `ThemeModStore(string $key)` where `$key` is the theme_mod key (e.g. stack ID).
  - `get`/`set`/`delete`/`all`/`has`: read/write one serialized array via `get_theme_mod($key)` / `set_theme_mod($key, $data)`.
  - Mirror `OptionsStore` behavior: `loadData()` returns array, `saveData(array)` persists it; support `setMany`, `replace`, `deleteAll`, `clearCache` if needed for compatibility.
- [x] Unit tests for `ThemeModStore` (get, set, delete, all, has, replace).

### 2. Stack API for Customizer + storage choice

- [x] Extend stack API so a stack can be marked as “Customizer” and given a storage type:
  - Option A: `->forCustomizer(string $storage = 'theme_mod')` where `$storage` is `'theme_mod'` or `'option'`. Implies context `customizer` and binds either `ThemeModStore` or `OptionsStore` in Bootstrap.
  - Option B: `->forOptions()->customizeStorage('theme_mod'|'option')` and a flag like `->showInCustomizer(true)` so the same options stack can also appear in Customizer with the same or different storage (if we ever want different storage per UI).
  - Decide whether one stack can be both “admin options page” and “Customizer” (same data, two UIs) or only one. Document the choice.
- [x] In `Bootstrap::bindStore()` (or equivalent), when context is `customizer` or when `customizeStorage` is set, bind `ThemeModStore` or `OptionsStore` accordingly.
- [x] Ensure REST API (or a dedicated Customizer save path) can read/write the same store so the React UI can load/save when used inside Customizer (Phase 1).

### 3. Customizer registration (Phase 1)

- [x] Add a class or functions that run on `customize_register` and:
  - Collect stacks that are registered for Customizer (e.g. by context or flag).
  - For each such stack, add a Customizer **panel** (or section) with title/description from the stack.
  - Add one **setting** (e.g. `optstack_{stack_id}`) that stores the full stack data (serialized array). Transport: `refresh` for Phase 1.
  - Add one **control** that either:
    - Renders a placeholder and enqueues the OptStack admin script, then mounts the existing OptStack React UI in that control’s container, **or**
    - Uses a hidden input and a “Open editor” link that opens the full OptStack UI in a modal/side panel and writes back into the setting.
  - On save, the Customizer will persist the setting via theme_mod or option depending on how the setting was registered (`$wp_customize->add_setting(..., ['type' => 'theme_mod']` or `'option'`).
- [x] Ensure the OptStack REST (or Customizer-specific endpoint) for GET/POST stack data uses the correct store (ThemeModStore or OptionsStore) when the request comes from the Customizer (e.g. same stack ID; store already bound by context/storage).

### 4. Customizer registration (Phase 2, optional)

- [ ] Map a subset of OptStack field types to native Customizer controls (color, image, text, number, checkbox, etc.) and register one setting per field (or per dot-notation key) for those.
- [ ] Add `postMessage` support for those settings and minimal JS to update preview (e.g. CSS variables, or partial refresh).
- [ ] Leave complex fields (typography, repeaters, etc.) in the embedded React UI or fallback to refresh.

### 5. Documentation and examples

- [x] Document the new API (e.g. `forCustomizer()`, `customizeStorage()`) in [STORAGE-SYSTEM.md](../STORAGE-SYSTEM.md) and in a short “Customizer” usage guide (e.g. `documents/CUSTOMIZER.md`).
- [x] Add an example stack that uses Customizer + theme_mod (and optionally option) and show how theme code reads values via `OptStack::getField()`.

### 6. Helpers and compatibility

- [x] Ensure `optstack_resolve_typography_for_breakpoint()` and any other option-reading helpers work when the data source is theme_mod (same array shape).
- [x] If the same stack is used in both admin and Customizer with the same storage (option), no extra work; if different (e.g. admin = option, Customizer = theme_mod), document that they are separate datasets.

---

## API design

### Option A: Dedicated Customizer context

```php
OptStack::make('theme_options')
    ->forCustomizer('theme_mod')  // or 'option'
    ->label('Theme Options')
    ->define(function ($stack) {
        $stack->tab('general', function ($tab) {
            $tab->group('colors', function ($group) {
                $group->field('primary_color', ['type' => 'color', 'default' => '#2271b1']);
            });
        });
    })
    ->build();
```

- **Pros:** Clear; Customizer-only stack.  
- **Cons:** Doesn’t reuse the same stack in an admin page without registering twice.

### Option B: Options stack that also appears in Customizer

```php
OptStack::make('theme_options')
    ->forOptions()
    ->showInCustomizer(true)           // also register in Customizer
    ->customizeStorage('theme_mod')     // use theme_mod in Customizer (optional; default option)
    ->menuParent('themes.php')
    ->define(...)
    ->build();
```

- **Pros:** One definition, two UIs; can share option storage or use theme_mod in Customizer.  
- **Cons:** Slightly more options; must define behavior when both UIs edit the same option (last write wins).

Recommendation: Start with **Option A** for a clear “Customizer-only” path and add **Option B** if we need shared options + Customizer. Document both in the task and in the final docs.

---

## Technical notes

- **Setting type** – For “one setting = whole stack”, use a single setting with `sanitize_callback` that accepts array (and optionally JSON). For theme_mod: `$wp_customize->add_setting('optstack_theme_options', ['type' => 'theme_mod', 'default' => [], ...])`.
- **Capability** – Use `edit_theme_options` for Customizer; align with existing OptStack capability handling where possible.
- **Preview** – With one big setting and React UI, use `transport => 'refresh'` first; introduce `postMessage` only for Phase 2 and only for controls that are mapped to native Customizer controls.
- **REST in Customizer** – The existing REST endpoint for stack data can be used from the React app inside the Customizer; ensure the stack’s store is bound when the request is made (e.g. by stack ID and context so Bootstrap binds ThemeModStore or OptionsStore). No need for a separate “Customizer” REST route if the same route works with the bound store.

---

## Acceptance criteria

- [x] Theme mod store exists, implements `StoreInterface`, and is covered by tests.
- [x] Stacks can be registered for the Customizer with storage = theme_mod or option.
- [x] In Appearance → Customize, at least one OptStack stack appears as a panel/section.
- [x] The stack’s data can be edited (Phase 1: via existing OptStack React UI embedded or linked) and saved; persisted via theme_mod or option as configured.
- [x] Theme/plugin code can read values with `OptStack::getField($stackId, $key, $default)` when the stack uses Customizer + theme_mod or option.
- [x] Documentation (STORAGE-SYSTEM + Customizer guide) and one example are updated/added.

---

## Related docs

- [STORAGE-SYSTEM.md](../STORAGE-SYSTEM.md) – Current stores and binding
- [RESPONSIVE.md](../RESPONSIVE.md) – Responsive fields (value shape unchanged by storage)
- [GUTENBERG-BLOCK-REGISTER-GUIDE.md](../GUTENBERG-BLOCK-REGISTER-GUIDE.md) – Block vs options vs Customizer
- [optstack.php](../../optstack.php) – Helpers such as `optstack_resolve_typography_for_breakpoint`
- WordPress Customizer: [Theme Customization API](https://developer.wordpress.org/themes/customize-api/)
