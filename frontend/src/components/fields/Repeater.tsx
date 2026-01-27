import { useCallback, useState } from 'react'
import { useConditions } from '../../hooks/useConditions'
import { FieldRenderer } from '../FieldRenderer'
import type { FieldGroupSchema, RepeaterItem } from '../../schema/types'

interface RepeaterProps {
  group: FieldGroupSchema
  items: Record<string, unknown>[]
  onChange: (items: Record<string, unknown>[]) => void
  disabled?: boolean
}

function generateId(): string {
  return Math.random().toString(36).substring(2, 11)
}

export function Repeater({ group, items, onChange, disabled }: RepeaterProps) {
  const { isVisible } = useConditions({})
  const [collapsedItems, setCollapsedItems] = useState<Set<string>>(new Set())
  const [draggingIndex, setDraggingIndex] = useState<number | null>(null)

  // Ensure items is always an array
  const safeItems = Array.isArray(items) ? items : []

  // Ensure items have _id for React keys
  const itemsWithIds: RepeaterItem[] = safeItems.map((item) => ({
    ...item,
    _id: (item._id as string) || generateId(),
  }))

  const addItem = useCallback(() => {
    if (group.maxItems && itemsWithIds.length >= group.maxItems) {
      return
    }

    const newItem: RepeaterItem = { _id: generateId() }

    if (group.fields) {
      Object.entries(group.fields).forEach(([key, field]) => {
        if (field.default !== undefined) {
          newItem[key] = field.default
        }
      })
    }

    onChange([...itemsWithIds, newItem])
  }, [itemsWithIds, group, onChange])

  const removeItem = useCallback((index: number) => {
    if (group.minItems && itemsWithIds.length <= group.minItems) {
      return
    }

    const newItems = [...itemsWithIds]
    newItems.splice(index, 1)
    onChange(newItems)
  }, [itemsWithIds, group, onChange])

  const updateItem = useCallback((index: number, key: string, value: unknown) => {
    const newItems = [...itemsWithIds]
    newItems[index] = { ...newItems[index], [key]: value }
    onChange(newItems)
  }, [itemsWithIds, onChange])

  const moveItem = useCallback((fromIndex: number, toIndex: number) => {
    if (toIndex < 0 || toIndex >= itemsWithIds.length) {
      return
    }

    const newItems = [...itemsWithIds]
    const [removed] = newItems.splice(fromIndex, 1)
    newItems.splice(toIndex, 0, removed)
    onChange(newItems)
  }, [itemsWithIds, onChange])

  const toggleCollapse = useCallback((id: string) => {
    setCollapsedItems(prev => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }, [])

  const duplicateItem = useCallback((index: number) => {
    if (group.maxItems && itemsWithIds.length >= group.maxItems) {
      return
    }

    const itemToDuplicate = { ...itemsWithIds[index], _id: generateId() }
    const newItems = [...itemsWithIds]
    newItems.splice(index + 1, 0, itemToDuplicate)
    onChange(newItems)
  }, [itemsWithIds, group, onChange])

  const canAdd = !group.maxItems || itemsWithIds.length < group.maxItems
  const canRemove = !group.minItems || itemsWithIds.length > group.minItems

  // Get preview text from first field
  const getItemPreview = (item: RepeaterItem): string => {
    if (!group.fields) return ''
    const firstField = Object.entries(group.fields)[0]
    if (!firstField) return ''
    const [key] = firstField
    const value = item[key]
    if (typeof value === 'string' && value.length > 0) {
      return value.length > 50 ? value.substring(0, 50) + '...' : value
    }
    return ''
  }

  return (
    <div className="os-repeater">
      {itemsWithIds.length === 0 ? (
        <div className="os-repeater-empty">
          <div className="os-repeater-empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
              <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
          </div>
          <p className="os-repeater-empty-text">No items yet</p>
          <p className="os-repeater-empty-hint">Click the button below to add your first item</p>
        </div>
      ) : (
        <div className="os-repeater-items">
          {itemsWithIds.map((item, index) => {
            const isCollapsed = collapsedItems.has(item._id)
            const preview = getItemPreview(item)
            
            return (
              <div 
                key={item._id} 
                className={`os-repeater-item ${isCollapsed ? 'os-collapsed' : ''} ${draggingIndex === index ? 'os-dragging' : ''}`}
                draggable={!disabled}
                onDragStart={() => setDraggingIndex(index)}
                onDragEnd={() => setDraggingIndex(null)}
                onDragOver={(e) => {
                  e.preventDefault()
                  if (draggingIndex !== null && draggingIndex !== index) {
                    moveItem(draggingIndex, index)
                    setDraggingIndex(index)
                  }
                }}
              >
                <div className="os-repeater-item-header">
                  <div className="os-repeater-item-handle" title="Drag to reorder">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                      <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z" />
                    </svg>
                  </div>
                  
                  <button
                    type="button"
                    className="os-repeater-item-toggle"
                    onClick={() => toggleCollapse(item._id)}
                  >
                    <span className="os-repeater-item-index">#{index + 1}</span>
                    {preview && isCollapsed && (
                      <span className="os-repeater-item-preview">{preview}</span>
                    )}
                    <svg 
                      className={`os-repeater-chevron ${isCollapsed ? '' : 'os-rotated'}`} 
                      viewBox="0 0 20 20" 
                      fill="currentColor"
                    >
                      <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                  </button>
                  
                  <div className="os-repeater-actions">
                    {index > 0 && (
                      <button
                        type="button"
                        onClick={() => moveItem(index, index - 1)}
                        disabled={disabled}
                        className="os-btn-icon"
                        title="Move up"
                      >
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                          <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
                        </svg>
                      </button>
                    )}
                    {index < itemsWithIds.length - 1 && (
                      <button
                        type="button"
                        onClick={() => moveItem(index, index + 1)}
                        disabled={disabled}
                        className="os-btn-icon"
                        title="Move down"
                      >
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                          <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                        </svg>
                      </button>
                    )}
                    <button
                      type="button"
                      onClick={() => duplicateItem(index)}
                      disabled={disabled || !canAdd}
                      className="os-btn-icon"
                      title="Duplicate"
                    >
                      <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                        <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                      </svg>
                    </button>
                    {canRemove && (
                      <button
                        type="button"
                        onClick={() => removeItem(index)}
                        disabled={disabled}
                        className="os-btn-icon os-btn-icon-danger"
                        title="Remove"
                      >
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                          <path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                      </button>
                    )}
                  </div>
                </div>

                {!isCollapsed && (
                  <div className="os-repeater-item-content">
                    {group.fields && Object.entries(group.fields).map(([key, field]) => {
                      if (!isVisible(field.conditions)) {
                        return null
                      }

                      return (
                        <FieldRenderer
                          key={key}
                          field={field}
                          value={item[key]}
                          onChange={(value) => updateItem(index, key, value)}
                          disabled={disabled}
                        />
                      )
                    })}
                  </div>
                )}
              </div>
            )
          })}
        </div>
      )}

      {canAdd && (
        <button
          type="button"
          onClick={addItem}
          disabled={disabled}
          className="os-repeater-add"
        >
          <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
            <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
          </svg>
          Add {group.label || 'Item'}
        </button>
      )}

      {group.minItems !== undefined && group.maxItems !== undefined && (
        <p className="os-repeater-hint">
          {group.minItems === group.maxItems
            ? `Exactly ${group.minItems} item(s) required`
            : `${group.minItems} to ${group.maxItems} items allowed`}
          {` • ${itemsWithIds.length} added`}
        </p>
      )}
    </div>
  )
}
