import type { FieldSchema, FieldRendererProps } from '../schema/types'
import { TextField } from './fields/TextField'
import { NumberField } from './fields/NumberField'
import { SelectField } from './fields/SelectField'
import { ToggleField } from './fields/ToggleField'
import { TextareaField } from './fields/TextareaField'
import { ColorField } from './fields/ColorField'
import { RadioField } from './fields/RadioField'
import { CheckboxGroupField } from './fields/CheckboxGroupField'
import { DateField } from './fields/DateField'
import { RangeField } from './fields/RangeField'
import { MediaField } from './fields/MediaField'
import { WysiwygField } from './fields/WysiwygField'
import { CodeField } from './fields/CodeField'
import { TypographyField } from './fields/TypographyField'

/**
 * Map of field types to components.
 */
const fieldComponents: Record<string, React.ComponentType<FieldRendererProps>> = {
  // Text types
  text: TextField,
  string: TextField,
  email: TextField,
  url: TextField,
  password: TextField,
  tel: TextField,
  
  // Number types
  number: NumberField,
  integer: NumberField,
  float: NumberField,
  
  // Selection types
  select: SelectField,
  dropdown: SelectField,
  radio: RadioField,
  'checkbox-group': CheckboxGroupField,
  checkboxes: CheckboxGroupField,
  
  // Boolean types
  boolean: ToggleField,
  toggle: ToggleField,
  checkbox: ToggleField,
  switch: ToggleField,
  
  // Text area types
  textarea: TextareaField,
  wysiwyg: WysiwygField,
  editor: WysiwygField,
  richtext: WysiwygField,
  
  // Specialized types
  color: ColorField,
  'color-picker': ColorField,
  date: DateField,
  datetime: DateField,
  range: RangeField,
  slider: RangeField,
  
  // Media types
  media: MediaField,
  image: MediaField,
  file: MediaField,
  
  // Code types
  code: CodeField,
  'code-editor': CodeField,
  
  // Composite types
  typography: TypographyField,
}

interface Props {
  field: FieldSchema
  value: unknown
  onChange: (value: unknown) => void
  disabled?: boolean
  error?: string
}

export function FieldRenderer({ field, value, onChange, disabled, error }: Props) {
  const Component = fieldComponents[field.type] || TextField

  return (
    <div className="os-field-wrapper">
      <Component
        field={field}
        value={value}
        onChange={onChange}
        disabled={disabled}
        error={error}
      />
    </div>
  )
}

/**
 * Register a custom field component.
 */
export function registerFieldComponent(
  type: string,
  component: React.ComponentType<FieldRendererProps>
) {
  fieldComponents[type] = component
}
