# OptStack - Active Context

## Current Focus

**Foundation Complete** - Core framework is built and ready for use.

## Recent Changes

- Created complete PHP backend with Core and WordPress layers
- Implemented React frontend with TypeScript and TailwindCSS
- Set up REST API for schema and data management
- Created example usage documentation
- Added Admin class for WordPress admin menu integration
- Fixed TypeScript build errors
- Integrated frontend with WordPress (enqueue scripts, localize data)

## Active Development

### Phase 1: Foundation ✅ Complete

All foundation components are now in place:
- Core PHP classes (Stack, Field, Condition, Support)
- WordPress integration (Stores, Bootstrap, REST API)
- Schema export system
- React frontend with field components
- Vite build configuration

### Next Steps

1. Run `composer install` in plugin directory
2. Run `npm install && npm run build` in frontend directory
3. Activate plugin in WordPress
4. Register stacks using `optstack_init` hook
5. Add admin menu pages for settings UI
6. Implement meta box rendering for post types

## Key Decisions

### Decided
- PSR-4 autoloading with `OptStack\` namespace
- Core layer is WP-independent and unit testable
- Fields are data descriptors, not UI components
- Schema-driven frontend rendering
- Vite for frontend build tooling
- TailwindCSS with `os-` prefix to avoid conflicts

### To Decide
- Specific sanitization strategy per field type
- Validation error handling approach
- Admin UI integration (menu pages, meta boxes)

## Working Patterns

### Stack Definition DSL
```php
OptStack::make('stack_name')
    ->forOptions()  // or forPostType(), forTaxonomy()
    ->define(function ($stack) {
        $stack->field('key', ['type' => 'text']);
        $stack->group('group_key', function ($group) {
            $group->field('nested_key');
        });
    });
```

### REST Endpoints
- `GET /wp-json/optstack/v1/stacks` - List all stacks
- `GET /wp-json/optstack/v1/stacks/{id}` - Get stack schema
- `GET /wp-json/optstack/v1/stacks/{id}/data` - Get stack data
- `POST /wp-json/optstack/v1/stacks/{id}/data` - Save stack data

## Notes

- Core classes are unit testable without WordPress
- Frontend consumes JSON schema via REST
- Initial field types: text, number, select, boolean, textarea
- Repeater groups fully functional with add/remove/reorder
