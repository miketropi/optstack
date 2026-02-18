import { useState, useEffect, useCallback, useRef } from 'react'
import ReactDOM from 'react-dom'
import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from './FieldRenderer'
import { GroupRenderer } from './GroupRenderer'
import type { FieldGroupSchema } from '../schema/types'

interface Props {
  group: FieldGroupSchema
  data: Record<string, unknown>
  onChange: (key: string, value: unknown) => void
  /** Called when Apply is clicked - receives the entire group data to replace */
  onGroupApply?: (data: Record<string, unknown>) => void
  disabled?: boolean
  errors?: Record<string, string[]>
}

/**
 * DeferredGroupModal
 *
 * Renders a deferred group as a trigger button that opens a modal.
 * The modal contains all the group's fields and nested groups.
 *
 * Data flow:
 * - Local state holds draft changes while modal is open
 * - On "Apply", local changes are committed to parent
 * - On "Cancel", local changes are discarded
 * - Data structure remains identical to non-deferred groups
 */
export function DeferredGroupModal({ group, data, onChange, onGroupApply, disabled, errors }: Props) {
  const [isOpen, setIsOpen] = useState(false)
  const [localData, setLocalData] = useState<Record<string, unknown>>({})
  const { isVisible } = useConditions(data)
  const modalRef = useRef<HTMLDivElement>(null)

  // Get UI configuration with defaults
  const triggerLabel = group.ui?.triggerLabel || `Configure ${group.label}`
  const renderMode = group.ui?.render || 'modal'

  // Initialize local data when modal opens
  useEffect(() => {
    if (isOpen) {
      setLocalData({ ...data })
    }
  }, [isOpen, data])

  // Handle escape key
  useEffect(() => {
    if (!isOpen) return

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setIsOpen(false)
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [isOpen])

  // Focus trap
  useEffect(() => {
    if (!isOpen || !modalRef.current) return

    const focusableElements = modalRef.current.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    const firstElement = focusableElements[0] as HTMLElement
    const lastElement = focusableElements[focusableElements.length - 1] as HTMLElement

    firstElement?.focus()

    const handleTab = (e: KeyboardEvent) => {
      if (e.key !== 'Tab') return

      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          e.preventDefault()
          lastElement?.focus()
        }
      } else {
        if (document.activeElement === lastElement) {
          e.preventDefault()
          firstElement?.focus()
        }
      }
    }

    document.addEventListener('keydown', handleTab)
    return () => document.removeEventListener('keydown', handleTab)
  }, [isOpen])

  // Handle local field changes
  const handleLocalChange = useCallback((key: string, value: unknown) => {
    setLocalData(prev => ({ ...prev, [key]: value }))
  }, [])

  // Apply changes to parent
  const handleApply = useCallback(() => {
    // Use onGroupApply if provided (preferred - updates entire group at once)
    if (onGroupApply) {
      onGroupApply(localData)
    } else {
      // Fallback: commit changes field by field (may cause issues with batched updates)
      Object.entries(localData).forEach(([key, value]) => {
        onChange(key, value)
      })
    }
    setIsOpen(false)
  }, [localData, onChange, onGroupApply])

  // Cancel and discard changes
  const handleCancel = useCallback(() => {
    setIsOpen(false)
  }, [])

  // Prevent body scroll when modal is open
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
    return () => {
      document.body.style.overflow = ''
    }
  }, [isOpen])

  const hasFields = group.fields && Object.keys(group.fields).length > 0
  const hasNestedGroups = group.groups && Object.keys(group.groups).length > 0

  // Count configured fields for summary
  const configuredCount = Object.values(data).filter(v => v !== null && v !== undefined && v !== '').length
  const totalFields = Object.keys(group.fields || {}).length

  // Render trigger button
  const renderTrigger = () => (
    <div className="os-deferred-group os-group-inline">
      <div className="os-deferred-group-label">
        <span className="os-deferred-group-label-text">{group.label}</span>
        {group.description && (
          <p className="os-deferred-group-description">{group.description}</p>
        )}
      </div>
      <div className="os-deferred-group-body">
        <button
          type="button"
          className="os-deferred-trigger"
          onClick={() => setIsOpen(true)}
          disabled={disabled}
        >
          <svg
            className="os-deferred-trigger-icon"
            viewBox="0 0 20 20"
            fill="currentColor"
            width="16"
            height="16"
          >
            <path
              fillRule="evenodd"
              d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
              clipRule="evenodd"
            />
          </svg>
          <span>{triggerLabel}</span>
        </button>
        {configuredCount > 0 && (
          <span className="os-deferred-summary">
            {configuredCount} of {totalFields} configured
          </span>
        )}
      </div>
    </div>
  )

  // Render modal content
  const renderModalContent = () => (
    <div className="os-deferred-modal-content">
      {/* Fields */}
      {hasFields && (
        <div className="os-deferred-modal-fields">
          {Object.entries(group.fields!).map(([key, field]) => {
            if (!isVisible(field.conditions)) {
              return null
            }

            return (
              <FieldRenderer
                key={key}
                field={field}
                value={localData[key]}
                onChange={(value) => handleLocalChange(key, value)}
                disabled={disabled}
                error={errors?.[key]?.[0]}
              />
            )
          })}
        </div>
      )}

      {/* Nested groups */}
      {hasNestedGroups && (
        <div className="os-deferred-modal-groups">
          {Object.entries(group.groups!).map(([key, nestedGroup]) => {
            if (!isVisible(nestedGroup.conditions)) {
              return null
            }

            const nestedData = nestedGroup.repeatable
              ? (Array.isArray(localData[key]) ? localData[key] : [])
              : ((localData[key] as Record<string, unknown>) || {})

            return (
              <GroupRenderer
                key={key}
                group={nestedGroup}
                data={nestedData as Record<string, unknown>}
                onChange={(fieldKey, value) => {
                  if (nestedGroup.repeatable) {
                    handleLocalChange(key, value)
                  } else {
                    const current = (localData[key] as Record<string, unknown>) || {}
                    handleLocalChange(key, { ...current, [fieldKey]: value })
                  }
                }}
                disabled={disabled}
                errors={errors}
              />
            )
          })}
        </div>
      )}
    </div>
  )

  // Render modal
  const renderModal = () => {
    if (!isOpen) return null

    const modalElement = (
      <div className="os-deferred-modal-overlay" onClick={handleCancel}>
        <div
          ref={modalRef}
          className={`os-deferred-modal os-deferred-modal-${renderMode}`}
          onClick={(e) => e.stopPropagation()}
          role="dialog"
          aria-modal="true"
          aria-labelledby={`deferred-modal-title-${group.key}`}
        >
          <div className="os-deferred-modal-header">
            <h2 id={`deferred-modal-title-${group.key}`} className="os-deferred-modal-title">
              {group.label}
            </h2>
            <button
              type="button"
              className="os-deferred-modal-close"
              onClick={handleCancel}
              aria-label="Close"
            >
              <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                <path
                  fillRule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clipRule="evenodd"
                />
              </svg>
            </button>
          </div>

          {group.description && (
            <p className="os-deferred-modal-description">{group.description}</p>
          )}

          <div className="os-deferred-modal-body">
            {renderModalContent()}
          </div>

          <div className="os-deferred-modal-footer">
            <button
              type="button"
              className="os-btn os-btn-secondary"
              onClick={handleCancel}
            >
              Cancel
            </button>
            <button
              type="button"
              className="os-btn os-btn-primary"
              onClick={handleApply}
              disabled={disabled}
            >
              Apply Changes
            </button>
          </div>
        </div>
      </div>
    )

    // Use portal to render modal at document body
    return ReactDOM.createPortal(modalElement, document.body)
  }

  return (
    <>
      {renderTrigger()}
      {renderModal()}
    </>
  )
}
