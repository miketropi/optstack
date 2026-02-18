import { useState } from 'react'
import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from './FieldRenderer'
import { Repeater } from './fields/Repeater'
import { DeferredGroupModal } from './DeferredGroupModal'
import type { FieldGroupSchema } from '../schema/types'

interface Props {
  group: FieldGroupSchema
  data: Record<string, unknown>
  onChange: (key: string, value: unknown) => void
  /** Called when a deferred group applies all changes at once */
  onGroupApply?: (data: Record<string, unknown>) => void
  disabled?: boolean
  errors?: Record<string, string[]>
}

export function GroupRenderer({ group, data, onChange, onGroupApply, disabled, errors }: Props) {
  const { isVisible } = useConditions(data)
  const [isCollapsed, setIsCollapsed] = useState(false)
  
  const collapsible = group.collapsible === true
  // Use inline layout (2-col like fields) by default, or box layout if specified
  const layout = group.layout || 'inline'
  const isInline = layout === 'inline'

  // Handle deferred groups - render trigger button + modal instead of inline fields
  if (group.deferred) {
    return (
      <DeferredGroupModal
        group={group}
        data={data}
        onChange={onChange}
        onGroupApply={onGroupApply}
        disabled={disabled}
        errors={errors}
      />
    )
  }

  // Handle repeatable groups
  if (group.repeatable) {
    const items = Array.isArray(data) ? data : []
    
    if (isInline) {
      // Inline layout for repeatable (2-col: label | repeater)
      return (
        <div className={`os-group os-group-inline os-group-repeatable ${isCollapsed ? 'os-collapsed' : ''}`}>
          <div className="os-group-label">
            <div className="os-group-label-content">
              {collapsible && (
                <button
                  type="button"
                  className="os-group-collapse-btn os-group-collapse-btn-sm"
                  onClick={() => setIsCollapsed(!isCollapsed)}
                >
                  <svg 
                    className={`os-group-chevron ${isCollapsed ? '' : 'os-rotated'}`}
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                    width="16"
                    height="16"
                  >
                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </button>
              )}
              <div>
                <span className="os-group-label-text">
                  {group.label}
                  {items.length > 0 && (
                    <span className="os-group-count">{items.length}</span>
                  )}
                </span>
                {group.description && (
                  <p className="os-group-label-description">{group.description}</p>
                )}
              </div>
            </div>
          </div>
          
          {!isCollapsed && (
            <div className="os-group-body">
              <Repeater
                group={group}
                items={items}
                onChange={(newItems: Record<string, unknown>[]) => onChange(group.key, newItems)}
                disabled={disabled}
              />
            </div>
          )}
        </div>
      )
    }
    
    // Box layout for repeatable (original style)
    return (
      <div className={`os-group os-group-box os-group-repeatable ${isCollapsed ? 'os-collapsed' : ''}`}>
        <div className="os-group-header">
          <div className="os-group-header-content">
            {collapsible && (
              <button
                type="button"
                className="os-group-collapse-btn"
                onClick={() => setIsCollapsed(!isCollapsed)}
              >
                <svg 
                  className={`os-group-chevron ${isCollapsed ? '' : 'os-rotated'}`}
                  viewBox="0 0 20 20" 
                  fill="currentColor"
                  width="20"
                  height="20"
                >
                  <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </button>
            )}
            <div>
              <h3 className="os-group-title">
                {group.label}
                {items.length > 0 && (
                  <span className="os-group-count">{items.length}</span>
                )}
              </h3>
              {group.description && (
                <p className="os-group-description">{group.description}</p>
              )}
            </div>
          </div>
        </div>
        
        {!isCollapsed && (
          <div className="os-group-content">
            <Repeater
              group={group}
              items={items}
              onChange={(newItems: Record<string, unknown>[]) => onChange(group.key, newItems)}
              disabled={disabled}
            />
          </div>
        )}
      </div>
    )
  }

  const hasFields = group.fields && Object.keys(group.fields).length > 0
  const hasNestedGroups = group.groups && Object.keys(group.groups).length > 0

  // Inline layout (2-col: label | fields)
  if (isInline) {
    return (
      <div className={`os-group os-group-inline ${isCollapsed ? 'os-collapsed' : ''}`}>
        <div className="os-group-label">
          <div className="os-group-label-content">
            {collapsible && (
              <button
                type="button"
                className="os-group-collapse-btn os-group-collapse-btn-sm"
                onClick={() => setIsCollapsed(!isCollapsed)}
              >
                <svg 
                  className={`os-group-chevron ${isCollapsed ? '' : 'os-rotated'}`}
                  viewBox="0 0 20 20" 
                  fill="currentColor"
                  width="16"
                  height="16"
                >
                  <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              </button>
            )}
            <div>
              <span className="os-group-label-text">{group.label}</span>
              {group.description && (
                <p className="os-group-label-description">{group.description}</p>
              )}
            </div>
          </div>
        </div>

        {!isCollapsed && (
          <div className="os-group-body">
            {/* Fields in this group */}
            {hasFields && (
              <div className="os-group-inline-fields">
                {Object.entries(group.fields!).map(([key, field]) => {
                  if (!isVisible(field.conditions)) {
                    return null
                  }

                  return (
                    <FieldRenderer
                      key={key}
                      field={field}
                      value={data[key]}
                      onChange={(value) => onChange(key, value)}
                      disabled={disabled}
                      error={errors?.[key]?.[0]}
                    />
                  )
                })}
              </div>
            )}

            {/* Nested groups */}
            {hasNestedGroups && (
              <div className="os-group-inline-nested">
                {Object.entries(group.groups!).map(([key, nestedGroup]) => {
                  if (!isVisible(nestedGroup.conditions)) {
                    return null
                  }

                  const nestedData = nestedGroup.repeatable
                    ? (Array.isArray(data[key]) ? data[key] : [])
                    : ((data[key] as Record<string, unknown>) || {})

                  return (
                    <GroupRenderer
                      key={key}
                      group={nestedGroup}
                      data={nestedData as Record<string, unknown>}
                      onChange={(fieldKey, value) => {
                        if (nestedGroup.repeatable) {
                          onChange(key, value)
                        } else {
                          const current = (data[key] as Record<string, unknown>) || {}
                          onChange(key, { ...current, [fieldKey]: value })
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
        )}
      </div>
    )
  }

  // Box layout (original style)
  return (
    <div className={`os-group os-group-box ${isCollapsed ? 'os-collapsed' : ''}`}>
      <div className="os-group-header">
        <div className="os-group-header-content">
          {collapsible && (
            <button
              type="button"
              className="os-group-collapse-btn"
              onClick={() => setIsCollapsed(!isCollapsed)}
            >
              <svg 
                className={`os-group-chevron ${isCollapsed ? '' : 'os-rotated'}`}
                viewBox="0 0 20 20" 
                fill="currentColor"
                width="20"
                height="20"
              >
                <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
            </button>
          )}
          <div>
            <h3 className="os-group-title">{group.label}</h3>
            {group.description && (
              <p className="os-group-description">{group.description}</p>
            )}
          </div>
        </div>
      </div>

      {!isCollapsed && (
        <div className="os-group-content">
          {/* Fields in this group */}
          {hasFields && (
            <div className="os-group-fields">
              {Object.entries(group.fields!).map(([key, field]) => {
                if (!isVisible(field.conditions)) {
                  return null
                }

                return (
                  <FieldRenderer
                    key={key}
                    field={field}
                    value={data[key]}
                    onChange={(value) => onChange(key, value)}
                    disabled={disabled}
                    error={errors?.[key]?.[0]}
                  />
                )
              })}
            </div>
          )}

          {/* Nested groups */}
          {hasNestedGroups && (
            <div className="os-nested-groups">
              {Object.entries(group.groups!).map(([key, nestedGroup]) => {
                if (!isVisible(nestedGroup.conditions)) {
                  return null
                }

                const nestedData = nestedGroup.repeatable
                  ? (Array.isArray(data[key]) ? data[key] : [])
                  : ((data[key] as Record<string, unknown>) || {})

                return (
                  <GroupRenderer
                    key={key}
                    group={nestedGroup}
                    data={nestedData as Record<string, unknown>}
                    onChange={(fieldKey, value) => {
                      if (nestedGroup.repeatable) {
                        onChange(key, value)
                      } else {
                        const current = (data[key] as Record<string, unknown>) || {}
                        onChange(key, { ...current, [fieldKey]: value })
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
      )}
    </div>
  )
}
