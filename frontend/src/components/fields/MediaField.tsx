import { useCallback, useState } from 'react'
import type { FieldRendererProps } from '../../schema/types'
import '../../types/wordpress.d.ts'

interface MediaValue {
  id?: number
  url?: string
  alt?: string
  title?: string
  filename?: string
  filesize?: number
  mime?: string
}

type MediaFieldValue = MediaValue | MediaValue[] | null

export function MediaField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isDragging, setIsDragging] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [dragIndex, setDragIndex] = useState<number | null>(null)
  const [dragOverIndex, setDragOverIndex] = useState<number | null>(null)
  
  // Check if multiple selection is enabled
  const isMultiple = field.attributes?.multiple === true
  const maxFiles = (field.attributes?.maxFiles as number) || 0 // 0 = unlimited
  const allowedTypes = (field.attributes?.allowedTypes as string[]) || ['image']
  const buttonText = (field.attributes?.buttonText as string) || (isMultiple ? 'Select Images' : 'Select Media')
  const previewSize = (field.attributes?.previewSize as string) || 'thumbnail'
  
  // Normalize value to always work with arrays internally for multiple, or single object for single
  const normalizeValue = (val: MediaFieldValue): MediaValue[] => {
    if (!val) return []
    if (Array.isArray(val)) return val.filter(v => v && v.id)
    if (val && typeof val === 'object' && (val as MediaValue).id) return [val as MediaValue]
    return []
  }
  
  const mediaItems = normalizeValue(value as MediaFieldValue)
  const hasMedia = mediaItems.length > 0
  const canAddMore = isMultiple && (maxFiles === 0 || mediaItems.length < maxFiles)
  
  // For single mode, use first item
  const singleMediaValue = isMultiple ? null : (mediaItems[0] || {})

  const isImage = (item: MediaValue) => {
    return allowedTypes.includes('image') || item.mime?.startsWith('image/')
  }

  const openMediaLibrary = useCallback(() => {
    if (!window.wp?.media) return

    const frame = window.wp.media({
      title: field.label,
      button: { text: isMultiple ? 'Add to Selection' : buttonText },
      multiple: isMultiple ? 'add' : false,
      library: { type: allowedTypes },
    })

    frame.on('select', () => {
      setIsLoading(true)
      
      if (isMultiple) {
        // Multiple selection mode
        const selection = frame.state().get('selection').toJSON()
        const newItems: MediaValue[] = selection.map((attachment: {
          id: number
          url: string
          alt?: string
          title?: string
          filename?: string
          filesizeInBytes?: number
          mime?: string
          sizes?: Record<string, { url: string }>
        }) => {
          const size = attachment.sizes?.[previewSize] || attachment.sizes?.full
          return {
            id: attachment.id,
            url: size?.url || attachment.url,
            alt: attachment.alt,
            title: attachment.title,
            filename: attachment.filename,
            filesize: attachment.filesizeInBytes,
            mime: attachment.mime,
          }
        })
        
        // Merge with existing items, avoiding duplicates
        const existingIds = new Set(mediaItems.map(item => item.id))
        const uniqueNewItems = newItems.filter(item => !existingIds.has(item.id))
        let mergedItems = [...mediaItems, ...uniqueNewItems]
        
        // Apply max files limit
        if (maxFiles > 0 && mergedItems.length > maxFiles) {
          mergedItems = mergedItems.slice(0, maxFiles)
        }
        
        onChange(mergedItems)
      } else {
        // Single selection mode
        const attachment = frame.state().get('selection').first().toJSON()
        const size = attachment.sizes?.[previewSize] || attachment.sizes?.full

        onChange({
          id: attachment.id,
          url: size?.url || attachment.url,
          alt: attachment.alt,
          title: attachment.title,
          filename: attachment.filename,
          filesize: attachment.filesizeInBytes,
          mime: attachment.mime,
        })
      }
      
      setIsLoading(false)
    })

    frame.open()
  }, [field.label, buttonText, allowedTypes, previewSize, onChange, isMultiple, mediaItems, maxFiles])

  const clearMedia = useCallback(() => {
    onChange(isMultiple ? [] : {})
  }, [onChange, isMultiple])

  const removeItem = useCallback((index: number) => {
    if (isMultiple) {
      const newItems = mediaItems.filter((_, i) => i !== index)
      onChange(newItems)
    } else {
      onChange({})
    }
  }, [mediaItems, onChange, isMultiple])

  // Drag and drop reordering for multiple items
  const handleDragStart = useCallback((index: number) => {
    setDragIndex(index)
  }, [])

  const handleDragOver = useCallback((e: React.DragEvent, index: number) => {
    e.preventDefault()
    setDragOverIndex(index)
  }, [])

  const handleDragEnd = useCallback(() => {
    if (dragIndex !== null && dragOverIndex !== null && dragIndex !== dragOverIndex) {
      const newItems = [...mediaItems]
      const [draggedItem] = newItems.splice(dragIndex, 1)
      newItems.splice(dragOverIndex, 0, draggedItem)
      onChange(newItems)
    }
    setDragIndex(null)
    setDragOverIndex(null)
  }, [dragIndex, dragOverIndex, mediaItems, onChange])

  const formatFileSize = (bytes?: number) => {
    if (!bytes) return ''
    const units = ['B', 'KB', 'MB', 'GB']
    let size = bytes
    let unitIndex = 0
    while (size >= 1024 && unitIndex < units.length - 1) {
      size /= 1024
      unitIndex++
    }
    return `${size.toFixed(1)} ${units[unitIndex]}`
  }

  const getFileIcon = (mime?: string) => {
    const mimeType = mime || ''
    if (mimeType.startsWith('video/')) return 'dashicons-video-alt3'
    if (mimeType.startsWith('audio/')) return 'dashicons-format-audio'
    if (mimeType.includes('pdf')) return 'dashicons-pdf'
    return 'dashicons-media-default'
  }

  // Render a single media item (for grid in multiple mode)
  const renderMediaItem = (item: MediaValue, index: number) => {
    const itemIsImage = isImage(item)
    
    return (
      <div 
        key={item.id || index}
        className={`os-media-grid-item ${dragIndex === index ? 'os-dragging' : ''} ${dragOverIndex === index ? 'os-drag-over' : ''}`}
        draggable={isMultiple && !disabled}
        onDragStart={() => handleDragStart(index)}
        onDragOver={(e) => handleDragOver(e, index)}
        onDragEnd={handleDragEnd}
      >
        {itemIsImage ? (
          <img src={item.url} alt={item.alt || ''} className="os-media-grid-image" />
        ) : (
          <div className="os-media-grid-file">
            <span className={`dashicons ${getFileIcon(item.mime)}`} />
            <span className="os-media-grid-filename">{item.filename || item.title || 'File'}</span>
          </div>
        )}
        
        <button
          type="button"
          onClick={() => removeItem(index)}
          disabled={disabled}
          className="os-media-grid-remove"
          aria-label="Remove"
        >
          <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
            <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
          </svg>
        </button>
        
        {isMultiple && (
          <div className="os-media-grid-drag-handle" aria-label="Drag to reorder">
            <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
              <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z" />
            </svg>
          </div>
        )}
      </div>
    )
  }

  // Render the add more button for multiple mode
  const renderAddButton = () => (
    <button
      type="button"
      onClick={openMediaLibrary}
      disabled={disabled}
      className="os-media-grid-add"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="24" height="24">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      <span>Add</span>
    </button>
  )

  return (
    <div className={`os-field os-field-media ${isMultiple ? 'os-field-media-multiple' : ''} ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
        {isMultiple && maxFiles > 0 && (
          <span className="os-media-count">({mediaItems.length}/{maxFiles})</span>
        )}
        {isMultiple && maxFiles === 0 && mediaItems.length > 0 && (
          <span className="os-media-count">({mediaItems.length})</span>
        )}
      </label>
      
      <div className="os-field-body">
        <div 
          className={`os-media-wrapper ${isDragging ? 'os-dragging' : ''} ${hasMedia ? 'os-has-media' : ''}`}
          onDragOver={(e) => { e.preventDefault(); setIsDragging(true) }}
          onDragLeave={() => setIsDragging(false)}
          onDrop={(e) => { e.preventDefault(); setIsDragging(false) }}
        >
          {isLoading ? (
            <div className="os-media-loading">
              <svg className="os-spinner" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeDasharray="32" strokeDashoffset="12" />
              </svg>
              <span>Loading...</span>
            </div>
          ) : isMultiple ? (
            // Multiple mode: grid layout
            <div className="os-media-grid">
              {mediaItems.map((item, index) => renderMediaItem(item, index))}
              {canAddMore && renderAddButton()}
              {mediaItems.length === 0 && (
                <>
                  {/* <button type="button" onClick={openMediaLibrary} disabled={disabled} className="os-media-select os-media-select-empty">
                    <div className="os-media-select-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                      </svg>
                    </div>
                    <span className="os-media-select-text">{buttonText}</span>
                  </button> */}
                </>
              )}
            </div>
          ) : hasMedia && singleMediaValue ? (
            // Single mode with media selected
            <div className="os-media-preview">
              {isImage(singleMediaValue) ? (
                <div className="os-media-image-wrap">
                  <img src={singleMediaValue.url} alt={singleMediaValue.alt || ''} className="os-media-image" />
                </div>
              ) : (
                <div className="os-media-file">
                  <span className={`dashicons ${getFileIcon(singleMediaValue.mime)}`} />
                  <div className="os-media-file-info">
                    <span className="os-media-filename">{singleMediaValue.filename || singleMediaValue.title || 'File'}</span>
                    {singleMediaValue.filesize && <span className="os-media-filesize">{formatFileSize(singleMediaValue.filesize)}</span>}
                  </div>
                </div>
              )}
              
              <div className="os-media-actions">
                <button type="button" onClick={openMediaLibrary} disabled={disabled} className="os-btn os-btn-secondary os-btn-sm">
                  Replace
                </button>
                <button type="button" onClick={clearMedia} disabled={disabled} className="os-btn os-btn-secondary os-btn-sm os-btn-danger-text">
                  Remove
                </button>
              </div>
            </div>
          ) : (
            // Single mode without media
            <button type="button" onClick={openMediaLibrary} disabled={disabled} className="os-media-select">
              <div className="os-media-select-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                </svg>
              </div>
              <span className="os-media-select-text">{buttonText}</span>
            </button>
          )}
        </div>

        {isMultiple && hasMedia && (
          <div className="os-media-bulk-actions">
            <button type="button" onClick={clearMedia} disabled={disabled} className="os-btn os-btn-secondary os-btn-sm os-btn-danger-text">
              Remove All
            </button>
          </div>
        )}

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
