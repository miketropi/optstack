<?php
/**
 * OptStack Example: Select (WordPress Query) Field
 *
 * Demonstrates the select-wp-query field type: async dropdowns that load
 * options from WordPress (posts, pages, CPT, terms, users) when the user
 * types. Data is not loaded all at once.
 *
 * To enable: in optstack.php uncomment or add:
 *   require_once OPTSTACK_DIR . 'examples/select-wp-query-example.php';
 *
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

if (!defined('ABSPATH')) {
    exit;
}

add_action('optstack_init', function (): void {
    OptStack::make('select_wp_query_demo')
        ->forOptions()
        // ->menuParent('optstack')
        ->label('Select WP Query Demo')
        ->description('Examples of select-wp-query: search posts, pages, terms, users')
        ->define(function ($stack) {
            // -----------------------------------------------------------------
            // Single: Page
            // -----------------------------------------------------------------
            $stack->field('landing_page', [
                'type'  => 'select-wp-query',
                'label' => 'Landing Page',
                'description' => 'Choose a page (search by title).',
                'attributes' => [
                    'source'     => 'post',
                    'post_type'  => 'page',
                    'placeholder' => 'Search pages...',
                ],
            ]);

            // -----------------------------------------------------------------
            // Single: Post
            // -----------------------------------------------------------------
            $stack->field('featured_post', [
                'type'  => 'select-wp-query',
                'label' => 'Featured Post',
                'description' => 'Choose a blog post.',
                'attributes' => [
                    'source'     => 'post',
                    'post_type'  => 'post',
                    'placeholder' => 'Search posts...',
                ],
            ]);

            // -----------------------------------------------------------------
            // Single: Category (term)
            // -----------------------------------------------------------------
            $stack->field('default_category', [
                'type'  => 'select-wp-query',
                'label' => 'Default Category',
                'description' => 'Choose a category (taxonomy: category).',
                'attributes' => [
                    'source'    => 'term',
                    'taxonomy'  => 'category',
                    'placeholder' => 'Search categories...',
                ],
            ]);

            // -----------------------------------------------------------------
            // Single: User (author)
            // -----------------------------------------------------------------
            $stack->field('content_owner', [
                'type'  => 'select-wp-query',
                'label' => 'Content Owner',
                'description' => 'Choose a user (search by name or login).',
                'attributes' => [
                    'source' => 'user',
                    'placeholder' => 'Search users...',
                ],
            ]);

            // -----------------------------------------------------------------
            // Multiple: Categories
            // -----------------------------------------------------------------
            $stack->field('highlight_categories', [
                'type'  => 'select-wp-query',
                'label' => 'Highlight Categories',
                'description' => 'Select multiple categories to highlight.',
                'default' => [],
                'attributes' => [
                    'source'     => 'term',
                    'taxonomy'   => 'category',
                    'multiple'   => true,
                    'placeholder' => 'Search categories...',
                ],
            ]);

            // -----------------------------------------------------------------
            // Optional: Custom post type (e.g. 'product' – must be registered)
            // -----------------------------------------------------------------
            $stack->field('featured_product', [
                'type'  => 'select-wp-query',
                'label' => 'Featured Product (CPT)',
                'description' => 'Uses post type "product". Register that CPT or change to another.',
                'attributes' => [
                    'source'     => 'post',
                    'post_type'  => 'product',
                    'placeholder' => 'Search products...',
                ],
            ]);
        })
        ->build();
});

/*
 * Retrieving values (IDs):
 *
 * $options = get_option('select_wp_query_demo', []);
 *
 * $page_id    = (int) ($options['landing_page'] ?? 0);
 * $post_id    = (int) ($options['featured_post'] ?? 0);
 * $term_id    = (int) ($options['default_category'] ?? 0);
 * $user_id    = (int) ($options['content_owner'] ?? 0);
 * $cat_ids    = array_map('intval', (array) ($options['highlight_categories'] ?? []));
 *
 * if ($page_id)  $url = get_permalink($page_id);
 * if ($term_id)  $term = get_term($term_id, 'category');
 * if ($user_id)  $user = get_user_by('id', $user_id);
 */
