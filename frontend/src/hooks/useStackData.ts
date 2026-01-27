import { useState, useEffect, useCallback } from 'react'
import { apiFetch } from '../utils/config'
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

  const updateField = useCallback((key: string, value: unknown) => {
    setData((prev) => ({
      ...prev,
      [key]: value,
    }))
  }, [])

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
  }, [stackId, data])

  const reset = useCallback(() => {
    setData(originalData)
  }, [originalData])

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
