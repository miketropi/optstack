/* eslint-disable @typescript-eslint/no-explicit-any */
// Re-export ReactDOM from WordPress global (for dev mode)
const ReactDOM = (window as any).ReactDOM
export default ReactDOM
export const { createRoot, createPortal, flushSync } = ReactDOM
