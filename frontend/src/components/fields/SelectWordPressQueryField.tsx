import { useCallback, useMemo, useState, useEffect } from 'react'
import AsyncSelect from 'react-select/async'
import type { StylesConfig, SingleValue, MultiValue } from 'react-select'
import type { FieldRendererProps } from '../../schema/types'
import { apiFetch } from '../../utils/config'

interface WpQueryOption {
  value: number
  label: string
}

interface WpQueryResponse {
  options: WpQueryOption[]
  hasMore?: boolean
}

const DEBOUNCE_MS = 300
const MIN_INPUT_LENGTH = 1

function debounce(
  fn: (inputValue: string, callback: (opts: WpQueryOption[]) => void) => void,
  ms: number
): (inputValue: string, callback: (opts: WpQueryOption[]) => void) => void {
  let timeoutId: ReturnType<typeof setTimeout> | null = null
  return (inputValue: string, callback: (opts: WpQueryOption[]) => void) => {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(inputValue, callback), ms)
  }
}

export function SelectWordPressQueryField({
  field,
  value,
  onChange,
  disabled,
  error,
}: FieldRendererProps) {
  const attrs = field.attributes || {}
  const source = (attrs.source as string) || 'post'
  const postType = (attrs.post_type as string) || 'post'
  const taxonomy = (attrs.taxonomy as string) || 'category'
  const isMultiple = attrs.multiple === true
  const placeholder = (attrs.placeholder as string) || 'Search...'
  const clearable = attrs.clearable !== false
  const minInputLength = typeof attrs.minInputLength === 'number' ? attrs.minInputLength : MIN_INPUT_LENGTH

  const [defaultOption, setDefaultOption] = useState<WpQueryOption | WpQueryOption[] | null>(null)

  const selectedId = value !== undefined && value !== null && value !== '' ? value : null
  const selectedIds = isMultiple && Array.isArray(value) ? value : selectedId !== null ? [selectedId] : []

  useEffect(() => {
    if (selectedIds.length === 0) {
      setDefaultOption(null)
      return
    }
    const baseParams = { source, ...(source === 'post' && { post_type: postType }), ...(source === 'term' && { taxonomy }) }
    if (isMultiple) {
      Promise.all(
        selectedIds.map((id) =>
          apiFetch<WpQueryResponse>(`wp-query?${new URLSearchParams({ ...baseParams, id: String(id) })}`)
        )
      )
        .then((responses) => {
          const opts = responses.flatMap((r) => r.options || [])
          setDefaultOption(opts.length ? opts : null)
        })
        .catch(() => setDefaultOption(null))
    } else {
      apiFetch<WpQueryResponse>(`wp-query?${new URLSearchParams({ ...baseParams, id: String(selectedIds[0]) })}`)
        .then((res) => setDefaultOption(res.options?.[0] ?? null))
        .catch(() => setDefaultOption(null))
    }
  }, [source, postType, taxonomy, isMultiple, selectedIds.join(',')])

  const loadOptions = useCallback(
    (inputValue: string): Promise<WpQueryOption[]> =>
      new Promise((resolve) => {
        if (inputValue.length < minInputLength) {
          resolve([])
          return
        }
        const params = new URLSearchParams({
          source,
          search: inputValue,
          per_page: '20',
          ...(source === 'post' && { post_type: postType }),
          ...(source === 'term' && { taxonomy }),
        })
        apiFetch<WpQueryResponse>(`wp-query?${params}`)
          .then((res) => resolve(res.options || []))
          .catch(() => resolve([]))
      }),
    [source, postType, taxonomy, minInputLength]
  )

  const loadOptionsDebounced = useMemo(
    () =>
      debounce((inputValue: string, callback: (opts: WpQueryOption[]) => void) => {
        loadOptions(inputValue).then(callback)
      }, DEBOUNCE_MS),
    [loadOptions]
  )

  const selectedOption = useMemo((): SingleValue<WpQueryOption> | MultiValue<WpQueryOption> => {
    if (isMultiple) {
      if (Array.isArray(defaultOption)) return defaultOption
      if (defaultOption && selectedIds.length === 1 && defaultOption.value === selectedIds[0]) {
        return [defaultOption]
      }
      return selectedIds.length ? selectedIds.map((id: number | string) => ({ value: Number(id), label: `#${id}` })) : []
    }
    if (defaultOption && !Array.isArray(defaultOption)) return defaultOption
    if (selectedId != null) return { value: Number(selectedId), label: `#${selectedId}` }
    return null
  }, [defaultOption, selectedId, selectedIds, isMultiple])

  const handleChange = (newValue: SingleValue<WpQueryOption> | MultiValue<WpQueryOption>) => {
    if (isMultiple) {
      const multi = newValue as MultiValue<WpQueryOption>
      onChange(multi ? multi.map((o: WpQueryOption) => o.value) : [])
    } else {
      const single = newValue as SingleValue<WpQueryOption>
      onChange(single ? single.value : '')
    }
  }

  const customStyles: StylesConfig<WpQueryOption, boolean> = useMemo(
    () => ({
      control: (base: Record<string, unknown>, state: { isFocused: boolean; selectProps: { menuIsOpen: boolean } }) => ({
        ...base,
        minHeight: '38px',
        borderColor: error ? '#f44336' : state.isFocused ? '#2196f3' : '#e0e0e0',
        borderRadius: '4px',
        boxShadow: state.isFocused ? '0 0 0 1px #2196f3' : 'none',
        backgroundColor: disabled ? '#f5f5f5' : '#ffffff',
        cursor: disabled ? 'not-allowed' : 'pointer',
      }),
      valueContainer: (base: Record<string, unknown>) => ({ ...base, padding: '0 12px' }),
      placeholder: (base: Record<string, unknown>) => ({ ...base, color: '#9e9e9e', fontSize: '14px' }),
      singleValue: (base: Record<string, unknown>) => ({ ...base, color: '#212121', fontSize: '14px' }),
      input: (base: Record<string, unknown>) => ({ ...base, color: '#212121', fontSize: '14px', margin: 0, padding: 0 }),
      indicatorSeparator: () => ({ display: 'none' }),
      dropdownIndicator: (base: Record<string, unknown>, state: { selectProps: { menuIsOpen: boolean } }) => ({
        ...base,
        color: '#757575',
        padding: '0 10px',
        transform: state.selectProps.menuIsOpen ? 'rotate(180deg)' : 'rotate(0deg)',
      }),
      clearIndicator: (base: Record<string, unknown>) => ({ ...base, color: '#9e9e9e', padding: '0 8px' }),
      menu: (base: Record<string, unknown>) => ({
        ...base,
        marginTop: '4px',
        borderRadius: '4px',
        boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
        border: '1px solid #e0e0e0',
        zIndex: 100,
      }),
      menuList: (base: Record<string, unknown>) => ({ ...base, padding: '4px' }),
      option: (base: Record<string, unknown>, state: { isSelected: boolean; isFocused: boolean }) => ({
        ...base,
        backgroundColor: state.isSelected ? '#e3f2fd' : state.isFocused ? '#f5f5f5' : 'transparent',
        color: state.isSelected ? '#2196f3' : '#616161',
        fontSize: '14px',
        padding: '8px 12px',
        borderRadius: '4px',
        cursor: 'pointer',
      }),
      noOptionsMessage: (base: Record<string, unknown>) => ({ ...base, color: '#9e9e9e', fontSize: '14px', padding: '24px' }),
      loadingMessage: (base: Record<string, unknown>) => ({ ...base, color: '#9e9e9e', fontSize: '14px' }),
      multiValue: (base: Record<string, unknown>) => ({ ...base, backgroundColor: '#e3f2fd', borderRadius: '4px', margin: '2px' }),
      multiValueLabel: (base: Record<string, unknown>) => ({ ...base, color: '#1976d2', fontSize: '13px', padding: '2px 6px' }),
      multiValueRemove: (base: Record<string, unknown>) => ({ ...base, color: '#1976d2' }),
    }),
    [error, disabled]
  )

  return (
    <div className={`os-field os-field-select-wp-query ${isMultiple ? 'os-field-select-multiple' : ''} ${error ? 'os-field-error' : ''}`}>
      <label htmlFor={field.key} className="os-label">
        {field.label}
        {attrs.required === true && <span className="os-required">*</span>}
      </label>
      <div className="os-field-body">
        <div className="os-select-wrapper">
          <AsyncSelect<WpQueryOption, boolean>
            inputId={field.key}
            value={selectedOption}
            onChange={handleChange}
            loadOptions={loadOptionsDebounced}
            defaultOptions={defaultOption != null ? (Array.isArray(defaultOption) ? defaultOption : [defaultOption]) : false}
            placeholder={placeholder}
            isDisabled={disabled}
            isClearable={clearable}
            isMulti={isMultiple}
            closeMenuOnSelect={!isMultiple}
            styles={customStyles}
            noOptionsMessage={() => (minInputLength > 0 ? `Type ${minInputLength}+ character(s) to search` : 'No results')}
            loadingMessage={() => 'Loading...'}
            classNamePrefix="os-react-select"
            cacheOptions
            filterOption={null}
          />
        </div>
        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
