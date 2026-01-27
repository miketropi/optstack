<?php

declare(strict_types=1);

namespace OptStack\Core\Container;

use OptStack\Core\Field\Field;
use OptStack\Core\Field\FieldCollection;
use OptStack\Core\Field\FieldGroup;
use OptStack\Core\Condition\Condition;

/**
 * Tab
 *
 * Represents a tab container that holds fields and groups.
 * Tabs provide a way to organize complex forms into logical sections.
 */
class Tab
{
    /**
     * Tab key/identifier.
     */
    protected string $key;

    /**
     * Tab label.
     */
    protected string $label;

    /**
     * Tab icon (dashicon class or custom icon).
     */
    protected string $icon = '';

    /**
     * Tab description.
     */
    protected string $description = '';

    /**
     * Tab priority (for ordering).
     */
    protected int $priority = 10;

    /**
     * Fields in this tab.
     */
    protected FieldCollection $fields;

    /**
     * Field groups in this tab.
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
     * Create a new Tab instance.
     *
     * @param string $key Tab key
     * @param array<string, mixed> $config Tab configuration
     */
    public function __construct(string $key, array $config = [])
    {
        $this->key = $key;
        $this->label = $config['label'] ?? $this->generateLabel($key);
        $this->icon = $config['icon'] ?? '';
        $this->description = $config['description'] ?? '';
        $this->priority = $config['priority'] ?? 10;
        $this->fields = new FieldCollection();

        if (isset($config['conditions'])) {
            $this->setConditions($config['conditions']);
        }
    }

    /**
     * Get tab key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get tab label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set tab label.
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get tab icon.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Set tab icon.
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get tab description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set tab description.
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get tab priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set tab priority.
     */
    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Add a field to the tab.
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
     * Add a field group to the tab.
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
     * Get all fields.
     */
    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    /**
     * Get all groups.
     *
     * @return array<string, FieldGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Get a specific group.
     */
    public function getGroup(string $key): ?FieldGroup
    {
        return $this->groups[$key] ?? null;
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
     * Check if tab has conditions.
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
     * Define tab contents using a callback.
     *
     * @param callable $callback Callback receiving this tab
     */
    public function define(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /**
     * Convert tab to array (for schema export).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'label' => $this->label,
        ];

        if (!empty($this->icon)) {
            $data['icon'] = $this->icon;
        }

        if (!empty($this->description)) {
            $data['description'] = $this->description;
        }

        $data['priority'] = $this->priority;

        // Fields
        if (!$this->fields->isEmpty()) {
            $data['fields'] = $this->fields->toArray();
        }

        // Groups
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
     * Generate a label from tab key.
     */
    protected function generateLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }
}
