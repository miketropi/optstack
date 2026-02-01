# Email Field

An email input field with built-in email format validation.

## Basic Usage

```php
$stack->field('contact_email', [
    'type' => 'email',
    'label' => 'Contact Email',
    'default' => '',
    'description' => 'Enter a valid email address',
]);
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `type` | string | - | **Required.** Must be `'email'` |
| `label` | string | - | Field label displayed to users |
| `description` | string | `''` | Help text below the field |
| `default` | string | `''` | Default value |
| `attributes` | array | `[]` | Additional HTML attributes |
| `conditions` | array | `[]` | Conditional display rules |

## Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `placeholder` | string | `''` | Placeholder text |
| `readonly` | bool | `false` | Make field read-only |
| `disabled` | bool | `false` | Disable the field |
| `multiple` | bool | `false` | Allow multiple emails (comma-separated) |

## Examples

### Contact Email

```php
$stack->field('admin_email', [
    'type' => 'email',
    'label' => 'Admin Email',
    'description' => 'Notifications will be sent to this address',
    'attributes' => [
        'placeholder' => 'admin@example.com',
    ],
]);
```

### Support Email

```php
$stack->field('support_email', [
    'type' => 'email',
    'label' => 'Support Email',
    'default' => 'support@example.com',
]);
```

### In a Contact Group

```php
$stack->group('contact', function ($group) {
    $group->field('name', [
        'type' => 'text',
        'label' => 'Contact Name',
    ]);
    
    $group->field('email', [
        'type' => 'email',
        'label' => 'Email Address',
        'attributes' => [
            'placeholder' => 'name@company.com',
        ],
    ]);
    
    $group->field('phone', [
        'type' => 'text',
        'label' => 'Phone Number',
    ]);
}, ['label' => 'Contact Information']);
```

### With Conditional Display

```php
$stack->field('notification_email', [
    'type' => 'email',
    'label' => 'Notification Email',
    'conditions' => [
        ['field' => 'enable_notifications', 'operator' => '==', 'value' => true],
    ],
]);
```

## Retrieving Value

```php
// For options
$email = my_option('contact_email', '');

// Validate before use
$email = my_option('contact_email', '');
if (is_email($email)) {
    // Safe to use
}
```

## Output Example

```php
// Contact link
<?php $email = my_option('contact_email', ''); ?>
<?php if ($email): ?>
    <a href="mailto:<?php echo esc_attr($email); ?>">
        <?php echo esc_html($email); ?>
    </a>
<?php endif; ?>
```

## Validation

WordPress provides built-in email validation:

```php
$email = my_option('contact_email', '');

if (is_email($email)) {
    wp_mail($email, 'Subject', 'Message');
} else {
    // Invalid email
}
```

## Related Fields

- [text](./text.md) - For general text input
- [url](./url.md) - For URL input
