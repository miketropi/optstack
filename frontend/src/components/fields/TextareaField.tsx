import { useCallback, useRef, useEffect } from 'react'
import type { FieldRendererProps } from '../../schema/types'

export function TextareaField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const textareaRef = useRef<HTMLTextAreaElement>(null)
  
  const stringValue = (value as string) ?? (field.default as string) ?? ''
  const maxLength = field.attributes?.maxLength as number | undefined
  const showCount = field.attributes?.showCount !== false && maxLength
  const autoGrow = field.attributes?.autoGrow === true
  const rows = (field.attributes?.rows as number) ?? 4
  
  useEffect(() => {
    if (autoGrow && textareaRef.current) {
      textareaRef.current.style.height = 'auto'
      textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`
    }
  }, [stringValue, autoGrow])

  const handleChange = useCallback((e: React.ChangeEvent<HTMLTextAreaElement>) => {
    onChange(e.target.value)
  }, [onChange])

  return (
    <div className={`os-field os-field-textarea ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <textarea
          ref={textareaRef}
          id={field.key}
          name={field.key}
          value={stringValue}
          onChange={handleChange}
          disabled={disabled}
          rows={rows}
          className="os-textarea"
          placeholder={field.attributes?.placeholder as string}
          maxLength={maxLength}
        />

        {(field.description || showCount) && (
          <div className="os-field-footer">
            {field.description && <p className="os-description">{field.description}</p>}
            {showCount && (
              <span className={`os-char-count ${maxLength && stringValue.length >= maxLength ? 'os-char-limit' : ''}`}>
                {stringValue.length}{maxLength ? `/${maxLength}` : ''}
              </span>
            )}
          </div>
        )}
        
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
