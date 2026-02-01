<?php
/**
 * Field Unit Tests
 *
 * Tests for the Field class covering:
 * - Field creation and configuration
 * - Default value handling
 * - Searchable field validation
 * - Serialization (toArray)
 * - Condition assignment
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Field\Field;
use OptStack\Core\Condition\Condition;

class FieldTest extends TestCase
{
    /**
     * Test basic field creation with minimal config.
     */
    public function test_creates_field_with_key(): void
    {
        $field = new Field('my_field');

        $this->assertEquals('my_field', $field->getKey());
        $this->assertEquals('text', $field->getType());
    }

    /**
     * Test field creation with full configuration.
     */
    public function test_creates_field_with_full_config(): void
    {
        $field = new Field('price', [
            'type' => 'number',
            'label' => 'Product Price',
            'description' => 'Enter the product price',
            'default' => 99.99,
        ]);

        $this->assertEquals('price', $field->getKey());
        $this->assertEquals('number', $field->getType());
        $this->assertEquals('Product Price', $field->getLabel());
        $this->assertEquals('Enter the product price', $field->getDescription());
        $this->assertEquals(99.99, $field->getDefault());
    }

    /**
     * Test auto-generated label from field key.
     */
    public function test_generates_label_from_key(): void
    {
        $field = new Field('my_field_name');

        $this->assertEquals('My Field Name', $field->getLabel());
    }

    /**
     * Test auto-generated label with hyphen separator.
     */
    public function test_generates_label_from_hyphenated_key(): void
    {
        $field = new Field('my-field-name');

        $this->assertEquals('My Field Name', $field->getLabel());
    }

    /**
     * Test default value is null when not provided.
     */
    public function test_default_value_is_null_when_not_provided(): void
    {
        $field = new Field('test_field');

        $this->assertNull($field->getDefault());
    }

    /**
     * Test field with options (select/radio).
     */
    public function test_field_with_options(): void
    {
        $options = [
            ['value' => 'small', 'label' => 'Small'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'large', 'label' => 'Large'],
        ];

        $field = new Field('size', [
            'type' => 'select',
            'options' => $options,
        ]);

        $this->assertEquals($options, $field->getOptions());
    }

    /**
     * Test field with attributes.
     */
    public function test_field_with_attributes(): void
    {
        $attributes = [
            'min' => 0,
            'max' => 100,
            'step' => 5,
            'suffix' => 'px',
        ];

        $field = new Field('width', [
            'type' => 'number',
            'attributes' => $attributes,
        ]);

        $this->assertEquals($attributes, $field->getAttributes());
    }

    /**
     * Test searchable field creation.
     */
    public function test_searchable_field_creation(): void
    {
        $field = new Field('title', [
            'type' => 'text',
            'searchable' => true,
        ]);

        $this->assertTrue($field->isSearchable());
    }

    /**
     * Test non-searchable field by default.
     */
    public function test_field_not_searchable_by_default(): void
    {
        $field = new Field('title', ['type' => 'text']);

        $this->assertFalse($field->isSearchable());
    }

    /**
     * Test searchable can be set via method.
     */
    public function test_set_searchable_via_method(): void
    {
        $field = new Field('email', ['type' => 'email']);
        $field->setSearchable(true);

        $this->assertTrue($field->isSearchable());
    }

    /**
     * Test invalid searchable type throws exception.
     */
    public function test_invalid_searchable_type_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field type "wysiwyg" cannot be searchable');

        $field = new Field('content', ['type' => 'wysiwyg']);
        $field->setSearchable(true);
    }

    /**
     * Test static method for checking searchable types.
     */
    public function test_is_type_searchable_static_method(): void
    {
        $this->assertTrue(Field::isTypeSearchable('text'));
        $this->assertTrue(Field::isTypeSearchable('number'));
        $this->assertTrue(Field::isTypeSearchable('select'));
        $this->assertTrue(Field::isTypeSearchable('toggle'));
        $this->assertTrue(Field::isTypeSearchable('email'));
        $this->assertTrue(Field::isTypeSearchable('url'));
        $this->assertTrue(Field::isTypeSearchable('color'));
        $this->assertTrue(Field::isTypeSearchable('date'));

        $this->assertFalse(Field::isTypeSearchable('wysiwyg'));
        $this->assertFalse(Field::isTypeSearchable('media'));
        $this->assertFalse(Field::isTypeSearchable('code'));
        $this->assertFalse(Field::isTypeSearchable('typography'));
    }

    /**
     * Test field toArray serialization with minimal config.
     */
    public function test_to_array_minimal(): void
    {
        $field = new Field('simple', ['type' => 'text']);
        $array = $field->toArray();

        $this->assertEquals('simple', $array['key']);
        $this->assertEquals('text', $array['type']);
        $this->assertEquals('Simple', $array['label']);
        $this->assertArrayNotHasKey('default', $array);
        $this->assertArrayNotHasKey('description', $array);
    }

    /**
     * Test field toArray serialization with full config.
     */
    public function test_to_array_full(): void
    {
        $field = new Field('price', [
            'type' => 'number',
            'label' => 'Price',
            'description' => 'Product price',
            'default' => 100,
            'options' => [['value' => '1', 'label' => 'One']],
            'attributes' => ['min' => 0],
            'searchable' => true,
        ]);

        $array = $field->toArray();

        $this->assertEquals('price', $array['key']);
        $this->assertEquals('number', $array['type']);
        $this->assertEquals('Price', $array['label']);
        $this->assertEquals('Product price', $array['description']);
        $this->assertEquals(100, $array['default']);
        $this->assertArrayHasKey('options', $array);
        $this->assertArrayHasKey('attributes', $array);
        $this->assertTrue($array['searchable']);
    }

    /**
     * Test field with conditions from array.
     */
    public function test_field_with_conditions_from_array(): void
    {
        $field = new Field('message', [
            'type' => 'textarea',
            'conditions' => [
                ['field' => 'show_message', 'operator' => '==', 'value' => true],
            ],
        ]);

        $this->assertTrue($field->hasConditions());
        $this->assertCount(1, $field->getConditions());
        $this->assertInstanceOf(Condition::class, $field->getConditions()[0]);
    }

    /**
     * Test field with multiple conditions.
     */
    public function test_field_with_multiple_conditions(): void
    {
        $field = new Field('advanced_option', [
            'type' => 'text',
            'conditions' => [
                ['field' => 'enable_advanced', 'operator' => '==', 'value' => true],
                ['field' => 'user_level', 'operator' => '>=', 'value' => 5],
            ],
        ]);

        $this->assertTrue($field->hasConditions());
        $this->assertCount(2, $field->getConditions());
    }

    /**
     * Test conditions included in toArray.
     */
    public function test_conditions_in_to_array(): void
    {
        $field = new Field('conditional_field', [
            'type' => 'text',
            'conditions' => [
                ['field' => 'enable', 'operator' => '==', 'value' => true],
            ],
        ]);

        $array = $field->toArray();

        $this->assertArrayHasKey('conditions', $array);
        $this->assertCount(1, $array['conditions']);
        $this->assertEquals('enable', $array['conditions'][0]['field']);
        $this->assertEquals('==', $array['conditions'][0]['operator']);
        $this->assertTrue($array['conditions'][0]['value']);
    }

    /**
     * Test set conditions via method.
     */
    public function test_set_conditions_via_method(): void
    {
        $field = new Field('test', ['type' => 'text']);

        $this->assertFalse($field->hasConditions());

        $field->setConditions([
            ['field' => 'other', 'operator' => '!=', 'value' => 'none'],
        ]);

        $this->assertTrue($field->hasConditions());
        $this->assertCount(1, $field->getConditions());
    }

    /**
     * Test sanitize callback is stored.
     */
    public function test_sanitize_callback_stored(): void
    {
        $sanitizer = fn($value) => trim($value);

        $field = new Field('name', [
            'type' => 'text',
            'sanitize' => $sanitizer,
        ]);

        $this->assertSame($sanitizer, $field->getSanitize());
    }

    /**
     * Test validate callback is stored.
     */
    public function test_validate_callback_stored(): void
    {
        $validator = fn($value) => strlen($value) >= 3;

        $field = new Field('username', [
            'type' => 'text',
            'validate' => $validator,
        ]);

        $this->assertSame($validator, $field->getValidate());
    }
}
