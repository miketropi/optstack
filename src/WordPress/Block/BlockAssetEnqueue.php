<?php

declare(strict_types=1);

namespace OptStack\WordPress\Block;

use OptStack\WordPress\Bootstrap;

/**
 * Block Asset Enqueue
 *
 * Enqueues OptStack block editor assets.
 */
class BlockAssetEnqueue
{
    /**
     * Script handle for the OptStack block.
     */
    public const SCRIPT_HANDLE = 'optstack-block';

    /**
     * Register (but do not enqueue) the block script.
     * Must run before register_block_type so editor_script can reference it.
     */
    public static function registerScript(): void
    {
        $context = Bootstrap::context();
        if (!$context) {
            return;
        }

        $distPath = $context->path('frontend/dist/');
        $distUrl = $context->url('frontend/dist/');

        $blockJsPath = $distPath . 'optstack-block.js';
        if (!file_exists($blockJsPath)) {
            return;
        }

        wp_register_script(
            self::SCRIPT_HANDLE,
            $distUrl . 'optstack-block.js',
            [
                'wp-blocks',
                'wp-element',
                'wp-block-editor',
                'wp-components',
                'wp-data',
                'wp-api-fetch',
                'wp-i18n',
                'wp-server-side-render',
            ],
            filemtime($blockJsPath),
            true
        );

        $blockCssPath = $distPath . 'optstack-block.css';
        if (file_exists($blockCssPath)) {
            wp_register_style(
                'optstack-block',
                $distUrl . 'optstack-block.css',
                ['wp-components'],
                filemtime($blockCssPath)
            );
        }

        $blockToStack = BlockRegistry::getBlockToStackMap();
        $blockMetadata = self::getBlockMetadata();

        wp_localize_script(self::SCRIPT_HANDLE, 'optstackBlocks', [
            'blockToStack'  => $blockToStack,
            'blockMetadata' => $blockMetadata,
            'restUrl'       => rest_url('optstack/v1/'),
            'nonce'         => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Enqueue block editor assets.
     * Called on enqueue_block_editor_assets as fallback for themes that don't auto-enqueue block scripts.
     */
    public static function enqueue(): void
    {
        $context = Bootstrap::context();

        if (!$context) {
            return;
        }

        $blockToStack = BlockRegistry::getBlockToStackMap();

        if (empty($blockToStack)) {
            return;
        }

        $isDevMode = defined('OPTSTACK_DEV_MODE') && OPTSTACK_DEV_MODE;
        $devServer = defined('OPTSTACK_DEV_SERVER') ? OPTSTACK_DEV_SERVER : 'http://localhost:5173';

        $distPath = $context->path('frontend/dist/');
        $distUrl = $context->url('frontend/dist/');

        // enqueue google fonts 
        wp_enqueue_style('os-google-fonts', 'https://fonts.googleapis.com/css2?family=Google+Sans+Code:ital,wght@0,300..800;1,300..800&family=Google+Sans+Flex:opsz,wght@6..144,1..1000&display=swap', [], $context->version);

        if ($isDevMode) {
            wp_enqueue_script(
                'vite-client',
                $devServer . '/@vite/client',
                [],
                null,
                false
            );

            wp_enqueue_script(
                self::SCRIPT_HANDLE,
                $devServer . '/src/blocks/index.tsx',
                [
                    'wp-blocks',
                    'wp-element',
                    'wp-block-editor',
                    'wp-components',
                    'wp-data',
                    'wp-api-fetch',
                    'wp-i18n',
                    'wp-server-side-render',
                ],
                null,
                true
            );

            add_filter('script_loader_tag', [self::class, 'addModuleType'], 10, 2);
        } else {
            if (!wp_script_is(self::SCRIPT_HANDLE, 'registered')) {
                self::registerScript();
            }
            wp_enqueue_script(self::SCRIPT_HANDLE);
            wp_enqueue_style('optstack-block');
        }

        $blockMetadata = self::getBlockMetadata();

        wp_localize_script(self::SCRIPT_HANDLE, 'optstackBlocks', [
            'blockToStack'   => $blockToStack,
            'blockMetadata'  => $blockMetadata,
            'restUrl'        => rest_url('optstack/v1/'),
            'nonce'          => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Get block metadata for JS (title, category, icon, attributes per block).
     *
     * @return array<string, array{title: string, category: string, icon: string, attributes: array}>
     */
    private static function getBlockMetadata(): array
    {
        $metadata = [];
        foreach (BlockRegistry::getBlockToStackMap() as $blockType => $stackId) {
            $stack = \OptStack\Core\Stack\StackRegistry::get($stackId);
            if ($stack) {
                $metadata[$blockType] = [
                    'title'       => (string) $stack->getConfig('block_title', $stack->getLabel()),
                    'category'    => (string) $stack->getConfig('block_category', 'theme'),
                    'icon'       => (string) $stack->getConfig('block_icon', 'admin-generic'),
                    'attributes'  => SchemaToAttributes::fromStack($stack),
                    'keywords'    => $stack->getConfig('block_keywords', [$stack->getLabel(), 'optstack']),
                ];
            }
        }

        return $metadata;
    }

    /**
     * Add type="module" to script tag for block script.
     */
    public static function addModuleType(string $tag, string $handle): string
    {
        if ($handle === self::SCRIPT_HANDLE || $handle === 'vite-client') {
            return str_replace(' src=', ' type="module" src=', $tag);
        }

        return $tag;
    }
}
