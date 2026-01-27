# OptStack

> **WordPress Data Stack Framework** — A PHP framework for defining, storing, and managing structured data in WordPress using a unified, extensible stack-based model.

## Features

- **Data-first, UI-agnostic** — Focus on data modeling, not UI
- **Native WordPress compatibility** — Works with `get_option()`, `get_post_meta()`, `get_term_meta()`
- **Unified data model** — Same field syntax across Options, Posts, Terms, Users
- **Composable & extensible** — Interface-driven architecture
- **Future-proof** — Ready for Headless WordPress and REST workflows
- **Modern Admin UI** — React + TypeScript + TailwindCSS frontend

## Requirements

- PHP 8.1+
- WordPress 6.0+
- Node.js 18+ (for frontend development)

## Installation

### As a WordPress Plugin

1. Clone or download to `wp-content/plugins/optstack/`
2. Run `composer install` in the plugin directory
3. Activate the plugin in WordPress admin

### For Frontend Development

```bash
cd wp-content/plugins/optstack/frontend
npm install
npm run build    # Production build
```

### Development Mode (Hot Reload)

For live reloading during frontend development:

1. Add to `wp-config.php`:
   ```php
   define('OPTSTACK_DEV_MODE', true);
   ```

2. Start the dev server:
   ```bash
   cd wp-content/plugins/optstack/frontend
   npm run dev
   ```

3. Open your WordPress admin page - CSS and component changes update instantly!

## Quick Start

### Define an Options Stack

```php
use OptStack\OptStack;

// Register on init
add_action('optstack_init', function() {
    OptStack::make('site_settings')
        ->forOptions()
        ->label('Site Settings')
        ->define(function ($stack) {
            $stack->field('site_color', [
                'type' => 'text',
                'label' => 'Primary Color',
                'default' => '#000000',
            ]);
            
            $stack->group('social', function ($group) {
                $group->field('twitter', ['type' => 'text']);
                $group->field('facebook', ['type' => 'text']);
            });
        });
});

// Access data using native WordPress
$settings = get_option('site_settings');
// ['site_color' => '#000000', 'social' => ['twitter' => '...', 'facebook' => '...']]
```

### Define a Post Type Stack

```php
add_action('optstack_init', function() {
    OptStack::make('product_data')
        ->forPostType('product')
        ->label('Product Data')
        ->define(function ($stack) {
            $stack->group('pricing', function ($group) {
                $group->field('price', ['type' => 'number']);
                $group->field('currency', [
                    'type' => 'select',
                    'options' => ['USD' => 'US Dollar', 'EUR' => 'Euro'],
                ]);
            });
        });
});

// Access via post meta
$product_data = get_post_meta($post_id, 'product_data', true);
```

### Define a Taxonomy Stack

```php
add_action('optstack_init', function() {
    OptStack::make('category_settings')
        ->forTaxonomy('category')
        ->define(function ($stack) {
            $stack->field('icon', ['type' => 'text']);
            $stack->field('color', ['type' => 'text']);
        });
});
```

## Field Types

| Type | Description | Options |
|------|-------------|---------|
| `text` | Single-line text input | `placeholder` |
| `number` | Numeric input | `min`, `max`, `step` |
| `textarea` | Multi-line text | `rows` |
| `select` | Dropdown select | `options` |
| `boolean` | Toggle/checkbox | — |

## Groups & Repeatables

```php
$stack->group('features', function ($group) {
    $group->repeatable(1, 10); // min 1, max 10 items
    
    $group->field('title', ['type' => 'text']);
    $group->field('enabled', ['type' => 'boolean']);
});

// Data structure:
// ['features' => [
//     ['title' => 'Feature A', 'enabled' => true],
//     ['title' => 'Feature B', 'enabled' => false],
// ]]
```

## Conditional Fields

```php
$stack->field('enable_advanced', ['type' => 'boolean']);

$stack->field('advanced_option', [
    'type' => 'text',
    'conditions' => [
        ['field' => 'enable_advanced', 'operator' => '==', 'value' => true]
    ]
]);
```

## REST API

OptStack exposes a REST API for frontend consumption:

- `GET /wp-json/optstack/v1/stacks` — List all stacks
- `GET /wp-json/optstack/v1/stacks/{id}` — Get stack schema
- `GET /wp-json/optstack/v1/stacks/{id}/data` — Get stack data
- `POST /wp-json/optstack/v1/stacks/{id}/data` — Save stack data

## Architecture

```
┌─────────────────────────────────────────┐
│           Renderers (UI/API)            │  ← React Admin, REST
├─────────────────────────────────────────┤
│         Store Adapters (WP)             │  ← OptionsStore, PostStore
├─────────────────────────────────────────┤
│           Core Framework                │  ← Pure PHP, no WP
└─────────────────────────────────────────┘
```

- **Core** — Pure PHP, no WordPress dependencies, fully unit testable
- **WordPress** — Store adapters and hooks
- **Frontend** — React + TypeScript admin UI

## Directory Structure

```
optstack/
├── src/
│   ├── Core/           # Pure PHP framework
│   ├── WordPress/      # WP integration
│   ├── Schema/         # Schema export
│   └── OptStack.php    # Main facade
├── frontend/
│   ├── src/            # React/TypeScript source
│   ├── dist/           # Built assets
│   └── vite.config.ts  # Build configuration
├── examples/           # Usage examples
├── tests/              # PHPUnit tests
└── optstack.php        # Plugin bootstrap
```

## Development

```bash
# Backend
composer install
composer test

# Frontend
cd frontend
npm install
npm run build    # Production build
npm run dev      # Development server (requires OPTSTACK_DEV_MODE)
```

### Frontend Dev Mode

OptStack supports hot module replacement (HMR) for rapid frontend development.

**Setup:**

1. Enable dev mode in `wp-config.php`:
   ```php
   define('OPTSTACK_DEV_MODE', true);
   // Optional: custom port
   // define('OPTSTACK_DEV_SERVER', 'http://localhost:3000');
   ```

2. Start Vite dev server:
   ```bash
   cd frontend && npm run dev
   ```

3. Refresh your WordPress admin page - changes will update live!

**How it works:**
- In dev mode, WordPress loads assets from Vite's dev server (`localhost:5173`)
- CSS changes apply instantly via HMR
- JS/TSX changes trigger a page refresh
- In production mode (default), built assets from `frontend/dist/` are used

## License

MIT License
