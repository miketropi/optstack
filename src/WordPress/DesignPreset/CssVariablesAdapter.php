<?php

declare(strict_types=1);

namespace OptStack\WordPress\DesignPreset;

use OptStack\Core\Contract\DesignPresetAdapterInterface;

class CssVariablesAdapter implements DesignPresetAdapterInterface
{
    protected string $prefix;
    protected string $selector;

    /** @var array{tablet?:string, mobile?:string} */
    protected array $breakpoints;

    /**
     * @param array{tablet?:string, mobile?:string} $breakpoints
     */
    public function __construct(
        string $prefix = 'os',
        string $selector = ':root',
        array  $breakpoints = ['tablet' => '1024px', 'mobile' => '767px'],
    ) {
        $this->prefix = $prefix;
        $this->selector = $selector;
        $this->breakpoints = $breakpoints;
    }

    /**
     * Render resolved tokens into a CSS string with custom properties.
     *
     * Non-responsive tokens produce a single variable:
     *   --os-heading-font-family: Inter, sans-serif;
     *
     * Responsive tokens produce a desktop default plus media-query overrides:
     *   --os-body-text-font-size: 16px;
     *   @media (max-width: 1024px) { :root { --os-body-text-font-size: 15px; } }
     *   @media (max-width: 767px)  { :root { --os-body-text-font-size: 14px; } }
     */
    public function render(array $resolvedTokens): string
    {
        $desktop = [];
        $tablet  = [];
        $mobile  = [];

        foreach ($resolvedTokens as $groupId => $groupTokens) {
            if (is_array($groupTokens) && $this->isVariantArray($groupTokens)) {
                foreach ($groupTokens as $variant) {
                    if (!is_array($variant) || !isset($variant['id'])) {
                        continue;
                    }
                    $variantId = $variant['id'];
                    foreach ($variant as $tokenKey => $tokenValue) {
                        if (in_array($tokenKey, ['id', 'label'], true)) {
                            continue;
                        }
                        $this->collectVar(
                            "{$groupId}.{$variantId}.{$tokenKey}",
                            $tokenValue,
                            $desktop,
                            $tablet,
                            $mobile,
                        );
                    }
                }
            } elseif (is_array($groupTokens)) {
                foreach ($groupTokens as $tokenKey => $tokenValue) {
                    $this->collectVar(
                        "{$groupId}.{$tokenKey}",
                        $tokenValue,
                        $desktop,
                        $tablet,
                        $mobile,
                    );
                }
            }
        }

        if (empty($desktop) && empty($tablet) && empty($mobile)) {
            return '';
        }

        $css = "{$this->selector} {\n";
        foreach ($desktop as $var => $val) {
            $css .= "  {$var}: {$val};\n";
        }
        $css .= "}";

        if (!empty($tablet) && isset($this->breakpoints['tablet'])) {
            $mq = $this->breakpoints['tablet'];
            $css .= "\n@media (max-width: {$mq}) {\n  {$this->selector} {\n";
            foreach ($tablet as $var => $val) {
                $css .= "    {$var}: {$val};\n";
            }
            $css .= "  }\n}";
        }

        if (!empty($mobile) && isset($this->breakpoints['mobile'])) {
            $mq = $this->breakpoints['mobile'];
            $css .= "\n@media (max-width: {$mq}) {\n  {$this->selector} {\n";
            foreach ($mobile as $var => $val) {
                $css .= "    {$var}: {$val};\n";
            }
            $css .= "  }\n}";
        }

        return $css;
    }

    public function getType(): string
    {
        return 'css_variables';
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Route a single token value into the correct breakpoint bucket(s).
     *
     * @param array<string,string> &$desktop
     * @param array<string,string> &$tablet
     * @param array<string,string> &$mobile
     */
    protected function collectVar(
        string $path,
        mixed  $tokenValue,
        array  &$desktop,
        array  &$tablet,
        array  &$mobile,
    ): void {
        if (is_scalar($tokenValue)) {
            $desktop[$this->toVarName($path)] = (string) $tokenValue;
            return;
        }

        if (!is_array($tokenValue)) {
            return;
        }

        $isResponsive = isset($tokenValue['desktop'])
                      || isset($tokenValue['tablet'])
                      || isset($tokenValue['mobile']);

        if ($isResponsive) {
            $onlyBreakpointKeys = empty(array_diff(
                array_keys($tokenValue),
                ['desktop', 'tablet', 'mobile'],
            ));

            // Simple responsive scalar (e.g. fontSize: {desktop:'16px', tablet:'15px', mobile:'14px'})
            if ($onlyBreakpointKeys && !is_array($tokenValue['desktop'] ?? null)) {
                $varName = $this->toVarName($path);
                if (isset($tokenValue['desktop'])) {
                    $desktop[$varName] = (string) $tokenValue['desktop'];
                }
                if (isset($tokenValue['tablet'])) {
                    $tablet[$varName] = (string) $tokenValue['tablet'];
                }
                if (isset($tokenValue['mobile'])) {
                    $mobile[$varName] = (string) $tokenValue['mobile'];
                }
                return;
            }

            // Nested responsive object (e.g. sizeScale: {desktop:{h1:'3rem',…}, tablet:{…}, mobile:{…}})
            foreach (['desktop', 'tablet', 'mobile'] as $bp) {
                if (!isset($tokenValue[$bp]) || !is_array($tokenValue[$bp])) {
                    continue;
                }
                $bucket = &${$bp};
                foreach ($tokenValue[$bp] as $subKey => $subValue) {
                    if (is_scalar($subValue)) {
                        $bucket[$this->toVarName("{$path}.{$subKey}")] = (string) $subValue;
                    }
                }
                unset($bucket);
            }
            return;
        }

        // Plain sub-key object (non-responsive)
        foreach ($tokenValue as $subKey => $subValue) {
            if (is_scalar($subValue)) {
                $desktop[$this->toVarName("{$path}.{$subKey}")] = (string) $subValue;
            }
        }
    }

    /**
     * Convert a dot-notation token path to a CSS custom property name.
     *
     *   "heading.fontFamily"        → "--os-heading-font-family"
     *   "button.primary.hoverColor" → "--os-button-primary-hover-color"
     *   "heading.sizeScale.h1"      → "--os-heading-size-scale-h1"
     */
    protected function toVarName(string $path): string
    {
        $segments = explode('.', $path);
        $kebab = array_map(fn(string $s) => $this->camelToKebab($s), $segments);
        return "--{$this->prefix}-" . implode('-', $kebab);
    }

    protected function camelToKebab(string $str): string
    {
        return strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1-$2', $str));
    }

    protected function isVariantArray(mixed $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        return array_is_list($value) && is_array($value[0]) && isset($value[0]['id']);
    }
}
