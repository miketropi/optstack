import { useState, useCallback } from 'react'
import type { FieldRendererProps } from '../../schema/types'

// SVG Icons
const Icons = {
  email: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
      <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
    </svg>
  ),
  url: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path fillRule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clipRule="evenodd" />
    </svg>
  ),
  tel: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
    </svg>
  ),
  password: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
    </svg>
  ),
  eye: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
      <path fillRule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clipRule="evenodd" />
    </svg>
  ),
  eyeOff: (
    <svg className="os-input-icon" viewBox="0 0 20 20" fill="currentColor">
      <path fillRule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clipRule="evenodd" />
      <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
    </svg>
  ),
}

export function TextField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [showPassword, setShowPassword] = useState(false)
  
  const stringValue = (value as string) ?? (field.default as string) ?? ''
  const maxLength = field.attributes?.maxLength as number | undefined
  const showCount = field.attributes?.showCount === true && maxLength
  
  const inputType = field.type === 'email' ? 'email' 
    : field.type === 'url' ? 'url'
    : field.type === 'password' ? (showPassword ? 'text' : 'password')
    : field.type === 'tel' ? 'tel'
    : 'text'

  const hasIcon = ['email', 'url', 'tel', 'password'].includes(field.type)
  const icon = Icons[field.type as keyof typeof Icons]

  const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    onChange(e.target.value)
  }, [onChange])

  return (
    <div className={`os-field os-field-text ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div className={`os-input-wrapper ${hasIcon ? 'os-has-icon' : ''}`}>
          {hasIcon && field.type !== 'password' && (
            <span className="os-input-icon-left">{icon}</span>
          )}
          
          <input
            type={inputType}
            id={field.key}
            name={field.key}
            value={stringValue}
            onChange={handleChange}
            disabled={disabled}
            className="os-input"
            placeholder={field.attributes?.placeholder as string}
            maxLength={maxLength}
            autoComplete={field.attributes?.autoComplete as string}
          />
          
          {field.type === 'password' && (
            <button
              type="button"
              className="os-input-action"
              onClick={() => setShowPassword(!showPassword)}
              tabIndex={-1}
            >
              {showPassword ? Icons.eyeOff : Icons.eye}
            </button>
          )}
        </div>

        {(field.description || showCount) && (
          <div className="os-field-footer">
            {field.description && <p className="os-description">{field.description}</p>}
            {showCount && (
              <span className={`os-char-count ${stringValue.length >= maxLength ? 'os-char-limit' : ''}`}>
                {stringValue.length}/{maxLength}
              </span>
            )}
          </div>
        )}
        
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
