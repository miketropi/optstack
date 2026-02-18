import { registerBlockType } from '@wordpress/blocks'
import { OptStackBlockEdit } from './OptStackBlockEdit'
import '../styles/main.css'

interface OptStackBlocksConfig {
  blockToStack?: Record<string, string>
  blockMetadata?: Record<string, {
    title: string
    category: string
    icon: string
    keywords?: string[]
    attributes: Record<string, { type: string; default?: unknown }>
  }>
}

const config = (window as unknown as { optstackBlocks?: OptStackBlocksConfig })?.optstackBlocks ?? {}
const blockToStack = config.blockToStack ?? {}
const blockMetadata = config.blockMetadata ?? {}

if (Object.keys(blockToStack).length === 0) {
  console.warn('OptStack: No blocks registered. Add stacks with forBlockType() and ensure BlockRegistry runs.')
}

Object.entries(blockToStack).forEach(([blockType]) => {
  if (!blockType.includes('/')) return

  const meta = blockMetadata[blockType] ?? {}
  registerBlockType(blockType, {
    apiVersion: 2,
    title: meta.title || blockType,
    category: (meta.category as 'text' | 'media' | 'design' | 'widgets' | 'theme' | 'embed') || 'theme',
    icon: meta.icon || 'admin-generic',
    keywords: meta.keywords || [meta.title || blockType, 'optstack'],
    attributes: meta.attributes || {},
    edit: (props: { attributes: Record<string, unknown>; setAttributes: (attrs: Record<string, unknown>) => void }) => {
      const { attributes, setAttributes } = props
      return (
        <OptStackBlockEdit
          attributes={attributes}
          setAttributes={setAttributes}
          name={blockType}
        />
      )
    },
    save: () => null,
  } as never)
})
