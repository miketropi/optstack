<?php

declare(strict_types=1);

namespace OptStack\Core\DesignPreset;

/**
 * Resolves design tokens by merging the cascade:
 *   built-in preset → custom preset tokens → overrides
 */
class TokenResolver
{
    /**
     * Resolve the final flat token map for a given field value.
     *
     * @param array{
     *   active_preset?: string,
     *   overrides?: array<string, string|int>,
     *   presets?: array<int, array<string, mixed>>
     * } $fieldValue The stored design_preset field value
     * @return array<string, mixed> Group-keyed resolved tokens
     */
    public static function resolve(array $fieldValue): array
    {
        $activePresetId = $fieldValue['active_preset'] ?? 'modern';
        $overrides = $fieldValue['overrides'] ?? [];

        $customPreset = self::findCustomPreset($fieldValue, $activePresetId);

        if ($customPreset !== null) {
            $baseId = $customPreset['base'] ?? $activePresetId;
            $basePreset = DesignPresetRegistry::get($baseId);
            $baseTokens = $basePreset['tokens'] ?? [];
            $customTokens = $customPreset['tokens'] ?? [];
            $merged = self::deepMergeTokens($baseTokens, $customTokens);
        } else {
            $registeredPreset = DesignPresetRegistry::get($activePresetId);
            $merged = $registeredPreset['tokens'] ?? [];
        }

        return self::applyOverrides($merged, $overrides);
    }

    /**
     * Flatten resolved tokens into a dot-notation map suitable for CSS variable generation.
     *
     * @param array<string, mixed> $tokens Group-keyed tokens
     * @return array<string, string|int|float> Flat map: "group.token" => value
     */
    public static function flatten(array $tokens): array
    {
        $flat = [];

        foreach ($tokens as $groupId => $groupTokens) {
            if (is_array($groupTokens) && self::isVariantArray($groupTokens)) {
                foreach ($groupTokens as $variant) {
                    if (!is_array($variant) || !isset($variant['id'])) {
                        continue;
                    }
                    $variantId = $variant['id'];
                    foreach ($variant as $tokenKey => $tokenValue) {
                        if (in_array($tokenKey, ['id', 'label'], true)) {
                            continue;
                        }
                        if (is_scalar($tokenValue)) {
                            $flat["{$groupId}.{$variantId}.{$tokenKey}"] = $tokenValue;
                        } elseif (is_array($tokenValue)) {
                            foreach ($tokenValue as $subKey => $subValue) {
                                if (is_scalar($subValue)) {
                                    $flat["{$groupId}.{$variantId}.{$tokenKey}.{$subKey}"] = $subValue;
                                }
                            }
                        }
                    }
                }
            } elseif (is_array($groupTokens)) {
                foreach ($groupTokens as $tokenKey => $tokenValue) {
                    if (is_scalar($tokenValue)) {
                        $flat["{$groupId}.{$tokenKey}"] = $tokenValue;
                    } elseif (is_array($tokenValue)) {
                        foreach ($tokenValue as $subKey => $subValue) {
                            if (is_scalar($subValue)) {
                                $flat["{$groupId}.{$tokenKey}.{$subKey}"] = $subValue;
                            }
                        }
                    }
                }
            }
        }

        return $flat;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function findCustomPreset(array $fieldValue, string $activePresetId): ?array
    {
        $presets = $fieldValue['presets'] ?? [];
        foreach ($presets as $preset) {
            if (isset($preset['id']) && $preset['id'] === $activePresetId) {
                return $preset;
            }
        }
        return null;
    }

    /**
     * Deep-merge two token maps. Variant arrays (arrays of objects with 'id') are
     * merged per-variant rather than replaced wholesale.
     */
    protected static function deepMergeTokens(array $base, array $overlay): array
    {
        $result = $base;

        foreach ($overlay as $groupId => $overlayGroupTokens) {
            if (!isset($result[$groupId])) {
                $result[$groupId] = $overlayGroupTokens;
                continue;
            }

            $baseGroupTokens = $result[$groupId];

            if (self::isVariantArray($baseGroupTokens) && self::isVariantArray($overlayGroupTokens)) {
                $result[$groupId] = self::mergeVariants($baseGroupTokens, $overlayGroupTokens);
            } elseif (is_array($baseGroupTokens) && is_array($overlayGroupTokens) && !self::isVariantArray($overlayGroupTokens)) {
                $result[$groupId] = array_merge($baseGroupTokens, $overlayGroupTokens);
            } else {
                $result[$groupId] = $overlayGroupTokens;
            }
        }

        return $result;
    }

    protected static function mergeVariants(array $baseVariants, array $overlayVariants): array
    {
        $indexed = [];
        foreach ($baseVariants as $v) {
            if (is_array($v) && isset($v['id'])) {
                $indexed[$v['id']] = $v;
            }
        }

        foreach ($overlayVariants as $v) {
            if (is_array($v) && isset($v['id'])) {
                if (isset($indexed[$v['id']])) {
                    $indexed[$v['id']] = array_merge($indexed[$v['id']], $v);
                } else {
                    $indexed[$v['id']] = $v;
                }
            }
        }

        return array_values($indexed);
    }

    /**
     * Apply dot-notation overrides to the resolved token map.
     */
    protected static function applyOverrides(array $tokens, array $overrides): array
    {
        foreach ($overrides as $path => $value) {
            $parts = explode('.', $path);

            if (count($parts) === 2) {
                [$groupId, $tokenKey] = $parts;
                if (isset($tokens[$groupId]) && is_array($tokens[$groupId]) && !self::isVariantArray($tokens[$groupId])) {
                    $tokens[$groupId][$tokenKey] = $value;
                }
            } elseif (count($parts) === 3) {
                [$groupId, $variantIdOrSubKey, $tokenKeyOrSubValue] = $parts;
                if (isset($tokens[$groupId]) && is_array($tokens[$groupId])) {
                    if (self::isVariantArray($tokens[$groupId])) {
                        foreach ($tokens[$groupId] as &$variant) {
                            if (is_array($variant) && ($variant['id'] ?? '') === $variantIdOrSubKey) {
                                $variant[$tokenKeyOrSubValue] = $value;
                                break;
                            }
                        }
                        unset($variant);
                    } elseif (isset($tokens[$groupId][$variantIdOrSubKey]) && is_array($tokens[$groupId][$variantIdOrSubKey])) {
                        $tokens[$groupId][$variantIdOrSubKey][$tokenKeyOrSubValue] = $value;
                    }
                }
            }
        }

        return $tokens;
    }

    protected static function isVariantArray(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (empty($value)) {
            return false;
        }
        return array_is_list($value) && is_array($value[0]) && isset($value[0]['id']);
    }
}
