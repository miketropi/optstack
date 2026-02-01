<?php

declare(strict_types=1);

namespace OptStack\WordPress;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;
use OptStack\Support\Context;
use OptStack\WordPress\Store\OptionsStore;
use OptStack\WordPress\Store\PostStore;
use OptStack\WordPress\Store\TermStore;
use OptStack\WordPress\Store\UserStore;
use OptStack\WordPress\Index\IndexedMetaManager;

/**
 * WordPress Bootstrap
 *
 * Single entry point for WordPress integration with Runtime Context Injection.
 * 
 * Design Principle:
 * - Plugin/Theme is the Host (provides context)
 * - OptStack is the Guest (receives context)
 * - No hardcoded assumptions about environment
 */
class Bootstrap
{
    /**
     * Runtime context provided by the host.
     */
    protected static ?Context $context = null;

    /**
     * Singleton instance.
     */
    private static ?self $instance = null;

    /**
     * Whether bootstrapped.
     */
    private bool $booted = false;

    /**
     * REST namespace.
     */
    private string $restNamespace = 'optstack/v1';

    /**
     * Indexed meta manager for searchable fields.
     */
    private IndexedMetaManager $indexedMetaManager;

    /**
     * Private constructor for singleton.
     */
    private function __construct()
    {
        $this->indexedMetaManager = new IndexedMetaManager();
    }

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the runtime context.
     * 
     * @return Context|null The context if set, null otherwise
     */
    public static function context(): ?Context
    {
        return self::$context;
    }

    /**
     * Get the indexed meta manager.
     */
    public function getIndexedMetaManager(): IndexedMetaManager
    {
        return $this->indexedMetaManager;
    }

    /**
     * Bootstrap OptStack with runtime context injection.
     * 
     * This is the single entry point for initializing OptStack.
     * The host (plugin/theme) must provide runtime context.
     *
     * @param array{file?: string, dir?: string, url?: string, version?: string} $config Runtime configuration
     */
    public static function boot(array $config = []): void
    {
        $config = array_merge(self::getDefaultConfig(), $config);
        // Create and store context
        self::$context = new Context($config);

        // Fail silently if WordPress is not available
        if (!function_exists('add_action')) {
            return;
        }

        // Perform bootstrap
        self::getInstance()->bootstrap();
    }

    /**
     * Get the default configuration.
     */
    public static function getDefaultConfig(): array
    {
        return [
            'file'    => self::resolveFile(),
            'dir'     => self::resolveDir(),
            'url'     => self::resolveUrl() . '/',
            'version' => 'dev',
        ];
    }

    /**
     * Resolve the file.
     */
    public static function resolveFile(): string
    {
        return dirname(__DIR__, 2) . '/optstack.php';
    }

    /**
     * Resolve the directory.
     */
    public static function resolveDir(): string
    {
        return dirname(__DIR__, 2) . '/';
    }

    /**
     * Resolve the URL.
     */
    public static function resolveUrl(): string
    {
        return plugins_url(
            basename(dirname(__DIR__, 2))
        );
    }

    /**
     * Perform the bootstrap.
     */
    public function bootstrap(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        // Register hooks
        add_action('init', [$this, 'onInit'], 5);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);

        // Initialize admin UI
        if (is_admin()) {
            Admin::init();
        }

        // Post meta hooks
        add_action('save_post', [$this, 'onSavePost'], 10, 2);

        // Term meta hooks
        add_action('created_term', [$this, 'onSaveTerm'], 10, 3);
        add_action('edited_term', [$this, 'onSaveTerm'], 10, 3);

