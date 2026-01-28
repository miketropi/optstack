import { useMemo } from 'react'
import type { FieldRendererProps } from '../../schema/types'

export function RangeField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const numValue = Number(value ?? field.default ?? 50)
  const min = Number(field.attributes?.min ?? 0)
  const max = Number(field.attributes?.max ?? 100)
  const step = Number(field.attributes?.step ?? 1)
  const showValue = field.attributes?.showValue !== false
  const unit = (field.attributes?.unit as string) || ''

  const percentage = useMemo(() => {
    return ((numValue - min) / (max - min)) * 100
  }, [numValue, min, max])

  const displayValue = useMemo(() => {
    const precision = step < 1 ? String(step).split('.')[1]?.length || 0 : 0
    return numValue.toFixed(precision) + unit
  }, [numValue, step, unit])

  return (
    <div className={`os-field os-field-range ${error ? 'os-field-error' : ''}`}>
      <div className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </div>
      
      <div className="os-field-body">
        <div className="os-range-header os-font-mono">
          {showValue && <span className="os-range-value">{displayValue}</span>}
        </div>
        
        <div className="os-range-container">
          <div className="os-range-track">
            <div className="os-range-fill" style={{ width: `${percentage}%` }} />
            <input
              type="range"
              id={field.key}
              name={field.key}
              value={numValue}
              min={min}
              max={max}
              step={step}
              onChange={(e) => onChange(Number(e.target.value))}
              disabled={disabled}
              className="os-range-input"
            />
          </div>
          
          <div className="os-range-labels os-font-mono">
            <span>{min}{unit}</span>
            <span>{max}{unit}</span>
          </div>
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
