import { useState } from 'react'
import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from './FieldRenderer'
import { Repeater } from './fields/Repeater'
import type { FieldGroupSchema } from '../schema/types'

interface Props {
  group: FieldGroupSchema
  data: Record<string, unknown>
  onChange: (key: string, value: unknown) => void
  disabled?: boolean
  errors?: Record<string, string[]>
}

export function GroupRenderer({ group, data, onChange, disabled, errors }: Props) {
  const { isVisible } = useConditions(data)
  const [isCollapsed, setIsCollapsed] = useState(false)
  
  const collapsible = group.collapsible === true

  // Handle repeatable groups
  if (group.repeatable) {
    const items = Array.isArray(data) ? data : []
    
    return (
      <div className={`os-group os-group-repeatable ${isCollapsed ? 'os-collapsed' : ''}`}>
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
              onChange={(items) => onChange(group.key, items)}
              disabled={disabled}
            />
          </div>
        )}
      </div>
    )
  }

  const hasFields = group.fields && Object.keys(group.fields).length > 0
  const hasNestedGroups = group.groups && Object.keys(group.groups).length > 0

  return (
    <div className={`os-group ${isCollapsed ? 'os-collapsed' : ''}`}>
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

                return (
                  <GroupRenderer
                    key={key}
                    group={nestedGroup}
                    data={(data[key] as Record<string, unknown>) || {}}
                    onChange={(fieldKey, value) => {
                      const nestedData = (data[key] as Record<string, unknown>) || {}
                      onChange(key, { ...nestedData, [fieldKey]: value })
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
