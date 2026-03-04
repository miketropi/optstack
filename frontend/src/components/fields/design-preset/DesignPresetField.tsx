import { useState, useMemo, useCallback } from 'react'
import type { FieldRendererProps } from '../../../schema/types'
import type { DesignPresetFieldValue, DesignPresetData } from './types'
import { useDesignPresetData } from './useDesignPresetData'
import { PresetEditor } from './PresetEditor'

export function DesignPresetField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [editorOpen, setEditorOpen] = useState(false)
  const { data: systemData, loading, error: fetchError } = useDesignPresetData()

  const allowedPresets = field.attributes?.allowed_presets as string[] | undefined

  const allPresets = useMemo(() => {
    const all = systemData?.presets ?? []
    if (allowedPresets?.length) {
      return all.filter((p) => allowedPresets.includes(p.id))
    }
    return all
  }, [systemData, allowedPresets])

  const fieldValue = useMemo((): DesignPresetFieldValue => {
    const configuredDefault = (field.attributes?.default_preset as string) || 'modern'
    const defaultPreset = allPresets.find((p) => p.id === configuredDefault)
      ? configuredDefault
      : (allPresets[0]?.id ?? configuredDefault)

    if (value && typeof value === 'object' && !Array.isArray(value)) {
      return {
        active_preset: (value as Record<string, unknown>).active_preset as string || defaultPreset,
        overrides: (value as Record<string, unknown>).overrides as Record<string, string | number> || {},
        presets: (value as Record<string, unknown>).presets as DesignPresetData[] || [],
      }
    }
    return { active_preset: defaultPreset, overrides: {} }
  }, [value, field.attributes?.default_preset, allPresets])

  const activePreset = useMemo(() => {
    const customMatch = fieldValue.presets?.find((p) => p.id === fieldValue.active_preset)
    if (customMatch) return customMatch
    return allPresets.find((p) => p.id === fieldValue.active_preset) ?? allPresets[0]
  }, [allPresets, fieldValue])

  const overrideCount = Object.keys(fieldValue.overrides ?? {}).length

  const handleChange = useCallback((newValue: DesignPresetFieldValue) => {
    onChange(newValue)
  }, [onChange])

  const presetColors = useMemo(() => {
    if (!activePreset?.tokens) return []
    const colors: string[] = []
    const btn = activePreset.tokens.button
    if (Array.isArray(btn) && btn[0]?.background && typeof btn[0].background === 'string') {
      colors.push(btn[0].background)
    }
    const heading = activePreset.tokens.heading as Record<string, unknown> | undefined
    if (heading?.color && typeof heading.color === 'string') colors.push(heading.color)
    const bodyText = activePreset.tokens.body_text as Record<string, unknown> | undefined
    if (bodyText?.color && typeof bodyText.color === 'string') colors.push(bodyText.color)
    const link = activePreset.tokens.link as Record<string, unknown> | undefined
    if (link?.color && typeof link.color === 'string') colors.push(link.color)
    return colors.slice(0, 5)
  }, [activePreset])

  if (loading) {
    return (
      <div className="os-field os-field-design-preset">
        <label className="os-label">{field.label}</label>
        <div className="os-field-body">
          <div className="os-dp-loading">Loading design presets...</div>
        </div>
      </div>
    )
  }

  if (fetchError || !systemData) {
    return (
      <div className="os-field os-field-design-preset">
        <label className="os-label">{field.label}</label>
        <div className="os-field-body">
          <div className="os-dp-error">Failed to load design presets{fetchError ? `: ${fetchError}` : ''}</div>
        </div>
      </div>
    )
  }

  return (
    <div className={`os-field os-field-design-preset ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>

      <div className="os-field-body">
        <div className="os-dp-summary" onClick={() => !disabled && setEditorOpen(true)}>
          <div className="os-dp-summary-left">
            <div className="os-dp-summary-colors">
              {presetColors.map((c, i) => (
                <span key={i} className="os-dp-summary-swatch" style={{ backgroundColor: c }} />
              ))}
            </div>
            <div className="os-dp-summary-info">
              <span className="os-dp-summary-name">{activePreset?.label ?? fieldValue.active_preset}</span>
              {overrideCount > 0 && (
                <span className="os-dp-summary-overrides">{overrideCount} override{overrideCount !== 1 ? 's' : ''}</span>
              )}
            </div>
          </div>
          <button
            type="button"
            className="os-dp-btn os-dp-btn-sm os-dp-btn-outline"
            disabled={disabled}
            onClick={(e) => {
              e.stopPropagation()
              setEditorOpen(true)
            }}
          >
            Edit
          </button>
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>

      {editorOpen && (
        <PresetEditor
          value={fieldValue}
          groups={systemData.groups}
          presets={allPresets}
          allowedGroups={field.attributes?.allowed_groups as string[] | undefined}
          allowCustom={field.attributes?.allow_custom !== false}
          onChange={handleChange}
          onClose={() => setEditorOpen(false)}
        />
      )}
    </div>
  )
}
