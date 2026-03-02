import { useState } from 'react'
import { TokenControl } from './TokenControl'
import type {
  DesignGroupSchema,
  DesignGroupValue,
  DesignPresetVariant,
} from './types'

interface Props {
  group: DesignGroupSchema
  tokens: DesignGroupValue
  onTokenChange: (tokenKey: string, value: unknown, variantId?: string) => void
}

const WEIGHT_LABELS: Record<number, string> = {
  100: 'Thin', 200: 'ExtraLight', 300: 'Light', 400: 'Regular',
  500: 'Medium', 600: 'SemiBold', 700: 'Bold', 800: 'ExtraBold', 900: 'Black',
}

export function GroupSpecimen({ group, tokens, onTokenChange }: Props) {
  const isVariant = group.variant && Array.isArray(tokens)
  const variants = isVariant ? (tokens as DesignPresetVariant[]) : null
  const flat = !isVariant ? (tokens as Record<string, unknown>) : null

  switch (group.id) {
    case 'heading':
      return <HeadingSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    case 'body_text':
      return <BodyTextSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    case 'inline_text':
      return <InlineTextSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    case 'button':
      return <VariantSpecimen group={group} variants={variants ?? []} onTokenChange={onTokenChange} renderVariant={renderButtonVariant} />
    case 'card':
      return <VariantSpecimen group={group} variants={variants ?? []} onTokenChange={onTokenChange} renderVariant={renderCardVariant} />
    case 'alert':
      return <VariantSpecimen group={group} variants={variants ?? []} onTokenChange={onTokenChange} renderVariant={renderAlertVariant} />
    case 'link':
      return <LinkSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    case 'form_field':
      return <FormFieldSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    case 'navigation':
      return <NavigationSpecimen group={group} tokens={flat!} onTokenChange={onTokenChange} />
    default:
      return <GenericSpecimen group={group} tokens={tokens} onTokenChange={onTokenChange} />
  }
}

// ---------------------------------------------------------------------------
// Heading Specimen (H1–H6 with font specimen)
// ---------------------------------------------------------------------------
function HeadingSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
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
      {/* Font display */}
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

      {/* H1-H6 specimens */}
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

      {/* Edit properties */}
      <div className="os-dp-divider" />
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Body Text Specimen
// ---------------------------------------------------------------------------
function BodyTextSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
  const [editOpen, setEditOpen] = useState(false)
  const fontFamily = String(tokens.fontFamily ?? 'Inter, sans-serif')
  const fontSize = String(tokens.fontSize ?? '16px')
  const fontWeight = Number(tokens.fontWeight ?? 400)
  const lineHeight = Number(tokens.lineHeight ?? 1.6)
  const color = String(tokens.color ?? '#374151')
  const fontName = fontFamily.split(',')[0].replace(/['"]/g, '').trim()
  const weightLabel = WEIGHT_LABELS[fontWeight] ?? String(fontWeight)

  const levels = [
    { label: 'Body 1', size: fontSize, weight: fontWeight, lh: lineHeight },
    { label: 'Body 2', size: `calc(${fontSize} * 0.875)`, weight: fontWeight, lh: lineHeight },
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
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Inline Text Specimen
// ---------------------------------------------------------------------------
function InlineTextSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
  const [editOpen, setEditOpen] = useState(false)
  const linkColor = String(tokens.linkColor ?? '#2563EB')
  const linkHoverColor = String(tokens.linkHoverColor ?? '#1D4ED8')
  const codeBg = String(tokens.codeBackground ?? '#F3F4F6')
  const codeFont = String(tokens.codeFontFamily ?? 'monospace')
  const markBg = String(tokens.markBackground ?? '#FEF08A')

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Link</div>
          <div className="os-dp-inline-preview">
            This is a <a style={{ color: linkColor, textDecoration: 'underline', cursor: 'default' }}>sample link</a> inside text.
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Color</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: linkColor }} />{linkColor}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Hover</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: linkHoverColor }} />{linkHoverColor}</span></span>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Inline Code</div>
          <div className="os-dp-inline-preview">
            Use the <code style={{ background: codeBg, fontFamily: codeFont, padding: '2px 6px', borderRadius: '4px', fontSize: '0.875em' }}>console.log()</code> function.
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Background</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: codeBg }} />{codeBg}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Font</span><span className="os-dp-meta-value">{codeFont}</span></span>
          </div>
        </div>

        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Highlight</div>
          <div className="os-dp-inline-preview">
            This is <mark style={{ background: markBg, padding: '2px 4px', borderRadius: '2px' }}>highlighted text</mark> inside a sentence.
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Background</span><span className="os-dp-meta-value"><span className="os-dp-meta-swatch" style={{ background: markBg }} />{markBg}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Link Specimen
// ---------------------------------------------------------------------------
function LinkSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
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
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Form Field Specimen
// ---------------------------------------------------------------------------
function FormFieldSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
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
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Navigation Specimen
// ---------------------------------------------------------------------------
function NavigationSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: Record<string, unknown>; onTokenChange: Props['onTokenChange'] }) {
  const [editOpen, setEditOpen] = useState(false)
  const fontFamily = String(tokens.fontFamily ?? 'Inter, sans-serif')
  const fontSize = String(tokens.fontSize ?? '14px')
  const fontWeight = Number(tokens.fontWeight ?? 500)
  const color = String(tokens.color ?? '#374151')
  const activeColor = String(tokens.activeColor ?? '#2563EB')
  const padding = String(tokens.padding ?? '8px 16px')

  const items = ['Home', 'Products', 'About', 'Contact']

  return (
    <div className="os-dp-specimen">
      <div className="os-dp-inline-specimens">
        <div className="os-dp-inline-card">
          <div className="os-dp-inline-label">Navigation Bar</div>
          <div className="os-dp-inline-preview">
            <nav style={{ display: 'flex', gap: '4px' }}>
              {items.map((item, i) => (
                <span
                  key={item}
                  style={{
                    fontFamily, fontSize, fontWeight, padding,
                    color: i === 0 ? activeColor : color,
                    borderBottom: i === 0 ? `2px solid ${activeColor}` : '2px solid transparent',
                    cursor: 'default',
                  }}
                >
                  {item}
                </span>
              ))}
            </nav>
          </div>
          <div className="os-dp-type-meta">
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Font</span><span className="os-dp-meta-value">{fontFamily.split(',')[0]}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Size</span><span className="os-dp-meta-value">{fontSize}</span></span>
            <span className="os-dp-meta-item"><span className="os-dp-meta-label">Weight</span><span className="os-dp-meta-value">{fontWeight}</span></span>
          </div>
        </div>
      </div>

      <div className="os-dp-divider" />
      <PropertySection open={editOpen} onToggle={() => setEditOpen(!editOpen)} group={group} tokens={tokens} onTokenChange={onTokenChange} />
    </div>
  )
}

