import React, { useState, useMemo, useCallback } from 'react'
import type { FieldSchema, FieldRendererProps } from '../schema/types'

const RESPONSIVE_MODES = [
  { id: 'desktop' as const, label: 'Desktop', title: 'Large screens (≥1024px)' },
  { id: 'tablet' as const, label: 'Tablet', title: 'Medium screens (768px–1023px)' },
  { id: 'mobile' as const, label: 'Mobile', title: 'Small screens (<768px)' },
] as const

export type ResponsiveMode = 'desktop' | 'tablet' | 'mobile'

export interface ResponsiveValue {
  desktop?: unknown
  tablet?: unknown
  mobile?: unknown
}

function isResponsiveValue(v: unknown): v is ResponsiveValue {
  return typeof v === 'object' && v !== null && !Array.isArray(v) && ('desktop' in v || 'tablet' in v || 'mobile' in v)
}

function normalizeResponsiveValue(value: unknown, fallback: unknown): ResponsiveValue {
  if (isResponsiveValue(value)) {
    return {
      desktop: value.desktop !== undefined ? value.desktop : fallback,
      tablet: value.tablet !== undefined ? value.tablet : value.desktop !== undefined ? value.desktop : fallback,
      mobile: value.mobile !== undefined ? value.mobile : value.tablet !== undefined ? value.tablet : value.desktop !== undefined ? value.desktop : fallback,
    }
  }
  return {
    desktop: value !== undefined && value !== null ? value : fallback,
    tablet: value !== undefined && value !== null ? value : fallback,
    mobile: value !== undefined && value !== null ? value : fallback,
  }
}

interface Props {
  field: FieldSchema
  value: unknown
  onChange: (value: unknown) => void
  disabled?: boolean
  error?: string
  children: React.ReactElement<FieldRendererProps>
}

export function ResponsiveFieldWrapper({ field, value, onChange, disabled, error, children }: Props) {
  const [activeMode, setActiveMode] = useState<ResponsiveMode>('desktop')

  const fallback = field.default ?? (field.type === 'number' || field.type === 'range' ? 0 : '')

  const normalized = useMemo(
    () => normalizeResponsiveValue(value, fallback),
    [value, fallback]
  )

  const currentValue = normalized[activeMode]

  const handleChange = useCallback(
    (newVal: unknown) => {
      onChange({
        ...normalized,
        [activeMode]: newVal,
      })
    },
    [normalized, activeMode, onChange]
  )

  const child = children
  const clonedChild = child
    ? React.cloneElement(child, {
        field,
        value: currentValue,
        onChange: handleChange,
        disabled,
        error,
      } as FieldRendererProps)
    : null

  return (
    <div className="os-field-responsive">
      <div className="os-responsive-modes" role="tablist" aria-label="Viewport mode">
        {RESPONSIVE_MODES.map((mode) => (
          <button
            key={mode.id}
            type="button"
            role="tab"
            aria-selected={activeMode === mode.id}
            aria-controls={`os-responsive-panel-${field.key}`}
            id={`os-responsive-tab-${field.key}-${mode.id}`}
            title={mode.title}
            className={`os-responsive-mode ${activeMode === mode.id ? 'os-responsive-mode-active' : ''}`}
            onClick={() => setActiveMode(mode.id)}
          >
            <span className="os-responsive-mode-icon" aria-hidden>
              {mode.id === 'desktop' && (
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="2" y="3" width="20" height="14" rx="2" />
                  <line x1="8" y1="21" x2="16" y2="21" />
                  <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
              )}
              {mode.id === 'tablet' && (
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="4" y="2" width="16" height="20" rx="2" />
                  <line x1="12" y1="18" x2="12.01" y2="18" />
                </svg>
              )}
              {mode.id === 'mobile' && (
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="5" y="2" width="14" height="20" rx="2" />
                  <line x1="12" y1="18" x2="12.01" y2="18" />
                </svg>
              )}
            </span>
            <span className="os-responsive-mode-label">{mode.label}</span>
          </button>
        ))}
      </div>
      <div
        id={`os-responsive-panel-${field.key}`}
        role="tabpanel"
        aria-labelledby={`os-responsive-tab-${field.key}-${activeMode}`}
        className="os-responsive-panel"
      >
        {clonedChild}
      </div>
      
    </div>
  )
}
