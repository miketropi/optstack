<?php

declare(strict_types=1);

namespace OptStack\WordPress\DesignPreset;

use OptStack\Core\Contract\DesignPresetAdapterInterface;
use OptStack\Core\DesignPreset\TokenResolver;

class CssVariablesAdapter implements DesignPresetAdapterInterface
{
    protected string $prefix;
    protected string $selector;

    public function __construct(string $prefix = 'os', string $selector = ':root')
    {
        $this->prefix = $prefix;
        $this->selector = $selector;
    }

    public function render(array $resolvedTokens): string
    {
        $flat = TokenResolver::flatten($resolvedTokens);

        if (empty($flat)) {
            return '';
        }

        $lines = [];
        foreach ($flat as $path => $value) {
            $varName = $this->pathToVarName($path);
            $lines[] = "  {$varName}: {$value};";
        }

        return "{$this->selector} {\n" . implode("\n", $lines) . "\n}";
    }

    public function getType(): string
    {
        return 'css_variables';
    }

    /**
     * Convert a dot-notation token path to a CSS custom property name.
     * e.g. "button.primary.borderRadius" → "--os-button-primary-border-radius"
     */
    protected function pathToVarName(string $path): string
    {
        $parts = explode('.', $path);
        $kebab = array_map(fn(string $part) => $this->camelToKebab($part), $parts);
        return "--{$this->prefix}-" . implode('-', $kebab);
    }

    protected function camelToKebab(string $str): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '-$0', $str));
    }
}
