<?php
/**
 * ConditionEvaluator Unit Tests
 *
 * Tests for the ConditionEvaluator class covering:
 * - Evaluating multiple conditions
 * - AND/OR logic
 * - any() and all() helper methods
 *
 * @package OptStack\Tests\Unit
 */

declare(strict_types=1);

namespace OptStack\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OptStack\Core\Condition\Condition;
use OptStack\Core\Condition\ConditionEvaluator;

class ConditionEvaluatorTest extends TestCase
{
    private ConditionEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new ConditionEvaluator();
    }

    /**
     * Test empty conditions return true.
     */
    public function test_empty_conditions_return_true(): void
    {
        $result = $this->evaluator->evaluate([], ['any' => 'data']);

        $this->assertTrue($result);
    }

    /**
     * Test single condition evaluation.
     */
    public function test_single_condition(): void
    {
        $conditions = [
            new Condition('enabled', '==', true),
        ];

        $this->assertTrue($this->evaluator->evaluate($conditions, ['enabled' => true]));
        $this->assertFalse($this->evaluator->evaluate($conditions, ['enabled' => false]));
    }

    /**
     * Test multiple conditions with AND (default).
     */
    public function test_multiple_conditions_and(): void
    {
        $conditions = [
            new Condition('status', '==', 'active'),
            new Condition('verified', '==', true),
        ];

        // Both true
        $this->assertTrue($this->evaluator->evaluate($conditions, [
            'status' => 'active',
            'verified' => true,
        ]));

        // First false
        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'status' => 'inactive',
            'verified' => true,
        ]));

        // Second false
        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'status' => 'active',
            'verified' => false,
        ]));

        // Both false
        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'status' => 'inactive',
            'verified' => false,
        ]));
    }

    /**
     * Test multiple conditions with OR.
     *
     * Note: The relation on a condition defines how the NEXT condition is combined.
     * So for "A OR B", the first condition needs relation 'OR'.
     */
    public function test_multiple_conditions_or(): void
    {
        // relation 'OR' on first condition means: combine NEXT condition with OR
        $conditions = [
            new Condition('role', '==', 'admin', 'OR'),
            new Condition('role', '==', 'editor'),
        ];

        // First true (admin == admin) → true, then OR with (admin == editor) → false = true
        $this->assertTrue($this->evaluator->evaluate($conditions, ['role' => 'admin']));

        // First false (editor == admin), then OR with (editor == editor) → true = true
        $this->assertTrue($this->evaluator->evaluate($conditions, ['role' => 'editor']));

        // First false, OR second false = false
        $this->assertFalse($this->evaluator->evaluate($conditions, ['role' => 'subscriber']));
    }

    /**
     * Test mixed AND/OR conditions.
     *
     * Note: The relation on condition N determines how condition N+1 is combined.
     * Processing: ((A AND B) OR C) where A's relation determines A-B, B's relation determines B-C
     */
    public function test_mixed_and_or_conditions(): void
    {
        // A (status==active, AND) → B (role==admin, OR) → C (role==editor)
        // This means: (status==active AND role==admin) OR role==editor
        $conditions = [
            new Condition('status', '==', 'active'),          // relation='AND' (default)
            new Condition('role', '==', 'admin', 'OR'),       // relation='OR' for next
            new Condition('role', '==', 'editor'),            // combined via OR
        ];

        // (active==active AND admin==admin) = true, no need to check OR
        $this->assertTrue($this->evaluator->evaluate($conditions, [
            'status' => 'active',
            'role' => 'admin',
        ]));

        // (active==active AND subscriber==admin) = false, OR (subscriber==editor) = false
        // Result: false
        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'status' => 'active',
            'role' => 'subscriber',
        ]));

        // (active==active AND editor==admin) = false, OR (editor==editor) = true
        $this->assertTrue($this->evaluator->evaluate($conditions, [
            'status' => 'active',
            'role' => 'editor',
        ]));

        // (inactive==active) = false, AND (admin==admin) = false, OR (admin==editor) = false
        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'status' => 'inactive',
            'role' => 'admin',
        ]));
    }

    /**
     * Test any() helper method.
     */
    public function test_any_helper(): void
    {
        $conditions = [
            new Condition('role', '==', 'admin'),
            new Condition('role', '==', 'editor'),
            new Condition('role', '==', 'author'),
        ];

        // One matches
        $this->assertTrue($this->evaluator->any($conditions, ['role' => 'admin']));
        $this->assertTrue($this->evaluator->any($conditions, ['role' => 'editor']));
        $this->assertTrue($this->evaluator->any($conditions, ['role' => 'author']));

        // None match
        $this->assertFalse($this->evaluator->any($conditions, ['role' => 'subscriber']));
    }

    /**
     * Test any() with empty conditions.
     */
    public function test_any_empty_conditions(): void
    {
        $this->assertFalse($this->evaluator->any([], ['any' => 'data']));
    }

    /**
     * Test all() helper method.
     */
    public function test_all_helper(): void
    {
        $conditions = [
            new Condition('status', '==', 'active'),
            new Condition('verified', '==', true),
            new Condition('level', '>=', 5),
        ];

        // All match
        $this->assertTrue($this->evaluator->all($conditions, [
            'status' => 'active',
            'verified' => true,
            'level' => 10,
        ]));

        // One doesn't match
        $this->assertFalse($this->evaluator->all($conditions, [
            'status' => 'active',
            'verified' => true,
            'level' => 3,
        ]));

        // First doesn't match
        $this->assertFalse($this->evaluator->all($conditions, [
            'status' => 'inactive',
            'verified' => true,
            'level' => 10,
        ]));
    }

    /**
     * Test all() with empty conditions.
     */
    public function test_all_empty_conditions(): void
    {
        $this->assertTrue($this->evaluator->all([], ['any' => 'data']));
    }

    /**
     * Test conditions with nested field paths.
     */
    public function test_conditions_with_nested_fields(): void
    {
        $conditions = [
            new Condition('settings.enabled', '==', true),
            new Condition('settings.level', '>=', 3),
        ];

        $this->assertTrue($this->evaluator->evaluate($conditions, [
            'settings' => [
                'enabled' => true,
                'level' => 5,
            ],
        ]));

        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'settings' => [
                'enabled' => true,
                'level' => 2,
            ],
        ]));
    }

    /**
     * Test three conditions all AND.
     */
    public function test_three_conditions_all_and(): void
    {
        $conditions = [
            new Condition('a', '==', 1),
            new Condition('b', '==', 2),
            new Condition('c', '==', 3),
        ];

        $this->assertTrue($this->evaluator->evaluate($conditions, ['a' => 1, 'b' => 2, 'c' => 3]));
        $this->assertFalse($this->evaluator->evaluate($conditions, ['a' => 1, 'b' => 2, 'c' => 0]));
        $this->assertFalse($this->evaluator->evaluate($conditions, ['a' => 0, 'b' => 2, 'c' => 3]));
    }

    /**
     * Test three conditions all OR.
     *
     * Note: For A OR B OR C, each preceding condition needs relation='OR'.
     */
    public function test_three_conditions_all_or(): void
    {
        $conditions = [
            new Condition('a', '==', 1, 'OR'),  // OR with next
            new Condition('b', '==', 2, 'OR'),  // OR with next
            new Condition('c', '==', 3),        // final
        ];

        $this->assertTrue($this->evaluator->evaluate($conditions, ['a' => 1, 'b' => 0, 'c' => 0]));
        $this->assertTrue($this->evaluator->evaluate($conditions, ['a' => 0, 'b' => 2, 'c' => 0]));
        $this->assertTrue($this->evaluator->evaluate($conditions, ['a' => 0, 'b' => 0, 'c' => 3]));
        $this->assertFalse($this->evaluator->evaluate($conditions, ['a' => 0, 'b' => 0, 'c' => 0]));
    }

    /**
     * Test complex evaluation with multiple operators.
     */
    public function test_complex_evaluation(): void
    {
        $conditions = [
            new Condition('price', '>=', 100),
            new Condition('stock', '>', 0),
            new Condition('category', 'in', ['electronics', 'computers']),
        ];

        $this->assertTrue($this->evaluator->evaluate($conditions, [
            'price' => 150,
            'stock' => 10,
            'category' => 'electronics',
        ]));

        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'price' => 50, // Fails >= 100
            'stock' => 10,
            'category' => 'electronics',
        ]));

        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'price' => 150,
            'stock' => 0, // Fails > 0
            'category' => 'electronics',
        ]));

        $this->assertFalse($this->evaluator->evaluate($conditions, [
            'price' => 150,
            'stock' => 10,
            'category' => 'clothing', // Not in list
        ]));
    }
}
