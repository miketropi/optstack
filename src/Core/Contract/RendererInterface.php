<?php

declare(strict_types=1);

namespace OptStack\Core\Contract;

use OptStack\Core\Stack\Stack;

/**
 * Renderer Interface
 *
 * Defines the contract for rendering stacks in various contexts.
 * Implementations handle admin UI, REST API responses, etc.
 */
interface RendererInterface
{
    /**
     * Render a stack.
     *
     * @param Stack $stack The stack to render
     * @param array<string, mixed> $data Current data values
     * @param array<string, mixed> $options Rendering options
     * @return mixed Rendered output (string, array, or other)
     */
    public function render(Stack $stack, array $data = [], array $options = []): mixed;

    /**
     * Check if this renderer supports the given stack context.
     *
     * @param Stack $stack The stack to check
     * @return bool True if supported
     */
    public function supports(Stack $stack): bool;
}
