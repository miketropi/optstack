<?php

declare(strict_types=1);

namespace OptStack\Core\DesignPreset;

class DesignGroup
{
    protected string $id;
    protected string $label;

    /** @var string[] */
    protected array $appliesTo;

    /** @var string[] */
    protected array $supports;

    protected bool $variant;

    /** @var array<string, array<string, mixed>> */
    protected array $tokens;

    /**
     * @param array{
     *   label: string,
     *   applies_to?: string[],
     *   supports?: string[],
     *   variant?: bool,
     *   tokens?: array<string, array<string, mixed>>
     * } $config
     */
    public function __construct(string $id, array $config)
    {
        $this->id = $id;
        $this->label = $config['label'] ?? ucwords(str_replace('_', ' ', $id));
        $this->appliesTo = $config['applies_to'] ?? [];
        $this->supports = $config['supports'] ?? [];
        $this->variant = $config['variant'] ?? false;
        $this->tokens = $config['tokens'] ?? [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /** @return string[] */
    public function getAppliesTo(): array
    {
        return $this->appliesTo;
    }

    /** @return string[] */
    public function getSupports(): array
    {
        return $this->supports;
    }

    public function hasVariant(): bool
    {
        return $this->variant;
    }

    /** @return array<string, array<string, mixed>> */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'applies_to' => $this->appliesTo,
            'supports' => $this->supports,
            'variant' => $this->variant,
            'tokens' => $this->tokens,
        ];
    }
}
