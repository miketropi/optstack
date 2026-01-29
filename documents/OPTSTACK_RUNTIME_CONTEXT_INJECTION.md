# OptStack Runtime Context Injection

## ✅ Status: IMPLEMENTED

**Last Updated:** 2026-01-28  
**Implementation Files:**
- `src/Support/Context.php` - Runtime context object
- `src/WordPress/Bootstrap.php` - Context injection & WordPress adapter
- `optstack.php` - Host entry point with context injection
- `src/WordPress/Admin.php` - Updated to use context for asset loading
- `examples/runtime-context-injection-example.php` - Usage examples

---

## 🎯 Goal

Design and implement a **Runtime Context Injection** mechanism for OptStack to ensure:

* OptStack works as a **pure PHP Composer library** ✅
* No direct dependency on the WordPress plugin lifecycle ✅
* Multiple hosts (plugins / themes) can use OptStack **without constant conflicts** ✅
* Easy to extend, easy to test, and AI-agent friendly ✅

> ⚠️ Do NOT define WordPress plugin constants (`OPTSTACK_DIR`, `OPTSTACK_URL`, etc.) inside the core library.

---

## 🧠 Core Design Principle

> **Plugin / Theme is the Host – OptStack is the Guest**

* The Host is responsible for:

  * Determining `file`, `dir`, `url`, `version`
  * Injecting runtime context into OptStack

* OptStack only:

  * Receives the context
  * Stores the context
  * Uses the context when needed (enqueue assets, load files, expose REST APIs)

---

## 📦 Architecture Overview

```
Host Plugin / Theme
        │
        ▼
Bootstrap::boot(config)
        │
        ▼
Context Object (runtime)
        │
        ▼
OptStack WordPress Adapter
```

---

## 1️⃣ Context Object

### File

```
src/Support/Context.php
```

### Responsibilities

* Store runtime information provided by the host
* Must NOT call any WordPress functions
* Should be immutable after initialization

### Required Properties

| Property | Type   | Description                  |
| -------- | ------ | ---------------------------- |
| baseFile | string | Main plugin / theme file     |
| baseDir  | string | Absolute path to host root   |
| baseUrl  | string | URL corresponding to baseDir |
| version  | string | Version provided by the host |

### Reference Implementation

```php
namespace OptStack\Support;

final class Context
{
    public string $baseFile;
    public string $baseDir;
    public string $baseUrl;
    public string $version;

    public function __construct(array $config)
    {
        $this->baseFile = $config['file'] ?? '';
        $this->baseDir  = $config['dir'] ?? '';
        $this->baseUrl  = $config['url'] ?? '';
        $this->version  = $config['version'] ?? 'dev';
    }
}
```

---

## 2️⃣ WordPress Bootstrap

### File

```
src/WordPress/Bootstrap.php
```

### Responsibilities

* Single entry point for WordPress integration
* Receive runtime config from the host
* Initialize Context
* Register hooks / enqueue assets / expose REST APIs

### Rules

* Must NOT assume WordPress is always available
* Must fail silently if `add_action` does not exist
* Must NOT define global constants

### Reference Implementation

```php
namespace OptStack\WordPress;

use OptStack\Support\Context;

class Bootstrap
{
    protected static ?Context $context = null;

    public static function boot(array $config = []): void
    {
        self::$context = new Context($config);

        if (!function_exists('add_action')) {
            return;
        }

        // Example: register admin assets
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
    }

    public static function context(): ?Context
    {
        return self::$context;
    }

    public static function enqueueAssets(): void
    {
        $ctx = self::$context;
        if (!$ctx) {
            return;
        }

        wp_enqueue_script(
            'optstack-ui',
            $ctx->baseUrl . 'frontend/dist/optstack-ui.js',
            ['wp-element'],
            $ctx->version,
            true
        );
    }
}
```

---

## 3️⃣ Host Integration (Plugin / Theme)

### Plugin Entry File

