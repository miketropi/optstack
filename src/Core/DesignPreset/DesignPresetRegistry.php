<?php

declare(strict_types=1);

namespace OptStack\Core\DesignPreset;

class DesignPresetRegistry
{
    /** @var array<string, array<string, mixed>> */
    protected static array $presets = [];

    protected static bool $builtinsRegistered = false;

    /**
     * @param array{id: string, label: string, tokens: array<string, mixed>} $preset
     */
    public static function register(array $preset): void
    {
        $id = $preset['id'] ?? '';
        if ($id === '') {
            return;
        }
        self::$presets[$id] = $preset;
    }

    public static function get(string $id): ?array
    {
        self::ensureBuiltins();
        return self::$presets[$id] ?? null;
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        self::ensureBuiltins();
        return self::$presets;
    }

    public static function has(string $id): bool
    {
        self::ensureBuiltins();
        return isset(self::$presets[$id]);
    }

    public static function unregister(string $id): bool
    {
        if (isset(self::$presets[$id])) {
            unset(self::$presets[$id]);
            return true;
        }
        return false;
    }

    public static function reset(): void
    {
        self::$presets = [];
        self::$builtinsRegistered = false;
    }

    protected static function ensureBuiltins(): void
    {
        if (self::$builtinsRegistered) {
            return;
        }
        self::$builtinsRegistered = true;
        self::registerBuiltinPresets();
    }

