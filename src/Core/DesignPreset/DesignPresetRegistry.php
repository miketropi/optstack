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
                        'lineHeight' => ['desktop' => 1.2, 'tablet' => 1.25, 'mobile' => 1.3],
                        'letterSpacing' => ['desktop' => '-0.02em', 'tablet' => '-0.01em', 'mobile' => '0'],
                        'color' => '#111827',
                        'sizeScale' => [
                            'desktop' => ['h1' => '3rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                            'tablet'  => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                            'mobile'  => ['h1' => '2rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                        ],
                    ],
                    'body_text' => [
                        'fontFamily' => 'Inter, sans-serif',
                        'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                        'fontWeight' => 400,
                        'lineHeight' => ['desktop' => 1.6, 'tablet' => 1.6, 'mobile' => 1.5],
                        'color' => '#374151',
                    ],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'], 'fontWeight' => 600, 'padding' => ['desktop' => '10px 20px', 'tablet' => '10px 18px', 'mobile' => '8px 16px'], 'borderRadius' => '6px', 'borderWidth' => '0', 'background' => '#2563EB', 'color' => '#FFFFFF', 'hoverBackground' => '#1D4ED8', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'], 'fontWeight' => 600, 'padding' => ['desktop' => '10px 20px', 'tablet' => '10px 18px', 'mobile' => '8px 16px'], 'borderRadius' => '6px', 'borderWidth' => '1px', 'borderColor' => '#D1D5DB', 'background' => '#FFFFFF', 'color' => '#374151', 'hoverBackground' => '#F3F4F6', 'hoverColor' => '#111827'],
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
                        'padding' => ['desktop' => '10px 12px', 'tablet' => '10px 12px', 'mobile' => '8px 10px'],
                        'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '16px'],
                        'color' => '#111827',
                        'focusBorderColor' => '#2563EB',
                        'errorBorderColor' => '#EF4444',
                    ],
                    'form_meta' => [
                        'labelFontSize' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '13px'],
                        'labelFontWeight' => 500,
                        'labelColor' => '#374151',
                        'helpColor' => '#6B7280',
                        'errorColor' => '#EF4444',
                        'successColor' => '#10B981',
                    ],
                    'container' => [
                        'maxWidth' => ['desktop' => '1200px', 'tablet' => '768px', 'mobile' => '100%'],
                        'padding' => ['desktop' => '0 24px', 'tablet' => '0 20px', 'mobile' => '0 16px'],
                        'background' => '#FFFFFF',
                        'borderRadius' => '0',
                        'borderWidth' => '0',
                        'borderColor' => 'transparent',
                    ],
                    'table' => [
                        'headerBackground' => '#F9FAFB',
                        'headerColor' => '#111827',
                        'headerFontWeight' => 600,
                        'cellPadding' => ['desktop' => '12px 16px', 'tablet' => '10px 12px', 'mobile' => '8px 10px'],
                        'cellFontSize' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '12px'],
                        'cellColor' => '#374151',
                        'borderColor' => '#E5E7EB',
                        'borderWidth' => '1px',
                        'stripedBackground' => '#F9FAFB',
                        'hoverBackground' => '#F3F4F6',
                    ],
                    'list' => [
                        'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                        'lineHeight' => ['desktop' => 1.6, 'tablet' => 1.6, 'mobile' => 1.5],
                        'color' => '#374151',
                        'markerColor' => '#2563EB',
                        'markerSize' => '8px',
                        'itemSpacing' => ['desktop' => '8px', 'tablet' => '6px', 'mobile' => '6px'],
                        'indentSize' => ['desktop' => '24px', 'tablet' => '20px', 'mobile' => '16px'],
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
                        'lineHeight' => ['desktop' => 1.3, 'tablet' => 1.3, 'mobile' => 1.35],
                        'letterSpacing' => '0',
                        'color' => '#1a1a1a',
                        'sizeScale' => [
                            'desktop' => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                            'tablet'  => ['h1' => '2.25rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                            'mobile'  => ['h1' => '1.875rem', 'h2' => '1.5rem', 'h3' => '1.375rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                        ],
                    ],
                    'body_text' => ['fontFamily' => 'Georgia, serif', 'fontSize' => ['desktop' => '17px', 'tablet' => '16px', 'mobile' => '15px'], 'fontWeight' => 400, 'lineHeight' => ['desktop' => 1.7, 'tablet' => 1.7, 'mobile' => 1.6], 'color' => '#333333'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '15px', 'tablet' => '15px', 'mobile' => '14px'], 'fontWeight' => 600, 'padding' => ['desktop' => '12px 28px', 'tablet' => '10px 24px', 'mobile' => '10px 20px'], 'borderRadius' => '4px', 'borderWidth' => '0', 'background' => '#1a1a1a', 'color' => '#FFFFFF', 'hoverBackground' => '#333333', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '15px', 'tablet' => '15px', 'mobile' => '14px'], 'fontWeight' => 600, 'padding' => ['desktop' => '12px 28px', 'tablet' => '10px 24px', 'mobile' => '10px 20px'], 'borderRadius' => '4px', 'borderWidth' => '2px', 'borderColor' => '#1a1a1a', 'background' => 'transparent', 'color' => '#1a1a1a', 'hoverBackground' => '#1a1a1a', 'hoverColor' => '#FFFFFF'],
                    ],
                    'link' => ['color' => '#1a1a1a', 'hoverColor' => '#555555', 'decoration' => 'underline', 'hoverDecoration' => 'none'],
                    'form_field' => [
                        'background' => '#FFFFFF',
                        'borderColor' => '#C8C8C8',
                        'borderWidth' => '1px',
                        'borderRadius' => '4px',
                        'padding' => ['desktop' => '10px 14px', 'tablet' => '10px 14px', 'mobile' => '8px 12px'],
                        'fontSize' => ['desktop' => '15px', 'tablet' => '15px', 'mobile' => '16px'],
                        'color' => '#1a1a1a',
                        'focusBorderColor' => '#1a1a1a',
                        'errorBorderColor' => '#CC3333',
                    ],
                    'form_meta' => [
                        'labelFontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'],
                        'labelFontWeight' => 600,
                        'labelColor' => '#1a1a1a',
                        'helpColor' => '#666666',
                        'errorColor' => '#CC3333',
                        'successColor' => '#2E7D32',
                    ],
                    'container' => [
                        'maxWidth' => ['desktop' => '960px', 'tablet' => '720px', 'mobile' => '100%'],
                        'padding' => ['desktop' => '0 32px', 'tablet' => '0 24px', 'mobile' => '0 16px'],
                        'background' => '#FFFFFF',
                        'borderRadius' => '0',
                        'borderWidth' => '0',
                        'borderColor' => 'transparent',
                    ],
                    'table' => [
                        'headerBackground' => '#F5F5F5',
                        'headerColor' => '#1a1a1a',
                        'headerFontWeight' => 700,
                        'cellPadding' => ['desktop' => '14px 18px', 'tablet' => '12px 14px', 'mobile' => '8px 10px'],
                        'cellFontSize' => ['desktop' => '15px', 'tablet' => '14px', 'mobile' => '13px'],
                        'cellColor' => '#333333',
                        'borderColor' => '#D4D4D4',
                        'borderWidth' => '1px',
                        'stripedBackground' => '#FAFAFA',
                        'hoverBackground' => '#F0F0F0',
                    ],
                    'list' => [
                        'fontSize' => ['desktop' => '17px', 'tablet' => '16px', 'mobile' => '15px'],
                        'lineHeight' => ['desktop' => 1.7, 'tablet' => 1.7, 'mobile' => 1.6],
                        'color' => '#333333',
                        'markerColor' => '#1a1a1a',
                        'markerSize' => '6px',
                        'itemSpacing' => ['desktop' => '10px', 'tablet' => '8px', 'mobile' => '6px'],
                        'indentSize' => ['desktop' => '28px', 'tablet' => '24px', 'mobile' => '18px'],
                    ],
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
                        'lineHeight' => ['desktop' => 1.2, 'tablet' => 1.25, 'mobile' => 1.3],
                        'letterSpacing' => ['desktop' => '0.02em', 'tablet' => '0.01em', 'mobile' => '0'],
                        'color' => '#1a1a2e',
                        'sizeScale' => [
                            'desktop' => ['h1' => '3.25rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                            'tablet'  => ['h1' => '2.75rem', 'h2' => '2.125rem', 'h3' => '1.75rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                            'mobile'  => ['h1' => '2.25rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                        ],
                    ],
                    'body_text' => ['fontFamily' => '"Lato", "Helvetica Neue", sans-serif', 'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'], 'fontWeight' => 300, 'lineHeight' => ['desktop' => 1.8, 'tablet' => 1.7, 'mobile' => 1.6], 'color' => '#4a4a4a'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => '"Lato", sans-serif', 'fontSize' => ['desktop' => '12px', 'tablet' => '12px', 'mobile' => '11px'], 'fontWeight' => 400, 'padding' => ['desktop' => '14px 32px', 'tablet' => '12px 28px', 'mobile' => '10px 24px'], 'borderRadius' => '0', 'borderWidth' => '1px', 'borderColor' => '#1a1a2e', 'background' => '#1a1a2e', 'color' => '#FFFFFF', 'hoverBackground' => '#2d2d4e', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => '"Lato", sans-serif', 'fontSize' => ['desktop' => '12px', 'tablet' => '12px', 'mobile' => '11px'], 'fontWeight' => 400, 'padding' => ['desktop' => '14px 32px', 'tablet' => '12px 28px', 'mobile' => '10px 24px'], 'borderRadius' => '0', 'borderWidth' => '1px', 'borderColor' => '#c9b99a', 'background' => 'transparent', 'color' => '#c9b99a', 'hoverBackground' => '#c9b99a', 'hoverColor' => '#FFFFFF'],
                    ],
                    'link' => ['color' => '#c9b99a', 'hoverColor' => '#1a1a2e', 'decoration' => 'none', 'hoverDecoration' => 'underline'],
                    'form_field' => [
                        'background' => '#FFFFFF',
                        'borderColor' => '#e8e3da',
                        'borderWidth' => '1px',
                        'borderRadius' => '0',
                        'padding' => ['desktop' => '12px 16px', 'tablet' => '10px 14px', 'mobile' => '8px 12px'],
                        'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '16px'],
                        'color' => '#1a1a2e',
                        'focusBorderColor' => '#c9b99a',
                        'errorBorderColor' => '#B85C5C',
                    ],
                    'form_meta' => [
                        'labelFontSize' => ['desktop' => '13px', 'tablet' => '13px', 'mobile' => '12px'],
                        'labelFontWeight' => 400,
                        'labelColor' => '#1a1a2e',
                        'helpColor' => '#8a8a8a',
                        'errorColor' => '#B85C5C',
                        'successColor' => '#6B8E4E',
                    ],
                    'container' => [
                        'maxWidth' => ['desktop' => '1080px', 'tablet' => '720px', 'mobile' => '100%'],
                        'padding' => ['desktop' => '0 40px', 'tablet' => '0 28px', 'mobile' => '0 16px'],
                        'background' => '#FAFAF8',
                        'borderRadius' => '0',
                        'borderWidth' => '0',
                        'borderColor' => 'transparent',
                    ],
                    'table' => [
                        'headerBackground' => '#F8F6F3',
                        'headerColor' => '#1a1a2e',
                        'headerFontWeight' => 500,
                        'cellPadding' => ['desktop' => '14px 20px', 'tablet' => '12px 16px', 'mobile' => '8px 10px'],
                        'cellFontSize' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '12px'],
                        'cellColor' => '#4a4a4a',
                        'borderColor' => '#e8e3da',
                        'borderWidth' => '1px',
                        'stripedBackground' => '#FDFCFA',
                        'hoverBackground' => '#F5F3EF',
                    ],
                    'list' => [
                        'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                        'lineHeight' => ['desktop' => 1.8, 'tablet' => 1.7, 'mobile' => 1.6],
                        'color' => '#4a4a4a',
                        'markerColor' => '#c9b99a',
                        'markerSize' => '6px',
                        'itemSpacing' => ['desktop' => '12px', 'tablet' => '10px', 'mobile' => '8px'],
                        'indentSize' => ['desktop' => '24px', 'tablet' => '20px', 'mobile' => '16px'],
                    ],
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
                        'lineHeight' => ['desktop' => 1.2, 'tablet' => 1.25, 'mobile' => 1.3],
                        'letterSpacing' => ['desktop' => '-0.02em', 'tablet' => '-0.01em', 'mobile' => '0'],
                        'color' => '#F9FAFB',
                        'sizeScale' => [
                            'desktop' => ['h1' => '3rem', 'h2' => '2.5rem', 'h3' => '2rem', 'h4' => '1.5rem', 'h5' => '1.25rem', 'h6' => '1rem'],
                            'tablet'  => ['h1' => '2.5rem', 'h2' => '2rem', 'h3' => '1.75rem', 'h4' => '1.375rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                            'mobile'  => ['h1' => '2rem', 'h2' => '1.75rem', 'h3' => '1.5rem', 'h4' => '1.25rem', 'h5' => '1.125rem', 'h6' => '1rem'],
                        ],
                    ],
                    'body_text' => ['fontFamily' => 'Inter, sans-serif', 'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'], 'fontWeight' => 400, 'lineHeight' => ['desktop' => 1.6, 'tablet' => 1.6, 'mobile' => 1.5], 'color' => '#D1D5DB'],
                    'button' => [
                        ['id' => 'primary', 'label' => 'Primary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'], 'fontWeight' => 600, 'padding' => ['desktop' => '10px 20px', 'tablet' => '10px 18px', 'mobile' => '8px 16px'], 'borderRadius' => '6px', 'borderWidth' => '0', 'background' => '#6366F1', 'color' => '#FFFFFF', 'hoverBackground' => '#4F46E5', 'hoverColor' => '#FFFFFF'],
                        ['id' => 'secondary', 'label' => 'Secondary', 'fontFamily' => 'inherit', 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '13px'], 'fontWeight' => 600, 'padding' => ['desktop' => '10px 20px', 'tablet' => '10px 18px', 'mobile' => '8px 16px'], 'borderRadius' => '6px', 'borderWidth' => '1px', 'borderColor' => '#4B5563', 'background' => '#1F2937', 'color' => '#D1D5DB', 'hoverBackground' => '#374151', 'hoverColor' => '#F9FAFB'],
                    ],
                    'link' => ['color' => '#818CF8', 'hoverColor' => '#A5B4FC', 'decoration' => 'none', 'hoverDecoration' => 'underline'],
                    'form_field' => ['background' => '#1F2937', 'borderColor' => '#4B5563', 'borderWidth' => '1px', 'borderRadius' => '6px', 'padding' => ['desktop' => '10px 12px', 'tablet' => '10px 12px', 'mobile' => '8px 10px'], 'fontSize' => ['desktop' => '14px', 'tablet' => '14px', 'mobile' => '16px'], 'color' => '#F9FAFB', 'focusBorderColor' => '#6366F1', 'errorBorderColor' => '#EF4444'],
                    'form_meta' => [
                        'labelFontSize' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '13px'],
                        'labelFontWeight' => 500,
                        'labelColor' => '#E5E7EB',
                        'helpColor' => '#9CA3AF',
                        'errorColor' => '#F87171',
                        'successColor' => '#34D399',
                    ],
                    'container' => ['maxWidth' => ['desktop' => '1200px', 'tablet' => '768px', 'mobile' => '100%'], 'padding' => ['desktop' => '0 24px', 'tablet' => '0 20px', 'mobile' => '0 16px'], 'background' => '#111827', 'borderRadius' => '0', 'borderWidth' => '0', 'borderColor' => 'transparent'],
                    'table' => [
                        'headerBackground' => '#1F2937',
                        'headerColor' => '#F9FAFB',
                        'headerFontWeight' => 600,
                        'cellPadding' => ['desktop' => '12px 16px', 'tablet' => '10px 12px', 'mobile' => '8px 10px'],
                        'cellFontSize' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '12px'],
                        'cellColor' => '#D1D5DB',
                        'borderColor' => '#374151',
                        'borderWidth' => '1px',
                        'stripedBackground' => '#1A2332',
                        'hoverBackground' => '#253344',
                    ],
                    'list' => [
                        'fontSize' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                        'lineHeight' => ['desktop' => 1.6, 'tablet' => 1.6, 'mobile' => 1.5],
                        'color' => '#D1D5DB',
                        'markerColor' => '#818CF8',
                        'markerSize' => '8px',
                        'itemSpacing' => ['desktop' => '8px', 'tablet' => '6px', 'mobile' => '6px'],
                        'indentSize' => ['desktop' => '24px', 'tablet' => '20px', 'mobile' => '16px'],
                    ],
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