```php
<?php
/*
Plugin Name: OptStack Dev Plugin
*/

require_once __DIR__ . '/vendor/autoload.php';

\OptStack\WordPress\Bootstrap::boot([
    'file'    => __FILE__,
    'dir'     => plugin_dir_path(__FILE__),
    'url'     => plugin_dir_url(__FILE__),
    'version' => '0.1.1',
]);
```

### Notes for AI Agents

* Context MUST be injected by the host
* OptStack MUST NOT attempt to detect plugin paths on its own

---

## 4️⃣ Asset Loading Rules

AI Agents MUST follow:

* Use `$context->baseUrl` for URLs
* Use `$context->baseDir` for filesystem access
* Never call `plugin_dir_path()` or `plugin_dir_url()` inside the core library

---

## 5️⃣ Backward Compatibility (Optional)

If the host needs constants:

```php
$ctx = \OptStack\WordPress\Bootstrap::context();

if (!defined('OPTSTACK_DIR')) {
    define('OPTSTACK_DIR', $ctx->baseDir);
}
```

⚠️ **Must NOT be implemented inside the core library**

---

## 6️⃣ Validation Checklist

* [x] OptStack works when installed via Composer
* [x] No constants are defined inside `src/` (constants only in host entry point)
* [x] OptStack can be used by multiple plugins simultaneously
* [x] Frontend assets load with correct URLs from context
* [x] No fatal errors when running outside WordPress (fails silently)
* [x] `Bootstrap::context()` returns the injected context
* [x] `Admin.php` uses context for asset loading
* [x] All file/URL operations use context methods

---

## 7️⃣ Implementation Notes

### Files Modified

**Created:**
- `src/Support/Context.php` - Immutable context object with helper methods
  - `path()` - Build absolute paths
  - `url()` - Build URLs
  - `getAssetsDir()` / `getAssetsUrl()` - Asset helpers
  - `fileExists()` - Check file existence
  - `toArray()` - Debug output

**Updated:**
- `src/WordPress/Bootstrap.php`
  - Added `protected static ?Context $context`
  - Modified `boot()` to accept config array
  - Added `context()` static method
  - Fails silently if WordPress not available

- `src/WordPress/Admin.php`
  - Replaced all `OPTSTACK_DIR` with `$context->path()`
  - Replaced all `OPTSTACK_URL` with `$context->url()`
  - Replaced all `OPTSTACK_VERSION` with `$context->version`
  - Added context null checks

- `optstack.php` (host entry point)
  - Injects context via `Bootstrap::boot(['file' => ..., 'dir' => ..., 'url' => ..., 'version' => ...])`
  - Constants still defined here (for backward compatibility in this plugin)

### Usage Example

```php
// In your plugin/theme
\OptStack\WordPress\Bootstrap::boot([
    'file' => __FILE__,
    'dir' => plugin_dir_path(__FILE__),
    'url' => plugin_dir_url(__FILE__),
    'version' => '1.0.0',
]);

// Access context anywhere
$context = \OptStack\WordPress\Bootstrap::context();
echo $context->url('assets/logo.png');
```

### Backward Compatibility

The main plugin file (`optstack.php`) still defines constants for backward compatibility:
- `OPTSTACK_FILE`
- `OPTSTACK_DIR`
- `OPTSTACK_URL`
- `OPTSTACK_VERSION`

These constants are ONLY defined in the host entry point, never in the core library (`src/`).

When using OptStack as a Composer package, hosts provide their own context and no constants are needed.

---

## 8️⃣ Future Extensions (Consider for v2)

* Multi-context support (multiple hosts tracking)
* CLI / Headless contexts
* `ContextInterface` + `ContextResolver` pattern
* Separate package: `optstack/wordpress-adapter`
* Context caching/memoization
* Development/production context modes

---

## 🧩 Summary

Runtime Context Injection is a **foundational architectural concept** of OptStack.

**Key Principles:**

* Treat OptStack as framework core
* Treat WordPress as an adapter
* Never hardcode environment assumptions
* Host provides context, OptStack consumes it

> **No constants. No magic globals. Only explicit context.**

**Example Files:**
- See `examples/runtime-context-injection-example.php` for comprehensive usage examples
- See `optstack.php` for reference host implementation
