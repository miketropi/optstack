<?php
/**
 * ThemeModStore Unit Tests
 *
 * Tests for the ThemeModStore class. Requires WordPress (get_theme_mod/set_theme_mod).
 * Skips when not in WordPress environment.
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\WordPress\Store\ThemeModStore;

class ThemeModStoreTest extends TestCase
{
    private const TEST_KEY = 'optstack_test_theme_mod_store_' . self::class;

    private ThemeModStore $store;

    protected function setUp(): void
    {
        parent::setUp();
        if (!function_exists('get_theme_mod')) {
            $this->markTestSkipped('WordPress theme mod functions not available');
        }
        $this->store = new ThemeModStore(self::TEST_KEY);
        $this->store->deleteAll();
    }

    protected function tearDown(): void
    {
        if (isset($this->store)) {
            $this->store->deleteAll();
            $this->store->clearCache();
        }
        parent::tearDown();
    }

    public function test_get_returns_default_when_empty(): void
    {
        $this->assertNull($this->store->get('missing', null));
        $this->assertSame('default', $this->store->get('missing', 'default'));
    }

    public function test_set_and_get(): void
    {
        $this->assertTrue($this->store->set('name', 'Test'));
        $this->assertSame('Test', $this->store->get('name'));
    }

    public function test_has(): void
    {
        $this->assertFalse($this->store->has('name'));
        $this->store->set('name', 'Test');
        $this->assertTrue($this->store->has('name'));
    }

    public function test_delete(): void
    {
        $this->store->set('name', 'Test');
        $this->assertTrue($this->store->delete('name'));
        $this->assertFalse($this->store->has('name'));
        $this->assertNull($this->store->get('name'));
    }

    public function test_all_returns_full_array(): void
    {
        $this->store->set('a', 1);
        $this->store->set('b', 'two');
        $all = $this->store->all();
        $this->assertIsArray($all);
        $this->assertSame(1, $all['a']);
        $this->assertSame('two', $all['b']);
    }

    public function test_setMany(): void
    {
        $this->store->setMany(['x' => 10, 'y' => 20]);
        $this->assertSame(10, $this->store->get('x'));
        $this->assertSame(20, $this->store->get('y'));
    }

    public function test_replace_overwrites_all(): void
    {
        $this->store->set('old', 'value');
        $this->store->replace(['new' => 'only']);
        $this->assertFalse($this->store->has('old'));
        $this->assertSame('only', $this->store->get('new'));
    }

    public function test_getKey_returns_constructor_key(): void
    {
        $this->assertSame(self::TEST_KEY, $this->store->getKey());
    }
}
