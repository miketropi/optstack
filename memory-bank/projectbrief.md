# OptStack - Project Brief

## Project Overview

**OptStack** is a WordPress Data Stack Framework - a PHP framework for defining, storing, and managing structured data in WordPress using a unified, extensible stack-based model.

## Core Requirements

### Philosophy
- **Data-first, UI-agnostic** - Focus on data modeling, not UI
- **Native WordPress compatibility** - Works with `get_option`, `get_post_meta`, `get_term_meta`
- **Unified data model** - Same field syntax across Options, Posts, Terms
- **Composable & extensible** - Interface-driven architecture
- **Future-proof** - Ready for Headless WordPress and REST workflows

### What is a "Data Stack"?
A Data Stack represents a logical root of structured data stored in WordPress:
- Has a **storage backend** (options, post meta, term meta)
- Contains **groups** and **fields**
- Supports **nested**, **repeatable**, and **conditional** data
- Think of it as **schema + storage adapter**, not a UI screen

## Architecture Layers

### 1. Core (Framework Layer)
Pure PHP. No WordPress dependencies.
- Stack definition
- Field schema
- Grouping & nesting
- Conditional metadata
- Path resolution
- Sanitization rules

### 2. Store Adapters (WordPress Integration)
Bridges OptStack with WordPress storage.
- `OptionsStore` - `wp_options`
- `PostStore` - `wp_postmeta`
- `TermStore` - `wp_termmeta`

### 3. Renderers (UI / Admin / API)
Optional and replaceable.
- Admin UI rendering
- Save hooks
- REST integration

## Supported Data Contexts

| Context     | Storage       | WP API            |
|-------------|---------------|-------------------|
| Options     | `wp_options`  | `get_option()`    |
| Post        | `wp_postmeta` | `get_post_meta()` |
| Post Type   | `wp_postmeta` | `get_post_meta()` |
| Term        | `wp_termmeta` | `get_term_meta()` |
| User        | `wp_usermeta` | `get_user_meta()` |

## Success Criteria

1. Core layer is fully unit-testable without WordPress
2. Clean separation between data layer and UI
3. Fields defined once, work everywhere
4. Modern React admin UI consuming schema contracts
5. Full TypeScript safety in frontend
