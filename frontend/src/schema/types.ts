/**
 * OptStack Schema Types
 *
 * TypeScript definitions for OptStack schema structures.
 * These types represent the JSON schema exported from PHP.
 */

/**
 * Conditional visibility rule.
 */
export interface Condition {
  field: string
  operator: '==' | '!=' | '>' | '<' | '>=' | '<=' | 'contains' | 'not_contains' | 'empty' | 'not_empty' | 'in' | 'not_in'
  value: unknown
  relation: 'AND' | 'OR'
}

/**
 * Field option (for select, radio, etc.).
 */
export interface FieldOption {
  value: string | number | boolean
  label: string
  description?: string
}

/**
 * Field schema.
 */
export interface FieldSchema {
  key: string
  type: string
  label: string
  default?: unknown
  description?: string
  options?: FieldOption[]
  attributes?: Record<string, unknown>
  conditions?: Condition[]
}

/**
 * Field group schema.
 */
export interface FieldGroupSchema {
  key: string
  label: string
  description?: string
  repeatable: boolean
  collapsible?: boolean
  minItems?: number
  maxItems?: number
  fields?: Record<string, FieldSchema>
  groups?: Record<string, FieldGroupSchema>
  conditions?: Condition[]
}

/**
 * Tab schema.
 */
export interface TabSchema {
  key: string
  label: string
  icon?: string
  description?: string
  priority: number
  fields?: Record<string, FieldSchema>
  groups?: Record<string, FieldGroupSchema>
  conditions?: Condition[]
}

/**
 * Stack schema.
 */
export interface StackSchema {
  id: string
  context: 'options' | 'post' | 'post_type' | 'term' | 'taxonomy' | 'user'
  label: string
  description?: string
  postType?: string
  taxonomy?: string
  fields?: Record<string, FieldSchema>
  groups?: Record<string, FieldGroupSchema>
  tabs?: Record<string, TabSchema>
}

/**
 * Stack data response from REST API.
 */
export interface StackDataResponse {
  schema: StackSchema
  data: Record<string, unknown>
}

/**
 * Stack state for form management.
 */
export interface StackState {
  data: Record<string, unknown>
  isDirty: boolean
  isSaving: boolean
  errors: Record<string, string[]>
}

/**
 * Field renderer props.
 */
export interface FieldRendererProps {
  field: FieldSchema
  value: unknown
  onChange: (value: unknown) => void
  disabled?: boolean
  error?: string
}

/**
 * Group renderer props.
 */
export interface GroupRendererProps {
  group: FieldGroupSchema
  data: Record<string, unknown>
  onChange: (key: string, value: unknown) => void
  disabled?: boolean
  errors?: Record<string, string[]>
}

/**
 * Repeater item.
 */
export interface RepeaterItem {
  _id: string
  [key: string]: unknown
}

/**
 * API response wrapper.
 */
export interface ApiResponse<T> {
  success: boolean
  data?: T
  error?: string
}
