import { useState, useCallback } from 'react'
import { TokenControl } from './TokenControl'
import { getGroupSpecimen } from './specimenRegistry'
import type {
  Breakpoint,
  DesignGroupSchema,
  DesignGroupValue,
  DesignPresetVariant,
} from './types'
import { isResponsiveValue, resolveBreakpointValue } from './types'

interface Props {
  group: DesignGroupSchema
  tokens: DesignGroupValue
  rawTokens: DesignGroupValue
  activeBreakpoint: Breakpoint
  onTokenChange: (tokenKey: string, value: unknown, variantId?: string) => void
  onBatchTokenChange: (changes: Record<string, unknown>, variantId?: string) => void
}

const WEIGHT_LABELS: Record<number, string> = {
  100: 'Thin', 200: 'ExtraLight', 300: 'Light', 400: 'Regular',
  500: 'Medium', 600: 'SemiBold', 700: 'Bold', 800: 'ExtraBold', 900: 'Black',
}

export function GroupSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: Props) {
  const isVariant = group.variant && Array.isArray(tokens)
  const variants = isVariant ? (tokens as DesignPresetVariant[]) : null
  const flat = !isVariant ? (tokens as Record<string, unknown>) : null

  const CustomSpecimen = getGroupSpecimen(group.id)
  if (CustomSpecimen) {
    return <CustomSpecimen group={group} tokens={tokens} onTokenChange={onTokenChange} />
  }

  switch (group.id) {
    case 'heading':
      return <HeadingSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'body_text':
      return <BodyTextSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'button':
      return <VariantSpecimen group={group} variants={variants ?? []} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} renderVariant={renderButtonVariant} />
    case 'link':
      return <LinkSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'form_field':
      return <FormFieldSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'form_meta':
      return <FormMetaSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'container':
      return <ContainerSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'table':
      return <TableSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    case 'list':
      return <ListSpecimen group={group} tokens={flat!} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    default:
      return <GenericSpecimen group={group} tokens={tokens} rawTokens={rawTokens} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
  }
}

interface FlatSpecimenProps {
  group: DesignGroupSchema
  tokens: Record<string, unknown>
  rawTokens: DesignGroupValue
  activeBreakpoint: Breakpoint
  onTokenChange: Props['onTokenChange']
  onBatchTokenChange: Props['onBatchTokenChange']
}

