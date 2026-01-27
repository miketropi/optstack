import { useState, useEffect, useCallback } from 'react'
import { apiFetch } from '../utils/config'
import type { StackSchema } from '../schema/types'

interface UseStackResult {
  schema: StackSchema | null
  loading: boolean
  error: string | null
  refresh: () => void
}

/**
 * Hook for fetching a single stack schema by ID.
 */
export function useStack(stackId: string): UseStackResult {
  const [schema, setSchema] = useState<StackSchema | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchStack = useCallback(async () => {
    setLoading(true)
    setError(null)

    try {
      const data = await apiFetch<StackSchema>(`stacks/${stackId}`)
      setSchema(data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to fetch stack')
    } finally {
      setLoading(false)
    }
  }, [stackId])

  useEffect(() => {
    fetchStack()
  }, [fetchStack])

  return {
    schema,
    loading,
    error,
    refresh: fetchStack,
  }
}
