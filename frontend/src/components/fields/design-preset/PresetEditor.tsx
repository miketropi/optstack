import { useState, useMemo, useCallback, useRef, useEffect } from 'react'
import { GroupSpecimen } from './GroupSpecimen'
import type {
  Breakpoint,
  DesignGroupSchema,
  DesignPresetData,
  DesignPresetFieldValue,
  DesignGroupValue,
} from './types'
import { BREAKPOINTS, isResponsiveValue, resolveBreakpointValue } from './types'

interface Props {
  value: DesignPresetFieldValue
  groups: Record<string, DesignGroupSchema>
  presets: DesignPresetData[]
  allowedGroups?: string[]
  allowCustom?: boolean
  onChange: (value: DesignPresetFieldValue) => void
  onClose: () => void
}

const BREAKPOINT_ICONS: Record<Breakpoint, { svg: string; label: string }> = {
  desktop: {
    svg: '<rect x="2" y="3" width="20" height="14" rx="2" /><line x1="8" y1="21" x2="16" y2="21" /><line x1="12" y1="17" x2="12" y2="21" />',
    label: 'Desktop',
  },
  tablet: {
    svg: '<rect x="4" y="2" width="16" height="20" rx="2" /><line x1="12" y1="18" x2="12" y2="18" />',
    label: 'Tablet',
  },
  mobile: {
    svg: '<rect x="5" y="2" width="14" height="20" rx="2" /><line x1="12" y1="18" x2="12" y2="18" />',
    label: 'Mobile',
  },
}

