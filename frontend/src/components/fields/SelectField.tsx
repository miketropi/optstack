import { useState, useRef, useEffect, useCallback } from 'react'
import type { FieldRendererProps } from '../../schema/types'

export function SelectField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [search, setSearch] = useState('')
  const wrapperRef = useRef<HTMLDivElement>(null)
  const searchInputRef = useRef<HTMLInputElement>(null)
  
  const selectedValue = value ?? field.default ?? ''
  const placeholder = (field.attributes?.placeholder as string) || 'Select...'
  const searchable = field.attributes?.searchable === true
  const options = field.options || []
  
  const selectedOption = options.find(opt => String(opt.value) === String(selectedValue))
  
  const filteredOptions = searchable && search
    ? options.filter(opt => opt.label.toLowerCase().includes(search.toLowerCase()))
    : options

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
        setIsOpen(false)
        setSearch('')
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  useEffect(() => {
    if (isOpen && searchable && searchInputRef.current) {
      searchInputRef.current.focus()
    }
  }, [isOpen, searchable])

  const handleSelect = useCallback((optionValue: string | number | boolean) => {
    onChange(optionValue)
    setIsOpen(false)
    setSearch('')
  }, [onChange])

  return (
    <div className={`os-field os-field-select ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div ref={wrapperRef} className={`os-select-wrapper ${isOpen ? 'os-open' : ''}`}>
          <button
            type="button"
            id={field.key}
            onClick={() => !disabled && setIsOpen(!isOpen)}
            disabled={disabled}
            className={`os-select-trigger ${!selectedOption ? 'os-placeholder' : ''}`}
            aria-haspopup="listbox"
            aria-expanded={isOpen}
          >
            <span className="os-select-value">
              {selectedOption ? selectedOption.label : placeholder}
            </span>
            <svg className={`os-select-arrow ${isOpen ? 'os-rotated' : ''}`} viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
            </svg>
          </button>
          
          {isOpen && (
            <div className="os-select-dropdown">
              {searchable && (
                <div className="os-select-search">
                  <svg className="os-search-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clipRule="evenodd" />
                  </svg>
                  <input
                    ref={searchInputRef}
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search..."
                    className="os-select-search-input"
                  />
                </div>
              )}
              
              <ul className="os-select-options" role="listbox">
                {filteredOptions.length === 0 ? (
                  <li className="os-select-empty">No options</li>
                ) : (
                  filteredOptions.map((option) => (
                    <li
                      key={String(option.value)}
                      role="option"
                      aria-selected={String(option.value) === String(selectedValue)}
                      className={`os-select-option ${String(option.value) === String(selectedValue) ? 'os-selected' : ''}`}
                      onClick={() => handleSelect(option.value)}
                    >
                      <span>{option.label}</span>
                      {String(option.value) === String(selectedValue) && (
                        <svg className="os-check-icon" viewBox="0 0 20 20" fill="currentColor">
                          <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                        </svg>
                      )}
                    </li>
                  ))
                )}
              </ul>
            </div>
          )}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
