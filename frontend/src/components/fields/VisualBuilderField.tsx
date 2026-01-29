import { useState, useCallback, useEffect, useRef } from 'react'
import ReactDOM from 'react-dom'
import {
  GripVertical,
  Image,
  Menu,
  MousePointerClick,
  Search,
  Space,
  Minus,
  Share2,
  Type,
  Plus,
  X,
  Trash2,
  Settings,
  Layout,
  ChevronRight,
  Columns,
  Rows,
  ChevronDown,
  ChevronUp,
  Square,
} from 'lucide-react'
import type { FieldRendererProps, VisualBuilderValue, VisualBuilderBlock, BlockTypeDefinition, FieldSchema } from '../../schema/types'
import { FieldRenderer } from '../FieldRenderer'

// =============================================================================
// Block Type Definitions
// =============================================================================

type BlockCategory = 'element' | 'container'

interface ExtendedBlockTypeDefinition extends Omit<BlockTypeDefinition, 'icon'> {
  icon: React.ReactNode
  category: BlockCategory
  /** For containers: how many slots/columns it has */
  slots?: number
  /** For containers: slot labels */
  slotLabels?: string[]
}

// =============================================================================
// Built-in Block Registry
// =============================================================================

const BUILT_IN_BLOCKS: Record<string, ExtendedBlockTypeDefinition> = {
  // ===== ELEMENT BLOCKS =====
  logo: {
    type: 'logo',
    label: 'Logo',
    icon: <Image size={18} />,
    category: 'element',
    description: 'Site logo or brand image',
    defaultProps: { align: 'left', size: 'medium' },
    propsSchema: {
      align: {
        type: 'select',
        label: 'Alignment',
        default: 'left',
        options: [
          { value: 'left', label: 'Left' },
          { value: 'center', label: 'Center' },
          { value: 'right', label: 'Right' },
        ],
      },
      size: {
        type: 'select',
        label: 'Size',
        default: 'medium',
        options: [
          { value: 'small', label: 'Small' },
          { value: 'medium', label: 'Medium' },
          { value: 'large', label: 'Large' },
        ],
      },
    },
  },
  menu: {
    type: 'menu',
    label: 'Menu',
    icon: <Menu size={18} />,
    category: 'element',
    description: 'Navigation menu',
    defaultProps: { menu_id: '', style: 'horizontal' },
    propsSchema: {
      menu_id: {
        type: 'text',
        label: 'Menu ID',
        default: '',
      },
      style: {
        type: 'select',
        label: 'Style',
        default: 'horizontal',
        options: [
          { value: 'horizontal', label: 'Horizontal' },
          { value: 'vertical', label: 'Vertical' },
          { value: 'dropdown', label: 'Dropdown' },
        ],
      },
    },
  },
  button: {
    type: 'button',
    label: 'Button',
    icon: <MousePointerClick size={18} />,
    category: 'element',
    description: 'Call-to-action button',
    defaultProps: { text: 'Click Me', url: '', style: 'primary' },
    propsSchema: {
      text: {
        type: 'text',
        label: 'Button Text',
        default: 'Click Me',
      },
      url: {
        type: 'text',
        label: 'URL',
        default: '',
      },
      style: {
        type: 'select',
        label: 'Style',
        default: 'primary',
        options: [
          { value: 'primary', label: 'Primary' },
          { value: 'secondary', label: 'Secondary' },
          { value: 'outline', label: 'Outline' },
          { value: 'ghost', label: 'Ghost' },
        ],
      },
    },
  },
  search: {
    type: 'search',
    label: 'Search',
    icon: <Search size={18} />,
    category: 'element',
    description: 'Search form',
    defaultProps: { placeholder: 'Search...', style: 'default' },
    propsSchema: {
      placeholder: {
        type: 'text',
        label: 'Placeholder',
        default: 'Search...',
      },
      style: {
        type: 'select',
        label: 'Style',
        default: 'default',
        options: [
          { value: 'default', label: 'Default' },
          { value: 'minimal', label: 'Minimal' },
          { value: 'expanded', label: 'Expanded' },
        ],
      },
    },
  },
  spacer: {
    type: 'spacer',
    label: 'Spacer',
    icon: <Space size={18} />,
    category: 'element',
    description: 'Flexible space between blocks',
    defaultProps: { grow: true, width: '' },
    propsSchema: {
      grow: {
        type: 'toggle',
        label: 'Flexible',
        default: true,
      },
      width: {
        type: 'text',
        label: 'Fixed Width (px)',
        default: '',
      },
    },
  },
  divider: {
    type: 'divider',
    label: 'Divider',
    icon: <Minus size={18} />,
    category: 'element',
    description: 'Visual separator',
    defaultProps: { style: 'line' },
    propsSchema: {
      style: {
        type: 'select',
        label: 'Style',
        default: 'line',
        options: [
          { value: 'line', label: 'Line' },
          { value: 'dot', label: 'Dot' },
          { value: 'none', label: 'None (spacing only)' },
        ],
      },
    },
  },
  social: {
    type: 'social',
    label: 'Social Icons',
    icon: <Share2 size={18} />,
    category: 'element',
    description: 'Social media links',
    defaultProps: { style: 'default' },
    propsSchema: {
      style: {
        type: 'select',
        label: 'Style',
        default: 'default',
        options: [
          { value: 'default', label: 'Default' },
          { value: 'outlined', label: 'Outlined' },
          { value: 'filled', label: 'Filled' },
        ],
      },
    },
  },
  text: {
    type: 'text',
    label: 'Text',
    icon: <Type size={18} />,
    category: 'element',
    description: 'Custom text content',
    defaultProps: { content: 'Text content', tag: 'span' },
    propsSchema: {
      content: {
        type: 'text',
        label: 'Content',
        default: 'Text content',
      },
      tag: {
        type: 'select',
        label: 'HTML Tag',
        default: 'span',
        options: [
          { value: 'span', label: 'Span' },
          { value: 'p', label: 'Paragraph' },
          { value: 'h1', label: 'Heading 1' },
          { value: 'h2', label: 'Heading 2' },
          { value: 'h3', label: 'Heading 3' },
        ],
      },
    },
  },

  // ===== CONTAINER BLOCKS (Structure) =====
  section: {
    type: 'section',
    label: 'Section',
    icon: <Square size={18} />,
    category: 'container',
    description: 'Full width section with single column',
    slots: 1,
    slotLabels: ['Content'],
    defaultProps: { padding: 20 },
    propsSchema: {
      padding: {
        type: 'number',
        label: 'Padding (px)',
        default: 20,
      },
    },
  },
  columns_2: {
    type: 'columns_2',
    label: '2 Columns',
    icon: <Columns size={18} />,
    category: 'container',
    description: 'Two equal columns',
    slots: 2,
    slotLabels: ['Left', 'Right'],
    defaultProps: { gap: 20 },
    propsSchema: {
      gap: {
        type: 'number',
        label: 'Gap (px)',
        default: 20,
      },
    },
  },
  columns_3: {
    type: 'columns_3',
    label: '3 Columns',
    icon: <Columns size={18} />,
    category: 'container',
    description: 'Three equal columns',
    slots: 3,
    slotLabels: ['Left', 'Center', 'Right'],
    defaultProps: { gap: 20 },
    propsSchema: {
      gap: {
        type: 'number',
        label: 'Gap (px)',
        default: 20,
      },
    },
  },
  columns_4: {
    type: 'columns_4',
    label: '4 Columns',
    icon: <Columns size={18} />,
    category: 'container',
    description: 'Four equal columns',
    slots: 4,
    slotLabels: ['Col 1', 'Col 2', 'Col 3', 'Col 4'],
    defaultProps: { gap: 16 },
    propsSchema: {
      gap: {
        type: 'number',
        label: 'Gap (px)',
        default: 16,
      },
    },
  },
  row: {
    type: 'row',
    label: 'Row',
    icon: <Rows size={18} />,
    category: 'container',
    description: 'Horizontal flex row',
    slots: 1,
    slotLabels: ['Items'],
    defaultProps: { gap: 16, align: 'center', justify: 'start' },
    propsSchema: {
      gap: {
        type: 'number',
        label: 'Gap (px)',
        default: 16,
      },
      align: {
        type: 'select',
        label: 'Vertical Align',
        default: 'center',
        options: [
          { value: 'start', label: 'Top' },
          { value: 'center', label: 'Center' },
          { value: 'end', label: 'Bottom' },
          { value: 'stretch', label: 'Stretch' },
        ],
      },
      justify: {
        type: 'select',
        label: 'Horizontal Align',
        default: 'start',
        options: [
          { value: 'start', label: 'Start' },
          { value: 'center', label: 'Center' },
          { value: 'end', label: 'End' },
          { value: 'space-between', label: 'Space Between' },
        ],
      },
    },
  },
}

