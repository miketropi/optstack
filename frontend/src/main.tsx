import React from 'react'
import ReactDOM from 'react-dom/client'
import { StackApp } from './StackApp'
import { registerGroupSpecimen, unregisterGroupSpecimen, hasGroupSpecimen } from './components/fields/design-preset'
import './styles/main.css'

// Expose design preset specimen registration on window.optstack
const win = window as Window & { optstack?: Record<string, unknown> }
win.optstack = {
  ...(win.optstack ?? {}),
  registerGroupSpecimen,
  unregisterGroupSpecimen,
  hasGroupSpecimen,
}

const mountedElements = new WeakSet<HTMLElement>()

/**
 * Find all OptStack mount points and render React apps.
 * Each mount point has data attributes specifying the stack to render.
 */
function mountOptStack(): void {
  const mountPoints = document.querySelectorAll<HTMLElement>('.optstack-mount')

  if (mountPoints.length === 0) {
    const legacyMount = document.getElementById('optstack-root')
    if (legacyMount) {
      mountSingle(legacyMount)
    }
    return
  }

  mountPoints.forEach(mountSingle)
}

/**
 * Mount a single React app on an element.
 */
function mountSingle(element: HTMLElement): void {
  if (mountedElements.has(element)) return
  mountedElements.add(element)

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

/**
 * Watch for dynamically added .optstack-mount elements (e.g. WordPress
 * Customizer lazy-renders control HTML only when a section is expanded).
 */
function observeNewMountPoints(): void {
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (!(node instanceof HTMLElement)) continue

        if (node.matches('.optstack-mount')) {
          mountSingle(node)
        }

        const nested = node.querySelectorAll<HTMLElement>('.optstack-mount')
        nested.forEach(mountSingle)
      }
    }
  })

  observer.observe(document.body, { childList: true, subtree: true })
}

// Mount existing elements when DOM is ready, then observe for new ones
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    mountOptStack()
    observeNewMountPoints()
  })
} else {
  mountOptStack()
  observeNewMountPoints()
}
