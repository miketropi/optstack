<?php

declare(strict_types=1);

namespace OptStack\Core\Stack;

use OptStack\Core\Contract\StoreInterface;
use OptStack\Core\Container\Tab;
use OptStack\Core\Field\Field;
use OptStack\Core\Field\FieldCollection;
use OptStack\Core\Field\FieldGroup;

/**
 * Stack
 *
 * Represents a logical root of structured data stored in WordPress.
 * A stack has a storage backend, contains groups and fields, and
 * supports nested, repeatable, and conditional data.
 */
class Stack
{
    /**
     * Stack identifier.
     */
    protected string $id;

    /**
     * Stack context (options, post, term, user).
     */
    protected string $context = 'options';

    /**
     * Post type (for post context).
     */
    protected ?string $postType = null;

    /**
     * Taxonomy (for term context).
     */
    protected ?string $taxonomy = null;

    /**
     * Stack label.
     */
    protected string $label;

    /**
     * Stack description.
     */
    protected string $description = '';

    /**
     * Root-level fields.
     */
    protected FieldCollection $fields;

    /**
     * Field groups.
     *
     * @var array<string, FieldGroup>
     */
    protected array $groups = [];

    /**
     * Tabs for organizing content.
     *
     * @var array<string, Tab>
     */
    protected array $tabs = [];

    /**
     * Store adapter.
     */
    protected ?StoreInterface $store = null;

    /**
     * Additional configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Whether the stack is registered.
     */
    protected bool $registered = false;

    /**
     * Create a new Stack instance.
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->label = $this->generateLabel($id);
        $this->fields = new FieldCollection();
    }

    /**
     * Get stack ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get context.
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Get post type.
     */
    public function getPostType(): ?string
    {
        return $this->postType;
    }

    /**
     * Get taxonomy.
     */
    public function getTaxonomy(): ?string
    {
        return $this->taxonomy;
    }

    /**
     * Get label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set label.
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set description.
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Configure for options storage.
     */
    public function forOptions(): self
    {
        $this->context = 'options';
        $this->postType = null;
        $this->taxonomy = null;

        return $this;
    }

    /**
     * Set menu parent for options pages (creates submenu).
     *
     * Common parent slugs:
     * - 'options-general.php' (Settings)
     * - 'tools.php' (Tools)
     * - 'themes.php' (Appearance)
     * - 'plugins.php' (Plugins)
     * - 'users.php' (Users)
     * - 'edit.php' (Posts)
     * - 'edit.php?post_type=page' (Pages)
     * - 'optstack' (OptStack parent menu)
     * - Any custom menu slug
     *
     * @param string $parent Parent menu slug
     */
    public function menuParent(string $parent): self
    {
        $this->config['menu_parent'] = $parent;

        return $this;
    }

    /**
     * Set menu icon for options pages (top-level only).
     *
     * @param string $icon Dashicon class or URL to icon
     */
    public function menuIcon(string $icon): self
    {
        $this->config['menu_icon'] = $icon;

        return $this;
    }

    /**
     * Set menu position for options pages.
     *
     * @param int $position Menu position (lower = higher in menu)
     */
    public function menuPosition(int $position): self
    {
        $this->config['menu_position'] = $position;

        return $this;
    }

    /**
     * Set capability required to access this stack.
     *
     * @param string $capability WordPress capability
     */
    public function capability(string $capability): self
    {
        $this->config['capability'] = $capability;

        return $this;
    }

    /**
     * Get menu parent slug.
     */
    public function getMenuParent(): ?string
    {
        return $this->config['menu_parent'] ?? null;
    }

    /**
     * Get menu icon.
     */
    public function getMenuIcon(): string
    {
        return $this->config['menu_icon'] ?? 'dashicons-admin-generic';
    }

    /**
     * Get menu position.
     */
    public function getMenuPosition(): int
    {
        return $this->config['menu_position'] ?? 80;
    }

    /**
     * Get required capability.
     */
    public function getCapability(): string
    {
        return $this->config['capability'] ?? 'manage_options';
    }

    /**
     * Configure for post storage.
     *
     * @param int|null $postId Specific post ID (optional)
     */
    public function forPost(?int $postId = null): self
    {
        $this->context = 'post';
        $this->config['post_id'] = $postId;

        return $this;
    }

    /**
     * Configure for post type storage.
     */
    public function forPostType(string $postType): self
    {
        $this->context = 'post_type';
        $this->postType = $postType;

        return $this;
    }

    /**
     * Configure for term storage.
     *
     * @param int|null $termId Specific term ID (optional)
     */
    public function forTerm(?int $termId = null): self
    {
        $this->context = 'term';
        $this->config['term_id'] = $termId;

        return $this;
    }

