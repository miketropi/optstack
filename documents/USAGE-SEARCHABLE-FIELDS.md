# Searchable Fields Guide

This guide explains how to use OptStack's **Searchable Fields** feature to enable efficient database queries on your custom meta data.

---

## Table of Contents

- [Overview](#overview)
- [The Problem](#the-problem)
- [The Solution](#the-solution)
- [How to Use](#how-to-use)
  - [Basic Usage](#basic-usage)
  - [Nested Fields in Groups](#nested-fields-in-groups)
  - [Supported Field Types](#supported-field-types)
- [Querying Searchable Fields](#querying-searchable-fields)
  - [WP_Query with meta_query](#wp_query-with-meta_query)
  - [get_posts() Examples](#get_posts-examples)
  - [WP_Term_Query Examples](#wp_term_query-examples)
  - [WP_User_Query Examples](#wp_user_query-examples)
- [Meta Key Format](#meta-key-format)
- [How It Works](#how-it-works)
- [Best Practices](#best-practices)
- [Limitations](#limitations)
- [Debugging](#debugging)

---

## Overview

OptStack's **Searchable Fields** feature allows you to mark specific fields as queryable, enabling you to use WordPress's native `meta_query` to filter posts, terms, or users by your custom field values.

Without searchable fields, all OptStack data is stored as a single serialized array, making it impossible to query individual field values efficiently.

---

## The Problem

By default, WordPress stores meta values as serialized arrays:

```php
// How OptStack normally stores data
update_post_meta($post_id, 'product_data', [
    'price' => 99.99,
    'sku' => 'PROD-001',
    'stock' => 50,
    'status' => 'active',
]);
```

This results in database storage like:

```
meta_key: product_data
meta_value: a:4:{s:5:"price";d:99.99;s:3:"sku";s:8:"PROD-001";s:5:"stock";i:50;s:6:"status";s:6:"active";}
```

**The problem**: You cannot efficiently query this data. For example, you cannot find "all products where price > 50" using `WP_Query`.

---

## The Solution

Mark fields as `searchable` to create **indexed meta keys** that can be queried:

```php
$stack->field('price', [
    'type' => 'number',
    'label' => 'Price',
    'searchable' => true,  // Enable indexing
]);
```

OptStack will now store **two things**:

1. **Main data** (as usual): `product_data` → full serialized array
2. **Indexed meta**: `_optstack_idx_post_price` → `99.99`

Now you can query:

```php
$products = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_price',
            'value' => 50,
            'compare' => '>',
            'type' => 'NUMERIC',
        ]
    ]
]);
```

---

## How to Use

### Basic Usage

Add `'searchable' => true` to any supported field:

```php
OptStack::make('product_data')
    ->forPostType('product')
    ->define(function ($stack) {
        // Searchable fields
        $stack->field('price', [
            'type' => 'number',
            'label' => 'Price',
            'searchable' => true,
        ]);
        
        $stack->field('sku', [
            'type' => 'text',
            'label' => 'SKU',
            'searchable' => true,
        ]);
        
        $stack->field('status', [
            'type' => 'select',
            'label' => 'Status',
            'searchable' => true,
            'options' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
                ['value' => 'draft', 'label' => 'Draft'],
            ],
        ]);
        
        // Non-searchable field (no indexing)
        $stack->field('description', [
            'type' => 'textarea',
            'label' => 'Description',
        ]);
    })
    ->build();
```

### Nested Fields in Groups

You can mark fields inside groups as searchable:

```php
OptStack::make('product_data')
    ->forPostType('product')
    ->define(function ($stack) {
        $stack->group('seo', function ($group) {
            $group->field('title', [
                'type' => 'text',
                'label' => 'SEO Title',
                'searchable' => true,  // Will create: _optstack_idx_post_seo_title
            ]);
            
            $group->field('keywords', [
                'type' => 'text',
                'label' => 'Keywords',
                'searchable' => true,  // Will create: _optstack_idx_post_seo_keywords
            ]);
        });
        
        $stack->group('pricing', function ($group) {
            $group->field('regular_price', [
                'type' => 'number',
                'label' => 'Regular Price',
                'searchable' => true,  // Will create: _optstack_idx_post_pricing_regular_price
            ]);
            
            $group->field('sale_price', [
                'type' => 'number',
                'label' => 'Sale Price',
                'searchable' => true,  // Will create: _optstack_idx_post_pricing_sale_price
            ]);
        });
    })
    ->build();
```

### Supported Field Types

Only **scalar** (single-value) field types can be searchable:

| Field Type | Searchable | Notes |
|------------|------------|-------|
| `text` | ✅ Yes | String values |
| `textarea` | ✅ Yes | String values |
| `number` | ✅ Yes | Numeric values |
| `select` | ✅ Yes | Selected value |
| `radio` | ✅ Yes | Selected value |
| `toggle` | ✅ Yes | Boolean (true/false) |
| `checkbox` (single) | ✅ Yes | Boolean |
| `email` | ✅ Yes | String values |
| `url` | ✅ Yes | String values |
| `color` | ✅ Yes | Color string |
| `range` | ✅ Yes | Numeric values |
| `media` | ⚠️ Partial | Stores attachment ID (numeric) |
| `group` | ❌ No | Groups contain multiple values |
| `checkbox-group` | ❌ No | Returns array |
| `select` (multiple) | ❌ No | Returns array |
| Repeatable groups | ❌ No | Returns array |

---

## Querying Searchable Fields

### WP_Query with meta_query

**Find products priced over $50:**

```php
$query = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_price',
            'value' => 50,
            'compare' => '>',
            'type' => 'NUMERIC',
        ]
    ]
]);

while ($query->have_posts()) {
    $query->the_post();
    $price = OptStack::getField('product_data', 'price', 0, get_the_ID());
    echo get_the_title() . ': $' . $price;
}
wp_reset_postdata();
```

**Find active products:**

```php
$query = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
            'compare' => '=',
        ]
    ]
]);
```

**Multiple conditions (AND):**

```php
$query = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_price',
            'value' => 100,
            'compare' => '<=',
            'type' => 'NUMERIC',
        ],
    ]
]);
```

**Multiple conditions (OR):**

```php
$query = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'active',
        ],
        [
            'key' => '_optstack_idx_post_status',
            'value' => 'featured',
        ],
    ]
]);
```

**Order by searchable field:**

```php
$query = new WP_Query([
    'post_type' => 'product',
    'meta_key' => '_optstack_idx_post_price',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
]);
```

### get_posts() Examples

```php
$products = get_posts([
    'post_type' => 'product',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_optstack_idx_post_price',
            'value' => [50, 200],
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC',
        ]
    ]
]);
```

### WP_Term_Query Examples

For taxonomy stacks:

```php
OptStack::make('category_settings')
    ->forTaxonomy('category')
    ->define(function ($stack) {
        $stack->field('featured', [
            'type' => 'toggle',
            'label' => 'Featured',
            'searchable' => true,
        ]);
        
        $stack->field('sort_order', [
            'type' => 'number',
            'label' => 'Sort Order',
            'searchable' => true,
        ]);
    })
    ->build();
```

**Query featured categories:**

```php
$terms = get_terms([
    'taxonomy' => 'category',
    'meta_query' => [
        [
            'key' => '_optstack_idx_term_featured',
            'value' => '1',  // Toggle stores as "1" or ""
        ]
    ],
    'meta_key' => '_optstack_idx_term_sort_order',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
]);
```

### WP_User_Query Examples

For user stacks:

```php
OptStack::make('user_profile')
    ->forUser()
    ->define(function ($stack) {
        $stack->field('department', [
            'type' => 'select',
            'label' => 'Department',
            'searchable' => true,
            'options' => [
                ['value' => 'sales', 'label' => 'Sales'],
                ['value' => 'support', 'label' => 'Support'],
                ['value' => 'engineering', 'label' => 'Engineering'],
            ],
        ]);
    })
    ->build();
```

**Query users by department:**

```php
$users = get_users([
    'meta_query' => [
        [
            'key' => '_optstack_idx_user_department',
            'value' => 'engineering',
        ]
    ]
]);
```

---

## Meta Key Format

All indexed meta keys follow this convention:

```
_optstack_idx_{context}_{field_path}
```

**Components:**

| Part | Description | Examples |
|------|-------------|----------|
| `_optstack_idx` | Prefix (always the same) | - |
| `{context}` | Storage context | `post`, `term`, `user` |
| `{field_path}` | Field path with dots as underscores | `price`, `seo_title`, `pricing_regular_price` |

**Examples:**

| Field Definition | Context | Indexed Meta Key |
|-----------------|---------|------------------|
| `price` | post_type | `_optstack_idx_post_price` |
| `seo.title` | post_type | `_optstack_idx_post_seo_title` |
| `featured` | taxonomy | `_optstack_idx_term_featured` |
| `pricing.sale_price` | taxonomy | `_optstack_idx_term_pricing_sale_price` |
| `department` | user | `_optstack_idx_user_department` |

---

## How It Works

### Dual-Write Strategy

When data is saved, OptStack performs two writes:

```
┌─────────────────────────────────────────────────────────────┐
│                    Save Data Flow                            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. Save main data (always)                                  │
│     update_post_meta($id, 'product_data', $all_data)        │
│                                                              │
│  2. For each searchable field:                               │
│     update_post_meta($id, '_optstack_idx_post_price', 99)   │
│     update_post_meta($id, '_optstack_idx_post_sku', 'ABC')  │
│     update_post_meta($id, '_optstack_idx_post_status', 'active')  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Automatic Sync

The indexed meta is automatically synced when:

- Data is saved via the OptStack UI (REST API)
- `OptStack::saveData()` is called
- `OptStack::updateField()` is called for a searchable field

### Cleanup

When a searchable field value is empty, null, or removed:
- The indexed meta key is **deleted** (not set to empty)
- This keeps the database clean

---

## Best Practices

### 1. Only Mark What You Need

Don't mark every field as searchable. Only use it for fields you actually need to query:

```php
// Good: Only searchable fields that need querying
$stack->field('price', ['type' => 'number', 'searchable' => true]);
$stack->field('status', ['type' => 'select', 'searchable' => true]);

// These don't need to be searchable
$stack->field('description', ['type' => 'textarea']);
$stack->field('notes', ['type' => 'wysiwyg']);
```

### 2. Use Appropriate Compare Types

Match the `type` parameter to your field type:

```php
// For numbers
'type' => 'NUMERIC'

// For dates
'type' => 'DATE'

// For strings (default)
'type' => 'CHAR'
```

### 3. Add Database Indexes for Large Sites

For high-traffic sites with many posts, consider adding MySQL indexes:

```sql
-- Add index for frequently queried meta
ALTER TABLE wp_postmeta ADD INDEX idx_optstack_price (meta_key(40), meta_value(20));
```

### 4. Cache Query Results

Use WordPress transients or object cache for frequently-run queries:

```php
$cache_key = 'active_products_under_100';
$products = get_transient($cache_key);

if ($products === false) {
    $products = get_posts([
        'post_type' => 'product',
        'meta_query' => [
            ['key' => '_optstack_idx_post_status', 'value' => 'active'],
            ['key' => '_optstack_idx_post_price', 'value' => 100, 'compare' => '<', 'type' => 'NUMERIC'],
        ]
    ]);
    set_transient($cache_key, $products, HOUR_IN_SECONDS);
}
```

---

## Limitations

### 1. Options Context Not Supported

Searchable fields only work for:
- Post meta (`forPostType()`)
- Term meta (`forTaxonomy()`)
- User meta (`forUser()`)

**Not supported:** `forOptions()` (no meta_query for options)

### 2. Arrays Cannot Be Searchable

These cannot be searchable:
- Repeatable groups (returns array)
- Multiple select fields (returns array)
- Checkbox groups (returns array)

### 3. No Full-Text Search

Searchable fields use exact matching or comparison operators. For full-text search, consider:
- WordPress's built-in search
- Elasticsearch/Algolia plugins

### 4. Storage Overhead

Each searchable field creates an additional meta row. For 10 searchable fields on 1000 posts, that's 10,000 extra meta rows.

---

## Debugging

### View Indexed Meta Keys

```php
$stack = OptStack::get('product_data');
$manager = new \OptStack\WordPress\Index\IndexedMetaManager();
$keys = $manager->getIndexedMetaKeys($stack);

print_r($keys);
// ['price' => '_optstack_idx_post_price', 'sku' => '_optstack_idx_post_sku', ...]
```

### Check Indexed Values in Database

```sql
SELECT * FROM wp_postmeta 
WHERE post_id = 123 
AND meta_key LIKE '_optstack_idx_%';
```

### Debug Hook

Hook into the sync process:

```php
add_action('optstack_indexed_meta_debug', function ($info) {
    error_log('OptStack Index Sync: ' . print_r($info, true));
});
```

### Verify Field is Searchable

```php
// In your stack definition
$field = $stack->getFields()->get('price');
var_dump($field->isSearchable()); // true/false
```

---

## Related Documentation

- [API-REFERENCE.md](./API-REFERENCE.md) - Complete API reference
- [USAGE-FIELD.md](./USAGE-FIELD.md) - Field definition guide
- [STORAGE-SYSTEM.md](./STORAGE-SYSTEM.md) - How data is stored
- [UPDATE_FIELD_FEATURE.md](./UPDATE_FIELD_FEATURE.md) - Updating single fields
