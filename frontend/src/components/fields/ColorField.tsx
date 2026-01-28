import { useState, useCallback, useRef, useEffect } from 'react'
import { SketchPicker, ColorResult } from 'react-color'
import type { FieldRendererProps } from '../../schema/types'

const DEFAULT_PRESETS = [
  '#D0021B', '#F5A623', '#F8E71C', '#8B572A', '#7ED321',
  '#417505', '#BD10E0', '#9013FE', '#4A90D9', '#50E3C2',
  '#B8E986', '#000000', '#4A4A4A', '#9B9B9B', '#FFFFFF',
]

export function ColorField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [inputValue, setInputValue] = useState('')
  const wrapperRef = useRef<HTMLDivElement>(null)
  
  const colorValue = (value as string) || (field.default as string) || '#3b82f6'
  const presets = (field.attributes?.presets as string[]) || DEFAULT_PRESETS
  const showAlpha = field.attributes?.alpha === true

  useEffect(() => {
    setInputValue(colorValue.toUpperCase())
  }, [colorValue])

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const handleColorChange = useCallback((color: ColorResult) => {
    let newColor: string
    if (showAlpha && color.rgb.a !== undefined && color.rgb.a < 1) {
      newColor = `rgba(${color.rgb.r}, ${color.rgb.g}, ${color.rgb.b}, ${color.rgb.a})`
    } else {
      newColor = color.hex.toUpperCase()
    }
    onChange(newColor)
    setInputValue(color.hex.toUpperCase())
  }, [onChange, showAlpha])

  const handleInputBlur = useCallback(() => {
    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(inputValue)) {
      onChange(inputValue)
    } else {
      setInputValue(colorValue.toUpperCase())
    }
  }, [inputValue, colorValue, onChange])

  const handleInputChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value.toUpperCase()
    setInputValue(val)
  }, [])

  return (
    <div className={`os-field os-field-color ${error ? 'os-field-error' : ''}`}>
      <label className="os-label" htmlFor={field.key}>
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div ref={wrapperRef} className="os-color-wrapper">
          <div className="os-color-input-group">
            <button
              type="button"
              className="os-color-preview"
              style={{ backgroundColor: colorValue }}
              onClick={() => !disabled && setIsOpen(!isOpen)}
              disabled={disabled}
              aria-label="Open color picker"
            >
              <span className="os-color-checkerboard" />
            </button>
            
            <input
              type="text"
              id={field.key}
              value={inputValue}
              onChange={handleInputChange}
              onBlur={handleInputBlur}
              onKeyDown={(e) => e.key === 'Enter' && handleInputBlur()}
              disabled={disabled}
              className="os-color-input"
              placeholder="#000000"
              maxLength={7}
            />
          </div>
          
          {isOpen && (
            <div className="os-color-picker-popover">
              <SketchPicker
                color={colorValue}
                onChange={handleColorChange}
                onChangeComplete={handleColorChange}
                disableAlpha={!showAlpha}
                presetColors={presets}
                width="220"
              />
            </div>
          )}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
