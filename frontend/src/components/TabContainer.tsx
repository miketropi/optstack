import { useState, useCallback } from 'react'
import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from './FieldRenderer'
import { GroupRenderer } from './GroupRenderer'
import type { TabSchema, FieldGroupSchema } from '../schema/types'

interface TabContainerProps {
  tabs: Record<string, TabSchema>
  data: Record<string, unknown>
  onChange: (key: string, value: unknown) => void
  disabled?: boolean
  errors?: Record<string, string[]>
}

export function TabContainer({ tabs, data, onChange, disabled, errors }: TabContainerProps) {
  const tabEntries = Object.entries(tabs)
  const [activeTab, setActiveTab] = useState(tabEntries[0]?.[0] || '')
  const { isVisible } = useConditions(data)

  const handleGroupChange = useCallback((groupKey: string, fieldKey: string, value: unknown, group: FieldGroupSchema) => {
    if (group.repeatable) {
      // For repeatable groups, value is the array
      onChange(groupKey, value)
    } else {
      // For regular groups, merge the field
      const currentGroupData = (data[groupKey] as Record<string, unknown>) || {}
      onChange(groupKey, { ...currentGroupData, [fieldKey]: value })
    }
  }, [data, onChange])

  // Handle deferred group apply - replaces entire group data at once
  const handleGroupApply = useCallback((groupKey: string, newData: Record<string, unknown>) => {
    onChange(groupKey, newData)
  }, [onChange])

  if (tabEntries.length === 0) {
    return null
  }

  // Filter visible tabs
  const visibleTabs = tabEntries.filter(([, tab]) => isVisible(tab.conditions))

  if (visibleTabs.length === 0) {
    return null
  }

  // Ensure active tab is visible
  if (!visibleTabs.find(([key]) => key === activeTab)) {
    setActiveTab(visibleTabs[0][0])
  }

  const activeTabData = tabs[activeTab]

  return (
    <div className="os-tab-container">
      {/* Tab navigation */}
      <div className="os-tab-nav" role="tablist">
        {visibleTabs.map(([key, tab]) => (
          <button
            key={key}
            type="button"
            role="tab"
            aria-selected={activeTab === key}
            aria-controls={`tabpanel-${key}`}
            className={`os-tab-button ${activeTab === key ? 'os-tab-active' : ''}`}
            onClick={() => setActiveTab(key)}
          >
            {tab.icon && (
              <span className={`os-tab-icon dashicons ${tab.icon}`} aria-hidden="true" />
            )}
            <span className="os-tab-label">{tab.label}</span>
          </button>
        ))}
      </div>

      {/* Tab content */}
      <div className="os-tab-content">
        {activeTabData && (
          <div
            id={`tabpanel-${activeTab}`}
            role="tabpanel"
            aria-labelledby={activeTab}
            className="os-tab-panel"
          >
            {/* Tab description */}
            {activeTabData.description && (
              <p className="os-tab-description">{activeTabData.description}</p>
            )}

            {/* Tab fields */}
            {activeTabData.fields && Object.entries(activeTabData.fields).map(([key, field]) => {
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

            {/* Tab groups */}
            {activeTabData.groups && Object.entries(activeTabData.groups).map(([key, group]) => {
              if (!isVisible(group.conditions)) {
                return null
              }

              const groupData = data[key]

              return (
                <GroupRenderer
                  key={key}
                  group={group}
                  data={group.repeatable 
                    ? (groupData as Record<string, unknown>) 
                    : ((groupData as Record<string, unknown>) || {})
                  }
                  onChange={(fieldKey, value) => handleGroupChange(key, fieldKey, value, group)}
                  // For deferred groups, provide a way to update the entire group at once
                  onGroupApply={group.deferred ? (newData) => handleGroupApply(key, newData) : undefined}
                  disabled={disabled}
                  errors={errors}
                />
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}
