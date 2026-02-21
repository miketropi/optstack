# Select (WordPress Query) Field

A select field that loads options from WordPress content—posts, pages, custom post types, taxonomy terms, or users. Options are **not** loaded on init; they are fetched when the user types in the field (search-by-keyword). Suitable for large datasets.

**Similar to:** [select](./select.md) (same UI pattern, different data source)

---

## Behavior

- **UI:** Same as standard select (dropdown, single or multiple), but the list is **async**.
- **Data source:** Configured in PHP (post type, taxonomy, or users).
- **Loading:** No options loaded initially (or only the currently selected item for display).
- **Search:** When the user types, the frontend calls a REST endpoint with the search keyword; the backend returns matching items. Results are shown in the dropdown.
- **Value:** Stored as ID (integer or string). Optionally store a small object `{ id, label }` for display without an extra lookup.

---

## Basic Usage

```php
// Select a single page
$stack->field('landing_page', [
    'type'  => 'select-wp-query',
    'label' => 'Landing Page',
    'attributes' => [
        'source'     => 'post',
        'post_type'  => 'page',
        'placeholder' => 'Search pages...',
    ],
]);

// Select posts from a custom post type
$stack->field('featured_product', [
    'type'  => 'select-wp-query',
    'label' => 'Featured Product',
    'attributes' => [
        'source'     => 'post',
        'post_type'  => 'product',
        'placeholder' => 'Search products...',
    ],
]);

// Select a category (term)
$stack->field('category', [
    'type'  => 'select-wp-query',
    'label' => 'Category',
    'attributes' => [
        'source'    => 'term',
        'taxonomy'  => 'category',
        'placeholder' => 'Search categories...',
    ],
]);

// Select a user
$stack->field('author', [
    'type'  => 'select-wp-query',
    'label' => 'Author',
    'attributes' => [
        'source' => 'user',
        'placeholder' => 'Search users...',
    ],
]);
```

---

## Properties

| Property   | Type   | Default | Description |
|-----------|--------|---------|-------------|
| `type`    | string | -       | **Required.** Must be `'select-wp-query'` |
| `label`   | string | -       | Field label |
| `description` | string | `''` | Help text below the field |
| `default` | string/number/array | `''` | Default selected ID(s) |
| `attributes` | array | `[]` | Source config and UI options (see below) |
| `conditions` | array | `[]` | Conditional display rules |

---

## Attributes (source & UI)

### Source (required)

| Attribute   | Type   | Values | Description |
|------------|--------|--------|-------------|
| `source`   | string | `'post'`, `'term'`, `'user'` | What to query |
| `post_type`| string | `'post'`, `'page'`, or any CPT | For `source: 'post'` only |
| `taxonomy` | string | `'category'`, `'post_tag'`, or any taxonomy | For `source: 'term'` only |

### Optional filters (backend applies these to WP_Query / get_terms / get_users)

| Attribute      | Type   | Description |
|----------------|--------|-------------|
| `post_status` | string | For posts: `'publish'`, `'draft'`, etc. Default `'publish'` |
| `number`      | int    | Max results per search request (e.g. `20`) |
| `orderby`     | string | e.g. `'title'`, `'date'`, `'name'` |
| `order`       | string | `'ASC'` or `'DESC'` |

### UI (optional)

| Attribute     | Type    | Default           | Description |
|---------------|---------|-------------------|-------------|
| `placeholder` | string  | `'Search...'`     | Input placeholder when empty |
| `multiple`    | boolean | `false`           | Allow multiple selection |
| `clearable`   | boolean | `true`            | Show clear button |
| `minInputLength` | int  | `1` or `2`         | Min characters before calling API |

---

## Source Reference

| `source` | Required attribute | Backend query | Label used in options |
|----------|--------------------|---------------|------------------------|
| `post`   | `post_type`        | `WP_Query` (post_type, s, post_status) | Post title |
| `term`   | `taxonomy`         | `get_terms()` with search | Term name |
| `user`   | -                  | `WP_User_Query` with search | Display name or user_login |

---

## Value Format

- **Single:** Stored as the entity ID (e.g. `123` for post/term, user ID for users). Frontend may store `{ id: 123, label: 'Page Title' }` for display without a second request.
- **Multiple:** Array of IDs or array of `{ id, label }` depending on implementation.

For block attributes and REST, prefer **ID only** (number or string) to keep schema simple; the frontend can resolve the label when opening the select.

---

## Backend: REST Endpoint (implementation)

Add a route that accepts the field config + search term and returns options.

**Suggested route:** `GET /wp-json/optstack/v1/wp-query`

**Query params:**

