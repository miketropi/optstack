/* eslint-disable @typescript-eslint/no-explicit-any */
// Re-export ReactDOM client from WordPress global (for dev mode)
const ReactDOM = (window as any).ReactDOM

export const createRoot = ReactDOM.createRoot
export default ReactDOM
