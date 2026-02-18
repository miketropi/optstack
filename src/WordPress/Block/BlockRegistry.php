<?php

declare(strict_types=1);

namespace OptStack\WordPress\Block;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;
use OptStack\WordPress\Bootstrap;

/**
 * Block Registry
 *
 * Registers Gutenberg blocks from OptStack stacks with context 'block'.
 */
class BlockRegistry
{
    /**
     * Block type to stack ID mapping (for JS).
     *
     * @var array<string, string>
     */
    private static array $blockToStack = [];

    /**
     * Whether blocks have been registered.
     */
    private static bool $registered = false;

    /**
     * Register all OptStack blocks.
     */
    public static function registerAll(): void
    {
        if (self::$registered) {
            return;
        }

        $stacks = StackRegistry::byContext('block');

        if (empty($stacks)) {
            return;
        }

        // Populate blockToStack first (required for script localization)
        foreach ($stacks as $stack) {
            $blockType = $stack->getBlockType();
            if ($blockType) {
                self::$blockToStack[$blockType] = $stack->getId();
            }
        }

        // Register block script before blocks so editor_script can reference it
        BlockAssetEnqueue::registerScript();

        foreach ($stacks as $stack) {
            self::registerBlock($stack);
        }

        self::$registered = true;

        do_action('optstack_register_blocks', $stacks);
    }

    /**
     * Register a single block from a stack.
     */
    public static function registerBlock(Stack $stack): void
    {
        $blockType = $stack->getBlockType();

        if (!$blockType) {
            return;
        }

        $stackId = $stack->getId();
        self::$blockToStack[$blockType] = $stackId;

        $blockConfig = [
            'attributes'      => SchemaToAttributes::fromStack($stack),
            'render_callback' => [BlockRenderer::class, 'render'],
            'editor_script'   => BlockAssetEnqueue::SCRIPT_HANDLE,
            'editor_style'    => 'optstack-block',
            'optstack_stack'  => $stackId,
            'title'           => $stack->getConfig('block_title', $stack->getLabel()),
            'category'        => $stack->getConfig('block_category', 'theme'),
            'icon'            => $stack->getConfig('block_icon', 'admin-generic'),
            'description'     => $stack->getConfig('block_description', $stack->getDescription()) ?: null,
            'keywords'        => $stack->getConfig('block_keywords', [$stack->getLabel(), 'optstack']),
            'supports'        => ['customClassName' => true, 'html' => false],
        ];

        $blockConfig = apply_filters('optstack_block_config', $blockConfig, $stack);

        register_block_type($blockType, $blockConfig);
    }

    /**
     * Get block type to stack ID mapping for JavaScript.
     *
     * @return array<string, string>
     */
    public static function getBlockToStackMap(): array
    {
        return self::$blockToStack;
    }

    /**
     * Get stack ID for a block type.
     */
    public static function getStackIdForBlock(string $blockType): ?string
    {
        return self::$blockToStack[$blockType] ?? null;
    }
}
