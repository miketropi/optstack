# OptStack Development Mode

Development mode enables hot module replacement (HMR) so you can see changes instantly without rebuilding.

## Quick Start

### 1. Enable Dev Mode in WordPress

Add this to your `wp-config.php` (before `/* That's all, stop editing! */`):

```php
define('OPTSTACK_DEV_MODE', true);
```

### 2. Start the Vite Dev Server

```bash
cd wp-content/plugins/optstack/frontend
npm run dev
```

The dev server runs on `http://localhost:5173` by default.

### 3. Refresh WordPress Admin

Open your OptStack admin page. Changes to React components and CSS will update automatically!

## Configuration

### Custom Dev Server URL

If you need a different port or host:

```php
define('OPTSTACK_DEV_MODE', true);
define('OPTSTACK_DEV_SERVER', 'http://localhost:3000');
```

Then update `frontend/vite.config.ts`:

```ts
server: {
  port: 3000,
  // ...
}
```

## Switching Back to Production

1. Stop the dev server (Ctrl+C)
2. Remove or set `OPTSTACK_DEV_MODE` to `false` in `wp-config.php`:

```php
define('OPTSTACK_DEV_MODE', false);
```

3. Rebuild if you made changes:

```bash
npm run build
```

## Troubleshooting

### "React is not defined" Error

Make sure the Vite dev server is running (`npm run dev`) before loading the WordPress admin page.

### Changes Not Appearing

1. Check the browser console for errors
2. Make sure `OPTSTACK_DEV_MODE` is `true` in `wp-config.php`
3. Hard refresh the page (Cmd+Shift+R or Ctrl+Shift+R)

### CORS Errors

The Vite config already includes CORS settings. If you're using a custom domain:

```ts
// vite.config.ts
server: {
  cors: true,
  hmr: {
    host: 'your-local-domain.test',
  },
}
```