// ---------------------------------------------------------------------------
// Heading Specimen (H1–H6 with font specimen)
// ---------------------------------------------------------------------------
function HeadingSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const fontFamily = String(tokens.fontFamily ?? 'Inter, sans-serif')
  const fontWeight = Number(tokens.fontWeight ?? 600)
  const lineHeight = Number(tokens.lineHeight ?? 1.25)
  const color = String(tokens.color ?? '#111827')
  const sizeScale = (tokens.sizeScale ?? {}) as Record<string, string>

  const fontName = fontFamily.split(',')[0].replace(/['"]/g, '').trim()
  const weightLabel = WEIGHT_LABELS[fontWeight] ?? String(fontWeight)

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-font-display" style={{ fontFamily, color }}>
        <h1 className="os-dp-font-name" style={{ fontFamily, fontWeight }}>{fontName}</h1>
        <div className="os-dp-font-alphabet" style={{ fontFamily }}>
          <div>ABCDEFGHIJKLMNOPQRSTUVWXYZ</div>
          <div>abcdefghijklmnopqrstuvwxyz</div>
        </div>
        <div className="os-dp-font-weights">
          {[700, 600, 500, 400].map((w) => (
            <span key={w} className={`os-dp-font-weight-chip ${w === fontWeight ? 'is-active' : ''}`} style={{ fontFamily, fontWeight: w }}>
              {WEIGHT_LABELS[w] ?? w}
            </span>
          ))}
        </div>
      </div>

      <div className="os-dp-divider" />

      <div className="os-dp-type-scale">
        {['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].map((level) => {
          const size = sizeScale[level] ?? '1rem'
          return (
            <div key={level} className="os-dp-type-row">
              <div className="os-dp-type-level">{level.toUpperCase()}</div>
              <div className="os-dp-type-sample" style={{ fontFamily, fontWeight, lineHeight, color, fontSize: size }}>
                Typography
              </div>
              <div className="os-dp-type-meta">
                <span className="os-dp-meta-item">
                  <span className="os-dp-meta-label">Size</span>
                  <span className="os-dp-meta-value">{size}</span>
                </span>
                <span className="os-dp-meta-item">
                  <span className="os-dp-meta-label">Weight</span>
                  <span className="os-dp-meta-value">{fontWeight} / {weightLabel.toLowerCase()}</span>
                </span>
                <span className="os-dp-meta-item">
                  <span className="os-dp-meta-label">Line Height</span>
                  <span className="os-dp-meta-value">{lineHeight}</span>
                </span>
              </div>
            </div>
          )
        })}
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Body Text Specimen
// ---------------------------------------------------------------------------
function BodyTextSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const fontFamily = String(tokens.fontFamily ?? 'Inter, sans-serif')
  const fontSize = String(tokens.fontSize ?? '16px')
  const fontWeight = Number(tokens.fontWeight ?? 400)
  const lineHeight = Number(tokens.lineHeight ?? 1.6)
  const color = String(tokens.color ?? '#374151')
  const fontName = fontFamily.split(',')[0].replace(/['"]/g, '').trim()
  const weightLabel = WEIGHT_LABELS[fontWeight] ?? String(fontWeight)

  const levels = [
    { label: 'Body', size: fontSize, weight: fontWeight, lh: lineHeight },
    // { label: 'Body 2', size: `calc(${fontSize} * 0.875)`, weight: fontWeight, lh: lineHeight },
  ]

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-font-display" style={{ fontFamily, color }}>
        <h1 className="os-dp-font-name" style={{ fontFamily, fontWeight }}>{fontName}</h1>
        <div className="os-dp-font-alphabet" style={{ fontFamily }}>
          <div>ABCDEFGHIJKLMNOPQRSTUVWXYZ</div>
          <div>abcdefghijklmnopqrstuvwxyz</div>
        </div>
      </div>

      <div className="os-dp-divider" />

      <div className="os-dp-type-scale">
        {levels.map((level) => (
          <div key={level.label} className="os-dp-type-row">
            <div className="os-dp-type-level">{level.label}</div>
            <div className="os-dp-type-sample" style={{ fontFamily, fontWeight: level.weight, lineHeight: level.lh, color, fontSize: level.size }}>
              The quick brown fox jumps over the lazy dog. Pack my box with five dozen liquor jugs.
            </div>
            <div className="os-dp-type-meta">
              <span className="os-dp-meta-item"><span className="os-dp-meta-label">Size</span><span className="os-dp-meta-value">{fontSize}</span></span>
              <span className="os-dp-meta-item"><span className="os-dp-meta-label">Weight</span><span className="os-dp-meta-value">{fontWeight} / {weightLabel.toLowerCase()}</span></span>
              <span className="os-dp-meta-item"><span className="os-dp-meta-label">Line Height</span><span className="os-dp-meta-value">{lineHeight}</span></span>
            </div>
          </div>
        ))}
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Link Specimen
// ---------------------------------------------------------------------------
function LinkSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const color = String(tokens.color ?? '#2563EB')
  const hoverColor = String(tokens.hoverColor ?? '#1D4ED8')
  const decoration = String(tokens.decoration ?? 'underline')
  const hoverDecoration = String(tokens.hoverDecoration ?? 'underline')

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Default State</div>
          <div className="os-dp-inline-preview" style={{ fontSize: '1.125rem' }}>
            <a style={{ color, textDecoration: decoration, cursor: 'default' }}>This is a link</a>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Color</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: color }} />{color}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Decoration</span><span className="os-dp-meta-value">{decoration}</span></span>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Hover State</div>
          <div className="os-dp-inline-preview" style={{ fontSize: '1.125rem' }}>
            <a style={{ color: hoverColor, textDecoration: hoverDecoration, cursor: 'default' }}>This is a link (hover)</a>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Color</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: hoverColor }} />{hoverColor}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Decoration</span><span className="os-dp-meta-value">{hoverDecoration}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Form Field Specimen
