import { useState, useRef, useEffect, useCallback } from 'react'
import { SketchPicker, type ColorResult } from 'react-color'
import type { TokenDefinition } from './types'

interface Props {
  tokenKey: string
  definition: TokenDefinition
  value: unknown
  onChange: (value: unknown) => void
  disabled?: boolean
}

export function TokenControl({ tokenKey, definition, value, onChange, disabled }: Props) {
  const label = camelToLabel(tokenKey)

  switch (definition.control) {
    case 'color':
      return <ColorControl label={label} value={value} onChange={onChange} disabled={disabled} />
    case 'font-family':
      return <FontFamilyControl label={label} value={value} onChange={onChange} disabled={disabled} />
    case 'size':
      return <SizeControl label={label} value={value} onChange={onChange} disabled={disabled} units={definition.units} />
    case 'spacing':
      return <TextControl label={label} value={value} onChange={onChange} disabled={disabled} placeholder="e.g. 10px 20px" />
    case 'shadow':
      return <TextControl label={label} value={value} onChange={onChange} disabled={disabled} placeholder="e.g. 0 1px 3px rgba(0,0,0,0.1)" />
    case 'range':
      return <RangeControl label={label} value={value} onChange={onChange} disabled={disabled} min={definition.min} max={definition.max} step={definition.step} />
    case 'select':
      return <SelectControl label={label} value={value} onChange={onChange} disabled={disabled} options={definition.options ?? []} />
    case 'scale':
      return <ScaleControl label={label} value={value} onChange={onChange} disabled={disabled} keys={definition.keys ?? []} />
    default:
      return <TextControl label={label} value={value} onChange={onChange} disabled={disabled} />
  }
}

function ColorControl({ label, value, onChange, disabled }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean }) {
  const [open, setOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)
  const colorVal = String(value || '#000000')

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  return (
    <div className="os-dp-field" ref={ref}>
      <label className="os-dp-field-label">{label}</label>
      <div className="os-dp-field-control os-dp-field-color">
        <button
          type="button"
          className="os-dp-color-swatch"
          style={{ backgroundColor: colorVal }}
          onClick={() => !disabled && setOpen(!open)}
          disabled={disabled}
        />
        <input
          type="text"
          className="os-dp-input"
          value={colorVal}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
          maxLength={9}
        />
        {open && (
          <div className="os-dp-color-popover">
            <SketchPicker
              color={colorVal}
              onChange={(c: ColorResult) => onChange(c.hex.toUpperCase())}
              onChangeComplete={(c: ColorResult) => onChange(c.hex.toUpperCase())}
              disableAlpha
              width="210"
            />
          </div>
        )}
      </div>
    </div>
  )
}

function FontFamilyControl({ label, value, onChange, disabled }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean }) {
  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <input
        type="text"
        className="os-dp-input"
        value={String(value || '')}
        onChange={(e) => onChange(e.target.value)}
        disabled={disabled}
        placeholder="Inter, sans-serif"
        style={{ fontFamily: String(value || 'inherit') }}
      />
    </div>
  )
}

function SizeControl({ label, value, onChange, disabled, units }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean; units?: string[] }) {
  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <div className="os-dp-field-control os-dp-field-size">
        <input
          type="text"
          className="os-dp-input"
          value={String(value || '')}
          onChange={(e) => onChange(e.target.value)}
          disabled={disabled}
          placeholder={units ? `e.g. 16${units[0]}` : ''}
        />
        {units && units.length > 0 && (
          <span className="os-dp-size-hint">{units.join(' | ')}</span>
        )}
      </div>
    </div>
  )
}

function RangeControl({ label, value, onChange, disabled, min = 0, max = 10, step = 0.1 }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean; min?: number; max?: number; step?: number }) {
  const numVal = typeof value === 'number' ? value : parseFloat(String(value)) || min

  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <div className="os-dp-field-control os-dp-field-range">
        <input
          type="range"
          min={min}
          max={max}
          step={step}
          value={numVal}
          onChange={(e) => onChange(parseFloat(e.target.value))}
          disabled={disabled}
          className="os-dp-range"
        />
        <span className="os-dp-range-val">{numVal}</span>
      </div>
    </div>
  )
}

function SelectControl({ label, value, onChange, disabled, options }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean; options: (string | number)[] }) {
  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <select
        className="os-dp-select"
        value={String(value ?? '')}
        onChange={(e) => {
          const v = e.target.value
          onChange(options.some((o) => typeof o === 'number') ? Number(v) : v)
        }}
        disabled={disabled}
      >
        {options.map((opt) => (
          <option key={String(opt)} value={String(opt)}>{String(opt)}</option>
        ))}
      </select>
    </div>
  )
}

function ScaleControl({ label, value, onChange, disabled, keys }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean; keys: string[] }) {
  const objVal = (typeof value === 'object' && value !== null ? value : {}) as Record<string, string>

  const handleChange = useCallback((key: string, val: string) => {
    onChange({ ...objVal, [key]: val })
  }, [objVal, onChange])

  return (
    <div className="os-dp-field os-dp-field-scale-wrap">
      <label className="os-dp-field-label">{label}</label>
      <div className="os-dp-scale-grid">
        {keys.map((k) => (
          <div key={k} className="os-dp-scale-item">
            <span className="os-dp-scale-key">{k.toUpperCase()}</span>
            <input
              type="text"
              className="os-dp-input os-dp-scale-input"
              value={objVal[k] ?? ''}
              onChange={(e) => handleChange(k, e.target.value)}
              disabled={disabled}
              placeholder="2rem"
            />
          </div>
        ))}
      </div>
    </div>
  )
}

function TextControl({ label, value, onChange, disabled, placeholder }: { label: string; value: unknown; onChange: (v: unknown) => void; disabled?: boolean; placeholder?: string }) {
  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <input
        type="text"
        className="os-dp-input"
        value={String(value ?? '')}
        onChange={(e) => onChange(e.target.value)}
        disabled={disabled}
        placeholder={placeholder}
      />
    </div>
  )
}

function camelToLabel(str: string): string {
  return str.replace(/([A-Z])/g, ' $1').replace(/^./, (s) => s.toUpperCase()).trim()
}
