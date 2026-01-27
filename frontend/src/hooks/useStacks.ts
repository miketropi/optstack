import { useState, useEffect, useCallback } from 'react'
import { apiFetch } from '../utils/config'
import type { StackSchema } from '../schema/types'

interface UseStacksResult {
  stacks: StackSchema[]
  loading: boolean
  error: string | null
  refresh: () => void
}

/**
 * Hook for fetching all registered stacks.
 */
export function useStacks(): UseStacksResult {
  const [stacks, setStacks] = useState<StackSchema[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchStacks = useCallback(async () => {
    setLoading(true)
    setError(null)

    try {
      const data = await apiFetch<Record<string, StackSchema>>('stacks')

      // Convert object to array
      const stacksArray = Object.values(data)
      setStacks(stacksArray)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to fetch stacks')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    fetchStacks()
  }, [fetchStacks])

  return {
    stacks,
    loading,
    error,
    refresh: fetchStacks,
  }
}
