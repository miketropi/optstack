import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

/**
 * Vite config for building the OptStack block editor script.
 * Run: npm run build:block
 */
export default defineConfig(({ mode }) => ({
  plugins: [
    react({ jsxRuntime: 'classic' }),
  ],
  esbuild: {
    jsx: 'transform',
    jsxFactory: 'React.createElement',
    jsxFragment: 'React.Fragment',
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    lib: {
      entry: path.resolve(__dirname, 'src/blocks/index.tsx'),
      name: 'OptStackBlock',
      formats: ['iife'],
      fileName: () => 'optstack-block.js',
    },
    rollupOptions: {
      external: (id) => {
        if (id === 'react' || id.startsWith('react/')) return true
        if (id === 'react-dom' || id.startsWith('react-dom/')) return true
        if (id.startsWith('@wordpress/')) return true
        return false
      },
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'optstack-block.css'
          return 'optstack-[name].[ext]'
        },
        globals: (id: string) => {
          if (id === 'react' || id.startsWith('react/')) return 'React'
          if (id === 'react-dom' || id.startsWith('react-dom/')) return 'ReactDOM'
          if (id === '@wordpress/element') return 'wp.element'
          if (id === '@wordpress/components') return 'wp.components'
          if (id === '@wordpress/block-editor') return 'wp.blockEditor'
          if (id === '@wordpress/rich-text') return 'wp.richText'
          if (id === '@wordpress/data') return 'wp.data'
          if (id === '@wordpress/api-fetch') return 'wp.apiFetch'
          if (id === '@wordpress/i18n') return 'wp.i18n'
          if (id === '@wordpress/blocks') return 'wp.blocks'
          if (id === '@wordpress/server-side-render') return 'wp.serverSideRender'
          return id
        },
      },
    },
    sourcemap: true,
    minify: 'esbuild',
  },
  define: {
    'process.env.NODE_ENV': JSON.stringify(mode),
  },
}))
