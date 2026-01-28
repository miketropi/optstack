import { useCallback, useEffect, useRef } from 'react'
import { useStackData } from '../hooks/useStackData'
import { useConditions } from '../hooks/useConditions'
import { FieldRenderer } from './FieldRenderer'
import { GroupRenderer } from './GroupRenderer'
import { TabContainer } from './TabContainer'
import type { StackSchema } from '../schema/types'

interface StackRendererProps {
  schema: StackSchema
  objectId?: number
  objectType?: string
}

export function StackRenderer({ schema, objectId, objectType }: StackRendererProps) {
  const { data, loading, error, saving, isDirty, updateField, save, reset } = useStackData(schema.id, objectId)
  const { isVisible } = useConditions(data)
  const hiddenInputRef = useRef<HTMLInputElement>(null)

  // Determine if this is a standalone page (needs its own save button)
  // or embedded in WordPress form (meta box, term form)
  const isStandalone = schema.context === 'options'
  const isEmbedded = schema.context === 'post_type' || schema.context === 'taxonomy' || schema.context === 'user'

  // For embedded contexts, update hidden input with current data
  // so WordPress form submission includes our data
  useEffect(() => {
    if (isEmbedded && hiddenInputRef.current) {
      hiddenInputRef.current.value = JSON.stringify(data)
    }
  }, [data, isEmbedded])

  const handleSave = useCallback(async () => {
    const success = await save()
    if (success) {
      // Could show success notification
    }
  }, [save])

  if (loading) {
    return (
      <div className="os-flex os-items-center os-justify-center os-min-h-[100px]">
        <div className="os-animate-spin os-rounded-full os-h-6 os-w-6 os-border-b-2 os-border-wp-primary"></div>
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

  return (
    <div className="os-stack-renderer">
      {/* Hidden input for embedded forms (meta box, term, user) */}
      {isEmbedded && (
        <input
          ref={hiddenInputRef}
          type="hidden"
          name={`optstack_data[${schema.id}]`}
          defaultValue={JSON.stringify(data)}
        />
      )}

      {/* Root-level fields */}
      {schema.fields && Object.entries(schema.fields).map(([key, field]) => {
        if (!isVisible(field.conditions)) {
          return null
        }

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
      {schema.groups && Object.entries(schema.groups).map(([key, group]) => {
        if (!isVisible(group.conditions)) {
          return null
        }

        // For repeatable groups, data[key] is an array
        // For regular groups, data[key] is an object
        const groupData = data[key]

        return (
          <GroupRenderer
            key={key}
            group={group}
            data={group.repeatable 
              ? (groupData as Record<string, unknown>) 
              : ((groupData as Record<string, unknown>) || {})
            }
            onChange={(fieldKey, value) => {
              if (group.repeatable) {
                // For repeatable groups, value IS the array of items
                // fieldKey is group.key, we ignore it and use the outer key directly
                updateField(key, value)
              } else {
                // For regular groups, merge the field into the group object
                const currentGroupData = (data[key] as Record<string, unknown>) || {}
                updateField(key, { ...currentGroupData, [fieldKey]: value })
              }
            }}
          />
        )
      })}

      {/* Tabs */}
      {schema.tabs && Object.keys(schema.tabs).length > 0 && (
        <TabContainer
          tabs={schema.tabs}
          data={data}
          onChange={updateField}
        />
      )}

      {/* Save bar - only for standalone options pages */}
      {isStandalone && (
        <div className="os-save-bar">
          <div className="os-save-status">
            {isDirty && !saving && (
              <span className="os-text-yellow-600">You have unsaved changes</span>
            )}
            {saving && (
              <span className="os-text-gray-500">Saving...</span>
            )}
          </div>
          <div className="os-flex os-space-x-3">
            {isDirty && (
              <button
                type="button"
                onClick={reset}
                disabled={saving}
                className="os-btn-secondary"
              >
                Reset
              </button>
            )}
            <button
              type="button"
              onClick={handleSave}
              disabled={!isDirty || saving}
              className="os-btn-primary"
            >
              {saving ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </div>
      )}

      {/* Status indicator for embedded forms */}
      {isEmbedded && isDirty && (
        <div className="os-text-xs os-text-gray-500 os-mt-2 os-italic os-p-2 os-bg-gray-100">
          Changes will be saved when you update the {objectType || 'item'}.
        </div>
      )}
    </div>
  )
}