- `source` – `post` | `term` | `user`
- `post_type` – (optional) for `source=post`
- `taxonomy` – (optional) for `source=term`
- `search` – user input (keyword)
- `page` – (optional) for paging
- `per_page` – (optional) default 20

**Response:**

```json
{
  "options": [
    { "value": 123, "label": "Page Title" },
    { "value": 456, "label": "Another Page" }
  ],
  "hasMore": false
}
```

- **Posts:** `WP_Query` with `s` = search, `post_type`, `post_status`, `posts_per_page`. Return `post->ID` and `post->post_title`.
- **Terms:** `get_terms(['taxonomy' => $taxonomy, 'search' => $search, 'number' => $per_page])`. Return `term_id` and `name`.
- **Users:** `WP_User_Query` with `search` and `number`. Return `ID` and `display_name` (or `user_login`).

Only return items the current user is allowed to see (respect capabilities and post status).

---

## Frontend: Async Select (implementation)

- Use the same base UI as [SelectField.tsx](../../frontend/src/components/fields/SelectField.tsx) (e.g. `react-select`).
- Use **async** loading:
  - `loadOptions(inputValue)` (or equivalent) calls the REST endpoint with `search=inputValue` and the field’s `attributes` (source, post_type, taxonomy).
  - Map response `options` to `{ value, label }` and pass to the select.
- **Initial value:** If `value` is an ID and no options are loaded yet, either:
  - Call the API once with `search=ID` or a dedicated “get by id” endpoint to fetch the label for the selected ID, or
  - Store/cache `{ id, label }` when the user selects (e.g. in block attributes or form state) and use that for display.
- **Debounce:** Debounce `loadOptions` (e.g. 300 ms) to avoid too many requests while typing.
- **minInputLength:** Don’t call the API until `inputValue.length >= minInputLength` (from attributes).

Register the component in `FieldRenderer`:

```ts
'select-wp-query': SelectWordPressQueryField,
```

---

## Schema / Block Attributes

For Gutenberg blocks, map this field to a block attribute:

- **Type:** `number` (ID) or `string` (if IDs are stored as string). For multiple: `array` of numbers/strings.
- **Default:** `0` or `''` for single; `[]` for multiple.

In `SchemaToAttributes.php`, add:

```php
'select-wp-query' => 'number',  // or 'string'
```

and handle `multiple` so the attribute type is `array` when needed.

---

## Examples

### Single page selector

```php
$stack->field('redirect_page', [
    'type'  => 'select-wp-query',
    'label' => 'Redirect to Page',
    'attributes' => [
        'source'     => 'post',
        'post_type'  => 'page',
        'placeholder' => 'Search pages...',
        'clearable'  => true,
    ],
]);
```

### Multiple categories

```php
$stack->field('categories', [
    'type'  => 'select-wp-query',
    'label' => 'Categories',
    'default' => [],
    'attributes' => [
        'source'     => 'term',
        'taxonomy'  => 'category',
        'multiple'  => true,
        'placeholder' => 'Search categories...',
    ],
]);
```

### Author (user)

```php
$stack->field('assigned_to', [
    'type'  => 'select-wp-query',
    'label' => 'Assigned to',
    'attributes' => [
        'source' => 'user',
        'placeholder' => 'Search users...',
    ],
]);
```

### Custom post type with status filter

```php
$stack->field('event', [
    'type'  => 'select-wp-query',
    'label' => 'Event',
    'attributes' => [
        'source'      => 'post',
        'post_type'   => 'event',
        'post_status' => 'publish',
        'placeholder' => 'Search events...',
        'number'      => 20,
    ],
]);
```

---

## Retrieving the value

Value is the stored ID (or array of IDs). Resolve to title/name in PHP as needed:

```php
// Single post/page
$page_id = (int) $value;
if ($page_id) {
    $title = get_the_title($page_id);
    $url   = get_permalink($page_id);
}

// Term
$term_id = (int) $value;
if ($term_id) {
    $term = get_term($term_id, 'category');
    $name = $term ? $term->name : '';
}

// User
$user_id = (int) $value;
if ($user_id) {
    $user = get_user_by('id', $user_id);
    $name = $user ? $user->display_name : '';
}
```

---

## Summary

| Aspect | Detail |
|--------|--------|
| **Field type** | `select-wp-query` |
| **Data** | From WordPress: posts, CPT, terms, users |
| **Loading** | On demand when user types (search), not all on init |
| **Config** | `attributes.source` + `post_type` or `taxonomy` |
| **Backend** | New REST endpoint with search + source params |
| **Frontend** | Async select (e.g. react-select async) + debounce |
| **Value** | ID (or array of IDs for multiple) |

---

## Related

- [select](./select.md) – Static options select
- [media](./media.md) – Media library picker (another WP-backed picker)
