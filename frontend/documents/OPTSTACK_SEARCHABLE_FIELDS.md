## OptStack Searchable Fields

### Context

You are contributing to **OptStack**, a PHP framework designed as a **WordPress Data Stack Framework**.

OptStack is **NOT** a UI framework and **NOT** an options-only library.
Its core responsibility is **data definition, storage, and retrieval**, while remaining **WordPress-native**.

OptStack supports:

* Options
* Post Meta
* Term Meta (categories / taxonomies)
* User Meta 

All data is stored using **native WordPress meta APIs**.

---

### Problem Statement

By default, WordPress stores meta values as serialized arrays, which makes querying nested or grouped data inefficient.

OptStack introduces a concept called **searchable fields**, which allows selected scalar fields to be queried efficiently using native `WP_Query` / `meta_query`, **without creating custom database tables**.

---

### Objective

Implement a **Searchable / Indexed Meta system** with the following requirements:

---

### Core Requirements

1. **No custom database tables**
2. **Preserve existing structured meta storage**
3. **Only scalar fields can be searchable**
4. **Searchable fields must be stored additionally as separate meta keys**
5. Must support:

   * Post Meta
   * Term Meta
   * (Design-ready for User Meta)

---

### API Design (Required)

#### Field Definition (PHP)

```php
$stack->field('title', [
  'type' => 'text',
  'label' => 'Title',
  'searchable' => true // allow index field to search able
]);

$stack->field('seo', [
  'type' => 'text',
  'label' => 'Seo',
  'searchable' => true // allow index field to search able
]);
```

* `'searchable' => true` marks a field as indexable
* Group / repeater fields **cannot be searchable**
* Nested searchable fields inside groups ARE allowed

---

### Storage Strategy (Dual-write)

When saving data:

1. **Full structured data**

   * Stored as usual (e.g. `theme_settings`)

2. **Indexed meta**

   * For each searchable field, store its scalar value in a **dedicated meta key**

Example:

```text
theme_settings                  → full array
_optstack_idx_post_price        → 199
_optstack_idx_post_seo_title    → "Awesome product"
```

---

### Meta Key Convention (MANDATORY)

All indexed meta keys MUST follow this format:

```
_optstack_idx_{context}_{field_path}
```

Where:

* `{context}` = post | term | user
* `{field_path}` = flattened dot notation

  * `seo.title` → `seo_title`

Examples:

* `_optstack_idx_post_price`
* `_optstack_idx_term_color`
* `_optstack_idx_post_seo_title`

---

### Field Type Constraints

✅ Allowed searchable types:

* text
* textarea (optional)
* number
* select
* radio
* checkbox (single)
* boolean

🚫 Not allowed:

* group (itself)
* repeater
* flexible content
* arrays
* file / image

The system must **validate** this and prevent invalid usage.

---

### Implementation Expectations

You should:

1. Extend field metadata to support:

   * `searchable: bool`
2. Build a **Field Index Resolver**

   * Flattens nested fields
   * Resolves final meta keys
3. Hook into OptStack save lifecycle

   * After validation
   * Before / after main meta save
4. Write indexed meta using:

   * `update_post_meta`
   * `update_term_meta`
5. Remove indexed meta when:

   * Field is deleted
   * Value is empty or null

---

### Architecture Constraints

🚫 DO NOT:

* Add UI code
* Add WordPress hooks inside Core domain logic
* Hardcode post types or taxonomies
* Introduce custom tables

✅ DO:

* Keep logic modular
* Design for future query builders
* Follow OptStack naming conventions
* Write clean, readable PHP

---

### Deliverables

* PHP classes / functions implementing searchable fields
* Clear inline documentation
* No breaking changes to existing APIs
* Code that fits a Composer-installed WordPress plugin

---

### Mindset

Think like a **framework engineer**, not a plugin developer.

Your job is to:

* Preserve WordPress compatibility
* Enable powerful querying
* Keep the system extensible and predictable
