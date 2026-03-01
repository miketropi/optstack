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
     * Post type(s) (for post context).
     * 
     * @var string|array<string>|null
     */
    protected string|array|null $postType = null;

    /**
     * Taxonomy/taxonomies (for term context).
     * 
     * @var string|array<string>|null
     */
    protected string|array|null $taxonomy = null;

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
     * Get post type(s).
     * 
     * @return string|array<string>|null
     */
    public function getPostType(): string|array|null
    {
        return $this->postType;
    }

    /**
     * Get post types as array (normalized).
     * 
     * @return array<string>
     */
    public function getPostTypes(): array
    {
        if ($this->postType === null) {
            return [];
        }

        return is_array($this->postType) ? $this->postType : [$this->postType];
    }

    /**
     * Check if stack is registered for a specific post type.
     */
    public function hasPostType(string $postType): bool
    {
        if ($this->postType === null) {
            return false;
        }

        if (is_array($this->postType)) {
            return in_array($postType, $this->postType, true);
        }

        return $this->postType === $postType;
    }

    /**
     * Get taxonomy/taxonomies.
     * 
     * @return string|array<string>|null
     */
    public function getTaxonomy(): string|array|null
    {
        return $this->taxonomy;
    }

    /**
     * Get taxonomies as array (normalized).
     * 
     * @return array<string>
     */
    public function getTaxonomies(): array
    {
        if ($this->taxonomy === null) {
            return [];
        }

        return is_array($this->taxonomy) ? $this->taxonomy : [$this->taxonomy];
    }

    /**
     * Check if stack is registered for a specific taxonomy.
     */
    public function hasTaxonomy(string $taxonomy): bool
    {
        if ($this->taxonomy === null) {
            return false;
        }

        if (is_array($this->taxonomy)) {
            return in_array($taxonomy, $this->taxonomy, true);
        }

        return $this->taxonomy === $taxonomy;
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
     * Configure for WordPress Customizer (Appearance → Customize).
     * Storage can be 'theme_mod' (theme-specific) or 'option' (wp_options).
     *
     * @param string $storage 'theme_mod' or 'option'
     */
    public function forCustomizer(string $storage = 'theme_mod'): self
    {
        $this->context = 'customizer';
        $this->postType = null;
        $this->taxonomy = null;
        $this->config['customize_storage'] = $storage === 'option' ? 'option' : 'theme_mod';

        return $this;
    }

    /**
     * Get Customizer storage type when context is 'customizer'.
     *
     * @return string 'theme_mod' or 'option'
     */
    public function getCustomizeStorage(): string
    {
        return $this->config['customize_storage'] ?? 'theme_mod';
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
     * 
     * @param string|array<string> $postType Single post type or array of post types
     */
    public function forPostType(string|array $postType): self
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
     * 
     * @param string|array<string> $taxonomy Single taxonomy or array of taxonomies
     */
    public function forTaxonomy(string|array $taxonomy): self
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
     * Configure for Gutenberg block storage.
     * Data is stored in block attributes (post_content).
     *
     * @param string $blockType Block type name (e.g. 'optstack/hero' or 'mytheme/hero')
     */
    public function forBlockType(string $blockType): self
    {
        $this->context = 'block';
        $this->config['block_type'] = $blockType;

        return $this;
    }

    /**
     * Get block type (for block context).
     */
    public function getBlockType(): ?string
    {
        return $this->config['block_type'] ?? null;
    }

    /**
     * Set block title for inserter (block context).
     */
    public function blockTitle(string $title): self
    {
        $this->config['block_title'] = $title;

        return $this;
    }

    /**
     * Set block category for inserter (block context).
     */
    public function blockCategory(string $category): self
    {
        $this->config['block_category'] = $category;

        return $this;
    }

    /**
     * Set block icon (dashicon slug) for inserter (block context).
     */
    public function blockIcon(string $icon): self
    {
        $this->config['block_icon'] = $icon;

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
     * Get a single field value.
     *
     * This is a convenience method for retrieving a single field value
     * without needing to fetch the entire data array.
     *
     * @param string $key Field key (supports dot notation for nested fields)
     * @param mixed $default Default value if field not found
     * @param int|null $objectId Object ID (post/term/user) - for context binding
     * @return mixed Field value or default
     *
     * @example
     * // Simple field retrieval
     * $price = $stack->getField('price', 0, $post_id);
     *
     * // Nested field retrieval (group.field)
     * $regularPrice = $stack->getField('pricing.regular_price', 0, $post_id);
     */
    public function getField(string $key, mixed $default = null, ?int $objectId = null): mixed
    {
        // If no store bound yet, try to bind it
        if ($this->store === null) {
            // For post_type, taxonomy, user contexts, we need object ID to bind store
            if ($objectId !== null && in_array($this->context, ['post_type', 'post', 'taxonomy', 'term', 'user'])) {
                $this->bindStoreForObject($objectId);
            }
            
            // If still no store, return default
            if ($this->store === null) {
                return $default;
            }
        }

        // Handle nested keys (e.g., 'pricing.regular_price')
        if (str_contains($key, '.')) {
            return $this->getNestedField($key, $default);
        }

        // Get the field from store
        return $this->store->get($key, $default);
    }

    /**
     * Get a nested field value (supports dot notation).
     *
     * @param string $path Dot notation path (e.g., 'pricing.regular_price')
     * @param mixed $default Default value if not found
     * @return mixed Field value or default
     */
    protected function getNestedField(string $path, mixed $default = null): mixed
    {
        // Ensure store is available
        if ($this->store === null) {
            return $default;
        }

        $keys = explode('.', $path);
        $rootKey = array_shift($keys);

        // Get root value
        $value = $this->store->get($rootKey);
        
        if ($value === null) {
            return $default;
        }

        // Navigate to the nested key
        foreach ($keys as $nestedKey) {
            if (!is_array($value) || !array_key_exists($nestedKey, $value)) {
                return $default;
            }
            $value = $value[$nestedKey];
        }

        return $value;
    }

    /**
     * Update a single field value.
     *
     * This is a convenience method for updating a single field without
     * needing to fetch and merge the entire data array.
     *
     * If the field is marked as searchable, the indexed meta will be
     * automatically synced.
     *
     * @param string $key Field key (supports dot notation for nested fields)
     * @param mixed $value New value
     * @param int|null $objectId Object ID (post/term/user) - required for searchable field sync
     * @return bool Success status
     *
     * @example
     * // Simple field update
     * $stack->updateField('price', 99.99, $post_id);
     *
     * // Nested field update (group.field)
     * $stack->updateField('pricing.regular_price', 149.99, $post_id);
     */
    public function updateField(string $key, mixed $value, ?int $objectId = null): bool
    {
        // If no store bound yet, try to bind it
        if ($this->store === null) {
            // For post_type, taxonomy, user contexts, we need object ID to bind store
            if ($objectId !== null && in_array($this->context, ['post_type', 'post', 'taxonomy', 'term', 'user'])) {
                $this->bindStoreForObject($objectId);
            }
            
            // If still no store, can't proceed
            if ($this->store === null) {
                return false;
            }
        }

        // Handle nested keys (e.g., 'pricing.regular_price')
        if (str_contains($key, '.')) {
            return $this->updateNestedField($key, $value, $objectId);
        }

        // Update the field in store
        $success = $this->store->set($key, $value);

        if (!$success) {
            return false;
        }

        // Sync searchable field if applicable
        $this->syncSearchableField($key, $value, $objectId);

        return true;
    }

    /**
     * Bind store for a specific object ID.
     *
     * @param int $objectId The object ID (post/term/user)
     */
    protected function bindStoreForObject(int $objectId): void
    {
        // Only bind if WordPress store classes are available
        if (!class_exists('\\OptStack\\WordPress\\Store\\PostStore')) {
            return;
        }

        switch ($this->context) {
            case 'post':
            case 'post_type':
                $this->store = new \OptStack\WordPress\Store\PostStore($objectId, $this->id);
                break;

            case 'term':
            case 'taxonomy':
                $this->store = new \OptStack\WordPress\Store\TermStore($objectId, $this->id);
                break;

            case 'user':
                $this->store = new \OptStack\WordPress\Store\UserStore($objectId, $this->id);
                break;
        }
    }

    /**
     * Update a nested field value (supports dot notation).
     *
     * @param string $path Dot notation path (e.g., 'pricing.regular_price')
     * @param mixed $value New value
     * @param int|null $objectId Object ID for searchable field sync
     * @return bool Success status
     */
    protected function updateNestedField(string $path, mixed $value, ?int $objectId = null): bool
    {
        // Ensure store is available (should already be checked by updateField, but double-check)
        if ($this->store === null) {
            return false;
        }

        $keys = explode('.', $path);
        $rootKey = array_shift($keys);

        // Get current root value
        $rootData = $this->store->get($rootKey, []);
        
        if (!is_array($rootData)) {
            $rootData = [];
        }

        // Navigate to the nested key and update
        $current = &$rootData;
        foreach ($keys as $i => $nestedKey) {
            if ($i === count($keys) - 1) {
                // Last key - set the value
                $current[$nestedKey] = $value;
            } else {
                // Intermediate key - ensure it's an array
                if (!isset($current[$nestedKey]) || !is_array($current[$nestedKey])) {
                    $current[$nestedKey] = [];
                }
                $current = &$current[$nestedKey];
            }
        }

        // Save updated root data
        $success = $this->store->set($rootKey, $rootData);

        if (!$success) {
            return false;
        }

        // Sync searchable field if applicable
        $this->syncSearchableField($path, $value, $objectId);

        return true;
    }

    /**
     * Sync indexed meta for a single searchable field.
     *
     * @param string $fieldPath Field path (may include dots for nested)
     * @param mixed $value Field value
     * @param int|null $objectId Object ID (post/term/user)
     */
    protected function syncSearchableField(string $fieldPath, mixed $value, ?int $objectId = null): void
    {
        // Options context doesn't support searchable fields
        if ($this->context === 'options') {
            return;
        }

        // Need object ID for meta sync
        if ($objectId === null) {
            // Try to get object ID from store config
            $objectId = $this->getObjectIdFromStore();
            
            if ($objectId === null) {
                return;
            }
        }

        // Check if field is searchable
        if (!$this->isFieldSearchable($fieldPath)) {
            return;
        }

        // Sync the indexed meta via Bootstrap's IndexedMetaManager
        if (class_exists('\\OptStack\\WordPress\\Bootstrap')) {
            $bootstrap = \OptStack\WordPress\Bootstrap::getInstance();
            $manager = $bootstrap->getIndexedMetaManager();
            $manager->syncSingleField($this, $fieldPath, $value, $objectId);

            /**
             * Fires after a single searchable field has been synced.
             *
             * @param Stack $stack The stack instance
             * @param string $fieldPath The field path that was synced
             * @param mixed $value The field value
             * @param int $objectId The object ID
             */
            do_action('optstack_searchable_field_synced', $this, $fieldPath, $value, $objectId);
        }
    }

    /**
     * Check if a field is marked as searchable.
     *
     * @param string $path Field path (supports dot notation)
     * @return bool Whether the field is searchable
     */
    protected function isFieldSearchable(string $path): bool
    {
        $keys = explode('.', $path);
        
        // Check root-level fields
        foreach ($this->fields->all() as $field) {
            if ($field->getKey() === $keys[0]) {
                return $field->isSearchable();
            }
        }

        // Check fields in groups
        if (count($keys) > 1) {
            $groupKey = array_shift($keys);
            if (isset($this->groups[$groupKey])) {
                return $this->isFieldSearchableInGroup($this->groups[$groupKey], $keys);
            }
        }

        // Check fields in tabs
        foreach ($this->tabs as $tab) {
            foreach ($tab->getFields()->all() as $field) {
                if ($field->getKey() === $keys[0]) {
                    return $field->isSearchable();
                }
            }
            
            if (count($keys) > 1) {
                $groupKey = $keys[0];
                $tabGroups = $tab->getGroups();
                if (isset($tabGroups[$groupKey])) {
                    $remainingKeys = array_slice($keys, 1);
                    return $this->isFieldSearchableInGroup($tabGroups[$groupKey], $remainingKeys);
                }
            }
        }

        return false;
    }

    /**
     * Check if a field is searchable within a group (recursive).
     *
     * @param \OptStack\Core\Field\FieldGroup $group The group to search in
     * @param array<string> $keys Remaining path keys
     * @return bool Whether the field is searchable
     */
    protected function isFieldSearchableInGroup($group, array $keys): bool
    {
        $currentKey = array_shift($keys);
        
        // Check fields in this group
        foreach ($group->getFields()->all() as $field) {
            if ($field->getKey() === $currentKey && empty($keys)) {
                return $field->isSearchable();
            }
        }

        // Check nested groups
        if (!empty($keys)) {
            $nestedGroups = $group->getGroups();
            if (isset($nestedGroups[$currentKey])) {
                return $this->isFieldSearchableInGroup($nestedGroups[$currentKey], $keys);
            }
        }

        return false;
    }

    /**
     * Get object ID from the store configuration.
     *
     * @return int|null Object ID if available
     */
    protected function getObjectIdFromStore(): ?int
    {
        // Try to get from config
        $objectId = $this->getConfig('post_id') 
                 ?? $this->getConfig('term_id') 
                 ?? $this->getConfig('user_id');

        if ($objectId !== null) {
            return (int) $objectId;
        }

        // Try to get from store if it's a Post/Term/User store
        if (method_exists($this->store, 'getPostId')) {
            return $this->store->getPostId();
        }

        if (method_exists($this->store, 'getTermId')) {
            return $this->store->getTermId();
        }

        if (method_exists($this->store, 'getUserId')) {
            return $this->store->getUserId();
        }

        return null;
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
            $defaults[$field->getKey()] = $this->getFieldDefaultValue($field);
        }

        // Groups
        foreach ($this->groups as $key => $group) {
            $defaults[$key] = $this->getGroupDefaults($group);
        }

        // Tab fields (tabs don't create nesting, fields are at root level)
        foreach ($this->tabs as $tab) {
            foreach ($tab->getFields()->all() as $field) {
                $defaults[$field->getKey()] = $this->getFieldDefaultValue($field);
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
            // Always return as array for consistency in schema
            $data['postType'] = is_array($this->postType) ? $this->postType : [$this->postType];
        }

        if ($this->taxonomy !== null) {
            // Always return as array for consistency in schema
            $data['taxonomy'] = is_array($this->taxonomy) ? $this->taxonomy : [$this->taxonomy];
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
     * Get default value for a single field (scalar or responsive shape).
     *
     * @return mixed
     */
    protected function getFieldDefaultValue(Field $field): mixed
    {
        $def = $field->getDefault();
        if (!$field->isResponsive()) {
            return $def;
        }
        // Typography stores a single object; responsive sub-keys (fontSize, lineHeight, letterSpacing, color) are handled in the frontend.
        if ($field->getType() === 'typography') {
            return $def;
        }
        return [
            'desktop' => $def,
            'tablet'  => $def,
            'mobile'  => $def,
        ];
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
            $defaults[$field->getKey()] = $this->getFieldDefaultValue($field);
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
