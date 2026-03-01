import { useState, useEffect, useCallback, useRef } from 'react'
import { apiFetch, config } from '../utils/config'
import type { StackDataResponse } from '../schema/types'

interface UseStackDataResult {
  data: Record<string, unknown>
  loading: boolean
  error: string | null
  saving: boolean
  isDirty: boolean
  updateField: (key: string, value: unknown) => void
  save: () => Promise<boolean>
  reset: () => void
}

/**
 * Sync data to the WordPress Customizer setting so "Publish" activates
 * and the preview refreshes. Debounced to avoid excessive preview reloads.
 */
const CUSTOMIZER_SYNC_DELAY = 600

function syncToCustomizer(settingId: string, data: Record<string, unknown>): void {
  try {
    const setting = window.wp?.customize?.(settingId)
    if (setting) {
      setting.set(JSON.stringify(data))
    }
  } catch {
    // Customizer API may not be available
  }
}

/**
 * Hook for managing stack data (fetch, update, save).
 * 
 * @param stackId - The stack identifier
 * @param objectId - Optional object ID (post ID, term ID, user ID) for non-options contexts
 */
export function useStackData(stackId: string, objectId?: number): UseStackDataResult {
  const [originalData, setOriginalData] = useState<Record<string, unknown>>({})
  const [data, setData] = useState<Record<string, unknown>>({})
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [saving, setSaving] = useState(false)
  const customizerSyncTimer = useRef<ReturnType<typeof setTimeout> | null>(null)

  const isCustomizer = config.isCustomizer && !!config.customizerSettings?.[stackId]
  const customizerSettingId = config.customizerSettings?.[stackId]

  // Compute dirty state
  const isDirty = JSON.stringify(data) !== JSON.stringify(originalData)

  const fetchData = useCallback(async () => {
    setLoading(true)
    setError(null)

    try {
      const url = objectId 
        ? `stacks/${stackId}/data?object_id=${objectId}`
        : `stacks/${stackId}/data`
      const result = await apiFetch<StackDataResponse>(url)
      setOriginalData(result.data)
      setData(result.data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to fetch data')
    } finally {
      setLoading(false)
    }
  }, [stackId, objectId])

  useEffect(() => {
    fetchData()
  }, [fetchData])

  useEffect(() => {
    return () => {
      if (customizerSyncTimer.current) clearTimeout(customizerSyncTimer.current)
    }
  }, [])

  const updateField = useCallback((key: string, value: unknown) => {
    setData((prev) => {
      const next = { ...prev, [key]: value }

      if (isCustomizer && customizerSettingId) {
        if (customizerSyncTimer.current) clearTimeout(customizerSyncTimer.current)
        customizerSyncTimer.current = setTimeout(() => {
          syncToCustomizer(customizerSettingId, next)
        }, CUSTOMIZER_SYNC_DELAY)
      }

      return next
    })
  }, [isCustomizer, customizerSettingId])

  const save = useCallback(async (): Promise<boolean> => {
    setSaving(true)
    setError(null)

    try {
      const url = objectId 
        ? `stacks/${stackId}/data?object_id=${objectId}`
        : `stacks/${stackId}/data`
      const result = await apiFetch<{ success: boolean; data: Record<string, unknown>; error?: string }>(
        url,
        {
          method: 'POST',
          body: JSON.stringify(data),
        }
      )

      if (result.success) {
        setOriginalData(result.data)
        setData(result.data)
        if (isCustomizer && customizerSettingId) {
          syncToCustomizer(customizerSettingId, result.data)
        }
        return true
      } else {
        throw new Error(result.error || 'Failed to save')
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save data')
      return false
    } finally {
      setSaving(false)
    }
  }, [stackId, objectId, data, isCustomizer, customizerSettingId])

  const reset = useCallback(() => {
    setData(originalData)
    if (isCustomizer && customizerSettingId) {
      syncToCustomizer(customizerSettingId, originalData)
    }
  }, [originalData, isCustomizer, customizerSettingId])

  return {
    data,
    loading,
    error,
    saving,
    isDirty,
    updateField,
    save,
    reset,
  }
}