        // User meta hooks
        add_action('profile_update', [$this, 'onSaveUser']);
        add_action('user_register', [$this, 'onSaveUser']);
    }

    /**
     * Handle init hook.
     */
    public function onInit(): void
    {
        // Allow stacks to be registered
        do_action('optstack_init');

        // Bind stores to registered stacks
        $this->bindStores();

        // Fire event after stores are bound
        do_action('optstack_ready');
    }

    /**
     * Bind appropriate stores to all registered stacks.
     */
    public function bindStores(): void
    {
        foreach (StackRegistry::all() as $stack) {
            $this->bindStore($stack);
        }
    }

    /**
     * Bind appropriate store to a stack based on its context.
     */
    public function bindStore(Stack $stack): void
    {
        // Skip if store already bound
        if ($stack->getStore() !== null) {
            return;
        }

        switch ($stack->getContext()) {
            case 'options':
                $stack->setStore(new OptionsStore($stack->getId()));
                break;

            case 'post':
                $postId = $stack->getConfig('post_id', 0);
                if ($postId > 0) {
                    $stack->setStore(new PostStore($postId, $stack->getId()));
                }
                break;

            case 'post_type':
                // Store will be bound per-post in save_post hook
                break;

            case 'term':
                $termId = $stack->getConfig('term_id', 0);
                if ($termId > 0) {
                    $stack->setStore(new TermStore($termId, $stack->getId()));
                }
                break;

            case 'taxonomy':
                // Store will be bound per-term in term hooks
                break;

            case 'user':
                $userId = $stack->getConfig('user_id', 0);
                if ($userId > 0) {
                    $stack->setStore(new UserStore($userId, $stack->getId()));
                }
                break;
        }
    }

    /**
     * Register REST API routes.
     */
    public function registerRestRoutes(): void
    {
        // Debug/test endpoint (public - no auth required)
        register_rest_route($this->restNamespace, '/test', [
            'methods' => 'GET',
            'callback' => [$this, 'restTest'],
            'permission_callback' => '__return_true',
        ]);

        // Get all stacks schema
        register_rest_route($this->restNamespace, '/stacks', [
            'methods' => 'GET',
            'callback' => [$this, 'restGetStacks'],
            'permission_callback' => [$this, 'restPermissionCheck'],
        ]);

        // Get single stack schema
        register_rest_route($this->restNamespace, '/stacks/(?P<id>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'restGetStack'],
            'permission_callback' => [$this, 'restPermissionCheck'],
        ]);

        // Get stack data
        register_rest_route($this->restNamespace, '/stacks/(?P<id>[a-zA-Z0-9_-]+)/data', [
            'methods' => 'GET',
            'callback' => [$this, 'restGetStackData'],
            'permission_callback' => [$this, 'restPermissionCheck'],
        ]);

        // Save stack data
        register_rest_route($this->restNamespace, '/stacks/(?P<id>[a-zA-Z0-9_-]+)/data', [
            'methods' => 'POST',
            'callback' => [$this, 'restSaveStackData'],
            'permission_callback' => [$this, 'restPermissionCheck'],
        ]);
    }

    /**
     * REST permission check.
     */
    public function restPermissionCheck(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * REST: Test endpoint (public).
     *
     * @return \WP_REST_Response
     */
    public function restTest(): \WP_REST_Response
    {
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'OptStack REST API is working!',
            'version' => \OptStack\OptStack::VERSION,
            'stacks_count' => StackRegistry::count(),
            'stacks_ids' => array_keys(StackRegistry::all()),
        ]);
    }

    /**
     * REST: Get all stacks.
     *
     * @return \WP_REST_Response
     */
    public function restGetStacks(): \WP_REST_Response
    {
        $stacks = [];

        foreach (StackRegistry::all() as $stack) {
            $stacks[$stack->getId()] = $stack->toArray();
        }

        return new \WP_REST_Response($stacks);
    }

    /**
     * REST: Get single stack.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function restGetStack(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        $stack = StackRegistry::get($id);

        if ($stack === null) {
            return new \WP_Error('not_found', 'Stack not found', ['status' => 404]);
        }

        return new \WP_REST_Response($stack->toArray());
    }

    /**
     * REST: Get stack data.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function restGetStackData(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        $stack = StackRegistry::get($id);

        if ($stack === null) {
            return new \WP_Error('not_found', 'Stack not found', ['status' => 404]);
        }

        // Bind store based on context (object_id NOT required for GET - allows loading UI for new posts/terms)
        $bindResult = $this->bindStoreForRequest($stack, $request, false);
        if (is_wp_error($bindResult)) {
            return $bindResult;
        }

        // Get data from store (will be empty if no store bound - new post/term case)
        $data = $stack->getStore() !== null ? $stack->getData() : [];
        $defaults = $stack->getDefaults();

        // Deep merge defaults with data (data takes precedence)
        $mergedData = $this->deepMerge($defaults, $data);

        // Include isNew flag to indicate this is a new object (no object_id provided)
        $objectId = $request->get_param('object_id');
        
        return new \WP_REST_Response([
            'schema' => $stack->toArray(),
            'data' => $mergedData,
            'isNew' => empty($objectId),
        ]);
    }

    /**
     * REST: Save stack data.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function restSaveStackData(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        $stack = StackRegistry::get($id);

        if ($stack === null) {
            return new \WP_Error('not_found', 'Stack not found', ['status' => 404]);
        }

        // Bind store based on context (object_id IS required for POST - can't save without knowing the object)
        // Note: For 'options' context, object_id is not needed
        $requireObjectId = !in_array($stack->getContext(), ['options'], true);
        $bindResult = $this->bindStoreForRequest($stack, $request, $requireObjectId);
        if (is_wp_error($bindResult)) {
            return $bindResult;
        }

        $data = $request->get_json_params();

        // TODO: Add sanitization and validation
        $success = $stack->saveData($data);

        if (!$success) {
            return new \WP_Error('save_failed', 'Failed to save data', ['status' => 500]);
        }

        // Sync indexed meta for searchable fields
        $objectId = $request->get_param('object_id');
        if ($objectId !== null) {
            $this->indexedMetaManager->syncIndexedMeta($stack, $data, (int) $objectId);
            
            /**
             * Fires after indexed meta has been synced for a stack.
             *
             * @param Stack $stack The stack that was saved
             * @param array $data The saved data
             * @param int $objectId The object ID (post/term/user)
             */
            do_action('optstack_indexed_meta_synced', $stack, $data, (int) $objectId);
        }

        return new \WP_REST_Response([
            'success' => true,
            'data' => $stack->getData(),
        ]);
    }

    /**
     * Bind store for REST request based on stack context.
     *
     * @param Stack $stack
     * @param \WP_REST_Request $request
     * @param bool $requireObjectId Whether object_id is required (true for save, false for get)
     * @return true|\WP_Error
     */
    private function bindStoreForRequest(Stack $stack, \WP_REST_Request $request, bool $requireObjectId = false): true|\WP_Error
    {
        $context = $stack->getContext();
        $objectId = $request->get_param('object_id');

        // Options context doesn't need object_id
        if ($context === 'options') {
            // Ensure store is bound
            if ($stack->getStore() === null) {
                $stack->setStore(new Store\OptionsStore($stack->getId()));
            }
            return true;
        }

        // For post_type, taxonomy, user contexts
        if (in_array($context, ['post_type', 'post', 'taxonomy', 'term', 'user'], true)) {
            // If object_id is not provided
            if (!$objectId) {
                // For save operations, object_id is required
                if ($requireObjectId) {
                    return new \WP_Error(
                        'missing_object_id',
                        sprintf('object_id parameter is required for %s context when saving data', $context),
                        ['status' => 400]
                    );
                }
                
                // For read operations (e.g., new post/term), return true without binding store
                // The UI will load with default/empty values
                return true;
            }

            $objectId = (int) $objectId;

            // Validate object exists
            $validationError = $this->validateObjectExists($context, $objectId, $stack);
            if ($validationError) {
                return $validationError;
            }

            // Bind the appropriate store
            $this->bindStoreForObject($stack, $objectId);
            return true;
        }

        return true;
    }

    /**
     * Validate that the object exists for the given context.
     *
     * @param string $context
     * @param int $objectId
     * @param Stack $stack
     * @return \WP_Error|null
     */
    private function validateObjectExists(string $context, int $objectId, Stack $stack): ?\WP_Error
    {
        switch ($context) {
            case 'post_type':
            case 'post':
                $post = get_post($objectId);
                if (!$post) {
                    return new \WP_Error('post_not_found', 'Post not found', ['status' => 404]);
                }
                // Validate post type matches (for post_type context)
                if ($context === 'post_type' && $stack->getPostType() && $post->post_type !== $stack->getPostType()) {
                    return new \WP_Error(
                        'post_type_mismatch',
                        sprintf('Post type mismatch. Expected %s, got %s', $stack->getPostType(), $post->post_type),
                        ['status' => 400]
                    );
                }
                break;

            case 'taxonomy':
            case 'term':
                $term = get_term($objectId);
                if (!$term || is_wp_error($term)) {
                    return new \WP_Error('term_not_found', 'Term not found', ['status' => 404]);
                }
                // Validate taxonomy matches (for taxonomy context)
                if ($context === 'taxonomy' && $stack->getTaxonomy() && $term->taxonomy !== $stack->getTaxonomy()) {
                    return new \WP_Error(
                        'taxonomy_mismatch',
                        sprintf('Taxonomy mismatch. Expected %s, got %s', $stack->getTaxonomy(), $term->taxonomy),
                        ['status' => 400]
                    );
                }
                break;

            case 'user':
                $user = get_user_by('ID', $objectId);
                if (!$user) {
                    return new \WP_Error('user_not_found', 'User not found', ['status' => 404]);
                }
                break;
        }

        return null;
    }

    /**
     * Deep merge two arrays (second array takes precedence).
     *
     * @param array $defaults
     * @param array $data
     * @return array
     */
    private function deepMerge(array $defaults, array $data): array
    {
        $result = $defaults;

        foreach ($data as $key => $value) {
            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                // If both are arrays, merge recursively
                // But if it's a sequential array (repeater), replace entirely
                if (array_is_list($value) || array_is_list($result[$key])) {
                    $result[$key] = $value;
                } else {
                    $result[$key] = $this->deepMerge($result[$key], $value);
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Handle post save.
     *
     * @param int $postId
     * @param \WP_Post $post
     */
    public function onSavePost(int $postId, \WP_Post $post): void
    {
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Skip revisions
        if (wp_is_post_revision($postId)) {
            return;
        }

        // Get stacks for this post type
        $stacks = StackRegistry::forPostType($post->post_type);

        foreach ($stacks as $stack) {
            $this->bindStoreForObject($stack, $postId);

            // Allow filtering/processing of data before save
            do_action('optstack_before_save_post', $stack, $postId, $post);
        }
    }

    /**
     * Handle term save.
     *
     * @param int $termId
     * @param int $ttId
     * @param string $taxonomy
     */
    public function onSaveTerm(int $termId, int $ttId, string $taxonomy): void
    {
        $stacks = StackRegistry::forTaxonomy($taxonomy);

        foreach ($stacks as $stack) {
            $this->bindStoreForObject($stack, $termId);

            do_action('optstack_before_save_term', $stack, $termId, $taxonomy);
        }
    }

    /**
     * Handle user save.
     *
     * @param int $userId
     */
    public function onSaveUser(int $userId): void
    {
        $stacks = StackRegistry::byContext('user');

        foreach ($stacks as $stack) {
            $this->bindStoreForObject($stack, $userId);

            do_action('optstack_before_save_user', $stack, $userId);
        }
    }

    /**
     * Bind store for a specific object ID.
     */
    protected function bindStoreForObject(Stack $stack, int $objectId): void
    {
        switch ($stack->getContext()) {
            case 'post':
            case 'post_type':
                $stack->setStore(new PostStore($objectId, $stack->getId()));
                break;

            case 'term':
            case 'taxonomy':
                $stack->setStore(new TermStore($objectId, $stack->getId()));
                break;

            case 'user':
                $stack->setStore(new UserStore($objectId, $stack->getId()));
                break;
        }
    }

    /**
     * Get REST namespace.
     */
    public function getRestNamespace(): string
    {
        return $this->restNamespace;
    }
}