// ---------------------------------------------------------------------------
// Variant Specimen (button, card, alert)
// ---------------------------------------------------------------------------
function VariantSpecimen({ group, variants, onTokenChange, renderVariant }: {
  group: DesignGroupSchema; variants: DesignPresetVariant[];
  onTokenChange: Props['onTokenChange'];
  renderVariant: (variant: DesignPresetVariant) => React.ReactNode;
}) {
  const [activeVariantId, setActiveVariantId] = useState(variants[0]?.id ?? '')
  const [editOpen, setEditOpen] = useState(false)
  const currentVariant = variants.find((v) => v.id === activeVariantId) ?? variants[0]

  return (
    <div className="os-dp-specimen">
      {/* All variant specimens */}
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

      {/* Variant tabs + edit */}
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
              {currentVariant && Object.entries(group.tokens).map(([tokenKey, tokenDef]) => (
                <TokenControl
                  key={`${currentVariant.id}-${tokenKey}`}
                  tokenKey={tokenKey}
                  definition={tokenDef}
                  value={currentVariant[tokenKey]}
                  onChange={(v) => onTokenChange(tokenKey, v, currentVariant.id)}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  )
}

function renderButtonVariant(variant: DesignPresetVariant) {
  return (
    <div className="os-dp-inline-preview">
      <button
        type="button"
        style={{
          fontFamily: String(variant.fontFamily ?? 'inherit'),
          fontSize: String(variant.fontSize ?? '14px'),
          fontWeight: Number(variant.fontWeight ?? 600),
          padding: String(variant.padding ?? '10px 20px'),
          borderRadius: String(variant.borderRadius ?? '6px'),
          border: `${variant.borderWidth ?? '0'} solid ${variant.borderColor ?? 'transparent'}`,
          background: String(variant.background ?? '#2563EB'),
          color: String(variant.color ?? '#fff'),
          cursor: 'default',
          display: 'inline-block',
        }}
      >
        {variant.label || variant.id} Button
      </button>
    </div>
  )
}

function renderCardVariant(variant: DesignPresetVariant) {
  return (
    <div className="os-dp-inline-preview">
      <div
        style={{
          background: String(variant.background ?? '#fff'),
          borderRadius: String(variant.borderRadius ?? '8px'),
          border: `${variant.borderWidth ?? '1px'} solid ${variant.borderColor ?? '#e5e7eb'}`,
          padding: String(variant.padding ?? '20px'),
          boxShadow: String(variant.shadow ?? 'none'),
          minHeight: '80px',
        }}
      >
        <div style={{ fontWeight: 600, fontSize: '14px', marginBottom: '6px', color: '#111' }}>Card Title</div>
        <div style={{ fontSize: '13px', color: '#6B7280' }}>Card content goes here with some sample text.</div>
      </div>
    </div>
  )
}

function renderAlertVariant(variant: DesignPresetVariant) {
  return (
    <div className="os-dp-inline-preview">
      <div
        style={{
          background: String(variant.background ?? '#EFF6FF'),
          borderRadius: String(variant.borderRadius ?? '6px'),
          border: `1px solid ${variant.borderColor ?? '#BFDBFE'}`,
          padding: String(variant.padding ?? '12px 16px'),
          color: String(variant.color ?? '#1E40AF'),
          fontSize: '13px',
          display: 'flex',
          alignItems: 'center',
          gap: '8px',
        }}
      >
        <span style={{ color: String(variant.iconColor ?? variant.color ?? '#1E40AF'), fontSize: '16px' }}>●</span>
        This is a {variant.label || variant.id} alert message.
      </div>
    </div>
  )
}

// ---------------------------------------------------------------------------
// Generic Specimen (for groups without special specimens)
// ---------------------------------------------------------------------------
function GenericSpecimen({ group, tokens, onTokenChange }: { group: DesignGroupSchema; tokens: DesignGroupValue; onTokenChange: Props['onTokenChange'] }) {
  const [editOpen, setEditOpen] = useState(true)
  const isVariant = group.variant && Array.isArray(tokens)
  const variants = isVariant ? (tokens as DesignPresetVariant[]) : null
  const flat = !isVariant ? (tokens as Record<string, unknown>) : null
  const [activeVariantId, setActiveVariantId] = useState(variants?.[0]?.id ?? '')
  const currentVariant = variants?.find((v) => v.id === activeVariantId) ?? variants?.[0]

  return (
    <div className="os-dp-specimen">
      {/* Applies to */}
      <div className="os-dp-applies-to">
        {group.applies_to.map((tag) => (
          <span key={tag} className="os-dp-applies-tag">{tag}</span>
        ))}
      </div>

      {/* Token values as specimen cards */}
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
      <div className="os-dp-property-section">
        <button type="button" className="os-dp-property-toggle" onClick={() => setEditOpen(!editOpen)}>
          <svg className={`os-dp-property-chevron ${editOpen ? 'is-open' : ''}`} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="6 9 12 15 18 9" /></svg>
          <span>Edit Properties</span>
        </button>
        {editOpen && (
          <>
            {isVariant && variants && (
              <div className="os-dp-variant-tabs">
                {variants.map((v) => (
                  <button key={v.id} type="button" className={`os-dp-variant-tab ${v.id === activeVariantId ? 'is-active' : ''}`} onClick={() => setActiveVariantId(v.id)}>
                    {v.label || v.id}
                  </button>
                ))}
              </div>
            )}
            <div className="os-dp-property-grid">
              {isVariant && currentVariant
                ? Object.entries(group.tokens).map(([tokenKey, tokenDef]) => (
                    <TokenControl key={`${currentVariant.id}-${tokenKey}`} tokenKey={tokenKey} definition={tokenDef} value={currentVariant[tokenKey]} onChange={(v) => onTokenChange(tokenKey, v, currentVariant.id)} />
                  ))
                : flat && Object.entries(group.tokens).map(([tokenKey, tokenDef]) => (
                    <TokenControl key={tokenKey} tokenKey={tokenKey} definition={tokenDef} value={flat[tokenKey]} onChange={(v) => onTokenChange(tokenKey, v)} />
                  ))}
            </div>
          </>
        )}
      </div>
    </div>
  )
}

// ---------------------------------------------------------------------------
// Property Section (expandable controls using OptStack field types)
// ---------------------------------------------------------------------------
function PropertySection({ open, onToggle, group, tokens, onTokenChange }: {
  open: boolean; onToggle: () => void;
  group: DesignGroupSchema; tokens: Record<string, unknown>;
  onTokenChange: Props['onTokenChange'];
}) {
  return (
    <div className="os-dp-property-section">
      <button type="button" className="os-dp-property-toggle" onClick={onToggle}>
        <svg className={`os-dp-property-chevron ${open ? 'is-open' : ''}`} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="6 9 12 15 18 9" /></svg>
        <span>Edit Properties</span>
      </button>
      {open && (
        <div className="os-dp-property-grid">
          {Object.entries(group.tokens).map(([tokenKey, tokenDef]) => (
            <TokenControl
              key={tokenKey}
              tokenKey={tokenKey}
              definition={tokenDef}
              value={tokens[tokenKey]}
              onChange={(v) => onTokenChange(tokenKey, v)}
            />
          ))}
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
