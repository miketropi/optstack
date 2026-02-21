# OptStack Field Types Reference

This directory contains documentation for all OptStack field types.

## Quick Reference

| Field Type | Description | Use Case |
|------------|-------------|----------|
| [text](./text.md) | Single-line text input | Titles, names, short strings |
| [textarea](./textarea.md) | Multi-line text input | Descriptions, longer text |
| [number](./number.md) | Numeric input | Quantities, dimensions, prices |
| [email](./email.md) | Email input | Contact emails |
| [url](./url.md) | URL input | Links, social profiles |
| [select](./select.md) | Dropdown menu | Single selection from list |
| [select-wp-query](./select-wordpress-query.md) | Async dropdown | Search posts, terms, users (WordPress data) |
| [radio](./radio.md) | Radio buttons | Visible single selection |
| [radio-image](./radio-image.md) | Visual radio selection | Layout choices with previews |
| [checkbox-group](./checkbox-group.md) | Multiple checkboxes | Multiple selections |
| [toggle](./toggle.md) | On/off switch | Boolean settings |
| [color](./color.md) | Color picker | Theme colors, backgrounds |
| [media](./media.md) | Media library picker | Images, files |
| [range](./range.md) | Slider input | Visual numeric selection |
| [wysiwyg](./wysiwyg.md) | Rich text editor | Formatted content |
| [code](./code.md) | Code editor | CSS, JS, HTML snippets |
| [typography](./typography.md) | Typography controls | Font settings |
| [visual_builder](./visual-builder.md) | Drag-and-drop builder | Page layouts |

## Field Categories

### Input Fields
Basic text and numeric inputs:
- [text](./text.md) - Single-line text
- [textarea](./textarea.md) - Multi-line text
- [number](./number.md) - Numeric values
- [email](./email.md) - Email addresses
- [url](./url.md) - URLs

### Selection Fields
Choosing from predefined options:
- [select](./select.md) - Dropdown select
- [radio](./radio.md) - Radio buttons
- [radio-image](./radio-image.md) - Visual radio with images
- [checkbox-group](./checkbox-group.md) - Multiple checkboxes
- [toggle](./toggle.md) - Boolean switch

### Visual Fields
Interactive visual inputs:
- [color](./color.md) - Color picker
- [media](./media.md) - Media library
- [range](./range.md) - Slider

### Rich Content Fields
Complex content inputs:
- [wysiwyg](./wysiwyg.md) - Visual editor
- [code](./code.md) - Code editor
- [typography](./typography.md) - Typography settings

### Advanced Fields
Complex functionality:
- [visual_builder](./visual-builder.md) - Page builder

## Common Properties

All fields share these properties:

```php
$stack->field('field_id', [
    'type' => 'text',           // Required: field type
    'label' => 'Label',         // Field label
    'description' => 'Help',    // Help text
    'default' => 'value',       // Default value
    'attributes' => [],         // Type-specific attributes
    'conditions' => [],         // Conditional display
]);
```

## Conditional Logic

Show/hide fields based on other field values:

```php
'conditions' => [
    ['field' => 'other_field', 'operator' => '==', 'value' => true],
]
```

### Operators
- `==` - Equal to
- `!=` - Not equal to
- `>` / `<` - Greater/less than
- `>=` / `<=` - Greater/less than or equal
- `contains` / `not_contains` - Array contains

## Related Documentation

- [USAGE-FIELD.md](../USAGE-FIELD.md) - Complete field usage guide
- [EXAM2_README.md](../../examples/EXAM2_README.md) - Theme options example
- [basic-usage.php](../../examples/basic-usage.php) - Code examples
