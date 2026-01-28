import { useCallback } from 'react'
import type { FieldRendererProps, FieldOption } from '../../schema/types'

export function CheckboxGroupField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const options = (field.options || []) as FieldOption[]
  const currentValues = Array.isArray(value) ? value : (field.default as unknown[]) || []
  const layout = (field.attributes?.layout as 'vertical' | 'horizontal' | 'cards') || 'vertical'
  const showSelectAll = field.attributes?.showSelectAll === true && options.length > 2

  const handleChange = useCallback((optionValue: string | number | boolean, checked: boolean) => {
    if (checked) {
      onChange([...currentValues, optionValue])
    } else {
      onChange(currentValues.filter((v) => v !== optionValue))
    }
  }, [currentValues, onChange])

  const handleSelectAll = useCallback(() => {
    if (currentValues.length === options.length) {
      onChange([])
    } else {
      onChange(options.map(opt => opt.value))
    }
  }, [currentValues, options, onChange])

  const allSelected = currentValues.length === options.length
  const someSelected = currentValues.length > 0 && currentValues.length < options.length

  return (
    <div className={`os-field os-field-checkbox-group ${error ? 'os-field-error' : ''}`}>
      
      <legend className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </legend>
      
      <div className="os-field-body">
        {showSelectAll && (
          <label className={`os-checkbox-option os-select-all ${someSelected ? 'os-indeterminate' : ''}`}>
            <input
              type="checkbox"
              checked={allSelected}
              onChange={handleSelectAll}
              disabled={disabled}
              className="os-checkbox-input"
              ref={(el) => { if (el) el.indeterminate = someSelected }}
            />
            <span className="os-checkbox-control">
              {allSelected ? (
                <svg viewBox="0 0 20 20" fill="currentColor" className="os-checkbox-check">
                  <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                </svg>
              ) : someSelected ? (
                <svg viewBox="0 0 20 20" fill="currentColor" className="os-checkbox-minus">
                  <path fillRule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clipRule="evenodd" />
                </svg>
              ) : null}
            </span>
            <span className="os-checkbox-label">Select all</span>
          </label>
        )}
        
        <div className={`os-checkbox-group os-checkbox-${layout}`}>
          {options.map((option) => {
            const isChecked = currentValues.includes(option.value)
            
            return (
              <label 
                key={String(option.value)} 
                className={`os-checkbox-option ${isChecked ? 'os-selected' : ''} ${disabled ? 'os-disabled' : ''}`}
              >
                <input
                  type="checkbox"
                  name={`${field.key}[]`}
                  value={String(option.value)}
                  checked={isChecked}
                  onChange={(e) => handleChange(option.value, e.target.checked)}
                  disabled={disabled}
                  className="os-checkbox-input"
                />
                
                <span className="os-checkbox-control">
                  {isChecked && (
                    <svg viewBox="0 0 20 20" fill="currentColor" className="os-checkbox-check">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  )}
                </span>
                
                <span className="os-checkbox-content">
                  <span className="os-checkbox-label">{option.label}</span>
                  {option.description && (
                    <span className="os-checkbox-description">{option.description}</span>
                  )}
                </span>
              </label>
            )
          })}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
      
    </div>
  )
}
