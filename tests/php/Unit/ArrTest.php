<?php
/**
 * Arr Helper Unit Tests
 *
 * Tests for the Arr support class covering:
 * - Dot notation get/set/has/forget
 * - Array flattening and unflattening
 * - Filtering and search helpers
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Support\Arr;

class ArrTest extends TestCase
{
    // =========================================================================
    // get() Tests
    // =========================================================================

    /**
     * Test get simple key.
     */
    public function test_get_simple_key(): void
    {
        $array = ['name' => 'John', 'age' => 30];

        $this->assertEquals('John', Arr::get($array, 'name'));
        $this->assertEquals(30, Arr::get($array, 'age'));
    }

    /**
     * Test get nested key.
     */
    public function test_get_nested_key(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        $this->assertEquals('John', Arr::get($array, 'user.name'));
        $this->assertEquals('john@example.com', Arr::get($array, 'user.email'));
    }

    /**
     * Test get deeply nested key.
     */
    public function test_get_deeply_nested_key(): void
    {
        $array = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $this->assertEquals('#3b82f6', Arr::get($array, 'config.theme.colors.primary'));
    }

    /**
     * Test get returns default for missing key.
     */
    public function test_get_returns_default_for_missing(): void
    {
        $array = ['name' => 'John'];

        $this->assertNull(Arr::get($array, 'missing'));
        $this->assertEquals('default', Arr::get($array, 'missing', 'default'));
        $this->assertEquals(0, Arr::get($array, 'missing', 0));
    }

    /**
     * Test get returns default for missing nested key.
     */
    public function test_get_returns_default_for_missing_nested(): void
    {
        $array = ['user' => ['name' => 'John']];

        $this->assertNull(Arr::get($array, 'user.email'));
        $this->assertNull(Arr::get($array, 'user.profile.avatar'));
        $this->assertEquals('N/A', Arr::get($array, 'user.email', 'N/A'));
    }

    /**
     * Test get with key that exists directly.
     */
    public function test_get_direct_key_takes_precedence(): void
    {
        $array = [
            'user.name' => 'Direct',
            'user' => ['name' => 'Nested'],
        ];

        $this->assertEquals('Direct', Arr::get($array, 'user.name'));
    }

    // =========================================================================
    // set() Tests
    // =========================================================================

    /**
     * Test set simple key.
     */
    public function test_set_simple_key(): void
    {
        $array = [];
        Arr::set($array, 'name', 'John');

        $this->assertEquals(['name' => 'John'], $array);
    }

    /**
     * Test set nested key.
     */
    public function test_set_nested_key(): void
    {
        $array = [];
        Arr::set($array, 'user.name', 'John');

        $this->assertEquals(['user' => ['name' => 'John']], $array);
    }

    /**
     * Test set deeply nested key.
     */
    public function test_set_deeply_nested_key(): void
    {
        $array = [];
        Arr::set($array, 'config.theme.colors.primary', '#3b82f6');

        $expected = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Test set overwrites existing value.
     */
    public function test_set_overwrites_existing(): void
    {
        $array = ['name' => 'John'];
        Arr::set($array, 'name', 'Jane');

        $this->assertEquals('Jane', $array['name']);
    }

    /**
     * Test set preserves existing sibling keys.
     */
    public function test_set_preserves_siblings(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        Arr::set($array, 'user.email', 'john@example.com');

        $this->assertEquals('John', $array['user']['name']);
        $this->assertEquals(30, $array['user']['age']);
        $this->assertEquals('john@example.com', $array['user']['email']);
    }

    // =========================================================================
    // has() Tests
    // =========================================================================

    /**
     * Test has simple key.
     */
    public function test_has_simple_key(): void
    {
        $array = ['name' => 'John', 'active' => false];

        $this->assertTrue(Arr::has($array, 'name'));
        $this->assertTrue(Arr::has($array, 'active')); // false is a value
        $this->assertFalse(Arr::has($array, 'missing'));
    }

    /**
     * Test has nested key.
     */
    public function test_has_nested_key(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'bio' => null,
                ],
            ],
        ];

        $this->assertTrue(Arr::has($array, 'user.name'));
        $this->assertTrue(Arr::has($array, 'user.profile.bio')); // null is a value
        $this->assertFalse(Arr::has($array, 'user.email'));
        $this->assertFalse(Arr::has($array, 'user.profile.avatar'));
    }

    /**
     * Test has with direct key.
     */
    public function test_has_direct_key(): void
    {
        $array = ['user.name' => 'Direct'];

        $this->assertTrue(Arr::has($array, 'user.name'));
    }

    // =========================================================================
    // forget() Tests
    // =========================================================================

    /**
     * Test forget simple key.
     */
    public function test_forget_simple_key(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        Arr::forget($array, 'name');

        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
    }

    /**
     * Test forget nested key.
     */
    public function test_forget_nested_key(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        Arr::forget($array, 'user.email');

        $this->assertArrayHasKey('name', $array['user']);
        $this->assertArrayNotHasKey('email', $array['user']);
    }

    /**
     * Test forget multiple keys.
     */
    public function test_forget_multiple_keys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        Arr::forget($array, ['a', 'c']);

        $this->assertArrayNotHasKey('a', $array);
        $this->assertArrayHasKey('b', $array);
        $this->assertArrayNotHasKey('c', $array);
    }

    /**
     * Test forget non-existent key does nothing.
     */
    public function test_forget_nonexistent_key(): void
    {
        $array = ['name' => 'John'];
        Arr::forget($array, 'missing');

        $this->assertEquals(['name' => 'John'], $array);
    }

    // =========================================================================
    // dot() Tests
    // =========================================================================

    /**
     * Test dot flattens array.
     */
    public function test_dot_flattens_array(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        $expected = [
            'user.name' => 'John',
            'user.email' => 'john@example.com',
        ];

        $this->assertEquals($expected, Arr::dot($array));
    }

    /**
     * Test dot with deeply nested array.
     */
    public function test_dot_deeply_nested(): void
    {
        $array = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                        'secondary' => '#8b5cf6',
                    ],
                ],
            ],
        ];

        $expected = [
            'config.theme.colors.primary' => '#3b82f6',
            'config.theme.colors.secondary' => '#8b5cf6',
        ];

        $this->assertEquals($expected, Arr::dot($array));
    }

    /**
     * Test dot with prepend.
     */
    public function test_dot_with_prepend(): void
    {
        $array = ['name' => 'John'];

        $this->assertEquals(['prefix.name' => 'John'], Arr::dot($array, 'prefix.'));
    }

    /**
     * Test dot with empty array value.
     */
    public function test_dot_empty_array(): void
    {
        $array = ['items' => []];

        $this->assertEquals(['items' => []], Arr::dot($array));
    }

    // =========================================================================
    // undot() Tests
    // =========================================================================

    /**
     * Test undot expands array.
     */
    public function test_undot_expands_array(): void
    {
        $array = [
            'user.name' => 'John',
            'user.email' => 'john@example.com',
        ];

        $expected = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ];

        $this->assertEquals($expected, Arr::undot($array));
    }

    /**
     * Test undot with deeply nested keys.
     */
    public function test_undot_deeply_nested(): void
    {
        $array = [
            'config.theme.colors.primary' => '#3b82f6',
        ];

        $expected = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::undot($array));
    }

    // =========================================================================
    // where() Tests
    // =========================================================================

    /**
     * Test where filters array.
     */
    public function test_where_filters_array(): void
    {
        $array = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
        ];

        $result = Arr::where($array, fn($value) => $value > 2);

        $this->assertEquals(['c' => 3, 'd' => 4], $result);
    }

    /**
     * Test where with key access.
     */
    public function test_where_with_key(): void
    {
        $array = [
            'admin' => true,
            'editor' => true,
            'subscriber' => false,
        ];

        $result = Arr::where($array, fn($value, $key) => $key !== 'subscriber');

        $this->assertEquals(['admin' => true, 'editor' => true], $result);
    }

    // =========================================================================
    // first() Tests
    // =========================================================================

    /**
     * Test first returns first element.
     */
    public function test_first_returns_first(): void
    {
        $array = ['a', 'b', 'c'];

        $this->assertEquals('a', Arr::first($array));
    }

    /**
     * Test first with callback.
     */
    public function test_first_with_callback(): void
    {
        $array = [1, 2, 3, 4, 5];

        $result = Arr::first($array, fn($value) => $value > 3);

        $this->assertEquals(4, $result);
    }

    /**
     * Test first returns default when empty.
     */
    public function test_first_returns_default_when_empty(): void
    {
        $this->assertEquals('default', Arr::first([], null, 'default'));
    }

    /**
     * Test first returns default when no match.
     */
    public function test_first_returns_default_when_no_match(): void
    {
        $array = [1, 2, 3];

        $result = Arr::first($array, fn($value) => $value > 10, 'not found');

        $this->assertEquals('not found', $result);
    }

    // =========================================================================
    // last() Tests
    // =========================================================================

    /**
     * Test last returns last element.
     */
    public function test_last_returns_last(): void
    {
        $array = ['a', 'b', 'c'];

        $this->assertEquals('c', Arr::last($array));
    }

    /**
     * Test last with callback.
     */
    public function test_last_with_callback(): void
    {
        $array = [1, 2, 3, 4, 5];

        $result = Arr::last($array, fn($value) => $value < 4);

        $this->assertEquals(3, $result);
    }

    /**
     * Test last returns default when empty.
     */
    public function test_last_returns_default_when_empty(): void
    {
        $this->assertEquals('default', Arr::last([], null, 'default'));
    }

    // =========================================================================
    // only() Tests
    // =========================================================================

    /**
     * Test only returns subset.
     */
    public function test_only_returns_subset(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertEquals(['a' => 1, 'c' => 3], Arr::only($array, ['a', 'c']));
    }

    /**
     * Test only ignores missing keys.
     */
    public function test_only_ignores_missing(): void
    {
        $array = ['a' => 1, 'b' => 2];

        $this->assertEquals(['a' => 1], Arr::only($array, ['a', 'missing']));
    }

    // =========================================================================
    // except() Tests
    // =========================================================================

    /**
     * Test except removes keys.
     */
    public function test_except_removes_keys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertEquals(['b' => 2], Arr::except($array, ['a', 'c']));
    }

    // =========================================================================
    // mergeRecursive() Tests
    // =========================================================================

    /**
     * Test merge recursive.
     */
    public function test_merge_recursive(): void
    {
        $array1 = [
            'user' => [
                'name' => 'John',
                'settings' => [
                    'theme' => 'dark',
                ],
            ],
        ];

        $array2 = [
            'user' => [
                'email' => 'john@example.com',
                'settings' => [
                    'notifications' => true,
                ],
            ],
        ];

        $expected = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => true,
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::mergeRecursive($array1, $array2));
    }

    /**
     * Test merge recursive overwrites non-array values.
     */
    public function test_merge_recursive_overwrites_values(): void
    {
        $array1 = ['name' => 'John'];
        $array2 = ['name' => 'Jane'];

        $this->assertEquals(['name' => 'Jane'], Arr::mergeRecursive($array1, $array2));
    }
}