    /**
     * Configure for taxonomy storage.
     */
    public function forTaxonomy(string $taxonomy): self
    {
        $this->context = 'taxonomy';
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * Configure for user storage.
     *
     * @param int|null $userId Specific user ID (optional)
     */
    public function forUser(?int $userId = null): self
    {
        $this->context = 'user';
        $this->config['user_id'] = $userId;

        return $this;
    }

    /**
     * Add a field to the stack.
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
     * Add a field group to the stack.
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
     * Add a tab to the stack.
     *
     * @param string $key Tab key
     * @param callable|null $callback Callback to define tab contents
     * @param array<string, mixed> $config Tab configuration
     */
    public function tab(string $key, ?callable $callback = null, array $config = []): self
    {
        $tab = new Tab($key, $config);

        if ($callback !== null) {
            $callback($tab);
        }

        $this->tabs[$key] = $tab;

        return $this;
    }

    /**
     * Get all tabs.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * Get a specific tab.
     */
    public function getTab(string $key): ?Tab
    {
        return $this->tabs[$key] ?? null;
    }

    /**
     * Check if stack has tabs.
     */
    public function hasTabs(): bool
    {
        return !empty($this->tabs);
    }

    /**
     * Define stack structure using a callback.
     *
     * @param callable $callback Callback receiving this stack
     */
    public function define(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /**
     * Get all root-level fields.
     */
    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    /**
     * Get all field groups.
     *
     * @return array<string, FieldGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Get a specific group by key.
     */
    public function getGroup(string $key): ?FieldGroup
    {
        return $this->groups[$key] ?? null;
    }

    /**
     * Set the store adapter.
     */
    public function setStore(StoreInterface $store): self
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get the store adapter.
     */
    public function getStore(): ?StoreInterface
    {
        return $this->store;
    }

    /**
     * Get configuration value.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set configuration value.
     */
    public function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Check if stack is registered.
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Mark stack as registered.
     */
    public function markRegistered(): self
    {
        $this->registered = true;

        return $this;
    }

    /**
     * Get all data from the store.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if ($this->store === null) {
            return [];
        }

        return $this->store->all();
    }

    /**
     * Save data to the store.
     *
     * @param array<string, mixed> $data Data to save
     */
    public function saveData(array $data): bool
    {
        if ($this->store === null) {
            return false;
        }

        foreach ($data as $key => $value) {
            $this->store->set($key, $value);
        }

        return true;
    }

    /**
     * Get default values for all fields.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        $defaults = [];

        // Root-level fields
        foreach ($this->fields->all() as $field) {
            $defaults[$field->getKey()] = $field->getDefault();
        }

        // Groups
        foreach ($this->groups as $key => $group) {
            $defaults[$key] = $this->getGroupDefaults($group);
        }

        // Tab fields (tabs don't create nesting, fields are at root level)
        foreach ($this->tabs as $tab) {
            foreach ($tab->getFields()->all() as $field) {
                $defaults[$field->getKey()] = $field->getDefault();
            }
            foreach ($tab->getGroups() as $key => $group) {
                $defaults[$key] = $this->getGroupDefaults($group);
            }
        }

        return $defaults;
    }

    /**
     * Convert stack to array (for schema export).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'context' => $this->context,
            'label' => $this->label,
        ];

        if (!empty($this->description)) {
            $data['description'] = $this->description;
        }

        if ($this->postType !== null) {
            $data['postType'] = $this->postType;
        }

        if ($this->taxonomy !== null) {
            $data['taxonomy'] = $this->taxonomy;
        }

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

        // Tabs (sorted by priority)
        if (!empty($this->tabs)) {
            $sortedTabs = $this->tabs;
            uasort($sortedTabs, fn(Tab $a, Tab $b) => $a->getPriority() <=> $b->getPriority());
            
            $data['tabs'] = [];
            foreach ($sortedTabs as $key => $tab) {
                $data['tabs'][$key] = $tab->toArray();
            }
        }

        return $data;
    }

    /**
     * Get defaults for a field group.
     *
     * @return array<string, mixed>
     */
    protected function getGroupDefaults(FieldGroup $group): array
    {
        $defaults = [];

        foreach ($group->getFields()->all() as $field) {
            $defaults[$field->getKey()] = $field->getDefault();
        }

        foreach ($group->getGroups() as $key => $nestedGroup) {
            $defaults[$key] = $this->getGroupDefaults($nestedGroup);
        }

        if ($group->isRepeatable()) {
            return [$defaults]; // Return as array with one default item
        }

        return $defaults;
    }

    /**
     * Generate a label from stack ID.
     */
    protected function generateLabel(string $id): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $id));
    }
}
