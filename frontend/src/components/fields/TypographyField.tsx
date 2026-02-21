import { useState, useCallback, useMemo, useRef, useEffect, memo } from 'react'
import Select, { StylesConfig, SingleValue, components, OptionProps, SingleValueProps } from 'react-select'
import { SketchPicker, ColorResult } from 'react-color'
import type { FieldRendererProps, OptStackConfig } from '../../schema/types'

// Google Fonts API configuration
const GOOGLE_FONTS_API_KEY = (window as unknown as { optstack?: Partial<OptStackConfig> }).optstack?.googleFontsApiKey
const GOOGLE_FONTS_API_URL = `https://www.googleapis.com/webfonts/v1/webfonts?key=${GOOGLE_FONTS_API_KEY}&sort=popularity`

// Cap Google fonts in the dropdown to avoid lag (API is sorted by popularity; search still filters this list)
const MAX_GOOGLE_FONTS_IN_MENU = 150

interface TypographyValue {
  fontFamily?: string
  fontSize?: number
  fontSizeUnit?: string
  fontWeight?: string
  fontStyle?: string
  lineHeight?: number
  lineHeightUnit?: string
  letterSpacing?: number
  letterSpacingUnit?: string
  textTransform?: string
  textDecoration?: string
  color?: string
}

interface FontOption {
  value: string
  label: string
  category?: 'system' | 'google'
  variants?: string[]
}

interface SelectOption {
  value: string
  label: string
}

// Google Fonts API response types
interface GoogleFontItem {
  family: string
  variants: string[]
  subsets: string[]
  category: string
}

interface GoogleFontsApiResponse {
  kind: string
  items: GoogleFontItem[]
}

// System fonts
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

// Cache for Google Fonts to avoid repeated API calls
let googleFontsCache: FontOption[] | null = null
let googleFontsFetchPromise: Promise<FontOption[]> | null = null

// Convert Google Fonts API variant format to weight numbers
function parseVariantToWeight(variant: string): string | null {
  // Handle regular/italic variants
  if (variant === 'regular' || variant === 'italic') {
    return '400'
  }
  // Extract numeric weight from variants like '300', '300italic', '700', etc.
  const match = variant.match(/^(\d+)/)
  if (match) {
    return match[1]
  }
  return null
}

// Fetch Google Fonts from API
async function fetchGoogleFonts(): Promise<FontOption[]> {
  // Return cached fonts if available
  if (googleFontsCache) {
    return googleFontsCache
  }

  // If already fetching, wait for the existing promise
  if (googleFontsFetchPromise) {
    return googleFontsFetchPromise
  }

  // Start fetching
  googleFontsFetchPromise = (async () => {
    try {
      const response = await fetch(GOOGLE_FONTS_API_URL)
      
      if (!response.ok) {
        throw new Error(`Google Fonts API error: ${response.status}`)
      }

      const data: GoogleFontsApiResponse = await response.json()
      
      // Transform API response to FontOption format
      const fonts: FontOption[] = data.items.map((font) => {
        // Extract unique weight values from variants
        const weights = new Set<string>()
        font.variants.forEach((variant) => {
          const weight = parseVariantToWeight(variant)
          if (weight) {
            weights.add(weight)
          }
        })

        return {
          value: font.family,
          label: font.family,
          category: 'google' as const,
          variants: Array.from(weights).sort((a, b) => parseInt(a) - parseInt(b)),
        }
      })

      // Cache the results
      googleFontsCache = fonts
      return fonts
    } catch (error) {
      console.error('Failed to fetch Google Fonts:', error)
      // Return empty array on error, will fall back to system fonts only
      return []
    } finally {
      googleFontsFetchPromise = null
    }
  })()

  return googleFontsFetchPromise
}

// Hook to use Google Fonts with loading state
function useGoogleFonts(disabled: boolean = false) {
  const [fonts, setFonts] = useState<FontOption[]>(googleFontsCache || [])
  const [loading, setLoading] = useState(!googleFontsCache && !disabled)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (disabled) {
      setFonts([])
      setLoading(false)
      return
    }

    // If already cached, use it
    if (googleFontsCache) {
      setFonts(googleFontsCache)
      setLoading(false)
      return
    }

    // Fetch fonts
    setLoading(true)
    fetchGoogleFonts()
      .then((fetchedFonts) => {
        setFonts(fetchedFonts)
        setError(null)
      })
      .catch((err) => {
        setError(err.message)
        setFonts([])
      })
      .finally(() => {
        setLoading(false)
      })
  }, [disabled])

  return { fonts, loading, error }
}

