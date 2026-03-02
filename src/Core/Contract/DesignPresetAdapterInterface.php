<?php

declare(strict_types=1);

namespace OptStack\Core\Contract;

interface DesignPresetAdapterInterface
{
    /**
     * Render resolved tokens into the adapter's output format.
     *
     * @param array<string, mixed> $resolvedTokens Group-keyed token map from TokenResolver
     * @return string The rendered output (CSS, JSON, etc.)
     */
    public function render(array $resolvedTokens): string;

    /**
     * Get the adapter type identifier.
     */
    public function getType(): string;
}
