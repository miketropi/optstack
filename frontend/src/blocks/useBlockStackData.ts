import { useCallback } from 'react'
import { useStack } from '../hooks/useStack'
import type { StackSchema } from '../schema/types'

interface UseBlockStackDataResult {
  schema: StackSchema | null
  loading: boolean
  error: string | null
  data: Record<string, unknown>
  updateField: (key: string, value: unknown) => void
}

/**
 * Hook for block context: uses block attributes as data source,
 * setAttributes as save. Schema is fetched via REST.
 */
export function useBlockStackData(
  stackId: string,
  attributes: Record<string, unknown>,
  setAttributes: (attrs: Record<string, unknown>) => void
): UseBlockStackDataResult {
  const { schema, loading, error } = useStack(stackId)

  const updateField = useCallback(
    (key: string, value: unknown) => {
      setAttributes({ ...attributes, [key]: value })
    },
    [attributes, setAttributes]
  )

  return {
    schema,
    loading,
    error,
    data: attributes,
    updateField,
  }
}
