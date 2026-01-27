import React from 'react'
import ReactDOM from 'react-dom/client'
import { StackApp } from './StackApp'
import './styles/main.css'

/**
 * Find all OptStack mount points and render React apps.
 * Each mount point has data attributes specifying the stack to render.
 */
function mountOptStack(): void {
  // Find all mount points
  const mountPoints = document.querySelectorAll<HTMLElement>('.optstack-mount')

  if (mountPoints.length === 0) {
    // Fallback: try legacy mount point
    const legacyMount = document.getElementById('optstack-root')
    if (legacyMount) {
      mountSingle(legacyMount)
    }
    return
  }

  // Mount each stack
  mountPoints.forEach(mountSingle)
}

/**
 * Mount a single React app on an element.
 */
function mountSingle(element: HTMLElement): void {
  const stackId = element.dataset.stack
  const context = element.dataset.context || 'options'
  const objectId = element.dataset.objectId ? parseInt(element.dataset.objectId, 10) : undefined
  const objectType = element.dataset.objectType

  if (!stackId) {
    console.error('OptStack: Missing data-stack attribute on mount point', element)
    return
  }

  ReactDOM.createRoot(element).render(
    <React.StrictMode>
      <StackApp
        stackId={stackId}
        context={context}
        objectId={objectId}
        objectType={objectType}
      />
    </React.StrictMode>
  )
}

// Mount when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountOptStack)
} else {
  mountOptStack()
}
