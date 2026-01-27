import { useState, useCallback, useRef, useEffect } from 'react'
import type { FieldRendererProps } from '../../schema/types'

const DEFAULT_PRESETS = [
  '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
  '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
  '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
  '#ec4899', '#f43f5e', '#000000', '#6b7280', '#ffffff',
]

export function ColorField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [inputValue, setInputValue] = useState('')
  const wrapperRef = useRef<HTMLDivElement>(null)
  
  const colorValue = (value as string) || (field.default as string) || '#3b82f6'
  const presets = (field.attributes?.presets as string[]) || DEFAULT_PRESETS
  const showPresets = field.attributes?.showPresets !== false

  useEffect(() => {
    setInputValue(colorValue)
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

  const handleColorChange = useCallback((newColor: string) => {
    onChange(newColor)
    setInputValue(newColor)
  }, [onChange])

  const handleInputBlur = useCallback(() => {
    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(inputValue)) {
      onChange(inputValue)
    } else {
      setInputValue(colorValue)
    }
  }, [inputValue, colorValue, onChange])

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
            >
              <span className="os-color-checkerboard" />
            </button>
            
            <input
              type="text"
              id={field.key}
              value={inputValue}
              onChange={(e) => setInputValue(e.target.value.toUpperCase())}
              onBlur={handleInputBlur}
              onKeyDown={(e) => e.key === 'Enter' && handleInputBlur()}
              disabled={disabled}
              className="os-color-input"
              placeholder="#000000"
              maxLength={7}
            />
            
            <label className="os-color-picker-btn">
              <input
                type="color"
                value={colorValue}
                onChange={(e) => handleColorChange(e.target.value)}
                disabled={disabled}
                className="os-color-native"
              />
              <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                <path fillRule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clipRule="evenodd" />
              </svg>
            </label>
          </div>
          
          {isOpen && showPresets && (
            <div className="os-color-dropdown">
              <div className="os-color-presets">
                {presets.map((preset) => (
                  <button
                    key={preset}
                    type="button"
                    className={`os-color-preset ${preset === colorValue ? 'os-selected' : ''}`}
                    style={{ backgroundColor: preset }}
                    onClick={() => {
                      handleColorChange(preset)
                      setIsOpen(false)
                    }}
                    title={preset}
                  >
                    {preset === colorValue && (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="os-preset-check">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                    )}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
