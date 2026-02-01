# OptStack – PHP Testing Guide

## Purpose

This document defines the **official PHP testing strategy** for the OptStack framework.

The goal of testing in OptStack is **confidence, not coverage**. Tests exist to ensure core logic remains stable during refactors, feature expansion, and AI-assisted development.

---

## Scope

This guide applies **ONLY to PHP code**.

Frontend (React / TypeScript) testing is **explicitly excluded** from this document.

---

## Testing Philosophy

### 1. Test Core Logic, Not WordPress

* 90% of OptStack logic **must be testable without WordPress**
* WordPress APIs are treated as an external system
* Business rules must live outside WP adapters

If a component cannot be tested without loading WordPress, it likely violates architectural boundaries.

---

### 2. Prefer Deterministic Unit Tests

Tests must be:

* Fast
* Deterministic
* Isolated
* Free of side effects

Avoid:

* Database dependency
* Global state reliance
* Complex mocks

---

## Test Layers (PHP Only)

### 1️⃣ Core Domain Tests (Highest Priority)

**Location**

```
tests/php/Unit/
```

**What to test**

* Field definitions
* Group / Nested / Repeatable structures
* Conditional logic
* Data normalization
* Serialization output
* Default values
* Schema transformation
* Searchable indexing logic

**What NOT to test**

* WordPress functions
* HTML rendering
* Admin UI behavior

**Example**

```php
public function test_searchable_field_creates_index_meta()
{
    $field = Field::make('price')
        ->type('number')
        ->searchable();

    $result = $field->normalize(100);

    $this->assertEquals(100, $result['value']);
    $this->assertEquals(100, $result['_optstack_index_price']);
}
```

---

### 2️⃣ WordPress Adapter Tests (Limited)

**Location**

```
tests/php/wp/
```

**Purpose**
Verify that OptStack correctly interacts with WordPress APIs.

**Allowed scope**

* `update_post_meta`
* `get_post_meta`
* Term meta handling
* Option persistence
* Plugin bootstrap safety

**Rules**

* Keep the number of tests minimal
* Do not test WordPress internals
* No UI assertions

---

## Folder Structure

```
tests/
├── php/
│   ├── Unit/
│   │   ├── FieldTest.php
│   │   ├── GroupTest.php
│   │   ├── ConditionalTest.php
│   │   ├── SearchableTest.php
│   │   └── VisualBlockSchemaTest.php
│   ├── Fixtures/
│   │   └── schemas.php
│   └── bootstrap.php
```

---

## Required Test Coverage Rules

Every new **Field type** MUST include tests for:

* Normalization
* Serialization
* Default value behavior

Every feature affecting data storage MUST include:

* At least one unit test validating the data output

---

## Tooling

* PHPUnit (Composer-based)
* No external mocking libraries unless strictly necessary

Run tests via:

```bash
composer test
```

---

## Success Criteria

Testing is considered successful when:

* Core logic can be refactored without fear
* Failures clearly indicate logical regressions
* Tests run quickly and consistently
* Developers trust the test suite

---

## Final Principle

> Tests exist to **enable change**, not to slow it down.
