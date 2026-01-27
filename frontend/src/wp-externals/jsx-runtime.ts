/* eslint-disable @typescript-eslint/no-explicit-any */
// Re-export JSX runtime from WordPress's React global (for dev mode)
const React = (window as any).React

export const Fragment = React.Fragment
export const jsx = React.createElement
export const jsxs = React.createElement
