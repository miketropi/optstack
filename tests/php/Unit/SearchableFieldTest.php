<?php
/**
 * SearchableField Unit Tests
 *
 * Tests for the SearchableField and SearchableFieldResolver classes covering:
 * - SearchableField data object
 * - Meta key generation
 * - Value extraction
 * - Context normalization
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Field\Field;
use OptStack\Core\Index\SearchableField;
use OptStack\Core\Index\SearchableFieldResolver;

class SearchableFieldTest extends TestCase
{
    private SearchableFieldResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SearchableFieldResolver();
    }

    // =========================================================================
    // SearchableField Data Object Tests
    // =========================================================================

    /**
     * Test SearchableField creation.
     */
    public function test_searchable_field_creation(): void
    {
        $field = new Field('title', ['type' => 'text', 'searchable' => true]);
        $searchable = new SearchableField($field, 'seo.title', '_optstack_idx_post_seo_title');

        $this->assertSame($field, $searchable->getField());
        $this->assertEquals('title', $searchable->getKey());
        $this->assertEquals('text', $searchable->getType());
        $this->assertEquals('seo.title', $searchable->getPath());
        $this->assertEquals('_optstack_idx_post_seo_title', $searchable->getMetaKey());
    }

    /**
     * Test SearchableField toArray.
     */
    public function test_searchable_field_to_array(): void
    {
        $field = new Field('price', ['type' => 'number', 'searchable' => true]);
        $searchable = new SearchableField($field, 'product.price', '_optstack_idx_post_product_price');

        $array = $searchable->toArray();

        $this->assertEquals([
            'key' => 'price',
            'type' => 'number',
            'path' => 'product.price',
            'metaKey' => '_optstack_idx_post_product_price',
        ], $array);
    }

    // =========================================================================
    // SearchableFieldResolver Tests
    // =========================================================================

    /**
     * Test meta key generation with simple field.
     */
    public function test_generate_meta_key_simple(): void
    {
        $metaKey = $this->resolver->generateMetaKey('post', 'title');

        $this->assertEquals('_optstack_idx_post_title', $metaKey);
    }

    /**
     * Test meta key generation with nested path.
     */
    public function test_generate_meta_key_nested(): void
    {
        $metaKey = $this->resolver->generateMetaKey('post', 'seo.title');

        $this->assertEquals('_optstack_idx_post_seo_title', $metaKey);
    }

    /**
     * Test meta key generation with deeply nested path.
     */
    public function test_generate_meta_key_deeply_nested(): void
    {
        $metaKey = $this->resolver->generateMetaKey('term', 'settings.display.options.color');

        $this->assertEquals('_optstack_idx_term_settings_display_options_color', $metaKey);
    }

    /**
     * Test meta key generation for different contexts.
     */
    public function test_generate_meta_key_contexts(): void
    {
        $this->assertEquals(
            '_optstack_idx_post_field',
            $this->resolver->generateMetaKey('post', 'field')
        );

        $this->assertEquals(
            '_optstack_idx_term_field',
            $this->resolver->generateMetaKey('term', 'field')
        );

        $this->assertEquals(
            '_optstack_idx_user_field',
            $this->resolver->generateMetaKey('user', 'field')
        );

        $this->assertEquals(
            '_optstack_idx_options_field',
            $this->resolver->generateMetaKey('options', 'field')
        );
    }

    /**
     * Test meta prefix constant.
     */
    public function test_meta_prefix_constant(): void
    {
        $this->assertEquals('_optstack_idx', SearchableFieldResolver::META_PREFIX);
    }

    // =========================================================================
    // Value Extraction Tests
    // =========================================================================

    /**
     * Test extract simple value.
     */
    public function test_extract_value_simple(): void
    {
        $data = ['title' => 'Hello World'];

        $this->assertEquals('Hello World', $this->resolver->extractValue($data, 'title'));
    }

    /**
     * Test extract nested value.
     */
    public function test_extract_value_nested(): void
    {
        $data = [
            'seo' => [
                'title' => 'SEO Title',
                'description' => 'SEO Description',
            ],
        ];

        $this->assertEquals('SEO Title', $this->resolver->extractValue($data, 'seo.title'));
        $this->assertEquals('SEO Description', $this->resolver->extractValue($data, 'seo.description'));
    }

    /**
     * Test extract deeply nested value.
     */
    public function test_extract_value_deeply_nested(): void
    {
        $data = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            '#3b82f6',
            $this->resolver->extractValue($data, 'config.theme.colors.primary')
        );
    }

    /**
     * Test extract missing value returns null.
     */
    public function test_extract_value_missing(): void
    {
        $data = ['title' => 'Hello'];

        $this->assertNull($this->resolver->extractValue($data, 'missing'));
        $this->assertNull($this->resolver->extractValue($data, 'nested.missing'));
    }

    /**
     * Test extract value from partial path returns null.
     */
    public function test_extract_value_partial_path(): void
    {
        $data = [
            'seo' => [
                'title' => 'SEO Title',
            ],
        ];

        $this->assertNull($this->resolver->extractValue($data, 'seo.missing.field'));
    }

    /**
     * Test extract value with empty data.
     */
    public function test_extract_value_empty_data(): void
    {
        $this->assertNull($this->resolver->extractValue([], 'any.path'));
    }

    /**
     * Test extract value preserves type.
     */
    public function test_extract_value_preserves_type(): void
    {
        $data = [
            'string' => 'text',
            'number' => 42,
            'float' => 3.14,
            'boolean' => true,
            'array' => [1, 2, 3],
            'null' => null,
        ];

        $this->assertIsString($this->resolver->extractValue($data, 'string'));
        $this->assertIsInt($this->resolver->extractValue($data, 'number'));
        $this->assertIsFloat($this->resolver->extractValue($data, 'float'));
        $this->assertIsBool($this->resolver->extractValue($data, 'boolean'));
        $this->assertIsArray($this->resolver->extractValue($data, 'array'));
        $this->assertNull($this->resolver->extractValue($data, 'null'));
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    /**
     * Test meta key with special characters in field name.
     */
    public function test_generate_meta_key_special_characters(): void
    {
        // Dots are converted to underscores
        $this->assertEquals(
            '_optstack_idx_post_field_with_dots',
            $this->resolver->generateMetaKey('post', 'field.with.dots')
        );
    }

    /**
     * Test extract value with numeric array key.
     */
    public function test_extract_value_numeric_key(): void
    {
        $data = [
            'items' => [
                0 => 'first',
                1 => 'second',
            ],
        ];

        $this->assertEquals('first', $this->resolver->extractValue($data, 'items.0'));
        $this->assertEquals('second', $this->resolver->extractValue($data, 'items.1'));
    }
}
