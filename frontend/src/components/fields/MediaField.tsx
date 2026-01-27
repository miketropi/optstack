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

export function MediaField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isDragging, setIsDragging] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  
  const mediaValue = (value as MediaValue) || {}
  const allowedTypes = (field.attributes?.allowedTypes as string[]) || ['image']
  const buttonText = (field.attributes?.buttonText as string) || 'Select Media'
  const previewSize = (field.attributes?.previewSize as string) || 'thumbnail'
  const isImage = allowedTypes.includes('image') || mediaValue.mime?.startsWith('image/')

  const openMediaLibrary = useCallback(() => {
    if (!window.wp?.media) return

    const frame = window.wp.media({
      title: field.label,
      button: { text: buttonText },
      multiple: false,
      library: { type: allowedTypes },
    })

    frame.on('select', () => {
      setIsLoading(true)
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
      setIsLoading(false)
    })

    frame.open()
  }, [field.label, buttonText, allowedTypes, previewSize, onChange])

  const clearMedia = useCallback(() => {
    onChange({})
  }, [onChange])

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

  const getFileIcon = () => {
    const mime = mediaValue.mime || ''
    if (mime.startsWith('video/')) return 'dashicons-video-alt3'
    if (mime.startsWith('audio/')) return 'dashicons-format-audio'
    if (mime.includes('pdf')) return 'dashicons-pdf'
    return 'dashicons-media-default'
  }

  return (
    <div className={`os-field os-field-media ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div 
          className={`os-media-wrapper ${isDragging ? 'os-dragging' : ''} ${mediaValue.url ? 'os-has-media' : ''}`}
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
          ) : mediaValue.url ? (
            <div className="os-media-preview">
              {isImage ? (
                <div className="os-media-image-wrap">
                  <img src={mediaValue.url} alt={mediaValue.alt || ''} className="os-media-image" />
                </div>
              ) : (
                <div className="os-media-file">
                  <span className={`dashicons ${getFileIcon()}`} />
                  <div className="os-media-file-info">
                    <span className="os-media-filename">{mediaValue.filename || mediaValue.title || 'File'}</span>
                    {mediaValue.filesize && <span className="os-media-filesize">{formatFileSize(mediaValue.filesize)}</span>}
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

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