const FONT_WEIGHTS: SelectOption[] = [
  { value: '100', label: '100 - Thin' },
  { value: '200', label: '200 - Extra Light' },
  { value: '300', label: '300 - Light' },
  { value: '400', label: '400 - Regular' },
  { value: '500', label: '500 - Medium' },
  { value: '600', label: '600 - Semi Bold' },
  { value: '700', label: '700 - Bold' },
  { value: '800', label: '800 - Extra Bold' },
  { value: '900', label: '900 - Black' },
]

const FONT_STYLES: SelectOption[] = [
  { value: 'normal', label: 'Normal' },
  { value: 'italic', label: 'Italic' },
]

const TEXT_TRANSFORMS: SelectOption[] = [
  { value: 'none', label: 'None' },
  { value: 'uppercase', label: 'Uppercase' },
  { value: 'lowercase', label: 'Lowercase' },
  { value: 'capitalize', label: 'Capitalize' },
]

const TEXT_DECORATIONS: SelectOption[] = [
  { value: 'none', label: 'None' },
  { value: 'underline', label: 'Underline' },
  { value: 'line-through', label: 'Strikethrough' },
  { value: 'overline', label: 'Overline' },
]

const SIZE_UNITS: SelectOption[] = [
  { value: 'px', label: 'px' },
  { value: 'em', label: 'em' },
  { value: 'rem', label: 'rem' },
  { value: '%', label: '%' },
]

const LINE_HEIGHT_UNITS: SelectOption[] = [
  { value: '', label: '—' },
  { value: 'px', label: 'px' },
  { value: 'em', label: 'em' },
  { value: '%', label: '%' },
]

const DEFAULT_VALUE: TypographyValue = {
  fontFamily: 'inherit',
  fontSize: 16,
  fontSizeUnit: 'px',
  fontWeight: '400',
  fontStyle: 'normal',
  lineHeight: 1.5,
  lineHeightUnit: '',
  letterSpacing: 0,
  letterSpacingUnit: 'px',
  textTransform: 'none',
  textDecoration: 'none',
  color: '#000000',
}

// Track loaded Google Fonts to avoid duplicate loading
const loadedFonts = new Set<string>()

