# OptStack in the WordPress Customizer

OptStack stacks can be edited in **Appearance → Customize** by using the **Customizer** context. Values are stored in **theme mod** (theme-specific) or **option** (wp_options), and the same OptStack React UI used on options pages is embedded in a Customizer panel.

---

## Quick start

```php
add_action('optstack_init', function () {
    OptStack::make('theme_options')
        ->forCustomizer('theme_mod')
        ->label('Theme Options')
        ->description('Customize colors and layout')
        ->define(function ($stack) {
            $stack->tab('general', function ($tab) {
                $tab->group('colors', function ($group) {
                    $group->field('primary_color', [
                        'type'    => 'color',
                        'label'   => 'Primary Color',
                        'default' => '#2271b1',
                    ]);
                    $group->field('site_tagline', [
                        'type'  => 'text',
                        'label' => 'Tagline',
                    ]);
                });
            });
        })
        ->build();
});
```

- **Storage:** `'theme_mod'` (default) = theme-specific, stored with `set_theme_mod()` / `get_theme_mod()`.
- **Storage:** `'option'` = stored in `wp_options` (same as options context).

The stack appears in the Customizer as a **panel** with the same fields and tabs as in the schema. Editing and saving use the existing OptStack REST API; when you click **Publish** in the Customizer, the current value is persisted via the Customizer’s save (theme_mod or option).

---

## Reading values in the theme

Use the same API as for options stacks:

```php
$primary = OptStack::getField('theme_options', 'primary_color', '#2271b1');
$tagline = OptStack::getField('theme_options', 'site_tagline', '');
```

For nested keys (e.g. inside groups):

```php
$primary = OptStack::getField('theme_options', 'colors.primary_color', '#2271b1');
```

When the stack is registered with `forCustomizer('theme_mod')`, the store is `ThemeModStore` and values come from `get_theme_mod($stack_id)`. When using `forCustomizer('option')`, they come from `get_option($stack_id)`.

---

## Behavior

| Aspect | Detail |
|--------|--------|
| **UI** | One Customizer **panel** per stack; one **section** and one **control** that embeds the OptStack React form. |
| **Storage** | One setting per stack (theme_mod or option). The setting holds the full stack data array. |
| **Preview** | Transport is `refresh`: the preview updates when you click **Publish**. |
| **Capability** | `edit_theme_options` (Customizer default). |
| **Save flow** | Changes are saved via the OptStack REST API when the user saves in the React UI; the Customizer setting is kept in sync so **Publish** persists the same data. |

---

## Storage: theme_mod vs option

- **`forCustomizer('theme_mod')`**  
  - One theme_mod key = stack ID.  
  - Theme-specific; changing theme gives a different set of values unless you migrate.  
  - Fits typical “theme options” in the Customizer.

- **`forCustomizer('option')`**  
  - One option name = stack ID (same as `forOptions()`).  
  - Global, not tied to the active theme.  
  - Use when the same stack should behave like an options-based stack but be editable in the Customizer.

---

## Related

- [STORAGE-SYSTEM.md](./STORAGE-SYSTEM.md) – ThemeModStore and OptionsStore
- [documents/tasks/CUSTOMIZE-STORAGE.md](./tasks/CUSTOMIZE-STORAGE.md) – Task spec for this feature
- [RESPONSIVE.md](./RESPONSIVE.md) – Responsive fields (same value shape in Customizer stacks)
