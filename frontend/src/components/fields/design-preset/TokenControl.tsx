import { useState, useRef, useEffect, useCallback, useMemo, memo } from 'react'
import { SketchPicker, type ColorResult } from 'react-color'
import Select, { type StylesConfig, type SingleValue, components, type OptionProps, type SingleValueProps } from 'react-select'
import type { TokenDefinition } from './types'
import type { OptStackConfig } from '../../../schema/types'

// ---------------------------------------------------------------------------
// Google Fonts API integration (mirrors TypographyField approach)
// ---------------------------------------------------------------------------
const GOOGLE_FONTS_API_KEY = (window as unknown as { optstack?: Partial<OptStackConfig> }).optstack?.googleFontsApiKey
const GOOGLE_FONTS_API_URL = `https://www.googleapis.com/webfonts/v1/webfonts?key=${GOOGLE_FONTS_API_KEY}&sort=popularity`
const MAX_GOOGLE_FONTS = 150

interface FontOption {
  value: string
  label: string
  category?: 'system' | 'google'
  variants?: string[]
}

interface GoogleFontItem {
  family: string
  variants: string[]
}

const SYSTEM_FONTS: FontOption[] = [
  { value: 'inherit', label: 'Default (Inherit)', category: 'system' },
  { value: 'system-ui, -apple-system, sans-serif', label: 'System UI', category: 'system' },
  { value: 'Arial, sans-serif', label: 'Arial', category: 'system' },
  { value: 'Helvetica Neue, Helvetica, sans-serif', label: 'Helvetica', category: 'system' },
  { value: 'Georgia, serif', label: 'Georgia', category: 'system' },
  { value: 'Times New Roman, serif', label: 'Times New Roman', category: 'system' },
  { value: 'Verdana, sans-serif', label: 'Verdana', category: 'system' },
  { value: 'ui-monospace, SFMono-Regular, monospace', label: 'Monospace', category: 'system' },
]

let googleFontsCache: FontOption[] | null = null
let googleFontsFetchPromise: Promise<FontOption[]> | null = null

function parseVariantToWeight(variant: string): string | null {
  if (variant === 'regular' || variant === 'italic') return '400'
  const match = variant.match(/^(\d+)/)
  return match ? match[1] : null
}

async function fetchGoogleFonts(): Promise<FontOption[]> {
  if (googleFontsCache) return googleFontsCache
  if (googleFontsFetchPromise) return googleFontsFetchPromise

  googleFontsFetchPromise = (async () => {
    try {
      const response = await fetch(GOOGLE_FONTS_API_URL)
      if (!response.ok) throw new Error(`Google Fonts API error: ${response.status}`)
      const data = await response.json()
      const fonts: FontOption[] = (data.items as GoogleFontItem[]).map((font) => {
        const weights = new Set<string>()
        font.variants.forEach((v) => { const w = parseVariantToWeight(v); if (w) weights.add(w) })
        return {
          value: font.family,
          label: font.family,
          category: 'google' as const,
          variants: Array.from(weights).sort((a, b) => parseInt(a) - parseInt(b)),
        }
      })
      googleFontsCache = fonts
      return fonts
    } catch (error) {
      console.error('Failed to fetch Google Fonts:', error)
      return []
    } finally {
      googleFontsFetchPromise = null
    }
  })()

  return googleFontsFetchPromise
}

function useGoogleFonts() {
  const disabled = !GOOGLE_FONTS_API_KEY
  const [fonts, setFonts] = useState<FontOption[]>(googleFontsCache || [])
  const [loading, setLoading] = useState(!googleFontsCache && !disabled)

  useEffect(() => {
    if (disabled) { setLoading(false); return }
    if (googleFontsCache) { setFonts(googleFontsCache); setLoading(false); return }
    setLoading(true)
    fetchGoogleFonts()
      .then((f) => setFonts(f))
      .finally(() => setLoading(false))
  }, [disabled])

  return { fonts, loading }
}

const loadedFonts = new Set<string>()

