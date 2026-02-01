<?php

declare(strict_types=1);

namespace OptStack\Core\Stack;

/**
 * Stack Builder
 *
 * Fluent builder for creating stacks with a clean API.
 */
class StackBuilder
{
    /**
     * The stack being built.
     */
    protected Stack $stack;

    /**
     * Whether to auto-register the stack.
     */
    protected bool $autoRegister = true;

    /**
     * Create a new StackBuilder instance.
     */
    public function __construct(string $id)
    {
        $this->stack = new Stack($id);
    }

    /**
     * Create a new builder for a stack.
     */
    public static function make(string $id): self
    {
        return new self($id);
    }

    /**
     * Set stack label.
     */
    public function label(string $label): self
    {
        $this->stack->label($label);

        return $this;
    }

    /**
     * Set stack description.
     */
    public function description(string $description): self
    {
        $this->stack->description($description);

        return $this;
    }

    /**
     * Configure for options storage.
     */
    public function forOptions(): self
    {
        $this->stack->forOptions();

        return $this;
    }

    /**
     * Configure for post storage.
     */
    public function forPost(?int $postId = null): self
    {
        $this->stack->forPost($postId);

        return $this;
    }

    /**
     * Configure for post type storage.
     * 
     * @param string|array<string> $postType Single post type or array of post types
     */
    public function forPostType(string|array $postType): self
    {
        $this->stack->forPostType($postType);

        return $this;
    }

    /**
     * Configure for term storage.
     */
    public function forTerm(?int $termId = null): self
    {
        $this->stack->forTerm($termId);

        return $this;
    }

    /**
     * Configure for taxonomy storage.
     * 
     * @param string|array<string> $taxonomy Single taxonomy or array of taxonomies
     */
    public function forTaxonomy(string|array $taxonomy): self
    {
        $this->stack->forTaxonomy($taxonomy);

        return $this;
    }

    /**
     * Configure for user storage.
     */
    public function forUser(?int $userId = null): self
    {
        $this->stack->forUser($userId);

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
     */
    public function menuParent(string $parent): self
    {
        $this->stack->menuParent($parent);

        return $this;
    }

    /**
     * Set menu icon for top-level menu pages.
     */
    public function menuIcon(string $icon): self
    {
        $this->stack->menuIcon($icon);

        return $this;
    }

    /**
     * Set menu position.
     */
    public function menuPosition(int $position): self
    {
        $this->stack->menuPosition($position);

        return $this;
    }

    /**
     * Set required capability.
     */
    public function capability(string $capability): self
    {
        $this->stack->capability($capability);

        return $this;
    }

    /**
     * Define stack fields and groups.
     */
    public function define(callable $callback): self
    {
        $this->stack->define($callback);

        return $this;
    }

    /**
     * Add a field directly.
     *
     * @param array<string, mixed> $config
     */
    public function field(string $key, array $config = []): self
    {
        $this->stack->field($key, $config);

        return $this;
    }

    /**
     * Add a group directly.
     *
     * @param array<string, mixed> $config
     */
    public function group(string $key, ?callable $callback = null, array $config = []): self
    {
        $this->stack->group($key, $callback, $config);

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
        $this->stack->tab($key, $callback, $config);

        return $this;
    }

    /**
     * Disable auto-registration.
     */
    public function withoutRegistration(): self
    {
        $this->autoRegister = false;

        return $this;
    }

    /**
     * Enable auto-registration.
     */
    public function withRegistration(): self
    {
        $this->autoRegister = true;

        return $this;
    }

    /**
     * Get the built stack.
     */
    public function getStack(): Stack
    {
        return $this->stack;
    }

    /**
     * Register the stack and return it.
     */
    public function register(): Stack
    {
        if (!$this->stack->isRegistered()) {
            StackRegistry::register($this->stack);
        }

        return $this->stack;
    }

    /**
     * Build and optionally register the stack.
     */
    public function build(): Stack
    {
        if ($this->autoRegister && !$this->stack->isRegistered()) {
            StackRegistry::register($this->stack);
        }

        return $this->stack;
    }
}
