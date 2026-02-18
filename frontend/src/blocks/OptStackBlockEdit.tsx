import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { PanelBody, Spinner } from '@wordpress/components'
import ServerSideRender from '@wordpress/server-side-render'
import { BlockStackRenderer } from './BlockStackRenderer'

interface OptStackBlockEditProps {
  attributes: Record<string, unknown>
  setAttributes: (attrs: Record<string, unknown>) => void
  name: string
}

/**
 * Generic edit component for OptStack-powered blocks.
 * Renders InspectorControls with BlockStackRenderer; live preview via ServerSideRender.
 */
export function OptStackBlockEdit({
  attributes,
  setAttributes,
  name,
}: OptStackBlockEditProps) {
  const blockToStack = (window as unknown as { optstackBlocks?: { blockToStack?: Record<string, string> } })
    ?.optstackBlocks?.blockToStack ?? {}
  const stackId = blockToStack[name]

  const blockProps = useBlockProps({
    className: 'optstack-block-preview',
  })

  if (!stackId) {
    return (
      <div {...blockProps}>
        <div className="optstack-block-error" style={{ padding: '16px', background: '#fee', color: '#c00' }}>
          OptStack: No stack found for block &quot;{name}&quot;
        </div>
      </div>
    )
  }

  return (
    <>
      <InspectorControls>
        <PanelBody title="Block Settings" initialOpen={true}>
          <BlockStackRenderer
            stackId={stackId}
            attributes={attributes}
            setAttributes={setAttributes}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block={name}
          attributes={attributes}
          LoadingPlaceholder={() => (
            <div className="optstack-block-loading" style={{
              padding: '24px',
              textAlign: 'center' as const,
              color: '#666',
            }}>
              <Spinner />
              <p style={{ margin: '8px 0 0' }}>Loading preview…</p>
            </div>
          )}
          ErrorPlaceholder={({ response }) => (
            <div className="optstack-block-error" style={{
              padding: '16px',
              background: '#fef2f2',
              color: '#991b1b',
              borderRadius: '4px',
            }}>
              <strong>Preview error:</strong> {response?.message || 'Failed to load'}
            </div>
          )}
          EmptyPlaceholder={() => (
            <div className="optstack-block-empty" style={{
              padding: '24px',
              border: '1px dashed #ccc',
              background: '#f9fafb',
              textAlign: 'center' as const,
              color: '#6b7280',
            }}>
              <p style={{ margin: 0 }}>No preview available</p>
            </div>
          )}
        />
      </div>
    </>
  )
}
