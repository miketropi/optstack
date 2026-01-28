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
      // Handle all react imports
      if (source === 'react') {
        return path.join(externalsDir, 'react.ts')
      }
      if (source === 'react/jsx-runtime') {
        return path.join(externalsDir, 'jsx-runtime.ts')
      }
      if (source === 'react/jsx-dev-runtime') {
        return path.join(externalsDir, 'jsx-dev-runtime.ts')
      }
      
      // Handle all react-dom imports
      if (source === 'react-dom' || source === 'react-dom/client') {
        return path.join(externalsDir, 'react-dom-client.ts')
      }
      
      return null
    },
  }
}

export default defineConfig(({ command, mode }) => {
  const isDev = command === 'serve'

  return {
    plugins: [
      // Use WP externals plugin in dev mode
      ...(isDev ? [wpReactExternals()] : []),
      // Use React plugin in production
      ...(!isDev ? [react()] : []),
    ],
    // In dev mode, use esbuild for JSX
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
    // Dependency optimization for dev mode
    optimizeDeps: {
      // Force include these - Vite will pre-bundle them
      include: isDev ? ['react-select', 'react-color'] : [],
      esbuildOptions: isDev ? {
        // Tell esbuild to use externals for react when bundling react-select
        plugins: [{
          name: 'react-externals',
          setup(build) {
            // Mark react as external
            build.onResolve({ filter: /^react$/ }, () => ({
              path: 'react',
              namespace: 'react-external',
            }))
            build.onResolve({ filter: /^react-dom/ }, () => ({
              path: 'react-dom',
              namespace: 'react-external',
            }))
            // Load external react from global
            build.onLoad({ filter: /.*/, namespace: 'react-external' }, (args) => ({
              contents: args.path === 'react' 
                ? 'module.exports = window.React'
                : 'module.exports = window.ReactDOM',
              loader: 'js',
            }))
          },
        }],
      } : undefined,
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
        // Externalize react from ALL imports (including node_modules)
        external: (id) => {
          if (id === 'react' || id.startsWith('react/')) return true
          if (id === 'react-dom' || id.startsWith('react-dom/')) return true
          if (id.startsWith('@wordpress/')) return true
          return false
        },
        output: {
          globals: (id) => {
            if (id === 'react' || id.startsWith('react/')) return 'React'
            if (id === 'react-dom' || id.startsWith('react-dom/')) return 'ReactDOM'
            if (id === '@wordpress/element') return 'wp.element'
            if (id === '@wordpress/components') return 'wp.components'
            if (id === '@wordpress/block-editor') return 'wp.blockEditor'
            if (id === '@wordpress/rich-text') return 'wp.richText'
            if (id === '@wordpress/data') return 'wp.data'
            if (id === '@wordpress/api-fetch') return 'wp.apiFetch'
            if (id === '@wordpress/i18n') return 'wp.i18n'
            return id
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
