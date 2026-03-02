export interface TokenDefinition {
  type: 'string' | 'number' | 'object'
  control: string
  units?: string[]
  options?: (string | number)[]
  min?: number
  max?: number
  step?: number
  keys?: string[]
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
