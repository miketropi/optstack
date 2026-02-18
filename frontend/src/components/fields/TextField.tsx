import { useState, useCallback } from 'react'
import { Mail, Link, Phone, Lock, Eye, EyeOff } from 'lucide-react'
import type { FieldRendererProps } from '../../schema/types'

// Icon components from lucide-react
const Icons = {
  email: <Mail className="os-input-icon" />,
  url: <Link className="os-input-icon" />,
  tel: <Phone className="os-input-icon" />,
  password: <Lock className="os-input-icon" />,
  eye: <Eye className="os-input-icon" />,
  eyeOff: <EyeOff className="os-input-icon" />,
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
            disabled={disabled ?? field.attributes?.disabled === true}
            readOnly={field.attributes?.readOnly === true}
            multiple={field.attributes?.multiple === true}
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
