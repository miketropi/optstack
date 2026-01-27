import { defineConfig, Plugin } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// Plugin to redirect React imports to WordPress globals in dev mode
function wpReactExternals(): Plugin {
  const externalsDir = path.resolve(__dirname, 'src/wp-externals')
  
  return {
    name: 'wp-react-externals',
    enforce: 'pre',
    resolveId(source) {
      if (source === 'react') {
        return path.join(externalsDir, 'react.ts')
      }
      if (source === 'react-dom' || source === 'react-dom/client') {
        return path.join(externalsDir, 'react-dom-client.ts')
      }
      if (source === 'react/jsx-runtime') {
        return path.join(externalsDir, 'jsx-runtime.ts')
      }
      if (source === 'react/jsx-dev-runtime') {
        return path.join(externalsDir, 'jsx-dev-runtime.ts')
      }
      return null
    },
  }
}

export default defineConfig(({ command, mode }) => {
  const isDev = command === 'serve'

  return {
    plugins: [
      // Only use WP externals plugin in dev mode
      ...(isDev ? [wpReactExternals()] : []),
      // Only use React plugin in production (for build)
      ...(!isDev ? [react()] : []),
    ],
    // In dev mode, use esbuild for JSX (no Fast Refresh issues)
    esbuild: isDev ? {
      jsx: 'transform',
      jsxFactory: 'React.createElement',
      jsxFragment: 'React.Fragment',
    } : undefined,
    resolve: {
      alias: {
        '@': path.resolve(__dirname, './src'),
      },
    },
    // Dev server configuration
    server: {
      port: 5173,
      strictPort: true,
      cors: true,
      hmr: {
        host: 'localhost',
        port: 5173,
      },
      watch: {
        usePolling: true,
      },
    },
    build: {
      outDir: 'dist',
      emptyOutDir: true,
      lib: {
        entry: path.resolve(__dirname, 'src/main.tsx'),
        name: 'OptStackAdmin',
        formats: ['iife'],
        fileName: () => 'optstack-admin.js',
      },
      rollupOptions: {
        external: [
          'react',
          'react-dom',
          '@wordpress/element',
          '@wordpress/components',
          '@wordpress/block-editor',
          '@wordpress/rich-text',
          '@wordpress/data',
          '@wordpress/api-fetch',
          '@wordpress/i18n',
        ],
        output: {
          globals: {
            'react': 'React',
            'react-dom': 'ReactDOM',
            '@wordpress/element': 'wp.element',
            '@wordpress/components': 'wp.components',
            '@wordpress/block-editor': 'wp.blockEditor',
            '@wordpress/rich-text': 'wp.richText',
            '@wordpress/data': 'wp.data',
            '@wordpress/api-fetch': 'wp.apiFetch',
            '@wordpress/i18n': 'wp.i18n',
          },
          assetFileNames: (assetInfo) => {
            if (assetInfo.name === 'style.css') {
              return 'optstack-main.css'
            }
            return 'optstack-[name].[ext]'
          },
        },
      },
      sourcemap: true,
      minify: 'esbuild',
    },
    define: {
      'process.env.NODE_ENV': JSON.stringify(mode),
    },
  }
})