export function PresetEditor({ value, groups, presets, allowedGroups, allowCustom = true, onChange, onClose }: Props) {
  const filteredGroupIds = useMemo(() => {
    const ids = Object.keys(groups)
    return allowedGroups?.length ? ids.filter((id) => allowedGroups.includes(id)) : ids
  }, [groups, allowedGroups])

  const [activeGroupId, setActiveGroupId] = useState<string>(filteredGroupIds[0] ?? '')
  const [activeBreakpoint, setActiveBreakpoint] = useState<Breakpoint>('desktop')
  const [renaming, setRenaming] = useState(false)
  const renameInputRef = useRef<HTMLInputElement>(null)

  const allPresets = useMemo(() => {
    const custom = value.presets ?? []
    return [...presets, ...custom]
  }, [presets, value.presets])

  const activePreset = useMemo(() => {
    return allPresets.find((p) => p.id === value.active_preset) ?? presets[0]
  }, [allPresets, presets, value.active_preset])

  const isCustomPreset = useMemo(() => {
    return (value.presets ?? []).some((p) => p.id === value.active_preset)
  }, [value.presets, value.active_preset])

  const resolvedGroupTokens = useMemo((): DesignGroupValue => {
    if (!activeGroupId) return {}
    const base = activePreset?.tokens[activeGroupId]
    const overrides = value.overrides ?? {}

    if (Array.isArray(base)) {
      return base.map((variant) => {
        const merged = { ...variant }
        Object.entries(overrides).forEach(([path, val]) => {
          const parts = path.split('.')
          if (parts[0] === activeGroupId && parts[1] === variant.id && parts[2]) {
            if (parts[3]) {
              const cur = merged[parts[2]]
              if (typeof cur === 'object' && cur !== null) {
                merged[parts[2]] = { ...(cur as Record<string, unknown>), [parts[3]]: val }
              } else {
                merged[parts[2]] = { [parts[3]]: val }
              }
            } else {
              merged[parts[2]] = val
            }
          }
        })
        return merged
      })
    }

    const merged: Record<string, unknown> = { ...(base ?? {}) }

    Object.entries(overrides).forEach(([path, val]) => {
      const parts = path.split('.')
      if (parts[0] === activeGroupId && parts[1]) {
        if (parts.length === 2) {
          merged[parts[1]] = val
        } else if (parts.length === 3) {
          if (typeof merged[parts[1]] === 'object' && merged[parts[1]] !== null) {
            merged[parts[1]] = { ...(merged[parts[1]] as Record<string, unknown>), [parts[2]]: val }
          } else {
            merged[parts[1]] = { [parts[2]]: val }
          }
        }
      }
    })

    return merged as DesignGroupValue
  }, [activePreset, activeGroupId, value.overrides])

  const resolvedForPreview = useMemo((): DesignGroupValue => {
    if (!activeGroupId) return {}
    const group = groups[activeGroupId]
    if (!group) return resolvedGroupTokens

    if (Array.isArray(resolvedGroupTokens)) {
      return resolvedGroupTokens.map((variant) => {
        const resolved = { ...variant }
        Object.entries(group.tokens).forEach(([key, def]) => {
          if (def.responsive && isResponsiveValue(resolved[key])) {
            resolved[key] = resolveBreakpointValue(resolved[key], activeBreakpoint)
          }
        })
        return resolved
      })
    }

    const flat = resolvedGroupTokens as Record<string, unknown>
    const resolved: Record<string, unknown> = { ...flat }
    Object.entries(group.tokens).forEach(([key, def]) => {
      if (def.responsive && isResponsiveValue(resolved[key])) {
        resolved[key] = resolveBreakpointValue(resolved[key], activeBreakpoint)
      }
    })
    return resolved
  }, [resolvedGroupTokens, groups, activeGroupId, activeBreakpoint])

  const overrideCount = useMemo(() => Object.keys(value.overrides ?? {}).length, [value.overrides])

  const handlePresetChange = useCallback((presetId: string) => {
    setRenaming(false)
    onChange({ ...value, active_preset: presetId, overrides: {} })
  }, [value, onChange])

  const handleTokenChange = useCallback((tokenKey: string, tokenValue: unknown, variantId?: string) => {
    const path = variantId
      ? `${activeGroupId}.${variantId}.${tokenKey}`
      : `${activeGroupId}.${tokenKey}`
    onChange({ ...value, overrides: { ...(value.overrides ?? {}), [path]: tokenValue as string | number } })
  }, [value, onChange, activeGroupId])

  const handleBatchTokenChange = useCallback((changes: Record<string, unknown>, variantId?: string) => {
    const newOverrides = { ...(value.overrides ?? {}) }
    for (const [tokenKey, tokenValue] of Object.entries(changes)) {
      const path = variantId
        ? `${activeGroupId}.${variantId}.${tokenKey}`
        : `${activeGroupId}.${tokenKey}`
      if (tokenValue === undefined) {
        delete newOverrides[path]
      } else {
        newOverrides[path] = tokenValue as string | number
      }
    }
    onChange({ ...value, overrides: newOverrides })
  }, [value, onChange, activeGroupId])

  const handleResetOverrides = useCallback(() => {
    onChange({ ...value, overrides: {} })
  }, [value, onChange])

  const handleClonePreset = useCallback(() => {
    if (!activePreset) return
    const newId = `custom-${Date.now()}`
    const newPreset: DesignPresetData = {
      id: newId,
      label: `${activePreset.label} (Copy)`,
      base: activePreset.id,
      tokens: {},
    }
    const applyOverrides = value.overrides ?? {}
    if (Object.keys(applyOverrides).length > 0) {
      for (const [path, val] of Object.entries(applyOverrides)) {
        const parts = path.split('.')
        const groupId = parts[0]
        if (!newPreset.tokens[groupId]) newPreset.tokens[groupId] = {}
        if (parts.length === 2) {
          (newPreset.tokens[groupId] as Record<string, unknown>)[parts[1]] = val
        }
      }
    }
    setRenaming(false)
    onChange({ active_preset: newId, overrides: {}, presets: [...(value.presets ?? []), newPreset] })
  }, [activePreset, value, onChange])

  const handleRenamePreset = useCallback((newLabel: string) => {
    const trimmed = newLabel.trim()
    if (!trimmed || !isCustomPreset) return
    const updatedPresets = (value.presets ?? []).map((p) =>
      p.id === value.active_preset ? { ...p, label: trimmed } : p
    )
    onChange({ ...value, presets: updatedPresets })
    setRenaming(false)
  }, [value, onChange, isCustomPreset])

  const handleDeletePreset = useCallback(() => {
    if (!isCustomPreset) return
    const updatedPresets = (value.presets ?? []).filter((p) => p.id !== value.active_preset)
    const fallbackPreset = presets[0]?.id ?? ''
    setRenaming(false)
    onChange({ active_preset: fallbackPreset, overrides: {}, presets: updatedPresets })
  }, [value, onChange, isCustomPreset, presets])

  useEffect(() => {
    if (renaming && renameInputRef.current) {
      renameInputRef.current.focus()
      renameInputRef.current.select()
    }
  }, [renaming])

  const activeGroup = groups[activeGroupId]

  const groupIcons: Record<string, string> = {
    heading: 'Aa', body_text: 'Tt', button: '▢', link: '🔗',
    form_field: '▭', form_meta: '✎', container: '⊞',
    table: '▦', list: '☰',
  }

  return (
    <div className="os-dp-backdrop" onClick={onClose}>
      <div className="os-dp-modal" onClick={(e) => e.stopPropagation()}>
        {/* Header bar */}
        <div className="os-dp-header">
          <div className="os-dp-header-left">
            <h2 className="os-dp-header-title">Design System</h2>
            <div className="os-dp-header-preset">
              <select
                className="os-dp-preset-select"
                value={value.active_preset}
                onChange={(e) => handlePresetChange(e.target.value)}
              >
                {presets.map((p) => (
                  <option key={p.id} value={p.id}>{p.label}</option>
                ))}
                {(value.presets ?? []).length > 0 && (
                  <optgroup label="Custom">
                    {(value.presets ?? []).map((p) => (
                      <option key={p.id} value={p.id}>{p.label}</option>
                    ))}
                  </optgroup>
                )}
              </select>
              {allowCustom && (
                <button type="button" className="os-dp-header-action" onClick={handleClonePreset} title="Clone preset">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" /></svg>
                </button>
              )}
              {isCustomPreset && (
                <>
                  <button type="button" className="os-dp-header-action" onClick={() => setRenaming(true)} title="Rename preset">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                  </button>
                  <button type="button" className="os-dp-header-action os-dp-header-delete" onClick={handleDeletePreset} title="Delete preset">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /></svg>
                  </button>
                </>
              )}
              {overrideCount > 0 && (
                <button type="button" className="os-dp-header-action os-dp-header-reset" onClick={handleResetOverrides} title="Reset overrides">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 4v6h6" /><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" /></svg>
                  <span>{overrideCount}</span>
                </button>
              )}
            </div>
          </div>

          {/* Breakpoint switcher */}
          <div className="os-dp-breakpoint-bar">
            {BREAKPOINTS.map((bp) => (
              <button
                key={bp}
                type="button"
                className={`os-dp-breakpoint-btn ${bp === activeBreakpoint ? 'is-active' : ''}`}
                onClick={() => setActiveBreakpoint(bp)}
                title={BREAKPOINT_ICONS[bp].label}
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" dangerouslySetInnerHTML={{ __html: BREAKPOINT_ICONS[bp].svg }} />
              </button>
            ))}
          </div>

          <button type="button" className="os-dp-header-close" onClick={onClose}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
          </button>
        </div>

        {/* Rename bar */}
        {renaming && isCustomPreset && (
          <div className="os-dp-rename-bar">
            <input
              ref={renameInputRef}
              type="text"
              className="os-dp-rename-input"
              defaultValue={activePreset?.label ?? ''}
              onKeyDown={(e) => {
                if (e.key === 'Enter') handleRenamePreset(e.currentTarget.value)
                if (e.key === 'Escape') setRenaming(false)
              }}
              onBlur={(e) => handleRenamePreset(e.currentTarget.value)}
              placeholder="Preset name..."
            />
            <span className="os-dp-rename-hint">Press Enter to save, Escape to cancel</span>
          </div>
        )}

        {/* Body */}
        <div className="os-dp-body">
          {/* Sidebar */}
          <nav className="os-dp-sidebar">
            {filteredGroupIds.map((gid) => (
              <button
                key={gid}
                type="button"
                className={`os-dp-nav-item ${gid === activeGroupId ? 'is-active' : ''}`}
                onClick={() => setActiveGroupId(gid)}
              >
                <span className="os-dp-nav-icon">{groupIcons[gid] ?? '●'}</span>
                <span className="os-dp-nav-label">{groups[gid].label}</span>
              </button>
            ))}
          </nav>

          {/* Main content: specimen + controls */}
          <main className={`os-dp-main os-dp-viewport-${activeBreakpoint}`}>
            {activeBreakpoint !== 'desktop' && (
              <div className="os-dp-viewport-label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" dangerouslySetInnerHTML={{ __html: BREAKPOINT_ICONS[activeBreakpoint].svg }} />
                <span>{BREAKPOINT_ICONS[activeBreakpoint].label} Preview</span>
              </div>
            )}
            <div className="os-dp-viewport-frame">
              {activeGroup && (
                <GroupSpecimen
                  group={activeGroup}
                  tokens={resolvedForPreview}
                  rawTokens={resolvedGroupTokens}
                  activeBreakpoint={activeBreakpoint}
                  onTokenChange={handleTokenChange}
                  onBatchTokenChange={handleBatchTokenChange}
                />
              )}
            </div>
          </main>
        </div>
      </div>
    </div>
  )
}
