<?php
/**
 * FieldGroup Unit Tests
 *
 * Tests for the FieldGroup class covering:
 * - Group creation and configuration
 * - Repeatable groups
 * - Nested groups
 * - Deferred rendering
 * - Serialization (toArray)
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Field\FieldGroup;
use OptStack\Core\Field\Field;
use OptStack\Core\Condition\Condition;

class FieldGroupTest extends TestCase
{
    /**
     * Test basic group creation.
     */
    public function test_creates_group_with_key(): void
    {
        $group = new FieldGroup('settings');

        $this->assertEquals('settings', $group->getKey());
        $this->assertEquals('Settings', $group->getLabel());
    }

    /**
     * Test group with custom label.
     */
    public function test_group_with_custom_label(): void
    {
        $group = new FieldGroup('seo', [
            'label' => 'SEO Settings',
            'description' => 'Search engine optimization options',
        ]);

        $this->assertEquals('SEO Settings', $group->getLabel());
        $this->assertEquals('Search engine optimization options', $group->getDescription());
    }

    /**
     * Test auto-generated label from key.
     */
    public function test_generates_label_from_key(): void
    {
        $group = new FieldGroup('social_media_links');

        $this->assertEquals('Social Media Links', $group->getLabel());
    }

    /**
     * Test group is not repeatable by default.
     */
    public function test_group_not_repeatable_by_default(): void
    {
        $group = new FieldGroup('basic');

        $this->assertFalse($group->isRepeatable());
        $this->assertEquals(0, $group->getMinItems());
        $this->assertEquals(0, $group->getMaxItems());
    }

    /**
     * Test group repeatable via config.
     */
    public function test_group_repeatable_via_config(): void
    {
        $group = new FieldGroup('items', [
            'repeatable' => true,
            'min_items' => 1,
            'max_items' => 10,
        ]);

        $this->assertTrue($group->isRepeatable());
        $this->assertEquals(1, $group->getMinItems());
        $this->assertEquals(10, $group->getMaxItems());
    }

    /**
     * Test group repeatable via method.
     */
    public function test_group_repeatable_via_method(): void
    {
        $group = new FieldGroup('links');
        $group->repeatable(0, 15);

        $this->assertTrue($group->isRepeatable());
        $this->assertEquals(0, $group->getMinItems());
        $this->assertEquals(15, $group->getMaxItems());
    }

    /**
     * Test repeatable method returns self for chaining.
     */
    public function test_repeatable_method_returns_self(): void
    {
        $group = new FieldGroup('items');
        $result = $group->repeatable(1, 5);

        $this->assertSame($group, $result);
    }

    /**
     * Test group collapsible setting.
     */
    public function test_group_collapsible(): void
    {
        $group = new FieldGroup('advanced', [
            'collapsible' => true,
        ]);

        $this->assertTrue($group->isCollapsible());
    }

    /**
     * Test group layout setting.
     */
    public function test_group_layout(): void
    {
        $groupInline = new FieldGroup('inline_group', ['layout' => 'inline']);
        $groupBox = new FieldGroup('box_group', ['layout' => 'box']);

        $this->assertEquals('inline', $groupInline->getLayout());
        $this->assertEquals('box', $groupBox->getLayout());
    }

    /**
     * Test default layout is inline.
     */
    public function test_default_layout_is_inline(): void
    {
        $group = new FieldGroup('test');

        $this->assertEquals('inline', $group->getLayout());
    }

    /**
     * Test adding fields to group.
     */
    public function test_add_field_to_group(): void
    {
        $group = new FieldGroup('contact');
        $group->field('name', ['type' => 'text']);
        $group->field('email', ['type' => 'email']);

        $fields = $group->getFields();

        $this->assertCount(2, $fields->all());
    }

    /**
     * Test field method returns self for chaining.
     */
    public function test_field_method_returns_self(): void
    {
        $group = new FieldGroup('test');
        $result = $group->field('name', ['type' => 'text']);

        $this->assertSame($group, $result);
    }

    /**
     * Test adding nested groups.
     */
    public function test_add_nested_group(): void
    {
        $group = new FieldGroup('parent');
        $group->group('child', function ($child) {
            $child->field('name', ['type' => 'text']);
        });

        $groups = $group->getGroups();

        $this->assertCount(1, $groups);
        $this->assertArrayHasKey('child', $groups);
        $this->assertInstanceOf(FieldGroup::class, $groups['child']);
    }

    /**
     * Test nested group with config.
     */
    public function test_nested_group_with_config(): void
    {
        $group = new FieldGroup('parent');
        $group->group('child', function ($child) {
            $child->field('value', ['type' => 'number']);
        }, [
            'label' => 'Child Group',
            'collapsible' => true,
        ]);

        $groups = $group->getGroups();
        $child = $groups['child'];

        $this->assertEquals('Child Group', $child->getLabel());
        $this->assertTrue($child->isCollapsible());
    }

    /**
     * Test group method returns self for chaining.
     */
    public function test_group_method_returns_self(): void
    {
        $group = new FieldGroup('parent');
        $result = $group->group('child', null);

        $this->assertSame($group, $result);
    }

    /**
     * Test deferred group via config.
     */
    public function test_deferred_group_via_config(): void
    {
        $group = new FieldGroup('advanced', [
            'deferred' => true,
            'ui' => [
                'triggerLabel' => 'Configure Advanced',
                'render' => 'modal',
            ],
        ]);

        $this->assertTrue($group->isDeferred());
        $this->assertEquals('Configure Advanced', $group->getUi()['triggerLabel']);
        $this->assertEquals('modal', $group->getUi()['render']);
    }

    /**
     * Test deferred group via method.
     */
    public function test_deferred_group_via_method(): void
    {
        $group = new FieldGroup('settings');
        $group->deferred([
            'triggerLabel' => 'Open Settings',
            'render' => 'drawer',
        ]);

        $this->assertTrue($group->isDeferred());
        $this->assertEquals('Open Settings', $group->getUi()['triggerLabel']);
    }

    /**
     * Test deferred method returns self for chaining.
     */
    public function test_deferred_method_returns_self(): void
    {
        $group = new FieldGroup('test');
        $result = $group->deferred();

        $this->assertSame($group, $result);
    }

    /**
     * Test group not deferred by default.
     */
    public function test_group_not_deferred_by_default(): void
    {
        $group = new FieldGroup('basic');

        $this->assertFalse($group->isDeferred());
        $this->assertEmpty($group->getUi());
    }

    /**
     * Test group with conditions.
     */
    public function test_group_with_conditions(): void
    {
        $group = new FieldGroup('advanced', [
            'conditions' => [
                ['field' => 'show_advanced', 'operator' => '==', 'value' => true],
            ],
        ]);

        $this->assertTrue($group->hasConditions());
        $this->assertCount(1, $group->getConditions());
        $this->assertInstanceOf(Condition::class, $group->getConditions()[0]);
    }

    /**
     * Test set conditions via method.
     */
    public function test_set_conditions_via_method(): void
    {
        $group = new FieldGroup('conditional');

        $this->assertFalse($group->hasConditions());

        $group->setConditions([
            ['field' => 'enabled', 'operator' => '==', 'value' => true],
        ]);

        $this->assertTrue($group->hasConditions());
    }

    /**
     * Test fields callback method.
     */
    public function test_fields_callback_method(): void
    {
        $group = new FieldGroup('contact');
        $group->fields(function ($g) {
            $g->field('name', ['type' => 'text']);
            $g->field('email', ['type' => 'email']);
        });

        $this->assertCount(2, $group->getFields()->all());
    }

    /**
     * Test toArray serialization - basic.
     */
    public function test_to_array_basic(): void
    {
        $group = new FieldGroup('simple', [
            'label' => 'Simple Group',
        ]);
        $group->field('name', ['type' => 'text']);

        $array = $group->toArray();

        $this->assertEquals('simple', $array['key']);
        $this->assertEquals('Simple Group', $array['label']);
        $this->assertFalse($array['repeatable']);
        $this->assertArrayHasKey('fields', $array);
        $this->assertArrayHasKey('name', $array['fields']);
    }

    /**
     * Test toArray serialization - repeatable.
     */
    public function test_to_array_repeatable(): void
    {
        $group = new FieldGroup('items');
        $group->repeatable(1, 10);

        $array = $group->toArray();

        $this->assertTrue($array['repeatable']);
        $this->assertEquals(1, $array['minItems']);
        $this->assertEquals(10, $array['maxItems']);
    }

    /**
     * Test toArray serialization - collapsible and layout.
     */
    public function test_to_array_collapsible_and_layout(): void
    {
        $group = new FieldGroup('advanced', [
            'collapsible' => true,
            'layout' => 'box',
        ]);

        $array = $group->toArray();

        $this->assertTrue($array['collapsible']);
        $this->assertEquals('box', $array['layout']);
    }

    /**
     * Test toArray serialization - deferred.
     */
    public function test_to_array_deferred(): void
    {
        $group = new FieldGroup('deferred_group');
        $group->deferred([
            'triggerLabel' => 'Configure',
            'render' => 'modal',
        ]);

        $array = $group->toArray();

        $this->assertTrue($array['deferred']);
        $this->assertEquals('Configure', $array['ui']['triggerLabel']);
        $this->assertEquals('modal', $array['ui']['render']);
    }

    /**
     * Test toArray serialization - nested groups.
     */
    public function test_to_array_nested_groups(): void
    {
        $group = new FieldGroup('parent');
        $group->field('parent_field', ['type' => 'text']);
        $group->group('child', function ($child) {
            $child->field('child_field', ['type' => 'number']);
        });

        $array = $group->toArray();

        $this->assertArrayHasKey('fields', $array);
        $this->assertArrayHasKey('groups', $array);
        $this->assertArrayHasKey('child', $array['groups']);
        $this->assertArrayHasKey('child_field', $array['groups']['child']['fields']);
    }

    /**
     * Test toArray serialization - conditions.
     */
    public function test_to_array_conditions(): void
    {
        $group = new FieldGroup('conditional', [
            'conditions' => [
                ['field' => 'show', 'operator' => '==', 'value' => true],
            ],
        ]);

        $array = $group->toArray();

        $this->assertArrayHasKey('conditions', $array);
        $this->assertCount(1, $array['conditions']);
        $this->assertEquals('show', $array['conditions'][0]['field']);
    }

    /**
     * Test inline layout not included in toArray when default.
     */
    public function test_inline_layout_not_in_to_array(): void
    {
        $group = new FieldGroup('test');

        $array = $group->toArray();

        $this->assertArrayNotHasKey('layout', $array);
    }

    /**
     * Test description included in toArray when set.
     */
    public function test_description_in_to_array(): void
    {
        $group = new FieldGroup('test', [
            'description' => 'Help text for the group',
        ]);

        $array = $group->toArray();

        $this->assertEquals('Help text for the group', $array['description']);
    }
}
