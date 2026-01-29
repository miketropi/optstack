<?php

declare(strict_types=1);

namespace OptStack\Core\Field;

use OptStack\Core\Condition\Condition;

/**
 * Field Group
 *
 * Represents a logical grouping of fields.
 * Groups can be nested and can be marked as repeatable.
 *
 * Supports deferred rendering where fields are shown only
 * after user-triggered action (e.g., clicking a button that opens a modal).
 */
class FieldGroup
{
    /**
     * Group key/identifier.
     */
    protected string $key;

    /**
     * Group label.
     */
    protected string $label;

    /**
     * Group description.
     */
    protected string $description;

    /**
     * Whether this group is repeatable.
     */
    protected bool $repeatable = false;

    /**
     * Minimum items (for repeatable groups).
     */
    protected int $minItems = 0;

    /**
     * Maximum items (for repeatable groups).
     */
    protected int $maxItems = 0;

    /**
     * Whether this group is collapsible.
     */
    protected bool $collapsible = false;

    /**
     * Layout style ('inline' or 'box').
     */
    protected string $layout = 'inline';

    /**
     * Whether this group uses deferred rendering.
     *
     * Deferred groups render a trigger button instead of inline fields.
     * Fields are shown only when triggered (e.g., in a modal).
     * This is a rendering strategy only - data model remains unchanged.
     */
    protected bool $deferred = false;

    /**
     * UI configuration for deferred groups.
     *
     * @var array{triggerLabel?: string, render?: string}
     */
    protected array $ui = [];

    /**
     * Fields in this group.
     *
     * @var FieldCollection
     */
    protected FieldCollection $fields;

    /**
     * Nested groups.
     *
     * @var array<string, FieldGroup>
     */
    protected array $groups = [];

    /**
     * Conditional visibility rules.
     *
     * @var array<Condition>
     */
    protected array $conditions = [];

    /**
     * Create a new FieldGroup instance.
     *
     * @param string $key Group key
     * @param array<string, mixed> $config Group configuration
     *
     * Supported config options:
     * - label: string - Display label
     * - description: string - Help text
     * - repeatable: bool - Whether group can be repeated
     * - min_items: int - Minimum items (repeatable)
     * - max_items: int - Maximum items (repeatable)
     * - collapsible: bool - Whether group can be collapsed
     * - layout: 'inline'|'box' - Layout style
     * - deferred: bool - Use deferred rendering (show in modal on trigger)
     * - ui: array - UI hints for deferred rendering
     *   - triggerLabel: string - Button label to open deferred group
     *   - render: 'modal'|'drawer'|'panel' - How to render deferred content
     * - conditions: array - Conditional visibility rules
     */
    public function __construct(string $key, array $config = [])
    {
        $this->key = $key;
        $this->label = $config['label'] ?? $this->generateLabel($key);
        $this->description = $config['description'] ?? '';
        $this->repeatable = $config['repeatable'] ?? false;
        $this->minItems = $config['min_items'] ?? 0;
        $this->maxItems = $config['max_items'] ?? 0;
        $this->collapsible = $config['collapsible'] ?? false;
        $this->layout = $config['layout'] ?? 'inline';
        $this->deferred = $config['deferred'] ?? false;
        $this->ui = $config['ui'] ?? [];
        $this->fields = new FieldCollection();

        if (isset($config['conditions'])) {
            $this->setConditions($config['conditions']);
        }
    }

    /**
     * Get group key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get group label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get group description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if group is repeatable.
     */
    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    /**
     * Mark this group as repeatable.
     */
    public function repeatable(int $min = 0, int $max = 0): self
    {
        $this->repeatable = true;
        $this->minItems = $min;
        $this->maxItems = $max;

        return $this;
    }

    /**
     * Get minimum items.
     */
    public function getMinItems(): int
    {
        return $this->minItems;
    }

    /**
     * Get maximum items.
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * Check if group is collapsible.
     */
    public function isCollapsible(): bool
    {
        return $this->collapsible;
    }

    /**
     * Get layout style.
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Check if group uses deferred rendering.
     *
     * Deferred groups render a trigger button instead of inline fields.
     * The actual fields are shown only when triggered (e.g., in a modal).
     */
    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    /**
     * Mark this group as deferred.
     *
     * @param array{triggerLabel?: string, render?: string} $ui UI configuration
     */
    public function deferred(array $ui = []): self
    {
        $this->deferred = true;
        $this->ui = array_merge($this->ui, $ui);

        return $this;
    }

    /**
     * Get UI configuration for deferred rendering.
     *
     * @return array{triggerLabel?: string, render?: string}
     */
    public function getUi(): array
    {
        return $this->ui;
    }

    /**
     * Add a field to this group.
     *
     * @param string $key Field key
     * @param array<string, mixed> $config Field configuration
     */
    public function field(string $key, array $config = []): self
    {
        $this->fields->add(new Field($key, $config));

        return $this;
    }

    /**
     * Add a nested group.
     *
     * @param string $key Group key
     * @param callable|null $callback Callback to define group fields
     * @param array<string, mixed> $config Group configuration
     */
    public function group(string $key, ?callable $callback = null, array $config = []): self
    {
        $group = new FieldGroup($key, $config);

        if ($callback !== null) {
            $callback($group);
        }

        $this->groups[$key] = $group;

        return $this;
    }

    /**
     * Get all fields in this group.
     */
    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    /**
     * Get nested groups.
     *
     * @return array<string, FieldGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
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
     * Check if group has conditions.
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
     * Define fields using a callback.
     *
     * @param callable $callback Callback receiving this group
     */
    public function fields(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /**
     * Convert group to array (for schema export).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'label' => $this->label,
            'repeatable' => $this->repeatable,
        ];

        if (!empty($this->description)) {
            $data['description'] = $this->description;
        }

        if ($this->repeatable) {
            $data['minItems'] = $this->minItems;
            $data['maxItems'] = $this->maxItems;
        }

        // Layout and collapsible
        if ($this->collapsible) {
            $data['collapsible'] = true;
        }

        if ($this->layout !== 'inline') {
            $data['layout'] = $this->layout;
        }

        // Deferred rendering metadata
        if ($this->deferred) {
            $data['deferred'] = true;

            if (!empty($this->ui)) {
                $data['ui'] = $this->ui;
            }
        }

        $data['fields'] = [];
        foreach ($this->fields->all() as $field) {
            $data['fields'][$field->getKey()] = $field->toArray();
        }

        if (!empty($this->groups)) {
            $data['groups'] = [];
            foreach ($this->groups as $key => $group) {
                $data['groups'][$key] = $group->toArray();
            }
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
     * Generate a label from group key.
     */
    protected function generateLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
