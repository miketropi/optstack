/* eslint-disable @typescript-eslint/no-explicit-any */
// Re-export React from WordPress global (for dev mode)
const React = (window as any).React
export default React
export const {
  useState,
  useEffect,
  useCallback,
  useMemo,
  useRef,
  useContext,
  useReducer,
  useLayoutEffect,
  useId,
  useSyncExternalStore,
  useInsertionEffect,
  useDebugValue,
  useDeferredValue,
  useTransition,
  useImperativeHandle,
  createContext,
  createElement,
  createRef,
  forwardRef,
  Fragment,
  StrictMode,
  Suspense,
  lazy,
  memo,
  Children,
  cloneElement,
  isValidElement,
  Component,
  PureComponent,
  version,
} = React

// Additional exports for compatibility
export const startTransition = React.startTransition
export const act = React.act