// ---------------------------------------------------------------------------
function FormFieldSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const bg = String(tokens.background ?? '#FFFFFF')
  const borderColor = String(tokens.borderColor ?? '#D1D5DB')
  const borderWidth = String(tokens.borderWidth ?? '1px')
  const borderRadius = String(tokens.borderRadius ?? '6px')
  const padding = String(tokens.padding ?? '10px 12px')
  const fontSize = String(tokens.fontSize ?? '14px')
  const color = String(tokens.color ?? '#111827')
  const focusBorder = String(tokens.focusBorderColor ?? '#2563EB')

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Text Input</div>
          <div className="os-dp-inline-preview">
            <input
              type="text"
              readOnly
              value="Sample text"
              style={{
                background: bg, border: `${borderWidth} solid ${borderColor}`,
                borderRadius, padding, fontSize, color, width: '100%',
                boxSizing: 'border-box', outline: 'none',
              }}
            />
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Size</span><span className="os-dp-meta-value">{fontSize}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Radius</span><span className="os-dp-meta-value">{borderRadius}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Padding</span><span className="os-dp-meta-value">{padding}</span></span>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Focus State</div>
          <div className="os-dp-inline-preview">
            <input
              type="text"
              readOnly
              value="Focused input"
              style={{
                background: bg, border: `${borderWidth} solid ${focusBorder}`,
                borderRadius, padding, fontSize, color, width: '100%',
                boxSizing: 'border-box', outline: 'none',
                boxShadow: `0 0 0 3px ${focusBorder}22`,
              }}
            />
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Focus Border</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: focusBorder }} />{focusBorder}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Form Meta Specimen
// ---------------------------------------------------------------------------
function FormMetaSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const labelFontSize = String(tokens.labelFontSize ?? '14px')
  const labelFontWeight = Number(tokens.labelFontWeight ?? 500)
  const labelColor = String(tokens.labelColor ?? '#374151')
  const helpColor = String(tokens.helpColor ?? '#6B7280')
  const errorColor = String(tokens.errorColor ?? '#EF4444')
  const successColor = String(tokens.successColor ?? '#10B981')

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Label &amp; Help</div>
          <div className="os-dp-inline-preview">
            <div style={{ marginBottom: '4px' }}>
              <span style={{ fontSize: labelFontSize, fontWeight: labelFontWeight, color: labelColor }}>Field Label</span>
            </div>
            <div style={{ fontSize: `calc(${labelFontSize} * 0.85)`, color: helpColor }}>
              This is a helper text for the field above.
            </div>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Size</span><span className="os-dp-meta-value">{labelFontSize}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Weight</span><span className="os-dp-meta-value">{labelFontWeight}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Color</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: labelColor }} />{labelColor}</span></span>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Validation States</div>
          <div className="os-dp-inline-preview">
            <div style={{ fontSize: `calc(${labelFontSize} * 0.85)`, color: errorColor, marginBottom: '6px', display: 'flex', alignItems: 'center', gap: '4px' }}>
              <span style={{ fontSize: '12px' }}>&#9888;</span> This field is required.
            </div>
            <div style={{ fontSize: `calc(${labelFontSize} * 0.85)`, color: successColor, display: 'flex', alignItems: 'center', gap: '4px' }}>
              <span style={{ fontSize: '12px' }}>&#10003;</span> Looks good!
            </div>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Error</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: errorColor }} />{errorColor}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Success</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: successColor }} />{successColor}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Container Specimen
