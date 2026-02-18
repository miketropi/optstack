import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from '../components/FieldRenderer'
import { GroupRenderer } from '../components/GroupRenderer'
import { TabContainer } from '../components/TabContainer'
import { useBlockStackData } from './useBlockStackData'

interface BlockStackRendererProps {
  stackId: string
  attributes: Record<string, unknown>
  setAttributes: (attrs: Record<string, unknown>) => void
}

/**
 * Renders OptStack fields in block InspectorControls.
 * Uses block attributes as data source; setAttributes for updates.
 */
export function BlockStackRenderer({
  stackId,
  attributes,
  setAttributes,
}: BlockStackRendererProps) {
  const { schema, loading, error, data, updateField } = useBlockStackData(
    stackId,
    attributes,
    setAttributes
  )
  const { isVisible } = useConditions(data)

  if (loading) {
    return (
      <div className="os-flex os-items-center os-justify-center os-min-h-[60px]">
        <div className="os-animate-spin os-rounded-full os-h-5 os-w-5 os-border-b-2 os-border-wp-primary" />
      </div>
    )
  }

  if (error) {
    return (
      <div className="os-bg-red-50 os-border os-border-red-200 os-rounded os-p-3 os-text-red-700 os-text-sm">
        <strong>Error:</strong> {error}
      </div>
    )
  }

  if (!schema) {
    return (
      <div className="os-bg-yellow-50 os-border os-border-yellow-200 os-rounded os-p-3 os-text-yellow-700 os-text-sm">
        Stack &quot;{stackId}&quot; not found.
      </div>
    )
  }

  return (
    <div className="optstack-block-inspector os-stack-renderer">
      {/* Root-level fields */}
      {schema.fields &&
        Object.entries(schema.fields).map(([key, field]) => {
          if (!isVisible(field.conditions)) return null
          return (
            <FieldRenderer
              key={key}
              field={field}
              value={data[key]}
              onChange={(value) => updateField(key, value)}
            />
          )
        })}

      {/* Groups */}
      {schema.groups &&
        Object.entries(schema.groups).map(([key, group]) => {
          if (!isVisible(group.conditions)) return null
          const groupData = data[key]
          return (
            <GroupRenderer
              key={key}
              group={group}
              data={
                group.repeatable
                  ? (groupData as Record<string, unknown>)
                  : ((groupData as Record<string, unknown>) || {})
              }
              onChange={(fieldKey, value) => {
                if (group.repeatable) {
                  updateField(key, value)
                } else {
                  const current = (data[key] as Record<string, unknown>) || {}
                  updateField(key, { ...current, [fieldKey]: value })
                }
              }}
              onGroupApply={group.deferred ? (newData) => updateField(key, newData) : undefined}
            />
          )
        })}

      {/* Tabs */}
      {schema.tabs && Object.keys(schema.tabs).length > 0 && (
        <TabContainer tabs={schema.tabs} data={data} onChange={updateField} />
      )}
    </div>
  )
}
