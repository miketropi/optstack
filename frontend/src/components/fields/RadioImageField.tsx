import type { FieldRendererProps, FieldOption } from '../../schema/types'

/**
 * Extended option type for radio image field.
 * The label contains the image URL, and optionally a tooltip/alt text.
 */
interface RadioImageOption extends FieldOption {
  /** Image URL (can be in label or image property) */
  image?: string
  /** Tooltip text shown on hover */
  tooltip?: string
}

/**
 * RadioImageField - Image-based radio selection
 * 
 * Behaves like a Radio field but displays image thumbnails instead of text labels.
 * Options should have:
 * - value: The actual value to store
 * - label: The image URL (for backwards compatibility)
 * - image: Alternative property for image URL (takes precedence over label)
 * - tooltip: Optional hover text
 * 
 * Attributes:
 * - columns: Number of columns in the grid (default: auto-fit)
 * - imageWidth: Width of each image (default: 100px)
 * - imageHeight: Height of each image (default: 80px)
 * - showTooltip: Whether to show tooltips (default: true)
 * - objectFit: CSS object-fit value (default: 'cover')
 */
export function RadioImageField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const options = (field.options || []) as RadioImageOption[]
  const currentValue = value ?? field.default
  
  // Get attributes with defaults
  const columns = field.attributes?.columns as number | undefined
  const imageWidth = (field.attributes?.imageWidth as string) || '100px'
  const imageHeight = (field.attributes?.imageHeight as string) || '80px'
  const showTooltip = field.attributes?.showTooltip !== false
  const objectFit = (field.attributes?.objectFit as string) || 'cover'

  // Build grid style
  const gridStyle: React.CSSProperties = columns 
    ? { gridTemplateColumns: `repeat(${columns}, 1fr)` }
    : { gridTemplateColumns: `repeat(auto-fill, minmax(${imageWidth}, 1fr))` }

  return (
    <div className={`os-field os-field-radio-image ${error ? 'os-field-error' : ''}`}>
      <legend className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </legend>
      
      <div className="os-field-body">
        <div className="os-radio-image-group" style={gridStyle}>
          {options.map((option) => {
            const isSelected = currentValue === option.value
            // Use 'image' property if available, otherwise fall back to 'label'
            const imageUrl = option.image || option.label
            const tooltipText = option.tooltip || (option.image ? option.label : String(option.value))
            
            return (
              <label 
                key={String(option.value)} 
                className={`os-radio-image-option ${isSelected ? 'os-selected' : ''} ${disabled ? 'os-disabled' : ''}`}
                title={showTooltip ? tooltipText : undefined}
              >
                <input
                  type="radio"
                  name={field.key}
                  value={String(option.value)}
                  checked={isSelected}
                  onChange={() => !disabled && onChange(option.value)}
                  disabled={disabled}
                  className="os-radio-image-input"
                />
                
                <span 
                  className="os-radio-image-container"
                  style={{ 
                    width: imageWidth, 
                    height: imageHeight 
                  }}
                >
                  <img 
                    src={imageUrl} 
                    alt={tooltipText}
                    className="os-radio-image-img"
                    style={{ objectFit: objectFit as React.CSSProperties['objectFit'] }}
                  />
                  
                  {/* Selection indicator */}
                  <span className="os-radio-image-check">
                    <svg 
                      xmlns="http://www.w3.org/2000/svg" 
                      viewBox="0 0 24 24" 
                      fill="currentColor"
                      width="16"
                      height="16"
                    >
                      <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                    </svg>
                  </span>
                </span>
                
                {/* Optional description below the image */}
                {option.description && (
                  <span className="os-radio-image-description">{option.description}</span>
                )}
              </label>
            )
          })}
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