    protected static function registerBuiltinPresets(): void
    {
        $builtins = [
            [
                'id' => 'modern',
                'label' => 'Modern',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => 'Inter, sans-serif',
                        'fontWeight' => 700,
                        'lineHeight' => 1.2,
                        'letterSpacing' => '-0.02em',
                        'color' => '#111827',
                        'sizeScale' => ['h1' => '3rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                    ],
                    'body_text' => [
                        'fontFamily' => 'Inter, sans-serif',
                        'fontSize' => '16px',
                        'fontWeight' => 400,
                        'lineHeight' => 1.6,
                        'color' => '#374151',
                    ],
                    'inline_text' => [
                        'linkColor' => '#2563EB',
                        'linkHoverColor' => '#1D4ED8',
                        'codeBackground' => '#F3F4F6',
                        'codeFontFamily' => 'ui-monospace, SFMono-Regular, monospace',
                        'markBackground' => '#FEF08A',
                    ],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => '14px', 'fontWeight' => 600, 'padding' => '10px 20px', 'borderRadius' => '6px', 'borderWidth' => '0', 'background' => '#2563EB', 'color' => '#FFFFFF', 'hoverBackground' => '#1D4ED8', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => '14px', 'fontWeight' => 600, 'padding' => '10px 20px', 'borderRadius' => '6px', 'borderWidth' => '1px', 'borderColor' => '#D1D5DB', 'background' => '#FFFFFF', 'color' => '#374151', 'hoverBackground' => '#F3F4F6', 'hoverColor' => '#111827'],
                    ],
                    'link' => [
                        'color' => '#2563EB',
                        'hoverColor' => '#1D4ED8',
                        'decoration' => 'none',
                        'hoverDecoration' => 'underline',
                    ],
                    'form_field' => [
                        'background' => '#FFFFFF',
                        'borderColor' => '#D1D5DB',
                        'borderWidth' => '1px',
                        'borderRadius' => '6px',
                        'padding' => '10px 12px',
                        'fontSize' => '14px',
                        'color' => '#111827',
                        'focusBorderColor' => '#2563EB',
                        'errorBorderColor' => '#EF4444',
                    ],
                    'form_choice' => [
                        'size' => '18px',
                        'borderColor' => '#D1D5DB',
                        'checkedBackground' => '#2563EB',
                        'borderRadius' => '4px',
                    ],
                    'form_meta' => [
                        'labelFontSize' => '14px',
                        'labelFontWeight' => 500,
                        'labelColor' => '#374151',
                        'helpColor' => '#6B7280',
                        'errorColor' => '#EF4444',
                        'successColor' => '#10B981',
                    ],
                    'container' => [
                        'maxWidth' => '1200px',
                        'padding' => '0 24px',
                        'background' => '#FFFFFF',
                    ],
                    'card' => [
                        'background' => '#FFFFFF',
                        'borderRadius' => '8px',
                        'borderWidth' => '1px',
                        'borderColor' => '#E5E7EB',
                        'padding' => '24px',
                        'shadow' => '0 1px 3px rgba(0,0,0,0.1)',
                    ],
                    'navigation' => [
                        'fontFamily' => 'Inter, sans-serif',
                        'fontSize' => '14px',
                        'fontWeight' => 500,
                        'color' => '#374151',
                        'activeColor' => '#2563EB',
                        'hoverColor' => '#111827',
                        'padding' => '8px 16px',
                    ],
                    'alert' => [
                        ['id' => 'info', 'label' => 'Info', 'background' => '#EFF6FF', 'borderColor' => '#BFDBFE', 'color' => '#1E40AF', 'borderRadius' => '8px', 'padding' => '16px', 'iconColor' => '#3B82F6'],
                        ['id' => 'success', 'label' => 'Success', 'background' => '#F0FDF4', 'borderColor' => '#BBF7D0', 'color' => '#166534', 'borderRadius' => '8px', 'padding' => '16px', 'iconColor' => '#22C55E'],
                        ['id' => 'warning', 'label' => 'Warning', 'background' => '#FFFBEB', 'borderColor' => '#FDE68A', 'color' => '#92400E', 'borderRadius' => '8px', 'padding' => '16px', 'iconColor' => '#F59E0B'],
                        ['id' => 'error', 'label' => 'Error', 'background' => '#FEF2F2', 'borderColor' => '#FECACA', 'color' => '#991B1B', 'borderRadius' => '8px', 'padding' => '16px', 'iconColor' => '#EF4444'],
                    ],
                    'loading' => [
                        'color' => '#2563EB',
                        'trackColor' => '#E5E7EB',
                        'size' => '24px',
                    ],
                    'media' => [
                        'borderRadius' => '8px',
                        'borderWidth' => '0',
                        'borderColor' => '#E5E7EB',
                        'shadow' => 'none',
                    ],
                    'utility' => [
                        'badgeBackground' => '#EFF6FF',
                        'badgeColor' => '#2563EB',
                        'badgeBorderRadius' => '9999px',
                        'avatarSize' => '40px',
                        'avatarBorderRadius' => '9999px',
                        'dividerColor' => '#E5E7EB',
                        'dividerWidth' => '1px',
                    ],
                ],
            ],

