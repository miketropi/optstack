import { useState, useCallback } from 'react'
import type { FieldRendererProps } from '../../schema/types'

export function NumberField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isFocused, setIsFocused] = useState(false)
  
  const numValue = value !== undefined && value !== null ? Number(value) : (field.default as number) ?? ''
  const min = field.attributes?.min as number | undefined
  const max = field.attributes?.max as number | undefined
  const step = (field.attributes?.step as number) ?? 1
  const showControls = field.attributes?.showControls !== false
  const prefix = field.attributes?.prefix as string | undefined
  const suffix = field.attributes?.suffix as string | undefined

  const handleChange = useCallback((newValue: number | null) => {
    if (newValue === null) {
      onChange(null)
      return
    }
    
    let val = newValue
    if (min !== undefined && val < min) val = min
    if (max !== undefined && val > max) val = max
    onChange(val)
  }, [onChange, min, max])

  const increment = useCallback(() => {
    const current = typeof numValue === 'number' ? numValue : 0
    handleChange(current + step)
  }, [numValue, step, handleChange])

  const decrement = useCallback(() => {
    const current = typeof numValue === 'number' ? numValue : 0
    handleChange(current - step)
  }, [numValue, step, handleChange])

  return (
    <div className={`os-field os-field-number ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div className={`os-number-wrapper ${isFocused ? 'os-focused' : ''}`}>
          {prefix && <span className="os-input-prefix">{prefix}</span>}
          
          {showControls && (
            <button
              type="button"
              onClick={decrement}
              disabled={disabled || (min !== undefined && typeof numValue === 'number' && numValue <= min)}
              className="os-number-btn os-number-decrement"
              tabIndex={-1}
            >
              <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                <path fillRule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clipRule="evenodd" />
              </svg>
            </button>
          )}
          
          <input
            type="number"
            id={field.key}
            name={field.key}
            value={numValue}
            onChange={(e) => {
              const val = e.target.value
              handleChange(val === '' ? null : Number(val))
            }}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            disabled={disabled}
            className="os-input os-number-input"
            min={min}
            max={max}
            step={step}
            placeholder={field.attributes?.placeholder as string}
          />
          
          {showControls && (
            <button
              type="button"
              onClick={increment}
              disabled={disabled || (max !== undefined && typeof numValue === 'number' && numValue >= max)}
              className="os-number-btn os-number-increment"
              tabIndex={-1}
            >
              <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
              </svg>
            </button>
          )}
          
          {suffix && <span className="os-input-suffix">{suffix}</span>}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
