import { useState, useCallback } from 'react'
import type { FieldRendererProps } from '../../schema/types'

export function DateField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isFocused, setIsFocused] = useState(false)
  
  const dateValue = (value as string) || (field.default as string) || ''
  const showTime = field.attributes?.showTime === true
  const inputType = showTime ? 'datetime-local' : 'date'

  const handleClear = useCallback(() => {
    onChange('')
  }, [onChange])

  const formatDisplayDate = (dateStr: string) => {
    if (!dateStr) return ''
    try {
      const date = new Date(dateStr)
      if (showTime) {
        return date.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
      }
      return date.toLocaleDateString(undefined, { dateStyle: 'medium' })
    } catch {
      return dateStr
    }
  }

  return (
    <div className={`os-field os-field-date ${error ? 'os-field-error' : ''}`}>
      <label className="os-label" htmlFor={field.key}>
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div className={`os-date-wrapper ${isFocused ? 'os-focused' : ''}`}>
          <div className="os-date-icon">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
              <path fillRule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clipRule="evenodd" />
            </svg>
          </div>
          
          <input
            type={inputType}
            id={field.key}
            name={field.key}
            value={dateValue}
            onChange={(e) => onChange(e.target.value)}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            disabled={disabled}
            className="os-date-input"
            min={field.attributes?.min as string}
            max={field.attributes?.max as string}
          />
          
          {dateValue && !disabled && (
            <button type="button" onClick={handleClear} className="os-date-clear">
              <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
            </button>
          )}
        </div>
        
        {dateValue && <p className="os-date-display">{formatDisplayDate(dateValue)}</p>}
        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
