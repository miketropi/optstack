<?php

declare(strict_types=1);

namespace OptStack\WordPress\Block;

use WP_Block;

/**
 * Block Renderer
 *
 * PHP render_callback for OptStack-powered Gutenberg blocks.
 * Output is provided via the optstack_render_block filter.
 */
class BlockRenderer
{
    /**
     * Render an OptStack block.
     *
     * @param array<string, mixed> $attributes Block attributes
     * @param string $content Inner block content (empty for leaf blocks)
     * @param WP_Block $block Block instance
     * @return string HTML output
     */
    public static function render(array $attributes, string $content, WP_Block $block): string
    {
        $stackId = $block->block_type->optstack_stack ?? null;

        if (!$stackId || !is_string($stackId)) {
            return '';
        }

        $html = (string) apply_filters(
            'optstack_render_block',
            '',
            $stackId,
            $attributes,
            $block
        );

        return $html;
    }
}