            [
                'id' => 'classic',
                'label' => 'Classic',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => 'Georgia, "Times New Roman", serif',
                        'fontWeight' => 700,
                        'lineHeight' => 1.3,
                        'letterSpacing' => '0',
                        'color' => '#1a1a1a',
                        'sizeScale' => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                    ],
                    'body_text' => ['fontFamily' => 'Georgia, serif', 'fontSize' => '17px', 'fontWeight' => 400, 'lineHeight' => 1.7, 'color' => '#333333'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => '15px', 'fontWeight' => 600, 'padding' => '12px 28px', 'borderRadius' => '4px', 'borderWidth' => '0', 'background' => '#1a1a1a', 'color' => '#FFFFFF', 'hoverBackground' => '#333333', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => '15px', 'fontWeight' => 600, 'padding' => '12px 28px', 'borderRadius' => '4px', 'borderWidth' => '2px', 'borderColor' => '#1a1a1a', 'background' => 'transparent', 'color' => '#1a1a1a', 'hoverBackground' => '#1a1a1a', 'hoverColor' => '#FFFFFF'],
                    ],
                    'card' => ['background' => '#FFFFFF', 'borderRadius' => '4px', 'borderWidth' => '1px', 'borderColor' => '#e0e0e0', 'padding' => '28px', 'shadow' => '0 2px 8px rgba(0,0,0,0.08)'],
                    'link' => ['color' => '#1a1a1a', 'hoverColor' => '#555555', 'decoration' => 'underline', 'hoverDecoration' => 'none'],
                ],
            ],

            [
                'id' => 'minimal',
                'label' => 'Minimal',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => 'system-ui, -apple-system, sans-serif',
                        'fontWeight' => 600,
                        'lineHeight' => 1.25,
                        'letterSpacing' => '-0.01em',
                        'color' => '#000000',
                        'sizeScale' => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                    ],
                    'body_text' => ['fontFamily' => 'system-ui, -apple-system, sans-serif', 'fontSize' => '15px', 'fontWeight' => 400, 'lineHeight' => 1.6, 'color' => '#333333'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => '13px', 'fontWeight' => 500, 'padding' => '8px 16px', 'borderRadius' => '3px', 'borderWidth' => '1px', 'borderColor' => '#000000', 'background' => '#000000', 'color' => '#FFFFFF', 'hoverBackground' => '#333333', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => '13px', 'fontWeight' => 500, 'padding' => '8px 16px', 'borderRadius' => '3px', 'borderWidth' => '1px', 'borderColor' => '#d4d4d4', 'background' => 'transparent', 'color' => '#333333', 'hoverBackground' => '#f5f5f5', 'hoverColor' => '#000000'],
                    ],
                    'card' => ['background' => '#FFFFFF', 'borderRadius' => '3px', 'borderWidth' => '1px', 'borderColor' => '#e5e5e5', 'padding' => '20px', 'shadow' => 'none'],
                    'link' => ['color' => '#000000', 'hoverColor' => '#666666', 'decoration' => 'underline', 'hoverDecoration' => 'underline'],
                ],
            ],

            [
                'id' => 'playful',
                'label' => 'Playful',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => '"Nunito", "Comic Sans MS", sans-serif',
                        'fontWeight' => 800,
                        'lineHeight' => 1.2,
                        'letterSpacing' => '-0.01em',
                        'color' => '#1e1b4b',
                        'sizeScale' => ['h1' => '3.5rem', 'h2' => '2.75rem', 'h3' => '2.25rem', 'h4' => '1.75rem', 'h5' => '1.5rem', 'h6' => '1.25rem'],
                    ],
                    'body_text' => ['fontFamily' => '"Nunito", sans-serif', 'fontSize' => '16px', 'fontWeight' => 400, 'lineHeight' => 1.7, 'color' => '#374151'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => '15px', 'fontWeight' => 700, 'padding' => '12px 24px', 'borderRadius' => '9999px', 'borderWidth' => '0', 'background' => '#7C3AED', 'color' => '#FFFFFF', 'hoverBackground' => '#6D28D9', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => '15px', 'fontWeight' => 700, 'padding' => '12px 24px', 'borderRadius' => '9999px', 'borderWidth' => '2px', 'borderColor' => '#7C3AED', 'background' => 'transparent', 'color' => '#7C3AED', 'hoverBackground' => '#7C3AED', 'hoverColor' => '#FFFFFF'],
                    ],
                    'card' => ['background' => '#FFFFFF', 'borderRadius' => '16px', 'borderWidth' => '2px', 'borderColor' => '#E9D5FF', 'padding' => '28px', 'shadow' => '0 4px 14px rgba(124,58,237,0.1)'],
                    'link' => ['color' => '#7C3AED', 'hoverColor' => '#6D28D9', 'decoration' => 'none', 'hoverDecoration' => 'underline'],
                ],
            ],

            [
                'id' => 'elegant',
                'label' => 'Elegant',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => '"Playfair Display", Georgia, serif',
                        'fontWeight' => 400,
                        'lineHeight' => 1.2,
                        'letterSpacing' => '0.02em',
                        'color' => '#1a1a2e',
                        'sizeScale' => ['h1' => '3.25rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                    ],
                    'body_text' => ['fontFamily' => '"Lato", "Helvetica Neue", sans-serif', 'fontSize' => '16px', 'fontWeight' => 300, 'lineHeight' => 1.8, 'color' => '#4a4a4a'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => '"Lato", sans-serif', 'fontSize' => '12px', 'fontWeight' => 400, 'padding' => '14px 32px', 'borderRadius' => '0', 'borderWidth' => '1px', 'borderColor' => '#1a1a2e', 'background' => '#1a1a2e', 'color' => '#FFFFFF', 'hoverBackground' => '#2d2d4e', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => '"Lato", sans-serif', 'fontSize' => '12px', 'fontWeight' => 400, 'padding' => '14px 32px', 'borderRadius' => '0', 'borderWidth' => '1px', 'borderColor' => '#c9b99a', 'background' => 'transparent', 'color' => '#c9b99a', 'hoverBackground' => '#c9b99a', 'hoverColor' => '#FFFFFF'],
                    ],
                    'card' => ['background' => '#FFFFFF', 'borderRadius' => '0', 'borderWidth' => '1px', 'borderColor' => '#e8e3da', 'padding' => '32px', 'shadow' => 'none'],
                    'link' => ['color' => '#c9b99a', 'hoverColor' => '#1a1a2e', 'decoration' => 'none', 'hoverDecoration' => 'underline'],
                ],
            ],

            [
                'id' => 'dark',
                'label' => 'Dark',
                'builtin' => true,
                'tokens' => [
                    'heading' => [
                        'fontFamily' => 'Inter, sans-serif',
                        'fontWeight' => 700,
                        'lineHeight' => 1.2,
                        'letterSpacing' => '-0.02em',
                        'color' => '#F9FAFB',
                        'sizeScale' => ['h1' => '3rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                    ],
                    'body_text' => ['fontFamily' => 'Inter, sans-serif', 'fontSize' => '16px', 'fontWeight' => 400, 'lineHeight' => 1.6, 'color' => '#D1D5DB'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => '14px', 'fontWeight' => 600, 'padding' => '10px 20px', 'borderRadius' => '6px', 'borderWidth' => '0', 'background' => '#6366F1', 'color' => '#FFFFFF', 'hoverBackground' => '#4F46E5', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => '14px', 'fontWeight' => 600, 'padding' => '10px 20px', 'borderRadius' => '6px', 'borderWidth' => '1px', 'borderColor' => '#4B5563', 'background' => '#1F2937', 'color' => '#D1D5DB', 'hoverBackground' => '#374151', 'hoverColor' => '#F9FAFB'],
                    ],
                    'card' => ['background' => '#1F2937', 'borderRadius' => '8px', 'borderWidth' => '1px', 'borderColor' => '#374151', 'padding' => '24px', 'shadow' => '0 4px 12px rgba(0,0,0,0.3)'],
                    'container' => ['maxWidth' => '1200px', 'padding' => '0 24px', 'background' => '#111827'],
                    'form_field' => ['background' => '#1F2937', 'borderColor' => '#4B5563', 'borderWidth' => '1px', 'borderRadius' => '6px', 'padding' => '10px 12px', 'fontSize' => '14px', 'color' => '#F9FAFB', 'focusBorderColor' => '#6366F1', 'errorBorderColor' => '#EF4444'],
                    'link' => ['color' => '#818CF8', 'hoverColor' => '#A5B4FC', 'decoration' => 'none', 'hoverDecoration' => 'underline'],
                ],
            ],
        ];

        foreach ($builtins as $preset) {
            if (!isset(self::$presets[$preset['id']])) {
                self::$presets[$preset['id']] = $preset;
            }
        }
    }
}
