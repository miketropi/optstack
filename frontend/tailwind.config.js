/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        // WordPress admin colors
        wp: {
          primary: '#2271b1',
          'primary-hover': '#135e96',
          'primary-focus': '#043959',
          secondary: '#72aee6',
          accent: '#3582c4',
          success: '#00a32a',
          warning: '#dba617',
          error: '#d63638',
          info: '#72aee6',
        },
      },
      fontFamily: {
        sans: [
          '-apple-system',
          'BlinkMacSystemFont',
          'Segoe UI',
          'Roboto',
          'Oxygen-Sans',
          'Ubuntu',
          'Cantarell',
          'Helvetica Neue',
          'sans-serif',
        ],
      },
    },
  },
  plugins: [],
  // Prefix to avoid conflicts with WordPress admin styles
  prefix: 'os-',
}
