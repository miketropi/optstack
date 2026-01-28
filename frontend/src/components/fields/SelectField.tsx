import { useMemo } from 'react'
import Select, { StylesConfig, SingleValue } from 'react-select'
import type { FieldRendererProps } from '../../schema/types'

interface SelectOption {
  value: string
  label: string
}

export function SelectField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const selectedValue = value ?? field.default ?? ''
  const placeholder = (field.attributes?.placeholder as string) || 'Select...'
  const searchable = field.attributes?.searchable !== false
  const clearable = field.attributes?.clearable === true
  
  // Convert field options to react-select format
  const options: SelectOption[] = useMemo(() => {
    return (field.options || []).map(opt => ({
      value: String(opt.value),
      label: opt.label
    }))
  }, [field.options])
  
  // Find selected option
  const selectedOption = useMemo(() => {
    return options.find(opt => opt.value === String(selectedValue)) || null
  }, [options, selectedValue])

  const handleChange = (newValue: SingleValue<SelectOption>) => {
    onChange(newValue ? newValue.value : '')
  }

  // Custom styles to match the design
  const customStyles: StylesConfig<SelectOption, false> = {
    control: (base, state) => ({
      ...base,
      minHeight: '38px',
      borderColor: error ? '#f44336' : state.isFocused ? '#2196f3' : '#e0e0e0',
      borderRadius: '4px',
      boxShadow: state.isFocused ? '0 0 0 1px #2196f3' : 'none',
      '&:hover': {
        borderColor: state.isFocused ? '#2196f3' : '#bdbdbd'
      },
      backgroundColor: disabled ? '#f5f5f5' : '#ffffff',
      cursor: disabled ? 'not-allowed' : 'pointer'
    }),
    valueContainer: (base) => ({
      ...base,
      padding: '0 12px'
    }),
    placeholder: (base) => ({
      ...base,
      color: '#9e9e9e',
      fontSize: '14px'
    }),
    singleValue: (base) => ({
      ...base,
      color: '#212121',
      fontSize: '14px'
    }),
    input: (base) => ({
      ...base,
      color: '#212121',
      fontSize: '14px',
      margin: 0,
      padding: 0
    }),
    indicatorSeparator: () => ({
      display: 'none'
    }),
    dropdownIndicator: (base, state) => ({
      ...base,
      color: '#757575',
      padding: '0 10px',
      transition: 'transform 150ms ease',
      transform: state.selectProps.menuIsOpen ? 'rotate(180deg)' : 'rotate(0deg)',
      '&:hover': {
        color: '#424242'
      }
    }),
    clearIndicator: (base) => ({
      ...base,
      color: '#9e9e9e',
      padding: '0 8px',
      '&:hover': {
        color: '#f44336'
      }
    }),
    menu: (base) => ({
      ...base,
      marginTop: '4px',
      borderRadius: '4px',
      boxShadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
      border: '1px solid #e0e0e0',
      overflow: 'hidden',
      zIndex: 100
    }),
    menuList: (base) => ({
      ...base,
      padding: '4px'
    }),
    option: (base, state) => ({
      ...base,
      backgroundColor: state.isSelected 
        ? '#e3f2fd' 
        : state.isFocused 
          ? '#f5f5f5' 
          : 'transparent',
      color: state.isSelected ? '#2196f3' : '#616161',
      fontSize: '14px',
      padding: '8px 12px',
      borderRadius: '4px',
      cursor: 'pointer',
      '&:active': {
        backgroundColor: '#e3f2fd'
      }
    }),
    noOptionsMessage: (base) => ({
      ...base,
      color: '#9e9e9e',
      fontSize: '14px',
      padding: '24px'
    }),
    loadingMessage: (base) => ({
      ...base,
      color: '#9e9e9e',
      fontSize: '14px'
    })
  }

  return (
    <div className={`os-field os-field-select ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>
      
      <div className="os-field-body">
        <div className="os-select-wrapper">
          <Select<SelectOption, false>
            inputId={field.key}
            value={selectedOption}
            onChange={handleChange}
            options={options}
            placeholder={placeholder}
            isDisabled={disabled}
            isSearchable={searchable}
            isClearable={clearable}
            styles={customStyles}
            noOptionsMessage={() => 'No options'}
            classNamePrefix="os-react-select"
          />
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
