import type { FieldRendererProps, FieldOption } from '../../schema/types'

export function RadioField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const options = (field.options || []) as FieldOption[]
  const currentValue = value ?? field.default
  const layout = (field.attributes?.layout as 'vertical' | 'horizontal' | 'cards') || 'vertical'

  return (
    <div className={`os-field os-field-radio ${error ? 'os-field-error' : ''}`}>
      <fieldset className="os-fieldset">
        <legend className="os-label">
          {field.label}
          {field.attributes?.required === true && <span className="os-required">*</span>}
        </legend>
        
        <div className="os-field-body">
          <div className={`os-radio-group os-radio-${layout}`}>
            {options.map((option) => {
              const isSelected = currentValue === option.value
              
              return (
                <label 
                  key={String(option.value)} 
                  className={`os-radio-option ${isSelected ? 'os-selected' : ''} ${disabled ? 'os-disabled' : ''}`}
                >
                  <input
                    type="radio"
                    name={field.key}
                    value={String(option.value)}
                    checked={isSelected}
                    onChange={() => !disabled && onChange(option.value)}
                    disabled={disabled}
                    className="os-radio-input"
                  />
                  
                  <span className="os-radio-control">
                    <span className="os-radio-dot" />
                  </span>
                  
                  <span className="os-radio-content">
                    <span className="os-radio-label">{option.label}</span>
                    {option.description && (
                      <span className="os-radio-description">{option.description}</span>
                    )}
                  </span>
                </label>
              )
            })}
          </div>

          {field.description && <p className="os-description">{field.description}</p>}
          {error && <p className="os-error">{error}</p>}
        </div>
      </fieldset>
    </div>
  )
}
