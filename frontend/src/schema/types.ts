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
  /** When true, value is { desktop, tablet, mobile } and UI shows a mode switcher */
  responsive?: boolean
}

/**
 * UI configuration for deferred groups.
 */
export interface DeferredGroupUi {
  /** Button label to open deferred group */
  triggerLabel?: string
  /** How to render deferred content */
  render?: 'modal' | 'drawer' | 'panel'
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
  /** Layout style */
  layout?: 'inline' | 'box'
  minItems?: number
  maxItems?: number
  /**
   * Whether this group uses deferred rendering.
   * Deferred groups render a trigger button instead of inline fields.
   * Fields are shown only when triggered (e.g., in a modal).
   */
  deferred?: boolean
  /** UI configuration for deferred rendering */
  ui?: DeferredGroupUi
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
  context: 'options' | 'post' | 'post_type' | 'term' | 'taxonomy' | 'user' | 'block'
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

// =============================================================================
// Visual Block Builder Types
// =============================================================================

/**
 * A single block in the visual builder.
 */
export interface VisualBuilderBlock {
  /** Unique identifier for this block instance */
  id: string
  /** Block type (e.g., 'logo', 'menu', 'button') */
  type: string
  /** Block-specific properties */
  props: Record<string, unknown>
}

/**
 * Layout configuration for the visual builder.
 */
export interface VisualBuilderLayout {
  /** Layout direction */
  direction?: 'row' | 'column'
  /** Gap between blocks (in pixels) */
  gap?: number
  /** Horizontal alignment */
  align?: 'start' | 'center' | 'end' | 'stretch' | 'space-between'
  /** Vertical alignment */
  justify?: 'start' | 'center' | 'end' | 'stretch'
}

/**
 * The complete value stored by a visual builder field.
 */
export interface VisualBuilderValue {
  /** Array of blocks in order */
  blocks: VisualBuilderBlock[]
  /** Layout configuration */
  layout: VisualBuilderLayout
}

/**
 * Block type definition (for the block registry).
 */
export interface BlockTypeDefinition {
  /** Block type identifier */
  type: string
  /** Display label */
  label: string
  /** Icon (optional) - can be string or React node */
  icon?: string | React.ReactNode
  /** Description */
  description?: string
  /** Default props when block is added */
  defaultProps?: Record<string, unknown>
  /** Props schema for the inspector */
  propsSchema?: Record<string, {
    type: string
    label: string
    default?: unknown
    options?: FieldOption[]
  }>
}

/**
 * Design control types available in the visual builder.
 */
export type VisualBuilderDesignControl = 
  | 'alignment'
  | 'spacing'
  | 'direction'
  | 'gap'
  | 'justify'

/**
 * API response wrapper.
 */
export interface ApiResponse<T> {
  success: boolean
  data?: T
  error?: string
}

export interface OptStackConfig {
  nonce: string
  restUrl: string
  adminUrl: string
  stackId: string
  context: string
  version: string
  devMode: boolean
  googleFontsApiKey: string
}