function loadGoogleFont(fontFamily: string, weights: string[] = ['400', '700']) {
  if (loadedFonts.has(fontFamily)) return
  
  const fontName = fontFamily.replace(/ /g, '+')
  const weightsStr = weights.join(';')
  const link = document.createElement('link')
  link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@${weightsStr}&display=swap`
  link.rel = 'stylesheet'
  document.head.appendChild(link)
  loadedFonts.add(fontFamily)
}

// Custom option component: no Google font loading here to avoid lag (hundreds of fonts loading at once).
// Only the selected value shows the real font (loaded once in parent). Option preview uses system font.
const FontOption = memo((props: OptionProps<FontOption, false>) => {
  const { data } = props
  const isGoogle = data.category === 'google'
  return (
    <components.Option {...props}>
      <div className="os-font-option">
        <span
          className="os-font-option-preview"
          style={{
            fontFamily: isGoogle ? 'system-ui, sans-serif' : data.value,
          }}
        >
          Aa
        </span>
        <span className="os-font-option-label">{data.label}</span>
        {isGoogle && (
          <span className="os-font-option-badge">Google</span>
        )}
      </div>
    </components.Option>
  )
})
FontOption.displayName = 'FontOption'

// Custom single value component with font preview
const FontSingleValue = (props: SingleValueProps<FontOption, false>) => {
  const { data } = props
  
  return (
    <components.SingleValue {...props}>
      <div className="os-font-single-value">
        <span 
          className="os-font-single-preview"
          style={{ fontFamily: data.category === 'google' ? `"${data.value}", sans-serif` : data.value }}
        >
          Aa
        </span>
        <span>{data.label}</span>
      </div>
    </components.SingleValue>
  )
}

export function TypographyField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [showColorPicker, setShowColorPicker] = useState(false)
  const colorRef = useRef<HTMLDivElement>(null)

  // Check if Google fonts are disabled via field attributes
  const disableGoogleFonts = field.attributes?.disableGoogleFonts === true
  
  // Fetch Google Fonts from API
  const { fonts: googleFonts, loading: fontsLoading } = useGoogleFonts(disableGoogleFonts)

  const typoValue = useMemo(() => {
    const defaultVal = (field.default as TypographyValue) || {}
    const val = (value as TypographyValue) || {}
    return { ...DEFAULT_VALUE, ...defaultVal, ...val }
  }, [value, field.default])

  // Combine system and Google fonts, or use custom fonts from attributes
  const allFonts = useMemo((): FontOption[] => {
    const customFonts = field.attributes?.fonts as FontOption[] | undefined
    if (customFonts && customFonts.length > 0) {
      return customFonts
    }

    // If Google fonts are disabled, return only system fonts
    if (disableGoogleFonts) {
      return SYSTEM_FONTS
    }

    // Cap Google fonts to avoid rendering hundreds of options (API is sorted by popularity)
    const cappedGoogle = googleFonts.slice(0, MAX_GOOGLE_FONTS_IN_MENU)
    const combined = [...SYSTEM_FONTS, ...cappedGoogle]

    // Ensure currently selected Google font is in the list if it was outside the cap
    const currentFamily = (value as TypographyValue)?.fontFamily ?? (field.default as TypographyValue)?.fontFamily
    if (currentFamily) {
      const googleName = currentFamily.replace(/^["']|["'],.*$/g, '').trim()
      const alreadyInList = combined.some((f) => f.value === googleName || (f.category === 'google' && currentFamily.includes(f.value)))
      if (!alreadyInList) {
        const selectedFont = googleFonts.find((f) => f.value === googleName || currentFamily.includes(f.value))
        if (selectedFont) {
          combined.push(selectedFont)
        }
      }
    }
    return combined
  }, [field.attributes?.fonts, field.default, disableGoogleFonts, googleFonts, value])

  // Group fonts by category for the dropdown
  const groupedFonts = useMemo(() => {
    const hasGoogle = allFonts.some(f => f.category === 'google')
    if (!hasGoogle) return allFonts
    
    return [
      { label: 'System Fonts', options: allFonts.filter(f => f.category === 'system' || !f.category) },
      { label: 'Google Fonts', options: allFonts.filter(f => f.category === 'google') },
    ]
  }, [allFonts])

  // Load the currently selected Google font
  useEffect(() => {
    const selectedFont = allFonts.find(f => {
      // Match by exact value or by font name (for Google fonts)
      return f.value === typoValue.fontFamily || 
             (f.category === 'google' && typoValue.fontFamily?.includes(f.value))
    })
    
    if (selectedFont?.category === 'google' && selectedFont.variants) {
      loadGoogleFont(selectedFont.value, selectedFont.variants)
    }
  }, [typoValue.fontFamily, allFonts])

  // Close color picker on outside click
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (colorRef.current && !colorRef.current.contains(event.target as Node)) {
        setShowColorPicker(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const updateValue = useCallback((key: keyof TypographyValue, val: unknown) => {
    onChange({ ...typoValue, [key]: val })
  }, [typoValue, onChange])

  const selectStyles: StylesConfig<SelectOption, false> = {
    control: (base, state) => ({
      ...base,
      minHeight: '36px',
      borderColor: state.isFocused ? '#3b82f6' : '#e5e7eb',
      borderRadius: '6px',
      boxShadow: state.isFocused ? '0 0 0 2px rgba(59, 130, 246, 0.1)' : 'none',
      fontSize: '13px',
      '&:hover': { borderColor: '#d1d5db' },
    }),
    valueContainer: (base) => ({ ...base, padding: '0 10px' }),
    singleValue: (base) => ({ ...base, fontSize: '13px', color: '#374151' }),
    placeholder: (base) => ({ ...base, fontSize: '13px', color: '#9ca3af' }),
    indicatorSeparator: () => ({ display: 'none' }),
    dropdownIndicator: (base) => ({ ...base, padding: '0 8px', color: '#9ca3af' }),
    menu: (base) => ({ ...base, zIndex: 50, fontSize: '13px' }),
    option: (base, state) => ({
      ...base,
      fontSize: '13px',
      padding: '8px 12px',
      backgroundColor: state.isSelected ? '#eff6ff' : state.isFocused ? '#f3f4f6' : 'white',
      color: state.isSelected ? '#3b82f6' : '#374151',
    }),
  }

  const fontSelectStyles: StylesConfig<FontOption, false> = {
    ...selectStyles,
    option: (base, state) => ({
      ...base,
      fontSize: '13px',
      padding: '6px 12px',
      backgroundColor: state.isSelected ? '#eff6ff' : state.isFocused ? '#f3f4f6' : 'white',
      color: state.isSelected ? '#3b82f6' : '#374151',
    }),
    group: (base) => ({
      ...base,
      paddingTop: 8,
      paddingBottom: 0,
    }),
    groupHeading: (base) => ({
      ...base,
      fontSize: '10px',
      fontWeight: 600,
      textTransform: 'uppercase',
      letterSpacing: '0.05em',
      color: '#9ca3af',
      marginBottom: 4,
    }),
  }

  const smallSelectStyles: StylesConfig<SelectOption, false> = {
    ...selectStyles,
    control: (base, state) => ({
      ...selectStyles.control?.(base, state) as object,
      minHeight: '36px',
      width: '70px',
      flexShrink: 0,
    }),
    valueContainer: (base) => ({ ...base, padding: '0 6px' }),
    dropdownIndicator: (base) => ({ ...base, padding: '0 4px' }),
  }

  const handleColorChange = useCallback((color: ColorResult) => {
    updateValue('color', color.hex.toUpperCase())
  }, [updateValue])

  const handleFontChange = useCallback((opt: SingleValue<FontOption>) => {
    if (!opt) return
    
    // For Google fonts, store just the font name (will be wrapped in quotes for CSS)
    // For system fonts, store the full font stack
    const fontValue = opt.category === 'google' ? `"${opt.value}", sans-serif` : opt.value
    updateValue('fontFamily', fontValue)
  }, [updateValue])

  // Find currently selected font
  const selectedFont = useMemo(() => {
    return allFonts.find(f => {
      if (f.category === 'google') {
        return typoValue.fontFamily?.includes(f.value)
      }
      return f.value === typoValue.fontFamily
    }) || allFonts[0]
  }, [allFonts, typoValue.fontFamily])

  // Preview text style
  const previewStyle: React.CSSProperties = {
    fontFamily: typoValue.fontFamily,
    fontSize: `${typoValue.fontSize}${typoValue.fontSizeUnit}`,
    fontWeight: typoValue.fontWeight as React.CSSProperties['fontWeight'],
    fontStyle: typoValue.fontStyle as React.CSSProperties['fontStyle'],
    lineHeight: typoValue.lineHeightUnit 
      ? `${typoValue.lineHeight}${typoValue.lineHeightUnit}` 
      : typoValue.lineHeight,
    letterSpacing: `${typoValue.letterSpacing}${typoValue.letterSpacingUnit}`,
    textTransform: typoValue.textTransform as React.CSSProperties['textTransform'],
    textDecoration: typoValue.textDecoration,
    color: typoValue.color,
  }

  return (
    <div className={`os-field os-field-typography ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>

      <div className="os-field-body">
        <div className="os-typography-wrapper">
          {/* Preview */}
          <div className="os-typography-preview">
            <span style={previewStyle}>
              The quick brown fox jumps over the lazy dog
            </span>
          </div>

          {/* Controls Grid */}
          <div className="os-typography-controls">
            {/* Font Family */}
            <div className="os-typography-row os-typography-row-full">
              <label className="os-typography-label">
                Font Family
                {fontsLoading && <span className="os-typography-loading"> (Loading fonts...)</span>}
              </label>
              <div className="os-typography-input">
                <Select<FontOption, false>
                  value={selectedFont}
                  onChange={handleFontChange}
                  options={groupedFonts as FontOption[]}
                  styles={fontSelectStyles as StylesConfig<FontOption, false>}
                  isDisabled={disabled}
                  isLoading={fontsLoading}
                  isSearchable
                  menuPlacement="auto"
                  components={{ Option: FontOption, SingleValue: FontSingleValue }}
                  filterOption={(option, inputValue) => {
                    const label = option.data.label.toLowerCase()
                    return label.includes(inputValue.toLowerCase())
                  }}
                  placeholder={fontsLoading ? "Loading fonts..." : "Select font..."}
                />
              </div>
            </div>

            {/* Font Size */}
            <div className="os-typography-row">
              <label className="os-typography-label">Size</label>
              <div className="os-typography-input os-typography-input-with-unit">
                <input
                  type="number"
                  value={typoValue.fontSize}
                  onChange={(e) => updateValue('fontSize', e.target.value ? Number(e.target.value) : 0)}
                  disabled={disabled}
                  className="os-typography-number"
                  min={0}
                />
                <Select<SelectOption, false>
                  value={SIZE_UNITS.find(u => u.value === typoValue.fontSizeUnit)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('fontSizeUnit', opt?.value || 'px')}
                  options={SIZE_UNITS}
                  styles={smallSelectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Font Weight */}
            <div className="os-typography-row">
              <label className="os-typography-label">Weight</label>
              <div className="os-typography-input">
                <Select<SelectOption, false>
                  value={FONT_WEIGHTS.find(w => w.value === typoValue.fontWeight)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('fontWeight', opt?.value || '400')}
                  options={FONT_WEIGHTS}
                  styles={selectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Font Style */}
            <div className="os-typography-row">
              <label className="os-typography-label">Style</label>
              <div className="os-typography-input">
                <Select<SelectOption, false>
                  value={FONT_STYLES.find(s => s.value === typoValue.fontStyle)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('fontStyle', opt?.value || 'normal')}
                  options={FONT_STYLES}
                  styles={selectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Line Height */}
            <div className="os-typography-row">
              <label className="os-typography-label">Line Height</label>
              <div className="os-typography-input os-typography-input-with-unit">
                <input
                  type="number"
                  value={typoValue.lineHeight}
                  onChange={(e) => updateValue('lineHeight', e.target.value ? Number(e.target.value) : 0)}
                  disabled={disabled}
                  className="os-typography-number"
                  min={0}
                  step={0.1}
                />
                <Select<SelectOption, false>
                  value={LINE_HEIGHT_UNITS.find(u => u.value === typoValue.lineHeightUnit)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('lineHeightUnit', opt?.value || '')}
                  options={LINE_HEIGHT_UNITS}
                  styles={smallSelectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Letter Spacing */}
            <div className="os-typography-row">
              <label className="os-typography-label">Letter Spacing</label>
              <div className="os-typography-input os-typography-input-with-unit">
                <input
                  type="number"
                  value={typoValue.letterSpacing}
                  onChange={(e) => updateValue('letterSpacing', e.target.value ? Number(e.target.value) : 0)}
                  disabled={disabled}
                  className="os-typography-number"
                  step={0.1}
                />
                <Select<SelectOption, false>
                  value={SIZE_UNITS.find(u => u.value === typoValue.letterSpacingUnit)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('letterSpacingUnit', opt?.value || 'px')}
                  options={SIZE_UNITS}
                  styles={smallSelectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Text Transform */}
            <div className="os-typography-row">
              <label className="os-typography-label">Transform</label>
              <div className="os-typography-input">
                <Select<SelectOption, false>
                  value={TEXT_TRANSFORMS.find(t => t.value === typoValue.textTransform)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('textTransform', opt?.value || 'none')}
                  options={TEXT_TRANSFORMS}
                  styles={selectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Text Decoration */}
            <div className="os-typography-row">
              <label className="os-typography-label">Decoration</label>
              <div className="os-typography-input">
                <Select<SelectOption, false>
                  value={TEXT_DECORATIONS.find(d => d.value === typoValue.textDecoration)}
                  onChange={(opt: SingleValue<SelectOption>) => updateValue('textDecoration', opt?.value || 'none')}
                  options={TEXT_DECORATIONS}
                  styles={selectStyles}
                  isDisabled={disabled}
                  isSearchable={false}
                  menuPlacement="auto"
                />
              </div>
            </div>

            {/* Color */}
            <div className="os-typography-row">
              <label className="os-typography-label">Color</label>
              <div className="os-typography-input" ref={colorRef}>
                <div className="os-typography-color">
                  <button
                    type="button"
                    className="os-typography-color-swatch"
                    style={{ backgroundColor: typoValue.color }}
                    onClick={() => !disabled && setShowColorPicker(!showColorPicker)}
                    disabled={disabled}
                    aria-label="Select color"
                  />
                  <input
                    type="text"
                    value={typoValue.color}
                    onChange={(e) => updateValue('color', e.target.value)}
                    disabled={disabled}
                    className="os-typography-color-input"
                    placeholder="#000000"
                  />
                </div>
                {showColorPicker && (
                  <div className="os-typography-color-picker">
                    <SketchPicker
                      color={typoValue.color}
                      onChange={handleColorChange}
                      onChangeComplete={handleColorChange}
                      disableAlpha
                      width="220"
                    />
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
