export type Breakpoint = 'desktop' | 'tablet' | 'mobile'

export const BREAKPOINTS: Breakpoint[] = ['desktop', 'tablet', 'mobile']

export interface ResponsiveValue {
  desktop?: unknown
  tablet?: unknown
  mobile?: unknown
}

export interface TokenDefinition {
  type: 'string' | 'number' | 'object'
  control: string
  responsive?: boolean
  units?: string[]
  options?: (string | number)[]
  min?: number
  max?: number
  step?: number
  keys?: string[]
}

export function isResponsiveValue(val: unknown): val is ResponsiveValue {
  if (typeof val !== 'object' || val === null || Array.isArray(val)) return false
  const keys = Object.keys(val)
  return keys.some((k) => k === 'desktop' || k === 'tablet' || k === 'mobile')
}

export function resolveBreakpointValue(val: unknown, breakpoint: Breakpoint): unknown {
  if (!isResponsiveValue(val)) return val
  return val[breakpoint] ?? val.desktop ?? val.tablet ?? val.mobile
}

export interface DesignGroupSchema {
  id: string
  label: string
  applies_to: string[]
  supports: string[]
  variant: boolean
  tokens: Record<string, TokenDefinition>
}

export interface DesignPresetVariant {
  id: string
  label?: string
  [tokenKey: string]: unknown
}

export type DesignGroupValue =
  | Record<string, unknown>
  | DesignPresetVariant[]

export interface DesignPresetData {
  id: string
  label: string
  builtin?: boolean
  base?: string
  tokens: Record<string, DesignGroupValue>
}

export interface DesignPresetFieldValue {
  active_preset: string
  overrides?: Record<string, string | number>
  presets?: DesignPresetData[]
}

export interface DesignPresetSystemData {
  groups: Record<string, DesignGroupSchema>
  presets: DesignPresetData[]
}
