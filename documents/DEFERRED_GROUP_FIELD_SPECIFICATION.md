# AI Implementation Prompt — Deferred Group (OptStack)

## Project Context

You are contributing to **OptStack**, a PHP framework designed as a **WordPress Data Stack Framework**.

OptStack uses a **closure-based API** for defining stacks and groups:

```php
$stack->group('key', function ($group) {
    // define fields
}, $args);
```

Your task is to **extend the existing Group system** to support a concept called **Deferred Group**.

You MUST NOT change the existing API style.

---

## Problem Statement

Groups with many fields are rendered inline by default, causing:

* UI clutter
* cognitive overload
* poor usability for advanced settings

We want a way to **define a group whose fields are not rendered immediately**, but instead shown only after a user-triggered action (e.g. clicking a button that opens a modal).

---

## Concept Definition

A **Deferred Group** is a normal group field with additional metadata indicating:

* it should NOT render its fields inline by default
* its fields should be rendered only when triggered
* data structure, validation, and storage MUST remain unchanged

Deferred Group is **a rendering strategy only**, not a data model change.

---

## API Requirements (MANDATORY)

### Group Definition

Deferred Group MUST be defined using the existing API:

```php
$stack->group('pricing', function ($group) {
    $group->field('regular_price', [...]);
    $group->field('sale_price', [...]);
    $group->field('currency', [...]);
}, [
    'label'    => 'Pricing',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Configure pricing',
        'render'       => 'modal',
    ],
]);
```

### Rules

* `deferred` is a boolean flag
* `ui` is optional metadata
* No new builder or DSL is allowed
* No breaking changes to `group()` signature

---

## Schema Normalization

After normalization, a Deferred Group schema MUST look like:

```php
[
  'type' => 'group',
  'key' => 'pricing',
  'label' => 'Pricing',
  'deferred' => true,
  'ui' => [
    'triggerLabel' => 'Configure pricing',
    'render' => 'modal',
  ],
  'fields' => [ ... ]
]
```

* `fields` MUST be fully populated
* No lazy field registration at Core level

---

## Data Model Constraints

### Storage

Deferred Groups MUST:

* use the same data structure as normal groups
* be stored in the same meta key
* NOT introduce separate storage
* NOT wrap or modify values

Example saved data:

```php
[
  'pricing' => [
    'regular_price' => 100,
    'sale_price' => 80,
    'currency' => 'USD',
  ]
]
```

---

## Validation Rules

* Validation logic MUST NOT change
* Required fields inside Deferred Groups MUST still be required
* Validation MUST occur on full form submission
* Deferred rendering MUST NOT bypass validation

---

## Save Behavior

* If a Deferred Group is never opened:

  * existing data MUST be preserved
  * default values apply only if no previous data exists
* Data MUST NOT be auto-cleared

---

## Compatibility Requirements

Deferred Groups MUST remain compatible with:

* Nested groups
* Conditional logic
* Searchable fields
* Import / Export
* Revision systems

---

## Frontend Contract (Reference Only)

OptStack Core MUST ONLY expose metadata.

Frontend (React) is responsible for:

1. Rendering a trigger (button / link)
2. Opening a modal or overlay
3. Rendering group fields lazily
4. Binding values to the main form state

Core MUST NOT include any frontend logic.

---

## Architecture Constraints

### MUST NOT

* Change the existing Stack or Group API
* Add UI code to PHP Core
* Add WordPress hooks into group definition logic
* Introduce new storage mechanisms

### MUST

* Implement Deferred Group via metadata only
* Keep behavior predictable and explicit
* Preserve backward compatibility

---

## Deliverables

* Updated Group schema handling
* Support for `deferred` flag in group metadata
* Clear inline documentation
* Zero breaking changes

---

## Design Mindset

Think like a **framework engineer**, not a UI developer.

Your responsibility is to:

* define data behavior
* expose rendering hints
* keep the system extensible
* respect WordPress-native patterns

Do NOT optimize prematurely for UI convenience.

---

## Success Criteria

The implementation is correct if:

* Existing groups continue to work unchanged
* Deferred Groups store and validate data identically to normal groups
* Frontend can render Deferred Groups without special-case hacks
* No new public APIs are introduced unnecessarily

---

## End of Task
