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
  createContext,
  createElement,
  Fragment,
  StrictMode,
  Suspense,
  lazy,
  memo,
  forwardRef,
  Children,
  cloneElement,
  isValidElement,
} = React