// ---------------------------------------------------------------------------
function ContainerSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const maxWidth = String(tokens.maxWidth ?? '1200px')
  const padding = String(tokens.padding ?? '0 24px')
  const background = String(tokens.background ?? '#FFFFFF')
  const borderRadius = String(tokens.borderRadius ?? '0')
  const borderWidth = String(tokens.borderWidth ?? '0')
  const borderColor = String(tokens.borderColor ?? 'transparent')

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Container Preview</div>
          <div className="os-dp-inline-preview">
            <div style={{ background: '#f0f0f0', padding: '16px', borderRadius: '6px', position: 'relative' }}>
              <div style={{
                background,
                border: borderWidth !== '0' ? `${borderWidth} solid ${borderColor}` : 'none',
                borderRadius, padding, maxWidth: '100%', margin: '0 auto',
                minHeight: '60px', display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: '12px', color: '#9CA3AF',
              }}>
                Content Area
              </div>
            </div>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Max Width</span><span className="os-dp-meta-value">{maxWidth}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Padding</span><span className="os-dp-meta-value">{padding}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Background</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background }} />{background}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Table Specimen
// ---------------------------------------------------------------------------
function TableSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const headerBg = String(tokens.headerBackground ?? '#F9FAFB')
  const headerColor = String(tokens.headerColor ?? '#111827')
  const headerFontWeight = Number(tokens.headerFontWeight ?? 600)
  const cellPadding = String(tokens.cellPadding ?? '12px 16px')
  const cellFontSize = String(tokens.cellFontSize ?? '14px')
  const cellColor = String(tokens.cellColor ?? '#374151')
  const borderColor = String(tokens.borderColor ?? '#E5E7EB')
  const borderWidth = String(tokens.borderWidth ?? '1px')
  const stripedBg = String(tokens.stripedBackground ?? '#F9FAFB')
  const hoverBg = String(tokens.hoverBackground ?? '#F3F4F6')

  const [hoveredRow, setHoveredRow] = useState<number | null>(null)

  const rows = [
    ['Product', 'Category', 'Price'],
    ['Widget A', 'Hardware', '$29.99'],
    ['Service B', 'Software', '$49.00'],
    ['Bundle C', 'Mixed', '$79.50'],
  ]

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card" style={{ padding: 0, overflow: 'hidden' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: cellFontSize }}>
            <thead>
              <tr>
                {rows[0].map((h, i) => (
                  <th key={i} style={{
                    background: headerBg, color: headerColor, fontWeight: headerFontWeight,
                    padding: cellPadding, textAlign: 'left',
                    borderBottom: `${borderWidth} solid ${borderColor}`,
                  }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {rows.slice(1).map((row, ri) => (
                <tr
                  key={ri}
                  onMouseEnter={() => setHoveredRow(ri)}
                  onMouseLeave={() => setHoveredRow(null)}
                  style={{
                    background: hoveredRow === ri ? hoverBg : (ri % 2 === 1 ? stripedBg : 'transparent'),
                    transition: 'background 0.15s',
                  }}
                >
                  {row.map((cell, ci) => (
                    <td key={ci} style={{
                      padding: cellPadding, color: cellColor,
                      borderBottom: `${borderWidth} solid ${borderColor}`,
                    }}>{cell}</td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <div className="os-dp-type-meta" style={{ padding: '12px 16px' }}>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Header BG</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: headerBg }} />{headerBg}</span></span>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Cell Size</span><span className="os-dp-meta-value">{cellFontSize}</span></span>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Border</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: borderColor }} />{borderColor}</span></span>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// List Specimen (UL, OL, LI)
// ---------------------------------------------------------------------------
function ListSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: FlatSpecimenProps) {
  const [editOpen, setEditOpen] = useState(false)
  const fontSize = String(tokens.fontSize ?? '16px')
  const lineHeight = Number(tokens.lineHeight ?? 1.6)
  const color = String(tokens.color ?? '#374151')
  const markerColor = String(tokens.markerColor ?? '#2563EB')
  const itemSpacing = String(tokens.itemSpacing ?? '8px')
  const indentSize = String(tokens.indentSize ?? '24px')

  const baseStyle: React.CSSProperties = { fontSize, lineHeight, color, paddingLeft: indentSize }
  const itemStyle: React.CSSProperties = { marginBottom: itemSpacing }

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Unordered List</div>
          <div className="os-dp-inline-preview">
            <ul style={{ ...baseStyle, listStyleType: 'disc', margin: 0 }}>
              <li style={{ ...itemStyle, color: markerColor }}><span style={{ color }}>First item in the list</span></li>
              <li style={{ ...itemStyle, color: markerColor }}><span style={{ color }}>Second item with more text</span></li>
              <li style={{ color: markerColor }}><span style={{ color }}>Third item</span></li>
            </ul>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Ordered List</div>
          <div className="os-dp-inline-preview">
            <ol style={{ ...baseStyle, listStyleType: 'decimal', margin: 0 }}>
              <li style={{ ...itemStyle, color: markerColor }}><span style={{ color }}>Step one of the process</span></li>
              <li style={{ ...itemStyle, color: markerColor }}><span style={{ color }}>Step two continues here</span></li>
              <li style={{ color: markerColor }}><span style={{ color }}>Final step</span></li>
            </ol>
          </div>
        </div>
      </div>

      <div className="os-dp-type-meta" style={{ padding: '12px 16px' }}>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Size</span><span className="os-dp-meta-value">{fontSize}</span></span>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Line Height</span><span className="os-dp-meta-value">{lineHeight}</span></span>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Marker</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: markerColor }} />{markerColor}</span></span>
        <span className="os-dp-meta-item"><span className="os-dp-meta-label">Indent</span><span className="os-dp-meta-value">{indentSize}</span></span>
      </div>

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Variant Specimen (button)
// ---------------------------------------------------------------------------
function VariantSpecimen({ group, variants, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange, renderVariant }: {
  group: DesignGroupSchema; variants: DesignPresetVariant[];
  rawTokens: DesignGroupValue; activeBreakpoint: Breakpoint;
  onTokenChange: Props['onTokenChange'];
  onBatchTokenChange: Props['onBatchTokenChange'];
  renderVariant: (variant: DesignPresetVariant) => React.ReactNode;
}) {
  const [activeVariantId, setActiveVariantId] = useState(variants[0]?.id ?? '')
  const [editOpen, setEditOpen] = useState(false)
  const currentVariant = variants.find((v) => v.id === activeVariantId) ?? variants[0]
  const rawVariants = Array.isArray(rawTokens) ? rawTokens : []
  const currentRawVariant = rawVariants.find((v) => v.id === activeVariantId) ?? rawVariants[0]

  const handleResponsiveChange = useCallback((tokenKey: string, newValue: unknown, breakpoint?: Breakpoint) => {
    const key = breakpoint ? `${tokenKey}.${breakpoint}` : tokenKey
    onTokenChange(key, newValue, currentVariant?.id)
  }, [onTokenChange, currentVariant?.id])

  const handleSync = useCallback((tokenKey: string) => {
    const raw = currentRawVariant?.[tokenKey]
    const desktopVal = isResponsiveValue(raw) ? resolveBreakpointValue(raw, 'desktop') : raw
    onBatchTokenChange({
      [tokenKey]: desktopVal,
      [`${tokenKey}.desktop`]: undefined,
      [`${tokenKey}.tablet`]: undefined,
      [`${tokenKey}.mobile`]: undefined,
    }, currentVariant?.id)
  }, [onBatchTokenChange, currentVariant?.id, currentRawVariant])

  const handleUnsync = useCallback((tokenKey: string) => {
    const raw = currentRawVariant?.[tokenKey]
    const scalar = isResponsiveValue(raw) ? resolveBreakpointValue(raw, 'desktop') : raw
    onBatchTokenChange({
      [tokenKey]: undefined,
      [`${tokenKey}.desktop`]: scalar,
      [`${tokenKey}.tablet`]: scalar,
      [`${tokenKey}.mobile`]: scalar,
    }, currentVariant?.id)
  }, [onBatchTokenChange, currentVariant?.id, currentRawVariant])

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-variant-specimens">
        {variants.map((variant) => (
          <div key={variant.id} className="os-dp-inline-card">
            <div className="os-dp-inline-label">{variant.label || variant.id}</div>
            {renderVariant(variant)}
            <div className="os-dp-type-meta">
              {Object.entries(group.tokens).slice(0, 4).map(([key]) => {
                const val = variant[key]
                if (val === undefined) return null
                return (
                  <span key={key} className="os-dp-meta-item">
                    <span className="os-dp-meta-label">{camelToLabel(key)}</span>
                    <span className="os-dp-meta-value">
                      {isColorValue(String(val)) && <span className="os-dp-meta-swatch" style={{ background: String(val) }} />}
                      {String(val)}
                    </span>
                  </span>
                )
              })}
            </div>
          </div>
        ))}
      </div>

      <div className="os-dp-divider" />
      <div className="os-dp-property-section">
        <button type="button" className="os-dp-property-toggle" onClick={() => setEditOpen(!editOpen)}>
          <svg className={`os-dp-property-chevron ${editOpen ? 'is-open' : ''}`} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="6 9 12 15 18 9" /></svg>
          <span>Edit Properties</span>
        </button>
        {editOpen && (
          <>
            <div className="os-dp-variant-tabs">
              {variants.map((v) => (
                <button
                  key={v.id}
                  type="button"
                  className={`os-dp-variant-tab ${v.id === activeVariantId ? 'is-active' : ''}`}
                  onClick={() => setActiveVariantId(v.id)}
                >
                  {v.label || v.id}
                </button>
              ))}
            </div>
            <div className="os-dp-property-grid">
              {currentVariant && Object.entries(group.tokens).map(([tokenKey, tokenDef]) => {
                const rawVal = currentRawVariant?.[tokenKey]
                const isResp = tokenDef.responsive === true
                const isCurrentlyResponsive = isResp && isResponsiveValue(rawVal)
                const isNonDesktop = activeBreakpoint !== 'desktop'
                const lockedByBreakpoint = isNonDesktop && !isResp
                const displayValue = isCurrentlyResponsive
                  ? resolveBreakpointValue(rawVal, activeBreakpoint)
                  : currentVariant[tokenKey]

                return (
                  <div key={`${currentVariant.id}-${tokenKey}`} className={`os-dp-field-responsive-wrap ${lockedByBreakpoint ? 'is-locked' : ''}`}>
                    <TokenControl
                      tokenKey={tokenKey}
                      definition={tokenDef}
                      value={displayValue}
                      onChange={(v) => handleResponsiveChange(tokenKey, v, isCurrentlyResponsive ? activeBreakpoint : undefined)}
                      disabled={lockedByBreakpoint}
                    />
                    {isResp && (
                      <ResponsiveToggle
                        isResponsive={isCurrentlyResponsive}
                        activeBreakpoint={activeBreakpoint}
                        onSync={() => handleSync(tokenKey)}
                        onUnsync={() => handleUnsync(tokenKey)}
                      />
                    )}
                    {lockedByBreakpoint && <LockedIndicator />}
                  </div>
                )
              })}
            </div>
          </>
        )}
      </div>
    </div>
  )
}

function renderButtonVariant(variant: DesignPresetVariant) {
  const [hoveredVariantId, setHoveredVariantId] = useState<string | null>(null)
  const isHovered = hoveredVariantId === variant.id

  return (
    <div className="os-dp-inline-preview">
      <button
        type="button"
        onMouseEnter={() => setHoveredVariantId(variant.id)}
        onMouseLeave={() => setHoveredVariantId(null)}
        style={{
          fontFamily: String(variant.fontFamily ?? 'inherit'),
          fontSize: String(variant.fontSize ?? '14px'),
          fontWeight: Number(variant.fontWeight ?? 600),
          padding: String(variant.padding ?? '10px 20px'),
          borderRadius: String(variant.borderRadius ?? '6px'),
          border: `${variant.borderWidth ?? '0'} solid ${variant.borderColor ?? 'transparent'}`,
          background: isHovered ? String(variant.hoverBackground ?? '#2563EB') : String(variant.background ?? '#2563EB'),
          color: isHovered ? String(variant.hoverColor ?? '#fff') : String(variant.color ?? '#fff'),
          cursor: 'default',
          display: 'inline-block',
        }}
      >
        {variant.label || variant.id} Button
      </button>
    </div>
  )
}

// ---------------------------------------------------------------------------
// Generic Specimen
// ---------------------------------------------------------------------------
function GenericSpecimen({ group, tokens, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: { group: DesignGroupSchema; tokens: DesignGroupValue; rawTokens: DesignGroupValue; activeBreakpoint: Breakpoint; onTokenChange: Props['onTokenChange']; onBatchTokenChange: Props['onBatchTokenChange'] }) {
  const [editOpen, setEditOpen] = useState(true)
  const isVariant = group.variant && Array.isArray(tokens)
  const flat = !isVariant ? (tokens as Record<string, unknown>) : null

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-applies-to">
        {group.applies_to.map((tag) => (
          <span key={tag} className="os-dp-applies-tag">{tag}</span>
        ))}
      </div>

      {flat && (
        <div className="os-dp-generic-preview">
          {Object.entries(group.tokens).map(([key]) => {
            const val = flat[key]
            return (
              <div key={key} className="os-dp-generic-row">
                <span className="os-dp-generic-label">{camelToLabel(key)}</span>
                <span className="os-dp-generic-value">
                  {isColorValue(String(val ?? '')) && <span className="os-dp-meta-swatch" style={{ background: String(val) }} />}
                  {String(val ?? '—')}
                </span>
              </div>
            )
          })}
        </div>
      )}

      <div className="os-dp-divider" />
      <ResponsivePropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} rawTokens={rawTokens as Record<string, unknown>} activeBreakpoint={activeBreakpoint} onTokenChange={onTokenChange} onBatchTokenChange={onBatchTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Responsive Toggle (sync/unsync per token)
// ---------------------------------------------------------------------------
function ResponsiveToggle({ isResponsive, activeBreakpoint, onSync, onUnsync }: {
  isResponsive: boolean; activeBreakpoint: Breakpoint;
  onSync: () => void; onUnsync: () => void;
}) {
  return (
    <button
      type="button"
      className={`os-dp-responsive-toggle ${isResponsive ? 'is-responsive' : 'is-synced'}`}
      onClick={isResponsive ? onSync : onUnsync}
      title={isResponsive ? `Per-breakpoint (${activeBreakpoint}) — click to sync` : 'Synced — click for per-breakpoint'}
    >
      {isResponsive ? (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <rect x="5" y="2" width="14" height="20" rx="2" /><line x1="12" y1="18" x2="12" y2="18" />
        </svg>
      ) : (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
          <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
        </svg>
      )}
    </button>
  )
}

// ---------------------------------------------------------------------------
// Locked Indicator (non-responsive token on tablet/mobile)
// ---------------------------------------------------------------------------
function LockedIndicator() {
  return (
    <span className="os-dp-locked-indicator" title="Same across all breakpoints">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </svg>
    </span>
  )
}

// ---------------------------------------------------------------------------
// Responsive Property Section (flat tokens with responsive support)
// ---------------------------------------------------------------------------
function ResponsivePropertySection({ open, onToggle, group, rawTokens, activeBreakpoint, onTokenChange, onBatchTokenChange }: {
  open: boolean; onToggle: () => void;
  group: DesignGroupSchema; rawTokens: Record<string, unknown>;
  activeBreakpoint: Breakpoint; onTokenChange: Props['onTokenChange'];
  onBatchTokenChange: Props['onBatchTokenChange'];
}) {
  const handleResponsiveChange = useCallback((tokenKey: string, newValue: unknown, breakpoint?: Breakpoint) => {
    const key = breakpoint ? `${tokenKey}.${breakpoint}` : tokenKey
    onTokenChange(key, newValue)
  }, [onTokenChange])

  const handleSync = useCallback((tokenKey: string) => {
    const raw = rawTokens[tokenKey]
    const desktopVal = isResponsiveValue(raw) ? resolveBreakpointValue(raw, 'desktop') : raw
    onBatchTokenChange({
      [tokenKey]: desktopVal,
      [`${tokenKey}.desktop`]: undefined,
      [`${tokenKey}.tablet`]: undefined,
      [`${tokenKey}.mobile`]: undefined,
    })
  }, [onBatchTokenChange, rawTokens])

  const handleUnsync = useCallback((tokenKey: string) => {
    const raw = rawTokens[tokenKey]
    const scalar = isResponsiveValue(raw) ? resolveBreakpointValue(raw, 'desktop') : raw
    onBatchTokenChange({
      [tokenKey]: undefined,
      [`${tokenKey}.desktop`]: scalar,
      [`${tokenKey}.tablet`]: scalar,
      [`${tokenKey}.mobile`]: scalar,
    })
  }, [onBatchTokenChange, rawTokens])

  return (
    <div className="os-dp-property-section">
      <button type="button" className="os-dp-property-toggle" onClick={onToggle}>
        <svg className={`os-dp-property-chevron ${open ? 'is-open' : ''}`} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="6 9 12 15 18 9" /></svg>
        <span>Edit Properties</span>
      </button>
      {open && (
        <div className="os-dp-property-grid">
          {Object.entries(group.tokens).map(([tokenKey, tokenDef]) => {
            const rawVal = rawTokens[tokenKey]
            const isResp = tokenDef.responsive === true
            const isCurrentlyResponsive = isResp && isResponsiveValue(rawVal)
            const isNonDesktop = activeBreakpoint !== 'desktop'
            const lockedByBreakpoint = isNonDesktop && !isResp
            const displayValue = isCurrentlyResponsive
              ? resolveBreakpointValue(rawVal, activeBreakpoint)
              : rawVal

            return (
              <div key={tokenKey} className={`os-dp-field-responsive-wrap ${lockedByBreakpoint ? 'is-locked' : ''}`}>
                <TokenControl
                  tokenKey={tokenKey}
                  definition={tokenDef}
                  value={displayValue}
                  onChange={(v) => handleResponsiveChange(tokenKey, v, isCurrentlyResponsive ? activeBreakpoint : undefined)}
                  disabled={lockedByBreakpoint}
                />
                {isResp && (
                  <ResponsiveToggle
                    isResponsive={isCurrentlyResponsive}
                    activeBreakpoint={activeBreakpoint}
                    onSync={() => handleSync(tokenKey)}
                    onUnsync={() => handleUnsync(tokenKey)}
                  />
                )}
                {lockedByBreakpoint && <LockedIndicator />}
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function camelToLabel(str: string): string {
  return str.replace(/([A-Z])/g, ' $1').replace(/^./, (s) => s.toUpperCase()).trim()
}

function isColorValue(val: string): boolean {
  return /^#([0-9A-F]{3,8})$/i.test(val) || val.startsWith('rgb') || val.startsWith('hsl')
}
