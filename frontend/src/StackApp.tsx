import { StackRenderer } from './components/StackRenderer'
import { useStack } from './hooks/useStack'

interface StackAppProps {
  stackId: string
  context: string
  objectId?: number
  objectType?: string
}

/**
 * Single stack application component.
 * Fetches and renders a specific stack by ID.
 */
export function StackApp({ stackId, objectId, objectType }: StackAppProps) {
  const { schema, loading, error } = useStack(stackId)

  if (loading) {
    return (
      <div className="os-flex os-items-center os-justify-center os-min-h-[100px]">
        <div className="os-animate-spin os-rounded-full os-h-6 os-w-6 os-border-b-2 os-border-wp-primary"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="os-bg-red-50 os-border os-border-red-200 os-rounded os-p-3 os-text-red-700 os-text-sm">
        <strong>Error:</strong> {error}
      </div>
    )
  }

  if (!schema) {
    return (
      <div className="os-bg-yellow-50 os-border os-border-yellow-200 os-rounded os-p-3 os-text-yellow-700 os-text-sm">
        Stack "{stackId}" not found.
      </div>
    )
  }

  return (
    <StackRenderer 
      schema={schema} 
      objectId={objectId}
      objectType={objectType}
    />
  )
}
