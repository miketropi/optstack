<?php

declare(strict_types=1);

namespace OptStack\Core\Condition;

/**
 * Condition
 *
 * Represents a conditional visibility rule.
 * Conditions are metadata - the renderer decides how to interpret them.
 */
class Condition
{
    /**
     * Supported operators.
     */
    public const OPERATOR_EQUALS = '==';
    public const OPERATOR_NOT_EQUALS = '!=';
    public const OPERATOR_GREATER = '>';
    public const OPERATOR_LESS = '<';
    public const OPERATOR_GREATER_OR_EQUAL = '>=';
    public const OPERATOR_LESS_OR_EQUAL = '<=';
    public const OPERATOR_CONTAINS = 'contains';
    public const OPERATOR_NOT_CONTAINS = 'not_contains';
    public const OPERATOR_EMPTY = 'empty';
    public const OPERATOR_NOT_EMPTY = 'not_empty';
    public const OPERATOR_IN = 'in';
    public const OPERATOR_NOT_IN = 'not_in';

    /**
     * Field to check.
     */
    protected string $field;

    /**
     * Comparison operator.
     */
    protected string $operator;

    /**
     * Value to compare against.
     */
    protected mixed $value;

    /**
     * Logical relation with other conditions (AND/OR).
     */
    protected string $relation;

    /**
     * Create a new Condition instance.
     */
    public function __construct(
        string $field,
        string $operator = self::OPERATOR_EQUALS,
        mixed $value = null,
        string $relation = 'AND'
    ) {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->relation = strtoupper($relation);
    }

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['field'] ?? '',
            $data['operator'] ?? self::OPERATOR_EQUALS,
            $data['value'] ?? null,
            $data['relation'] ?? 'AND'
        );
    }

    /**
     * Get field.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get operator.
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get relation.
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * Evaluate the condition against given data.
     *
     * @param array<string, mixed> $data Form/stack data
     */
    public function evaluate(array $data): bool
    {
        $fieldValue = $this->getNestedValue($data, $this->field);

        return match ($this->operator) {
            self::OPERATOR_EQUALS => $fieldValue == $this->value,
            self::OPERATOR_NOT_EQUALS => $fieldValue != $this->value,
            self::OPERATOR_GREATER => $fieldValue > $this->value,
            self::OPERATOR_LESS => $fieldValue < $this->value,
            self::OPERATOR_GREATER_OR_EQUAL => $fieldValue >= $this->value,
            self::OPERATOR_LESS_OR_EQUAL => $fieldValue <= $this->value,
            self::OPERATOR_CONTAINS => is_string($fieldValue) && str_contains($fieldValue, (string) $this->value),
            self::OPERATOR_NOT_CONTAINS => is_string($fieldValue) && !str_contains($fieldValue, (string) $this->value),
            self::OPERATOR_EMPTY => empty($fieldValue),
            self::OPERATOR_NOT_EMPTY => !empty($fieldValue),
            self::OPERATOR_IN => is_array($this->value) && in_array($fieldValue, $this->value, false),
            self::OPERATOR_NOT_IN => is_array($this->value) && !in_array($fieldValue, $this->value, false),
            default => false,
        };
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
            'relation' => $this->relation,
        ];
    }

    /**
     * Get a nested value from an array using dot notation.
     *
     * @param array<string, mixed> $data
     */
    protected function getNestedValue(array $data, string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