function loadGoogleFont(name: string, weights: string[] = ['400', '700']) {
  if (loadedFonts.has(name)) return
  loadedFonts.add(name)
  const fontName = name.replace(/ /g, '+')
  const link = document.createElement('link')
  link.rel = 'stylesheet'
  link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@${weights.join(';')}&display=swap`
  document.head.appendChild(link)
}

const DpFontOption = memo((props: OptionProps<FontOption, false>) => {
  const { data } = props
  return (
    <components.Option {...props}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
        <span style={{
          fontFamily: data.category === 'google' ? `"${data.value}", sans-serif` : data.value,
          fontSize: '14px', width: '24px', textAlign: 'center', flexShrink: 0, color: '#9ca3af',
        }}>Aa</span>
        <span style={{ flex: 1 }}>{data.label}</span>
        {data.category === 'google' && (
          <span style={{ fontSize: '9px', fontWeight: 600, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '0.04em' }}>G</span>
        )}
      </div>
    </components.Option>
  )
})
DpFontOption.displayName = 'DpFontOption'

const DpFontSingleValue = (props: SingleValueProps<FontOption, false>) => {
  const { data } = props
  return (
    <components.SingleValue {...props}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
        <span style={{
          fontFamily: data.category === 'google' ? `"${data.value}", sans-serif` : data.value,
          fontSize: '13px', color: '#9ca3af',
        }}>Aa</span>
        <span>{data.label}</span>
      </div>
    </components.SingleValue>
  )
}

// ---------------------------------------------------------------------------
// Shared react-select styles for design-preset controls
// ---------------------------------------------------------------------------
interface SimpleOption { value: string; label: string }

const dpSelectStyles: StylesConfig<SimpleOption, false> = {
  control: (base, state) => ({
    ...base,
    minHeight: '34px',
    borderColor: state.isFocused ? '#93c5fd' : '#e5e7eb',
    borderRadius: '6px',
    boxShadow: state.isFocused ? '0 0 0 3px rgba(59,130,246,0.08)' : 'none',
    fontSize: '13px',
    '&:hover': { borderColor: '#d1d5db' },
  }),
  valueContainer: (base) => ({ ...base, padding: '0 8px' }),
  singleValue: (base) => ({ ...base, fontSize: '13px', color: '#374151' }),
  placeholder: (base) => ({ ...base, fontSize: '13px', color: '#9ca3af' }),
  indicatorSeparator: () => ({ display: 'none' }),
  dropdownIndicator: (base) => ({ ...base, padding: '0 6px', color: '#9ca3af' }),
  menu: (base) => ({ ...base, zIndex: 50, fontSize: '13px', borderRadius: '8px', boxShadow: '0 12px 36px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04)' }),
  menuList: (base) => ({ ...base, maxHeight: '300px' }),
  option: (base, state) => ({
    ...base,
    fontSize: '13px',
    padding: '6px 10px',
    backgroundColor: state.isSelected ? '#eff6ff' : state.isFocused ? '#f3f4f6' : 'white',
    color: state.isSelected ? '#2563eb' : '#374151',
    cursor: 'pointer',
  }),
}

const dpFontSelectStyles: StylesConfig<FontOption, false> = {
  ...dpSelectStyles as StylesConfig<FontOption, false>,
  group: (base) => ({ ...base, paddingTop: 6, paddingBottom: 0 }),
  groupHeading: (base) => ({
    ...base,
    fontSize: '10px',
    fontWeight: 700,
    textTransform: 'uppercase' as const,
    letterSpacing: '0.05em',
    color: '#9ca3af',
    marginBottom: 2,
  }),
}

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
  const { fonts: googleFonts, loading: fontsLoading } = useGoogleFonts()
  const [searchInput, setSearchInput] = useState('')
  const currentFont = String(value || '')

  const visibleFonts = useMemo((): FontOption[] => {
    const lowerSearch = searchInput.toLowerCase()
    const googlePool = lowerSearch
      ? googleFonts.filter((f) => f.label.toLowerCase().includes(lowerSearch))
      : googleFonts.slice(0, MAX_GOOGLE_FONTS)
    const systemPool = lowerSearch
      ? SYSTEM_FONTS.filter((f) => f.label.toLowerCase().includes(lowerSearch))
      : SYSTEM_FONTS
    const combined = [...systemPool, ...googlePool]
    const fontName = currentFont.split(',')[0].replace(/['"]/g, '').trim()
    if (fontName && !combined.some((f) => f.value === fontName || currentFont.includes(f.value))) {
      const match = googleFonts.find((f) => f.value === fontName || currentFont.includes(f.value))
      if (match) combined.push(match)
    }
    return combined
  }, [googleFonts, currentFont, searchInput])

  const groupedFonts = useMemo(() => {
    const system = visibleFonts.filter((f) => f.category === 'system' || !f.category)
    const google = visibleFonts.filter((f) => f.category === 'google')
    if (!google.length) return visibleFonts
    return [
      { label: 'System Fonts', options: system },
      { label: `Google Fonts${searchInput ? '' : ` (top ${MAX_GOOGLE_FONTS})`}`, options: google },
    ]
  }, [visibleFonts, searchInput])

  const selectedFont = useMemo(() => {
    const fromVisible = visibleFonts.find((f) => {
      if (f.category === 'google') return currentFont.includes(f.value)
      return f.value === currentFont
    })
    if (fromVisible) return fromVisible
    const fromAll = googleFonts.find((f) => currentFont.includes(f.value))
    return fromAll || SYSTEM_FONTS[0]
  }, [visibleFonts, googleFonts, currentFont])

  useEffect(() => {
    if (selectedFont?.category === 'google' && selectedFont.variants) {
      loadGoogleFont(selectedFont.value, selectedFont.variants)
    }
  }, [selectedFont])

  const handleFontChange = useCallback((opt: SingleValue<FontOption>) => {
    if (!opt) return
    const fontValue = opt.category === 'google' ? `"${opt.value}", sans-serif` : opt.value
    onChange(fontValue)
  }, [onChange])

  const handleInputChange = useCallback((input: string) => {
    setSearchInput(input)
  }, [])

  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <Select<FontOption, false>
        value={selectedFont}
        onChange={handleFontChange}
        onInputChange={handleInputChange}
        options={groupedFonts as FontOption[]}
        styles={dpFontSelectStyles}
        isDisabled={disabled}
        isLoading={fontsLoading}
        isSearchable
        filterOption={null}
        menuPlacement="auto"
        components={{ Option: DpFontOption, SingleValue: DpFontSingleValue }}
        placeholder={fontsLoading ? 'Loading fonts…' : 'Select font…'}
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
  const hasNumbers = options.some((o) => typeof o === 'number')
  const selectOptions = useMemo(
    () => options.map((opt) => ({ value: String(opt), label: String(opt) })),
    [options],
  )
  const selected = selectOptions.find((o) => o.value === String(value ?? '')) || null

  return (
    <div className="os-dp-field">
      <label className="os-dp-field-label">{label}</label>
      <Select<SimpleOption, false>
        value={selected}
        onChange={(opt: SingleValue<SimpleOption>) => {
          if (!opt) return
          onChange(hasNumbers ? Number(opt.value) : opt.value)
        }}
        options={selectOptions}
        styles={dpSelectStyles}
        isDisabled={disabled}
        isSearchable={false}
        menuPlacement="auto"
      />
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
