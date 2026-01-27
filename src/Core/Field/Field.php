<?php

declare(strict_types=1);

namespace OptStack\Core\Field;

use OptStack\Core\Condition\Condition;

/**
 * Field
 *
 * Represents a single data field in a stack.
 * Fields are data descriptors, not UI components.
 */
class Field
{
    /**
     * Field key/identifier.
     */
    protected string $key;

    /**
     * Field type (text, number, select, boolean, etc.).
     */
    protected string $type;

    /**
     * Default value.
     */
    protected mixed $default;

    /**
     * Sanitization callback or rule.
     */
    protected mixed $sanitize;

    /**
     * Validation callback or rules.
     */
    protected mixed $validate;

    /**
     * Field description/help text.
     */
    protected string $description;

    /**
     * Field label for UI.
     */
    protected string $label;

    /**
     * Conditional visibility rules.
     *
     * @var array<Condition>
     */
    protected array $conditions = [];

    /**
     * Additional field options.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * Field attributes/metadata.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Create a new Field instance.
     *
     * @param string $key Field key
     * @param array<string, mixed> $config Field configuration
     */
    public function __construct(string $key, array $config = [])
    {
        $this->key = $key;
        $this->type = $config['type'] ?? 'text';
        $this->default = $config['default'] ?? null;
        $this->sanitize = $config['sanitize'] ?? null;
        $this->validate = $config['validate'] ?? null;
        $this->description = $config['description'] ?? '';
        $this->label = $config['label'] ?? $this->generateLabel($key);
        $this->options = $config['options'] ?? [];
        $this->attributes = $config['attributes'] ?? [];

        if (isset($config['conditions'])) {
            $this->setConditions($config['conditions']);
        }
    }

    /**
     * Get field key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get field type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get default value.
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Get sanitization callback.
     */
    public function getSanitize(): mixed
    {
        return $this->sanitize;
    }

    /**
     * Get validation rules.
     */
    public function getValidate(): mixed
    {
        return $this->validate;
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get conditions.
     *
     * @return array<Condition>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Get options (for select, radio, etc.).
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Check if field has conditions.
     */
    public function hasConditions(): bool
    {
        return !empty($this->conditions);
    }

    /**
     * Set conditions.
     *
     * @param array<array<string, mixed>|Condition> $conditions
     */
    public function setConditions(array $conditions): self
    {
        $this->conditions = [];

        foreach ($conditions as $condition) {
            if ($condition instanceof Condition) {
                $this->conditions[] = $condition;
            } else {
                $this->conditions[] = Condition::fromArray($condition);
            }
        }

        return $this;
    }

    /**
     * Convert field to array (for schema export).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
        ];

        if ($this->default !== null) {
            $data['default'] = $this->default;
        }

        if (!empty($this->description)) {
            $data['description'] = $this->description;
        }

        if (!empty($this->options)) {
            $data['options'] = $this->options;
        }

        if (!empty($this->attributes)) {
            $data['attributes'] = $this->attributes;
        }

        if (!empty($this->conditions)) {
            $data['conditions'] = array_map(
                fn(Condition $c) => $c->toArray(),
                $this->conditions
            );
        }

        return $data;
    }

    /**
     * Generate a label from field key.
     */
    protected function generateLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
