<?php
/**
 * Condition Unit Tests
 *
 * Tests for the Condition class covering:
 * - Condition creation
 * - All comparison operators
 * - Nested value evaluation
 * - Serialization (toArray)
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Condition\Condition;

class ConditionTest extends TestCase
{
    /**
     * Test basic condition creation.
     */
    public function test_creates_condition(): void
    {
        $condition = new Condition('enabled', '==', true);

        $this->assertEquals('enabled', $condition->getField());
        $this->assertEquals('==', $condition->getOperator());
        $this->assertTrue($condition->getValue());
        $this->assertEquals('AND', $condition->getRelation());
    }

    /**
     * Test condition with custom relation.
     */
    public function test_condition_with_or_relation(): void
    {
        $condition = new Condition('status', '==', 'active', 'OR');

        $this->assertEquals('OR', $condition->getRelation());
    }

    /**
     * Test relation is uppercased.
     */
    public function test_relation_is_uppercased(): void
    {
        $condition = new Condition('field', '==', 'value', 'or');

        $this->assertEquals('OR', $condition->getRelation());
    }

    /**
     * Test fromArray factory method.
     */
    public function test_from_array(): void
    {
        $condition = Condition::fromArray([
            'field' => 'status',
            'operator' => '!=',
            'value' => 'inactive',
            'relation' => 'OR',
        ]);

        $this->assertEquals('status', $condition->getField());
        $this->assertEquals('!=', $condition->getOperator());
        $this->assertEquals('inactive', $condition->getValue());
        $this->assertEquals('OR', $condition->getRelation());
    }

    /**
     * Test fromArray with defaults.
     */
    public function test_from_array_with_defaults(): void
    {
        $condition = Condition::fromArray([
            'field' => 'name',
        ]);

        $this->assertEquals('name', $condition->getField());
        $this->assertEquals('==', $condition->getOperator());
        $this->assertNull($condition->getValue());
        $this->assertEquals('AND', $condition->getRelation());
    }

    /**
     * Test toArray serialization.
     */
    public function test_to_array(): void
    {
        $condition = new Condition('active', '==', true, 'AND');
        $array = $condition->toArray();

        $this->assertEquals([
            'field' => 'active',
            'operator' => '==',
            'value' => true,
            'relation' => 'AND',
        ], $array);
    }

    // =========================================================================
    // Operator Tests - evaluate()
    // =========================================================================

    /**
     * Test equals operator with boolean.
     */
    public function test_evaluate_equals_boolean(): void
    {
        $condition = new Condition('enabled', '==', true);

        $this->assertTrue($condition->evaluate(['enabled' => true]));
        $this->assertFalse($condition->evaluate(['enabled' => false]));
    }

    /**
     * Test equals operator with string.
     */
    public function test_evaluate_equals_string(): void
    {
        $condition = new Condition('status', '==', 'active');

        $this->assertTrue($condition->evaluate(['status' => 'active']));
        $this->assertFalse($condition->evaluate(['status' => 'inactive']));
    }

    /**
     * Test equals operator with number.
     */
    public function test_evaluate_equals_number(): void
    {
        $condition = new Condition('count', '==', 5);

        $this->assertTrue($condition->evaluate(['count' => 5]));
        $this->assertTrue($condition->evaluate(['count' => '5'])); // Loose comparison
        $this->assertFalse($condition->evaluate(['count' => 4]));
    }

    /**
     * Test not equals operator.
     */
    public function test_evaluate_not_equals(): void
    {
        $condition = new Condition('type', '!=', 'hidden');

        $this->assertTrue($condition->evaluate(['type' => 'visible']));
        $this->assertFalse($condition->evaluate(['type' => 'hidden']));
    }

    /**
     * Test greater than operator.
     */
    public function test_evaluate_greater_than(): void
    {
        $condition = new Condition('price', '>', 100);

        $this->assertTrue($condition->evaluate(['price' => 150]));
        $this->assertFalse($condition->evaluate(['price' => 100]));
        $this->assertFalse($condition->evaluate(['price' => 50]));
    }

    /**
     * Test less than operator.
     */
    public function test_evaluate_less_than(): void
    {
        $condition = new Condition('quantity', '<', 10);

        $this->assertTrue($condition->evaluate(['quantity' => 5]));
        $this->assertFalse($condition->evaluate(['quantity' => 10]));
        $this->assertFalse($condition->evaluate(['quantity' => 15]));
    }

    /**
     * Test greater than or equal operator.
     */
    public function test_evaluate_greater_or_equal(): void
    {
        $condition = new Condition('age', '>=', 18);

        $this->assertTrue($condition->evaluate(['age' => 18]));
        $this->assertTrue($condition->evaluate(['age' => 25]));
        $this->assertFalse($condition->evaluate(['age' => 17]));
    }

    /**
     * Test less than or equal operator.
     */
    public function test_evaluate_less_or_equal(): void
    {
        $condition = new Condition('level', '<=', 5);

        $this->assertTrue($condition->evaluate(['level' => 5]));
        $this->assertTrue($condition->evaluate(['level' => 3]));
        $this->assertFalse($condition->evaluate(['level' => 6]));
    }

    /**
     * Test contains operator.
     */
    public function test_evaluate_contains(): void
    {
        $condition = new Condition('email', 'contains', '@example');

        $this->assertTrue($condition->evaluate(['email' => 'user@example.com']));
        $this->assertFalse($condition->evaluate(['email' => 'user@other.com']));
    }

    /**
     * Test not contains operator.
     */
    public function test_evaluate_not_contains(): void
    {
        $condition = new Condition('domain', 'not_contains', 'spam');

        $this->assertTrue($condition->evaluate(['domain' => 'example.com']));
        $this->assertFalse($condition->evaluate(['domain' => 'spam-site.com']));
    }

    /**
     * Test empty operator.
     */
    public function test_evaluate_empty(): void
    {
        $condition = new Condition('optional', 'empty', null);

        $this->assertTrue($condition->evaluate(['optional' => '']));
        $this->assertTrue($condition->evaluate(['optional' => null]));
        $this->assertTrue($condition->evaluate(['optional' => []]));
        $this->assertTrue($condition->evaluate(['optional' => 0]));
        $this->assertTrue($condition->evaluate([])); // Field doesn't exist
        $this->assertFalse($condition->evaluate(['optional' => 'value']));
    }

    /**
     * Test not empty operator.
     */
    public function test_evaluate_not_empty(): void
    {
        $condition = new Condition('required', 'not_empty', null);

        $this->assertTrue($condition->evaluate(['required' => 'value']));
        $this->assertTrue($condition->evaluate(['required' => 1]));
        $this->assertTrue($condition->evaluate(['required' => ['item']]));
        $this->assertFalse($condition->evaluate(['required' => '']));
        $this->assertFalse($condition->evaluate(['required' => null]));
    }

    /**
     * Test in operator.
     */
    public function test_evaluate_in(): void
    {
        $condition = new Condition('role', 'in', ['admin', 'editor', 'author']);

        $this->assertTrue($condition->evaluate(['role' => 'admin']));
        $this->assertTrue($condition->evaluate(['role' => 'editor']));
        $this->assertFalse($condition->evaluate(['role' => 'subscriber']));
    }

    /**
     * Test not in operator.
     */
    public function test_evaluate_not_in(): void
    {
        $condition = new Condition('status', 'not_in', ['banned', 'suspended']);

        $this->assertTrue($condition->evaluate(['status' => 'active']));
        $this->assertFalse($condition->evaluate(['status' => 'banned']));
        $this->assertFalse($condition->evaluate(['status' => 'suspended']));
    }

    /**
     * Test unknown operator returns false.
     */
    public function test_evaluate_unknown_operator_returns_false(): void
    {
        $condition = new Condition('field', 'unknown_op', 'value');

        $this->assertFalse($condition->evaluate(['field' => 'value']));
    }

    // =========================================================================
    // Nested Value Tests
    // =========================================================================

    /**
     * Test evaluate with nested field path.
     */
    public function test_evaluate_nested_field(): void
    {
        $condition = new Condition('settings.enabled', '==', true);

        $data = [
            'settings' => [
                'enabled' => true,
            ],
        ];

        $this->assertTrue($condition->evaluate($data));
    }

    /**
     * Test evaluate with deeply nested field path.
     */
    public function test_evaluate_deeply_nested_field(): void
    {
        $condition = new Condition('config.theme.colors.primary', '==', '#3b82f6');

        $data = [
            'config' => [
                'theme' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                    ],
                ],
            ],
        ];

        $this->assertTrue($condition->evaluate($data));
    }

    /**
     * Test evaluate with missing nested path returns null.
     */
    public function test_evaluate_missing_nested_path(): void
    {
        $condition = new Condition('settings.missing.field', '==', 'value');

        $data = [
            'settings' => [],
        ];

        $this->assertFalse($condition->evaluate($data));
    }

    /**
     * Test evaluate with missing field.
     */
    public function test_evaluate_missing_field(): void
    {
        $condition = new Condition('nonexistent', '==', 'value');

        $this->assertFalse($condition->evaluate([]));
    }

    /**
     * Test evaluate missing field with empty check.
     */
    public function test_evaluate_missing_field_empty(): void
    {
        $condition = new Condition('optional', 'empty', null);

        $this->assertTrue($condition->evaluate([]));
    }

    // =========================================================================
    // Constant Tests
    // =========================================================================

    /**
     * Test operator constants are defined.
     */
    public function test_operator_constants(): void
    {
        $this->assertEquals('==', Condition::OPERATOR_EQUALS);
        $this->assertEquals('!=', Condition::OPERATOR_NOT_EQUALS);
        $this->assertEquals('>', Condition::OPERATOR_GREATER);
        $this->assertEquals('<', Condition::OPERATOR_LESS);
        $this->assertEquals('>=', Condition::OPERATOR_GREATER_OR_EQUAL);
        $this->assertEquals('<=', Condition::OPERATOR_LESS_OR_EQUAL);
        $this->assertEquals('contains', Condition::OPERATOR_CONTAINS);
        $this->assertEquals('not_contains', Condition::OPERATOR_NOT_CONTAINS);
        $this->assertEquals('empty', Condition::OPERATOR_EMPTY);
        $this->assertEquals('not_empty', Condition::OPERATOR_NOT_EMPTY);
        $this->assertEquals('in', Condition::OPERATOR_IN);
        $this->assertEquals('not_in', Condition::OPERATOR_NOT_IN);
    }
}
