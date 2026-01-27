import type { FieldRendererProps } from '../../schema/types'

export function ToggleField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const isChecked = Boolean(value ?? field.default ?? false)

  const handleToggle = () => {
    if (!disabled) onChange(!isChecked)
  }

  return (
    <div className={`os-field os-field-toggle-wrap ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div className="os-toggle-wrapper">
          <button
            type="button"
            role="switch"
            aria-checked={isChecked}
            onClick={handleToggle}
            disabled={disabled}
            className={`os-toggle ${isChecked ? 'os-toggle-active' : ''}`}
          />
          <span className="os-toggle-label" onClick={handleToggle}>
            {isChecked ? 'Enabled' : 'Disabled'}
          </span>
        </div>
        
        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
