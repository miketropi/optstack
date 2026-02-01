# OptStack Containers Guide: Groups, Tabs & Modals

This document provides a comprehensive guide on how to use Groups, Tabs, and Modals (Deferred Groups) in OptStack to organize your options and fields effectively.

---

## Table of Contents

- [Overview](#overview)
- [Groups](#groups)
  - [Basic Groups](#basic-groups)
  - [Group Configuration](#group-configuration)
  - [Nested Groups](#nested-groups)
  - [Repeatable Groups](#repeatable-groups)
  - [Collapsible Groups](#collapsible-groups)
  - [Conditional Groups](#conditional-groups)
- [Tabs](#tabs)
  - [Basic Tabs](#basic-tabs)
  - [Tab Configuration](#tab-configuration)
  - [Groups Inside Tabs](#groups-inside-tabs)
  - [Tab Priority & Ordering](#tab-priority--ordering)
- [Modals (Deferred Groups)](#modals-deferred-groups)
  - [What is a Deferred Group?](#what-is-a-deferred-group)
  - [Basic Modal Usage](#basic-modal-usage)
  - [Modal Configuration](#modal-configuration)
  - [When to Use Modals](#when-to-use-modals)
- [Combining Containers](#combining-containers)
- [Data Structure](#data-structure)
- [Retrieving Data](#retrieving-data)
- [Best Practices](#best-practices)

---

## Overview

OptStack provides three types of containers to organize fields:

| Container | Purpose | Use Case |
|-----------|---------|----------|
| **Group** | Logical grouping of related fields | Address fields, pricing info, SEO settings |
| **Tab** | Separate sections in a tabbed interface | General, Colors, Typography, Layout tabs |
| **Modal** | Deferred group shown in a modal/drawer | Advanced settings, rarely-used configurations |

---

## Groups

Groups organize related fields together, creating a logical structure for both the UI and the stored data.

### Basic Groups

```php
$stack->group('address', function ($group) {
    $group->field('street', ['type' => 'text', 'label' => 'Street Address']);
    $group->field('city', ['type' => 'text', 'label' => 'City']);
    $group->field('state', ['type' => 'text', 'label' => 'State']);
    $group->field('zip', ['type' => 'text', 'label' => 'ZIP Code']);
}, ['label' => 'Address']);
```

**Data Structure:**
```php
[
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'zip' => '10001',
    ]
]
```

### Group Configuration

Groups support the following configuration options:

```php
$stack->group('group_key', function ($group) {
    // Define fields here
}, [
    // Display options
    'label' => 'Group Label',           // Display label
    'description' => 'Help text',       // Description shown below label
    
    // Layout options
    'layout' => 'box',                  // 'inline' (default) or 'box'
    'collapsible' => true,              // Allow collapsing the group
    
    // Repeatable options
    'repeatable' => true,               // Allow multiple instances
    'min_items' => 1,                   // Minimum items (repeatable)
    'max_items' => 10,                  // Maximum items (repeatable)
    
    // Modal/Deferred options
    'deferred' => true,                 // Show in modal instead of inline
    'ui' => [
        'triggerLabel' => 'Configure',  // Button label
        'render' => 'modal',            // 'modal', 'drawer', or 'panel'
    ],
    
    // Conditional visibility
    'conditions' => [
        ['field' => 'enable_feature', 'operator' => '==', 'value' => true],
    ],
]);
```

### Nested Groups

Groups can be nested inside other groups:

```php
$stack->group('company', function ($group) {
    $group->field('name', ['type' => 'text', 'label' => 'Company Name']);
    $group->field('email', ['type' => 'email', 'label' => 'Email']);
    
    // Nested group for address
    $group->group('address', function ($nested) {
        $nested->field('street', ['type' => 'text', 'label' => 'Street']);
        $nested->field('city', ['type' => 'text', 'label' => 'City']);
        $nested->field('country', ['type' => 'select', 'label' => 'Country', 'options' => [...]]);
    }, ['label' => 'Company Address']);
    
}, ['label' => 'Company Information']);
```

**Data Structure:**
```php
[
    'company' => [
        'name' => 'Acme Inc',
        'email' => 'contact@acme.com',
        'address' => [
            'street' => '123 Business Ave',
            'city' => 'San Francisco',
            'country' => 'US',
        ]
    ]
]
```

### Repeatable Groups

Repeatable groups allow users to add multiple instances of a set of fields:

```php
$stack->group('team_members', function ($group) {
    $group->field('name', ['type' => 'text', 'label' => 'Name']);
    $group->field('role', ['type' => 'text', 'label' => 'Role']);
    $group->field('photo', ['type' => 'media', 'label' => 'Photo']);
    $group->field('bio', ['type' => 'textarea', 'label' => 'Bio']);
}, [
    'label' => 'Team Members',
    'repeatable' => true,
    'min_items' => 1,
    'max_items' => 20,
]);
```

**Data Structure (Array of items):**
```php
[
    'team_members' => [
        [
            'name' => 'John Doe',
            'role' => 'CEO',
            'photo' => 123,
            'bio' => 'John is the founder...',
        ],
        [
            'name' => 'Jane Smith',
            'role' => 'CTO',
            'photo' => 124,
            'bio' => 'Jane leads our tech team...',
        ],
    ]
]
```

### Collapsible Groups

Collapsible groups can be expanded/collapsed to reduce UI clutter:

```php
$stack->group('advanced_settings', function ($group) {
    $group->field('cache_duration', ['type' => 'number', 'label' => 'Cache Duration']);
    $group->field('debug_mode', ['type' => 'toggle', 'label' => 'Debug Mode']);
    $group->field('custom_code', ['type' => 'code', 'label' => 'Custom Code']);
}, [
    'label' => 'Advanced Settings',
    'collapsible' => true,
    'layout' => 'box',
]);
```

### Conditional Groups

Groups can be shown/hidden based on other field values:

```php
// Enable toggle
$stack->field('enable_shipping', [
    'type' => 'toggle',
    'label' => 'Enable Shipping',
    'default' => false,
]);

// Conditional group - only shows when shipping is enabled
$stack->group('shipping', function ($group) {
    $group->field('method', [
        'type' => 'select',
        'label' => 'Shipping Method',
        'options' => [
            ['value' => 'flat', 'label' => 'Flat Rate'],
            ['value' => 'free', 'label' => 'Free Shipping'],
            ['value' => 'calculated', 'label' => 'Calculated'],
        ],
    ]);
    $group->field('cost', ['type' => 'number', 'label' => 'Shipping Cost']);
}, [
    'label' => 'Shipping Options',
    'conditions' => [
        ['field' => 'enable_shipping', 'operator' => '==', 'value' => true],
    ],
]);
```

---

## Tabs

Tabs organize your options page into separate sections, making it easier to navigate complex settings.

### Basic Tabs

```php
OptStack::make('theme_options')
    ->forOptions()
    ->label('Theme Options')
    ->define(function ($stack) {
        
        // General Tab
        $stack->tab('general', function ($tab) {
            $tab->field('site_name', ['type' => 'text', 'label' => 'Site Name']);
            $tab->field('tagline', ['type' => 'text', 'label' => 'Tagline']);
        });
        
        // Colors Tab
        $stack->tab('colors', function ($tab) {
            $tab->field('primary_color', ['type' => 'color', 'label' => 'Primary']);
            $tab->field('secondary_color', ['type' => 'color', 'label' => 'Secondary']);
        });
        
        // Typography Tab
        $stack->tab('typography', function ($tab) {
            $tab->field('body_font', ['type' => 'typography', 'label' => 'Body Font']);
            $tab->field('heading_font', ['type' => 'typography', 'label' => 'Heading Font']);
        });
        
    })
    ->build();
```

### Tab Configuration

```php
$stack->tab('tab_key', function ($tab) {
    // Configure tab properties using fluent methods
    $tab->label('Tab Label')
        ->icon('dashicons-admin-settings')  // Dashicon or custom icon
        ->description('Description text')
        ->priority(10);                     // Lower = appears first
    
    // Add fields
    $tab->field('field_1', [...]);
    $tab->field('field_2', [...]);
});
```

### Groups Inside Tabs

You can add groups inside tabs to further organize fields:

```php
$stack->tab('general', function ($tab) {
    $tab->label('General')
        ->priority(10);
    
    // Direct fields in tab
    $tab->field('site_name', ['type' => 'text', 'label' => 'Site Name']);
    
    // Group inside tab
    $tab->group('identity', function ($group) {
        $group->field('logo', ['type' => 'media', 'label' => 'Logo']);
        $group->field('favicon', ['type' => 'media', 'label' => 'Favicon']);
    }, ['label' => 'Site Identity']);
    
    // Another group
    $tab->group('social_links', function ($group) {
        $group->field('facebook', ['type' => 'url', 'label' => 'Facebook']);
        $group->field('twitter', ['type' => 'url', 'label' => 'Twitter']);
        $group->field('instagram', ['type' => 'url', 'label' => 'Instagram']);
    }, ['label' => 'Social Links']);
});
```

**Data Structure (Groups inside tabs are stored at root level):**
```php
[
    'site_name' => 'My Site',
    'identity' => [
        'logo' => 123,
        'favicon' => 124,
    ],
    'social_links' => [
        'facebook' => 'https://facebook.com/...',
        'twitter' => 'https://twitter.com/...',
        'instagram' => 'https://instagram.com/...',
    ]
]
```

> **Note:** Tabs are for UI organization only. Fields and groups inside tabs are stored at the root level, not nested under the tab key.

### Tab Priority & Ordering

Control tab order using the `priority` setting:

```php
$stack->tab('general', function ($tab) {
    $tab->label('General')->priority(10);  // Appears first
    // ...
});

$stack->tab('appearance', function ($tab) {
    $tab->label('Appearance')->priority(20);  // Appears second
    // ...
});

$stack->tab('advanced', function ($tab) {
    $tab->label('Advanced')->priority(100);  // Appears last
    // ...
});
```

---

## Modals (Deferred Groups)

### What is a Deferred Group?

A **Deferred Group** (Modal) is a group that doesn't render its fields inline. Instead, it shows a trigger button that opens a modal/drawer/panel when clicked. This is useful for:

- Reducing UI clutter
- Hiding advanced or rarely-used settings
- Organizing complex configurations

**Key Principle:** A deferred group is purely a **rendering strategy**. The data structure and storage remain identical to normal groups.

### Basic Modal Usage

```php
$stack->group('seo_settings', function ($group) {
    $group->field('meta_title', ['type' => 'text', 'label' => 'Meta Title']);
    $group->field('meta_description', ['type' => 'textarea', 'label' => 'Meta Description']);
    $group->field('og_image', ['type' => 'media', 'label' => 'OG Image']);
    $group->field('robots', [
        'type' => 'select',
        'label' => 'Robots',
        'options' => [
            ['value' => 'index,follow', 'label' => 'Index, Follow'],
            ['value' => 'noindex,follow', 'label' => 'No Index, Follow'],
            ['value' => 'index,nofollow', 'label' => 'Index, No Follow'],
            ['value' => 'noindex,nofollow', 'label' => 'No Index, No Follow'],
        ],
    ]);
}, [
    'label' => 'SEO Settings',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Configure SEO',
        'render' => 'modal',
    ],
]);
```

**UI Behavior:**
1. A button labeled "Configure SEO" appears instead of inline fields
2. Clicking the button opens a modal
3. User edits fields in the modal
4. Modal closes and data is saved with the main form

### Modal Configuration

```php
$stack->group('advanced', function ($group) {
    // Fields...
}, [
    'label' => 'Advanced Options',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Advanced Options',  // Button text
        'render' => 'modal',                   // Render mode
    ],
]);
```

**Render Modes:**

| Mode | Description |
|------|-------------|
| `modal` | Center modal dialog (default) |
| `drawer` | Slide-in panel from the side |
| `panel` | Expandable panel below the trigger |

### Practical Modal Examples

#### 1. Advanced Pricing Configuration

```php
$stack->group('pricing', function ($group) {
    $group->field('regular_price', ['type' => 'number', 'label' => 'Regular Price']);
    $group->field('sale_price', ['type' => 'number', 'label' => 'Sale Price']);
    $group->field('sale_start', ['type' => 'text', 'label' => 'Sale Start Date']);
    $group->field('sale_end', ['type' => 'text', 'label' => 'Sale End Date']);
    $group->field('tax_class', ['type' => 'select', 'label' => 'Tax Class', 'options' => [...]]);
    $group->field('enable_wholesale', ['type' => 'toggle', 'label' => 'Enable Wholesale']);
    $group->field('wholesale_price', [
        'type' => 'number',
        'label' => 'Wholesale Price',
        'conditions' => [
            ['field' => 'enable_wholesale', 'operator' => '==', 'value' => true],
        ],
    ]);
}, [
    'label' => 'Pricing',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Configure Pricing',
        'render' => 'modal',
    ],
]);
```

#### 2. Custom Code Injection

```php
$stack->group('custom_code', function ($group) {
    $group->field('header_code', [
        'type' => 'code',
        'label' => 'Header Code',
        'description' => 'Added before </head>',
        'attributes' => ['language' => 'html'],
    ]);
    $group->field('footer_code', [
        'type' => 'code',
        'label' => 'Footer Code',
        'description' => 'Added before </body>',
        'attributes' => ['language' => 'html'],
    ]);
    $group->field('custom_css', [
        'type' => 'code',
        'label' => 'Custom CSS',
        'attributes' => ['language' => 'css'],
    ]);
    $group->field('custom_js', [
        'type' => 'code',
        'label' => 'Custom JavaScript',
        'attributes' => ['language' => 'javascript'],
    ]);
}, [
    'label' => 'Custom Code',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Add Custom Code',
        'render' => 'drawer',
    ],
]);
```

#### 3. Typography with Drawer

```php
$tab->group('typography_settings', function ($group) {
    $group->field('body_font', ['type' => 'typography', 'label' => 'Body Typography']);
    $group->field('heading_font', ['type' => 'typography', 'label' => 'Heading Typography']);
    $group->field('menu_font', ['type' => 'typography', 'label' => 'Menu Typography']);
    $group->field('button_font', ['type' => 'typography', 'label' => 'Button Typography']);
}, [
    'label' => 'Typography Settings',
    'deferred' => true,
    'ui' => [
        'triggerLabel' => 'Customize Typography',
        'render' => 'drawer',
    ],
]);
```

### When to Use Modals

**Use Modals When:**
- Group has many fields (5+)
- Settings are advanced or rarely changed
- Fields require significant screen space (code editors, typography)
- You want to reduce visual clutter

**Avoid Modals When:**
- Group has only 1-2 fields
- Fields are frequently accessed
- Quick editing is preferred

---

## Combining Containers

You can combine all container types for complex UIs:

```php
OptStack::make('theme_options')
    ->forOptions()
    ->define(function ($stack) {
        
        // Tab 1: General (with groups and modal)
        $stack->tab('general', function ($tab) {
            $tab->label('General')->priority(10);
            
            // Inline group
            $tab->group('identity', function ($group) {
                $group->field('logo', ['type' => 'media']);
                $group->field('favicon', ['type' => 'media']);
            }, ['label' => 'Site Identity']);
            
            // Modal group for advanced settings
            $tab->group('advanced', function ($group) {
                $group->field('custom_css', ['type' => 'code']);
                $group->field('custom_js', ['type' => 'code']);
            }, [
                'label' => 'Custom Code',
                'deferred' => true,
                'ui' => ['triggerLabel' => 'Add Custom Code', 'render' => 'modal'],
            ]);
        });
        
        // Tab 2: Layout (with repeatable group)
        $stack->tab('layout', function ($tab) {
            $tab->label('Layout')->priority(20);
            
            $tab->field('container_width', ['type' => 'range']);
            
            // Repeatable group for sidebars
            $tab->group('sidebars', function ($group) {
                $group->field('name', ['type' => 'text']);
                $group->field('position', ['type' => 'select']);
            }, [
                'label' => 'Custom Sidebars',
                'repeatable' => true,
                'max_items' => 5,
            ]);
        });
        
    })
    ->build();
```

---

## Data Structure

Understanding how data is structured helps when retrieving values:

### Groups in Stack (Root Level)

```php
$stack->group('seo', function ($group) {...});
// Data: ['seo' => ['title' => '...', 'description' => '...']]
```

### Groups in Tabs

```php
$stack->tab('general', function ($tab) {
    $tab->group('identity', function ($group) {...});
});
// Data: ['identity' => ['logo' => 123, 'favicon' => 124]]
// Note: NOT nested under 'general' - tabs don't affect data structure
```

### Nested Groups

```php
$stack->group('company', function ($group) {
    $group->group('address', function ($nested) {...});
});
// Data: ['company' => ['address' => ['street' => '...', 'city' => '...']]]
```

### Repeatable Groups

```php
$stack->group('items', function ($group) {...}, ['repeatable' => true]);
// Data: ['items' => [['name' => '...'], ['name' => '...']]]
```

---

## Retrieving Data

### Using OptStack::getField()

```php
// Simple group field
$logo = OptStack::getField('theme_options', 'identity.logo', '');

// Nested group field
$city = OptStack::getField('settings', 'company.address.city', '');

// Repeatable group (get all items)
$items = OptStack::getField('settings', 'team_members', []);

// Repeatable group (specific item)
$firstName = $items[0]['name'] ?? '';
```

### Using OptStack::getData()

```php
$data = OptStack::getData('theme_options');

// Access group data
$identity = $data['identity'] ?? [];
$logo = $identity['logo'] ?? '';

// Access nested data
$city = $data['company']['address']['city'] ?? '';
```

### For Post/Term Meta

```php
// With object ID
$seoTitle = OptStack::getField('product_meta', 'seo.title', '', $post_id);

// Get entire group
$seo = OptStack::getField('product_meta', 'seo', [], $post_id);
```

---

## Best Practices

### 1. Group Related Fields

```php
// Good: Related fields grouped together
$stack->group('contact', function ($group) {
    $group->field('email', [...]);
    $group->field('phone', [...]);
    $group->field('address', [...]);
}, ['label' => 'Contact Information']);

// Bad: Unrelated fields in same group
$stack->group('misc', function ($group) {
    $group->field('email', [...]);
    $group->field('theme_color', [...]);
    $group->field('enable_cache', [...]);
});
```

### 2. Use Tabs for Large Option Pages

```php
// Good: Organized with tabs
$stack->tab('general', function ($tab) {...});
$stack->tab('appearance', function ($tab) {...});
$stack->tab('advanced', function ($tab) {...});

// Bad: All fields at root level
$stack->field('site_name', [...]);
$stack->field('primary_color', [...]);
$stack->field('cache_duration', [...]);
// ... 50 more fields
```

### 3. Use Modals for Advanced Settings

```php
// Good: Advanced settings in modal
$tab->group('performance', function ($group) {
    $group->field('enable_cache', [...]);
    $group->field('cache_duration', [...]);
    $group->field('minify_css', [...]);
    $group->field('minify_js', [...]);
    $group->field('lazy_load', [...]);
}, [
    'label' => 'Performance',
    'deferred' => true,
    'ui' => ['triggerLabel' => 'Performance Settings'],
]);

// Bad: Many advanced fields cluttering main UI
$tab->field('enable_cache', [...]);
$tab->field('cache_duration', [...]);
// ... etc
```

### 4. Limit Nesting Depth

```php
// Good: Max 2 levels of nesting
$stack->group('company', function ($group) {
    $group->group('address', function ($nested) {
        $nested->field('street', [...]);
        $nested->field('city', [...]);
    });
});

// Bad: Too deeply nested
$stack->group('a', function ($g) {
    $g->group('b', function ($g) {
        $g->group('c', function ($g) {
            $g->group('d', function ($g) {...});
        });
    });
});
```

### 5. Use Descriptive Keys

```php
// Good: Descriptive, readable keys
$stack->group('shipping_options', function ($group) {
    $group->field('enable_free_shipping', [...]);
    $group->field('free_shipping_threshold', [...]);
});

// Bad: Cryptic or unclear keys
$stack->group('so', function ($group) {
    $group->field('efs', [...]);
    $group->field('fst', [...]);
});
```

---

## Related Documentation

- [USAGE-FIELD.md](./USAGE-FIELD.md) - Field types and usage
- [API-REFERENCE.md](./API-REFERENCE.md) - Complete API reference
- [DEFERRED_GROUP_FIELD_SPECIFICATION.md](./DEFERRED_GROUP_FIELD_SPECIFICATION.md) - Technical spec for deferred groups
- [Field Types](./fields/) - Individual field documentation