// =============================================================================
// Helper Functions
// =============================================================================

function generateBlockId(): string {
  return `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
}

function getDefaultValue(): VisualBuilderValue {
  return {
    blocks: [],
    layout: { direction: 'column', gap: 0, align: 'stretch' },
  }
}

function isContainerBlock(blockType: string): boolean {
  const def = BUILT_IN_BLOCKS[blockType]
  return def?.category === 'container'
}

/**
 * Convert propsSchema to FieldSchema array for FieldRenderer
 */
function propsSchemaToFieldSchemas(
  propsSchema: BlockTypeDefinition['propsSchema']
): FieldSchema[] {
  if (!propsSchema) return []
  
  return Object.entries(propsSchema).map(([key, schema]) => ({
    key,
    type: schema.type === 'toggle' ? 'boolean' : schema.type,
    label: schema.label,
    default: schema.default,
    options: schema.options,
  }))
}

// =============================================================================
// Extended Block Type with Children
// =============================================================================

interface BlockWithChildren extends VisualBuilderBlock {
  /** For container blocks: children in each slot */
  children?: BlockWithChildren[][]
}

// =============================================================================
// Drag & Drop Context
// =============================================================================

interface DragState {
  isDragging: boolean
  dragType: 'new' | 'reorder' | null
  dragData: string | null
  draggedBlockId: string | null
  dropTarget: {
    parentId: string | null
    slotIndex: number
    insertIndex: number
  } | null
}

// =============================================================================
// Block Palette Component
// =============================================================================

interface BlockPaletteProps {
  allowedBlocks: string[]
  onDragStart: (blockType: string) => void
  onDragEnd: () => void
  onAddBlock: (blockType: string) => void
}

function BlockPalette({ allowedBlocks, onDragStart, onDragEnd, onAddBlock }: BlockPaletteProps) {
  const availableBlocks = allowedBlocks.length > 0
    ? allowedBlocks.filter(type => BUILT_IN_BLOCKS[type])
    : Object.keys(BUILT_IN_BLOCKS)

  const containerBlocks = availableBlocks.filter(type => BUILT_IN_BLOCKS[type]?.category === 'container')
  const elementBlocks = availableBlocks.filter(type => BUILT_IN_BLOCKS[type]?.category === 'element')

  const renderBlockItem = (type: string) => {
    const blockDef = BUILT_IN_BLOCKS[type]
    if (!blockDef) return null

    return (
      <div
        key={type}
        className="os-vb-palette-item"
        draggable
        onDragStart={(e) => {
          e.dataTransfer.setData('application/x-block-type', type)
          e.dataTransfer.effectAllowed = 'copy'
          onDragStart(type)
        }}
        onDragEnd={onDragEnd}
        onClick={() => onAddBlock(type)}
        title={blockDef.description}
      >
        <div className="os-vb-palette-item-icon">
          {blockDef.icon}
        </div>
        <div className="os-vb-palette-item-label">{blockDef.label}</div>
      </div>
    )
  }

  return (
    <div className="os-vb-palette">
      <div className="os-vb-palette-header">
        <Layout size={16} />
        <span>Elements</span>
      </div>
      <div className="os-vb-palette-content">
        {/* Structure/Containers First */}
        <div className="os-vb-palette-section">
          <div className="os-vb-palette-section-title">Structure</div>
          <div className="os-vb-palette-grid">
            {containerBlocks.map(renderBlockItem)}
          </div>
        </div>
        
        {/* Elements */}
        <div className="os-vb-palette-section">
          <div className="os-vb-palette-section-title">Elements</div>
          <div className="os-vb-palette-grid">
            {elementBlocks.map(renderBlockItem)}
          </div>
        </div>
      </div>
    </div>
  )
}

// =============================================================================
// Drop Indicator Component
// =============================================================================

interface DropIndicatorProps {
  isActive: boolean
}

function DropIndicator({ isActive }: DropIndicatorProps) {
  return (
    <div className={`os-vb-drop-indicator ${isActive ? 'os-vb-drop-indicator-active' : ''}`}>
      <div className="os-vb-drop-indicator-line" />
    </div>
  )
}

// =============================================================================
// Container Slot Component
// =============================================================================

interface ContainerSlotProps {
  parentId: string
  parentType: string
  slotIndex: number
  slotLabel: string
  blocks: BlockWithChildren[]
  selectedBlockId: string | null
  dragState: DragState
  onSelectBlock: (id: string | null) => void
  onRemoveBlock: (id: string) => void
  onDragStart: (type: 'new' | 'reorder', data: string) => void
  onDragEnd: () => void
  onDragOver: (parentId: string | null, slotIndex: number, insertIndex: number) => void
  onDrop: () => void
}

function ContainerSlot({
  parentId,
  parentType,
  slotIndex,
  slotLabel,
  blocks,
  selectedBlockId,
  dragState,
  onSelectBlock,
  onRemoveBlock,
  onDragStart,
  onDragEnd,
  onDragOver,
  onDrop,
}: ContainerSlotProps) {
  const slotRef = useRef<HTMLDivElement>(null)
  const isRowContainer = parentType === 'row'

  const isDropTarget = (insertIndex: number) => {
    return dragState.dropTarget?.parentId === parentId &&
           dragState.dropTarget?.slotIndex === slotIndex &&
           dragState.dropTarget?.insertIndex === insertIndex
  }

  // Only accept elements (not containers) in slots
  const canAcceptDrop = () => {
    if (!dragState.isDragging || !dragState.dragData) return false
    if (dragState.dragType === 'new') {
      return !isContainerBlock(dragState.dragData)
    }
    return true
  }

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    e.stopPropagation()
    if (!canAcceptDrop()) return
    if (blocks.length === 0) {
      onDragOver(parentId, slotIndex, 0)
    }
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    e.stopPropagation()
    if (canAcceptDrop()) {
      onDrop()
    }
  }

  return (
    <div
      ref={slotRef}
      className={`os-vb-container-slot ${dragState.isDragging && canAcceptDrop() ? 'os-vb-container-slot-dragging' : ''} ${isRowContainer ? 'os-vb-container-slot-row' : ''}`}
      onDragOver={handleDragOver}
      onDrop={handleDrop}
    >
      <div className="os-vb-container-slot-label">{slotLabel}</div>
      <div className={`os-vb-container-slot-content ${isRowContainer ? 'os-vb-container-slot-content-row' : ''}`}>
        {blocks.length === 0 ? (
          <div className={`os-vb-container-slot-empty ${isDropTarget(0) ? 'os-vb-container-slot-empty-active' : ''}`}>
            <Plus size={14} />
            <span>Drop elements here</span>
          </div>
        ) : (
          <>
            {blocks.map((block, index) => (
              <div key={block.id} className="os-vb-slot-block-wrapper">
                <DropIndicator isActive={isDropTarget(index)} />
                <SlotBlock
                  block={block}
                  index={index}
                  parentId={parentId}
                  slotIndex={slotIndex}
                  isSelected={selectedBlockId === block.id}
                  isDragging={dragState.draggedBlockId === block.id}
                  onSelectBlock={onSelectBlock}
                  onRemoveBlock={onRemoveBlock}
                  onDragStart={onDragStart}
                  onDragEnd={onDragEnd}
                  onDragOver={onDragOver}
                />
              </div>
            ))}
            <DropIndicator isActive={isDropTarget(blocks.length)} />
          </>
        )}
      </div>
    </div>
  )
}

// =============================================================================
// Slot Block Component (Elements inside containers)
// =============================================================================

interface SlotBlockProps {
  block: BlockWithChildren
  index: number
  parentId: string
  slotIndex: number
  isSelected: boolean
  isDragging: boolean
  onSelectBlock: (id: string | null) => void
  onRemoveBlock: (id: string) => void
  onDragStart: (type: 'new' | 'reorder', data: string) => void
  onDragEnd: () => void
  onDragOver: (parentId: string | null, slotIndex: number, insertIndex: number) => void
}

function SlotBlock({
  block,
  index,
  parentId,
  slotIndex,
  isSelected,
  isDragging,
  onSelectBlock,
  onRemoveBlock,
  onDragStart,
  onDragEnd,
  onDragOver,
}: SlotBlockProps) {
  const blockDef = BUILT_IN_BLOCKS[block.type]
  const blockRef = useRef<HTMLDivElement>(null)

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    e.stopPropagation()
    
    if (!blockRef.current) return
    
    const rect = blockRef.current.getBoundingClientRect()
    const midPoint = rect.top + rect.height / 2
    
    if (e.clientY < midPoint) {
      onDragOver(parentId, slotIndex, index)
    } else {
      onDragOver(parentId, slotIndex, index + 1)
    }
  }

  return (
    <div
      ref={blockRef}
      className={`os-vb-slot-block ${isSelected ? 'os-vb-slot-block-selected' : ''} ${isDragging ? 'os-vb-slot-block-dragging' : ''}`}
      onClick={(e) => {
        e.stopPropagation()
        onSelectBlock(block.id)
      }}
      draggable
      onDragStart={(e) => {
        e.dataTransfer.setData('application/x-block-id', block.id)
        e.dataTransfer.effectAllowed = 'move'
        onDragStart('reorder', block.id)
      }}
      onDragEnd={onDragEnd}
      onDragOver={handleDragOver}
    >
      <div className="os-vb-slot-block-handle">
        <GripVertical size={12} />
      </div>
      <div className="os-vb-slot-block-icon">
        {blockDef?.icon || <Layout size={14} />}
      </div>
      <div className="os-vb-slot-block-label">
        {blockDef?.label || block.type}
      </div>
      <button
        type="button"
        className="os-vb-slot-block-remove"
        onClick={(e) => {
          e.stopPropagation()
          onRemoveBlock(block.id)
        }}
      >
        <Trash2 size={12} />
      </button>
    </div>
  )
}

// =============================================================================
// Container Block Component (Root level - full width)
// =============================================================================

interface ContainerBlockProps {
  block: BlockWithChildren
  index: number
  isSelected: boolean
  isDragging: boolean
  selectedBlockId: string | null
  dragState: DragState
  onSelectBlock: (id: string | null) => void
  onRemoveBlock: (id: string) => void
  onDragStart: (type: 'new' | 'reorder', data: string) => void
  onDragEnd: () => void
  onDragOver: (parentId: string | null, slotIndex: number, insertIndex: number) => void
  onDrop: () => void
}

function ContainerBlock({
  block,
  isSelected,
  selectedBlockId,
  dragState,
  onSelectBlock,
  onRemoveBlock,
  onDragStart,
  onDragEnd,
  onDragOver,
  onDrop,
}: ContainerBlockProps) {
  const blockDef = BUILT_IN_BLOCKS[block.type]
  const [isExpanded, setIsExpanded] = useState(true)
  const isRowType = block.type === 'row'

  const getGridColumns = () => {
    if (isRowType) return '1fr'
    return `repeat(${blockDef?.slots || 1}, 1fr)`
  }

  return (
    <div
      className={`os-vb-container-block ${isSelected ? 'os-vb-container-block-selected' : ''}`}
      onClick={(e) => {
        e.stopPropagation()
        onSelectBlock(block.id)
      }}
    >
      <div className="os-vb-container-block-header">
        <div className="os-vb-container-block-icon">
          {blockDef?.icon || <Layout size={16} />}
        </div>
        <div className="os-vb-container-block-title">
          {blockDef?.label || block.type}
        </div>
        <button
          type="button"
          className="os-vb-container-block-toggle"
          onClick={(e) => {
            e.stopPropagation()
            setIsExpanded(!isExpanded)
          }}
        >
          {isExpanded ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
        </button>
        <button
          type="button"
          className="os-vb-container-block-remove"
          onClick={(e) => {
            e.stopPropagation()
            onRemoveBlock(block.id)
          }}
        >
          <Trash2 size={14} />
        </button>
      </div>

      {isExpanded && blockDef?.slots && (
        <div
          className="os-vb-container-block-slots"
          style={{
            gridTemplateColumns: getGridColumns(),
            gap: `${(block.props.gap as number) || 16}px`,
          }}
        >
          {Array.from({ length: blockDef.slots }).map((_, i) => (
            <ContainerSlot
              key={i}
              parentId={block.id}
              parentType={block.type}
              slotIndex={i}
              slotLabel={blockDef.slotLabels?.[i] || `Slot ${i + 1}`}
              blocks={block.children?.[i] || []}
              selectedBlockId={selectedBlockId}
              dragState={dragState}
              onSelectBlock={onSelectBlock}
              onRemoveBlock={onRemoveBlock}
              onDragStart={onDragStart}
              onDragEnd={onDragEnd}
              onDragOver={onDragOver}
              onDrop={onDrop}
            />
          ))}
        </div>
      )}
    </div>
  )
}

// =============================================================================
// Canvas Component
// =============================================================================

interface CanvasProps {
  blocks: BlockWithChildren[]
  selectedBlockId: string | null
  dragState: DragState
  onSelectBlock: (id: string | null) => void
  onRemoveBlock: (id: string) => void
  onDragStart: (type: 'new' | 'reorder', data: string) => void
  onDragEnd: () => void
  onDragOver: (parentId: string | null, slotIndex: number, insertIndex: number) => void
  onDrop: () => void
}

function Canvas({
  blocks,
  selectedBlockId,
  dragState,
  onSelectBlock,
  onRemoveBlock,
  onDragStart,
  onDragEnd,
  onDragOver,
  onDrop,
}: CanvasProps) {
  const canvasRef = useRef<HTMLDivElement>(null)

  const isDropTarget = (insertIndex: number) => {
    return dragState.dropTarget?.parentId === null &&
           dragState.dropTarget?.slotIndex === 0 &&
           dragState.dropTarget?.insertIndex === insertIndex
  }

  // Only accept containers at root level
  const canAcceptDrop = () => {
    if (!dragState.isDragging || !dragState.dragData) return false
    if (dragState.dragType === 'new') {
      return isContainerBlock(dragState.dragData)
    }
    return false // Can't reorder containers via drag for now
  }

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    if (!canAcceptDrop()) return
    e.dataTransfer.dropEffect = 'copy'
    
    if (blocks.length === 0) {
      onDragOver(null, 0, 0)
    } else {
      onDragOver(null, 0, blocks.length)
    }
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    if (canAcceptDrop()) {
      onDrop()
    }
  }

  return (
    <div className="os-vb-canvas-wrapper">
      <div className="os-vb-canvas-header">
        <span>Canvas</span>
        <span className="os-vb-canvas-count">{blocks.length} containers</span>
      </div>
      <div
        ref={canvasRef}
        className={`os-vb-canvas ${blocks.length === 0 ? 'os-vb-canvas-empty' : ''} ${dragState.isDragging && canAcceptDrop() ? 'os-vb-canvas-dragging' : ''}`}
        onClick={() => onSelectBlock(null)}
        onDragOver={handleDragOver}
        onDrop={handleDrop}
      >
        {blocks.length === 0 ? (
          <div className={`os-vb-canvas-placeholder ${dragState.isDragging && canAcceptDrop() && isDropTarget(0) ? 'os-vb-canvas-placeholder-active' : ''}`}>
            <Columns size={32} />
            <span>Drag a structure element here to start</span>
            <span className="os-vb-canvas-hint">Add a Section, Columns, or Row first</span>
          </div>
        ) : (
          <>
            {blocks.map((block, index) => (
              <div key={block.id} className="os-vb-canvas-block-wrapper">
                <DropIndicator isActive={isDropTarget(index)} />
                <ContainerBlock
                  block={block}
                  index={index}
                  isSelected={selectedBlockId === block.id}
                  isDragging={dragState.draggedBlockId === block.id}
                  selectedBlockId={selectedBlockId}
                  dragState={dragState}
                  onSelectBlock={onSelectBlock}
                  onRemoveBlock={onRemoveBlock}
                  onDragStart={onDragStart}
                  onDragEnd={onDragEnd}
                  onDragOver={onDragOver}
                  onDrop={onDrop}
                />
              </div>
            ))}
            <DropIndicator isActive={isDropTarget(blocks.length)} />
          </>
        )}
      </div>
    </div>
  )
}

// =============================================================================
// Block Inspector Component
// =============================================================================

interface BlockInspectorProps {
  block: BlockWithChildren | null
  onUpdateProps: (props: Record<string, unknown>) => void
  onClose: () => void
}

function BlockInspector({ block, onUpdateProps, onClose }: BlockInspectorProps) {
  if (!block) {
    return (
      <div className="os-vb-inspector os-vb-inspector-empty">
        <div className="os-vb-inspector-header">
          <Settings size={16} />
          <span>Settings</span>
        </div>
        <div className="os-vb-inspector-placeholder">
          <ChevronRight size={24} />
          <p>Select an element to edit its properties</p>
        </div>
      </div>
    )
  }

  const blockDef = BUILT_IN_BLOCKS[block.type]
  const fieldSchemas = propsSchemaToFieldSchemas(blockDef?.propsSchema)

  const handlePropChange = (key: string, value: unknown) => {
    onUpdateProps({ ...block.props, [key]: value })
  }

  return (
    <div className="os-vb-inspector">
      <div className="os-vb-inspector-header">
        <div className="os-vb-inspector-header-title">
          {blockDef?.icon}
          <span>{blockDef?.label || block.type}</span>
        </div>
        <button type="button" className="os-vb-inspector-close" onClick={onClose}>
          <X size={16} />
        </button>
      </div>

      <div className="os-vb-inspector-body">
        {fieldSchemas.length === 0 ? (
          <div className="os-vb-inspector-no-props">
            This element has no configurable properties
          </div>
        ) : (
          <div className="os-vb-inspector-fields">
            {fieldSchemas.map((fieldSchema) => (
              <div key={fieldSchema.key} className="os-vb-inspector-field">
                <FieldRenderer
                  field={fieldSchema}
                  value={block.props[fieldSchema.key] ?? fieldSchema.default}
                  onChange={(value) => handlePropChange(fieldSchema.key, value)}
                />
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

// =============================================================================
// Builder Modal
// =============================================================================

interface BuilderModalProps {
  isOpen: boolean
  onClose: () => void
  title: string
  children: React.ReactNode
}

function BuilderModal({ isOpen, onClose, title, children }: BuilderModalProps) {
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
    return () => {
      document.body.style.overflow = ''
    }
  }, [isOpen])

  useEffect(() => {
    if (!isOpen) return
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose()
    }
    document.addEventListener('keydown', handleKeyDown)
    return () => document.removeEventListener('keydown', handleKeyDown)
  }, [isOpen, onClose])

  if (!isOpen) return null

  const modalElement = (
    <div className="os-vb-modal-overlay" onClick={onClose}>
      <div className="os-vb-modal" onClick={(e) => e.stopPropagation()}>
        <div className="os-vb-modal-header">
          <div className="os-vb-modal-title">
            <Layout size={18} />
            <span>{title}</span>
          </div>
          <button type="button" className="os-vb-modal-close" onClick={onClose}>
            <X size={18} />
            <span>Close</span>
          </button>
        </div>
        <div className="os-vb-modal-body">
          {children}
        </div>
      </div>
    </div>
  )

  return ReactDOM.createPortal(modalElement, document.body)
}

// =============================================================================
// Main Component
// =============================================================================

export function VisualBuilderField({ field, value, onChange, disabled }: FieldRendererProps) {
  const currentValue = (value as VisualBuilderValue) || getDefaultValue()
  const [selectedBlockId, setSelectedBlockId] = useState<string | null>(null)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [dragState, setDragState] = useState<DragState>({
    isDragging: false,
    dragType: null,
    dragData: null,
    draggedBlockId: null,
    dropTarget: null,
  })

  const allowedBlocks = (field.attributes?.blocks as string[]) || []

  // Cast blocks to include children
  const blocks = currentValue.blocks as BlockWithChildren[]

  const updateValue = useCallback((newBlocks: BlockWithChildren[]) => {
    onChange({ ...currentValue, blocks: newBlocks })
  }, [currentValue, onChange])

  // Find a block by ID (including nested blocks)
  const findBlockById = useCallback((
    blockList: BlockWithChildren[],
    id: string
  ): BlockWithChildren | null => {
    for (const block of blockList) {
      if (block.id === id) return block
      if (block.children) {
        for (const slot of block.children) {
          const found = findBlockById(slot, id)
          if (found) return found
        }
      }
    }
    return null
  }, [])

  // Add a new block
  const handleAddBlock = useCallback((
    blockType: string,
    parentId: string | null,
    slotIndex: number,
    insertIndex: number
  ) => {
    const blockDef = BUILT_IN_BLOCKS[blockType]
    if (!blockDef) return

    const newBlock: BlockWithChildren = {
      id: generateBlockId(),
      type: blockType,
      props: { ...blockDef.defaultProps },
      children: blockDef.category === 'container' && blockDef.slots
        ? Array.from({ length: blockDef.slots }, () => [])
        : undefined,
    }

    if (parentId === null) {
      // Add to root (containers only)
      const newBlocks = [...blocks]
      newBlocks.splice(insertIndex, 0, newBlock)
      updateValue(newBlocks)
    } else {
      // Add to container slot (elements only)
      const newBlocks = JSON.parse(JSON.stringify(blocks)) as BlockWithChildren[]
      const parent = findBlockById(newBlocks, parentId)
      if (parent && parent.children) {
        if (!parent.children[slotIndex]) {
          parent.children[slotIndex] = []
        }
        parent.children[slotIndex].splice(insertIndex, 0, newBlock)
        updateValue(newBlocks)
      }
    }

    setSelectedBlockId(newBlock.id)
  }, [blocks, updateValue, findBlockById])

  // Remove a block
  const handleRemoveBlock = useCallback((id: string) => {
    const removeFromList = (blockList: BlockWithChildren[]): BlockWithChildren[] => {
      return blockList.filter(block => {
        if (block.id === id) return false
        if (block.children) {
          block.children = block.children.map(slot => removeFromList(slot))
        }
        return true
      })
    }

    updateValue(removeFromList(blocks))
    if (selectedBlockId === id) {
      setSelectedBlockId(null)
    }
  }, [blocks, updateValue, selectedBlockId])

  // Move a block
  const handleMoveBlock = useCallback((
    blockId: string,
    toParentId: string | null,
    toSlotIndex: number,
    toInsertIndex: number
  ) => {
    let movedBlock: BlockWithChildren | null = null

    const removeBlock = (blockList: BlockWithChildren[]): BlockWithChildren[] => {
      return blockList.filter(block => {
        if (block.id === blockId) {
          movedBlock = { ...block }
          return false
        }
        if (block.children) {
          block.children = block.children.map(slot => removeBlock(slot))
        }
        return true
      })
    }

    const newBlocks = JSON.parse(JSON.stringify(blocks)) as BlockWithChildren[]
    const blocksAfterRemove = removeBlock(newBlocks)

    if (!movedBlock) return

    // Only allow moving elements to container slots
    if (toParentId === null) {
      // Can't move elements to root
      return
    }

    const parent = findBlockById(blocksAfterRemove, toParentId)
    if (parent && parent.children) {
      if (!parent.children[toSlotIndex]) {
        parent.children[toSlotIndex] = []
      }
      parent.children[toSlotIndex].splice(toInsertIndex, 0, movedBlock)
      updateValue(blocksAfterRemove)
    }
  }, [blocks, updateValue, findBlockById])

  // Update block props
  const handleUpdateBlockProps = useCallback((props: Record<string, unknown>) => {
    if (!selectedBlockId) return

    const updateProps = (blockList: BlockWithChildren[]): BlockWithChildren[] => {
      return blockList.map(block => {
        if (block.id === selectedBlockId) {
          return { ...block, props }
        }
        if (block.children) {
          return { ...block, children: block.children.map(slot => updateProps(slot)) }
        }
        return block
      })
    }

    updateValue(updateProps(blocks))
  }, [blocks, updateValue, selectedBlockId])

  // Drag state handlers
  const handleDragStart = useCallback((type: 'new' | 'reorder', data: string) => {
    setDragState({
      isDragging: true,
      dragType: type,
      dragData: data,
      draggedBlockId: type === 'reorder' ? data : null,
      dropTarget: null,
    })
  }, [])

  const handleDragEnd = useCallback(() => {
    setDragState({
      isDragging: false,
      dragType: null,
      dragData: null,
      draggedBlockId: null,
      dropTarget: null,
    })
  }, [])

  const handleDragOver = useCallback((parentId: string | null, slotIndex: number, insertIndex: number) => {
    setDragState(prev => ({
      ...prev,
      dropTarget: { parentId, slotIndex, insertIndex },
    }))
  }, [])

  const handleDrop = useCallback(() => {
    if (!dragState.dropTarget || !dragState.dragData) {
      handleDragEnd()
      return
    }

    const { parentId, slotIndex, insertIndex } = dragState.dropTarget

    if (dragState.dragType === 'new') {
      handleAddBlock(dragState.dragData, parentId, slotIndex, insertIndex)
    } else if (dragState.dragType === 'reorder') {
      handleMoveBlock(dragState.dragData, parentId, slotIndex, insertIndex)
    }

    handleDragEnd()
  }, [dragState, handleAddBlock, handleMoveBlock, handleDragEnd])

  // Get selected block
  const selectedBlock = selectedBlockId ? findBlockById(blocks, selectedBlockId) : null

  const renderTrigger = () => (
    <div className="os-vb-trigger">
      <button
        type="button"
        className="os-vb-trigger-btn"
        onClick={() => setIsModalOpen(true)}
        disabled={disabled}
      >
        <Layout size={18} />
        <span>Open Visual Builder</span>
        <span className="os-vb-trigger-count">
          {blocks.length} sections
        </span>
      </button>
    </div>
  )

  const renderBuilder = () => (
    <div className="os-vb-builder">
      <div className="os-vb-sidebar os-vb-sidebar-left">
        <BlockPalette
          allowedBlocks={allowedBlocks}
          onDragStart={(type) => handleDragStart('new', type)}
          onDragEnd={handleDragEnd}
          onAddBlock={(type) => {
            if (isContainerBlock(type)) {
              handleAddBlock(type, null, 0, blocks.length)
            }
          }}
        />
      </div>

      <div className="os-vb-main">
        <Canvas
          blocks={blocks}
          selectedBlockId={selectedBlockId}
          dragState={dragState}
          onSelectBlock={setSelectedBlockId}
          onRemoveBlock={handleRemoveBlock}
          onDragStart={handleDragStart}
          onDragEnd={handleDragEnd}
          onDragOver={handleDragOver}
          onDrop={handleDrop}
        />
      </div>

      <div className="os-vb-sidebar os-vb-sidebar-right">
        <BlockInspector
          block={selectedBlock}
          onUpdateProps={handleUpdateBlockProps}
          onClose={() => setSelectedBlockId(null)}
        />
      </div>
    </div>
  )

  return (
    <div className={`os-vb-field ${disabled ? 'os-vb-field-disabled' : ''}`}>
      {renderTrigger()}
      <BuilderModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={field.label || 'Visual Builder'}
      >
        {renderBuilder()}
      </BuilderModal>
    </div>
  )
}
