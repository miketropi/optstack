# OptStack - Tech Context

## Technology Stack

### Backend (PHP)
- **PHP 8.1+** minimum requirement
- **PSR-4 autoloading** via Composer
- **Namespace**: `OptStack\`

### Frontend
- **React 18+**
- **TypeScript 5+**
- **TailwindCSS 3+**
- **Vite** for build tooling
- **Zod** (optional) for runtime validation

### WordPress
- **WordPress 6.0+** minimum
- Uses native WP APIs (no custom tables)
- Compatible with multisite

## Directory Structure

```
optstack/
├── composer.json
├── src/                    # PHP SOURCE
│   ├── Core/               # Pure PHP – NO WordPress
│   │   ├── Stack/
│   │   ├── Field/
│   │   ├── Condition/
│   │   ├── Path/
│   │   ├── Contract/
│   │   └── Support/
│   │
│   ├── WordPress/          # WP Integration
│   │   ├── Store/
│   │   ├── Renderer/
│   │   ├── Hook/
│   │   └── Bootstrap.php
│   │
│   ├── Schema/             # Schema export
│   │   ├── SchemaExporter.php
│   │   └── SchemaNormalizer.php
│   │
│   └── OptStack.php        # Main Facade
│
├── plugin/                 # WordPress Plugin Wrapper
│   └── optstack.php
│
├── frontend/               # React Admin UI
│   ├── package.json
│   ├── tsconfig.json
│   ├── tailwind.config.js
│   ├── vite.config.ts
│   └── src/
│
├── tests/
│   ├── Core/
│   └── WordPress/
│
└── memory-bank/
```

## Development Setup

### Backend
```bash
cd optstack
composer install
```

### Frontend
```bash
cd optstack/frontend
npm install
npm run dev
```

### Testing
```bash
# Unit tests (Core)
composer test

# WordPress integration tests
composer test:wp
```

## Key Dependencies

### Composer (PHP)
- `psr/container` - DI container interface
- `phpunit/phpunit` - Testing (dev)

### NPM (Frontend)
- `react`, `react-dom`
- `typescript`
- `tailwindcss`
- `@wordpress/api-fetch` - WP REST client
- `vite`

## Coding Standards

### PHP
- PSR-12 coding style
- Strict types enabled
- Interface-driven design
- No WordPress in Core namespace

### TypeScript
- Strict mode enabled
- No `any` types
- Functional components
- Custom hooks for logic
