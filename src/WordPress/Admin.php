<?php

declare(strict_types=1);

namespace OptStack\WordPress;

use OptStack\Core\Stack\Stack;
use OptStack\Core\Stack\StackRegistry;

/**
 * WordPress Admin Integration
 *
 * Handles rendering stacks in their appropriate WordPress contexts:
 * - Options stacks → Admin menu pages
 * - Post type stacks → Meta boxes
 * - Taxonomy stacks → Term edit forms
 * - User stacks → User profile forms
 */
class Admin
{
    /**
     * Singleton instance.
     */
    private static ?self $instance = null;

    /**
     * Whether initialized.
     */
    private bool $initialized = false;

    /**
     * Private constructor for singleton.
     */
    private function __construct()
    {
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
     * Initialize admin integration.
     */
    public static function init(): void
    {
        self::getInstance()->setup();
    }

    /**
     * Setup admin hooks.
     */
    public function setup(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        // Wait for stacks to be registered, then set up rendering
        add_action('optstack_ready', [$this, 'registerRenderers']);
    }

    /**
     * Register renderers for all stacks based on their context.
     */
    public function registerRenderers(): void
    {
        foreach (StackRegistry::all() as $stack) {
            $this->registerStackRenderer($stack);
        }
    }

    /**
     * Register appropriate renderer for a stack based on its context.
     */
    private function registerStackRenderer(Stack $stack): void
    {
        switch ($stack->getContext()) {
            case 'options':
                $this->registerOptionsPage($stack);
                break;

            case 'post_type':
                $this->registerMetaBox($stack);
                break;

            case 'taxonomy':
                $this->registerTaxonomyFields($stack);
                break;

            case 'user':
                $this->registerUserFields($stack);
                break;
        }
    }

    // =========================================================================
    // OPTIONS PAGES
    // =========================================================================

    /**
     * Track created parent menus to avoid duplicates.
     */
    private static array $createdParentMenus = [];

    /**
     * Register an admin menu page for an options stack.
     */
    private function registerOptionsPage(Stack $stack): void
    {
        $menuSlug = 'optstack-' . $stack->getId();
        $menuParent = $stack->getMenuParent();
        $capability = $stack->getCapability();

        add_action('admin_menu', function () use ($stack, $menuSlug, $menuParent, $capability) {
            if ($menuParent) {
                // Create as submenu page
                $this->registerSubmenuPage($stack, $menuSlug, $menuParent, $capability);
            } else {
                // Create as top-level menu page
                $this->registerTopLevelPage($stack, $menuSlug, $capability);
            }
        });

        // Enqueue assets on this page
        add_action('admin_enqueue_scripts', function ($hook) use ($stack, $menuSlug, $menuParent) {
            // Match hook for both top-level and submenu pages
            $expectedHooks = [
                'toplevel_page_' . $menuSlug,
                sanitize_title($stack->getLabel()) . '_page_' . $menuSlug,
            ];

            // For submenus, hook format varies based on parent
            if ($menuParent) {
                $expectedHooks[] = $this->getSubmenuHook($menuParent, $menuSlug);
            }

            if (in_array($hook, $expectedHooks, true) || strpos($hook, $menuSlug) !== false) {
                $this->enqueueAssets($stack);
            }
        });
    }

    /**
     * Register a top-level menu page.
     */
    private function registerTopLevelPage(Stack $stack, string $menuSlug, string $capability): void
    {
        add_menu_page(
            $stack->getLabel(),
            $stack->getLabel(),
            $capability,
            $menuSlug,
            function () use ($stack) {
                $this->renderOptionsPage($stack);
            },
            $stack->getMenuIcon(),
            $stack->getMenuPosition()
        );
    }

    /**
     * Register a submenu page.
     */
    private function registerSubmenuPage(Stack $stack, string $menuSlug, string $menuParent, string $capability): void
    {
        // Handle special 'optstack' parent - create parent menu if needed
        if ($menuParent === 'optstack') {
            $this->ensureOptStackParentMenu();
        }

        add_submenu_page(
            $menuParent,
            $stack->getLabel(),
            $stack->getLabel(),
            $capability,
            $menuSlug,
            function () use ($stack) {
                $this->renderOptionsPage($stack);
            }
        );
    }

    /**
     * Ensure the OptStack parent menu exists.
     */
    private function ensureOptStackParentMenu(): void
    {
        if (isset(self::$createdParentMenus['optstack'])) {
            return;
        }

        self::$createdParentMenus['optstack'] = true;

        add_menu_page(
            __('OptStack', 'optstack'),
            __('OptStack', 'optstack'),
            'manage_options',
            'optstack',
            function () {
                $this->renderOptStackDashboard();
            },
            'dashicons-database',
            80
        );
    }

    /**
     * Render the main OptStack dashboard (when using 'optstack' as parent).
     */
    private function renderOptStackDashboard(): void
    {
        $optionsStacks = StackRegistry::byContext('options');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('OptStack', 'optstack'); ?></h1>
            <p><?php esc_html_e('WordPress Data Stack Framework', 'optstack'); ?></p>

            <?php if (!empty($optionsStacks)): ?>
                <h2><?php esc_html_e('Registered Settings', 'optstack'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'optstack'); ?></th>
                            <th><?php esc_html_e('ID', 'optstack'); ?></th>
                            <th><?php esc_html_e('Description', 'optstack'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($optionsStacks as $stack): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=optstack-' . $stack->getId())); ?>">
                                        <?php echo esc_html($stack->getLabel()); ?>
                                    </a>
                                </td>
                                <td><code><?php echo esc_html($stack->getId()); ?></code></td>
                                <td><?php echo esc_html($stack->getDescription()); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get the expected hook name for a submenu page.
     */
    private function getSubmenuHook(string $parent, string $slug): string
    {
        // WordPress generates hooks based on parent menu
        $parentBase = str_replace('.php', '', $parent);
        $parentBase = sanitize_title($parentBase);

        return $parentBase . '_page_' . $slug;
    }

    /**
     * Render an options page.
     */
    private function renderOptionsPage(Stack $stack): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($stack->getLabel()); ?></h1>
            <?php if ($stack->getDescription()): ?>
                <p class="description"><?php echo esc_html($stack->getDescription()); ?></p>
            <?php endif; ?>

            <?php $this->renderMountPoint($stack); ?>
        </div>
        <?php
    }

    // =========================================================================
    // META BOXES (Post Types)
    // =========================================================================

    /**
     * Register a meta box for a post type stack.
     */
    private function registerMetaBox(Stack $stack): void
    {
        $postType = $stack->getPostType();

        if (!$postType) {
            return;
        }

        // Register meta box - WordPress will only show it for the specified post type
        add_action('add_meta_boxes', function () use ($stack, $postType) {
            // Validate post type exists
            if (!post_type_exists($postType)) {
                return;
            }

            add_meta_box(
                'optstack-' . $stack->getId(),
                $stack->getLabel(),
                function ($post) use ($stack, $postType) {
                    $this->renderMetaBox($stack, $post, $postType);
                },
                $postType,
                'normal',
                'high'
            );
        });

        // Enqueue assets on post edit screen
        add_action('admin_enqueue_scripts', function ($hook) use ($stack, $postType) {
            global $post_type, $post;

            // Only on post edit screens
            if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
                return;
            }

            // Validate post type matches
            if ($post_type !== $postType) {
                return;
            }

            // Validate post type exists
            if (!post_type_exists($postType)) {
                return;
            }

            $postId = $post ? $post->ID : 0;
            $this->enqueueAssets($stack, ['post_id' => $postId, 'object_type' => 'post']);
        });

        // Save meta box data - use generic save_post with validation
        add_action('save_post', function ($postId, $post) use ($stack, $postType) {
            $this->saveMetaBoxData($stack, $postId, $post, $postType);
        }, 10, 2);
    }

    /**
     * Render a meta box.
     */
    private function renderMetaBox(Stack $stack, \WP_Post $post, string $expectedPostType): void
    {
        // Double-check post type matches (safety check)
        if ($post->post_type !== $expectedPostType) {
            return;
        }

        // Nonce for security
        wp_nonce_field('optstack_save_' . $stack->getId(), 'optstack_nonce_' . $stack->getId());

        $this->renderMountPoint($stack, [
            'object_id' => $post->ID,
            'object_type' => 'post',
        ]);
    }

    /**
     * Save meta box data.
     */
    private function saveMetaBoxData(Stack $stack, int $postId, \WP_Post $post, string $expectedPostType): void
    {
        // Validate post type matches
        if ($post->post_type !== $expectedPostType) {
            return;
        }

        // Skip revisions
        if (wp_is_post_revision($postId)) {
            return;
        }

        // Skip auto-drafts
        if ($post->post_status === 'auto-draft') {
            return;
        }

        // Verify nonce
        $nonceKey = 'optstack_nonce_' . $stack->getId();
        if (!isset($_POST[$nonceKey]) || !wp_verify_nonce($_POST[$nonceKey], 'optstack_save_' . $stack->getId())) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check AJAX (we handle AJAX separately via REST API)
        if (wp_doing_ajax()) {
            return;
        }

        // Check permissions based on post type
        $postTypeObj = get_post_type_object($expectedPostType);
        if (!$postTypeObj) {
            return;
        }

        $capability = $postTypeObj->cap->edit_post ?? 'edit_post';
        if (!current_user_can($capability, $postId)) {
            return;
        }

        // Get data from hidden field (JSON)
        $this->saveStackDataFromPost($stack, $postId, 'post');
    }

    // =========================================================================
    // TAXONOMY FIELDS
    // =========================================================================

    /**
     * Register fields for a taxonomy stack.
     */
    private function registerTaxonomyFields(Stack $stack): void
    {
        $taxonomy = $stack->getTaxonomy();

        if (!$taxonomy) {
            return;
        }

        // Add fields to "Add New Term" form
        add_action($taxonomy . '_add_form_fields', function () use ($stack) {
            $this->renderTaxonomyAddFields($stack);
        });

        // Add fields to "Edit Term" form
        add_action($taxonomy . '_edit_form_fields', function ($term) use ($stack) {
            $this->renderTaxonomyEditFields($stack, $term);
        });

        // Enqueue assets
        add_action('admin_enqueue_scripts', function ($hook) use ($stack, $taxonomy) {
            $screen = get_current_screen();
            if ($screen && ($screen->taxonomy === $taxonomy)) {
                $termId = isset($_GET['tag_ID']) ? (int) $_GET['tag_ID'] : 0;
                $this->enqueueAssets($stack, ['term_id' => $termId]);
            }
        });

        // Save term meta
        add_action('created_' . $taxonomy, function ($termId) use ($stack) {
            $this->saveTermData($stack, $termId);
        });
        add_action('edited_' . $taxonomy, function ($termId) use ($stack) {
            $this->saveTermData($stack, $termId);
        });
    }

    /**
     * Render fields for "Add New Term" form.
     */
    private function renderTaxonomyAddFields(Stack $stack): void
    {
        ?>
        <div class="form-field">
            <label><?php echo esc_html($stack->getLabel()); ?></label>
            <?php if ($stack->getDescription()): ?>
                <p class="description" style="font-weight: normal;"><?php echo esc_html($stack->getDescription()); ?></p>
            <?php endif; ?>
            <?php $this->renderMountPoint($stack, ['object_type' => 'term']); ?>
        </div>
        <?php
    }

    /**
     * Render fields for "Edit Term" form.
     */
    private function renderTaxonomyEditFields(Stack $stack, \WP_Term $term): void
    {
        ?>
        <tr class="form-field">
            <th scope="row">
                <label><?php echo esc_html($stack->getLabel()); ?></label>
            </th>
            <td>
                <?php $this->renderMountPoint($stack, [
                    'object_id' => $term->term_id,
                    'object_type' => 'term',
                ]); ?>
                <?php if ($stack->getDescription()): ?>
                    <p class="description"><?php echo esc_html($stack->getDescription()); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Save term data.
     */
    private function saveTermData(Stack $stack, int $termId): void
    {
        // Check permissions
        $taxonomy = $stack->getTaxonomy();
        $taxonomyObj = get_taxonomy($taxonomy);
        
        if ($taxonomyObj && !current_user_can($taxonomyObj->cap->edit_terms)) {
            return;
        }

        // Get data from hidden field (JSON)
        $this->saveStackDataFromPost($stack, $termId, 'term');
    }

    // =========================================================================
    // USER PROFILE FIELDS
    // =========================================================================

    /**
     * Register fields for user profiles.
     */
    private function registerUserFields(Stack $stack): void
    {
        // Show on own profile
        add_action('show_user_profile', function ($user) use ($stack) {
            $this->renderUserFields($stack, $user);
        });

        // Show on other user's profile (admin editing)
        add_action('edit_user_profile', function ($user) use ($stack) {
            $this->renderUserFields($stack, $user);
        });

        // Enqueue assets
        add_action('admin_enqueue_scripts', function ($hook) use ($stack) {
            if ($hook === 'profile.php' || $hook === 'user-edit.php') {
                $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : get_current_user_id();
                $this->enqueueAssets($stack, ['user_id' => $userId]);
            }
        });

        // Save user meta
        add_action('personal_options_update', function ($userId) use ($stack) {
            $this->saveUserData($stack, $userId);
        });
        add_action('edit_user_profile_update', function ($userId) use ($stack) {
            $this->saveUserData($stack, $userId);
        });
    }

    /**
     * Render fields on user profile.
     */
    private function renderUserFields(Stack $stack, \WP_User $user): void
    {
        ?>
        <h2><?php echo esc_html($stack->getLabel()); ?></h2>
        <?php if ($stack->getDescription()): ?>
            <p class="description"><?php echo esc_html($stack->getDescription()); ?></p>
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th scope="row">&nbsp;</th>
                <td>
                    <?php $this->renderMountPoint($stack, [
                        'object_id' => $user->ID,
                        'object_type' => 'user',
                    ]); ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save user data.
     */
    private function saveUserData(Stack $stack, int $userId): void
    {
        // Check permissions
        if (!current_user_can('edit_user', $userId)) {
            return;
        }

        // Get data from hidden field (JSON)
        $this->saveStackDataFromPost($stack, $userId, 'user');
    }

    // =========================================================================
    // SHARED HELPERS
    // =========================================================================

    /**
     * Save stack data from POST request (hidden field submission).
     *
     * @param Stack $stack The stack to save
     * @param int $objectId The object ID (post, term, or user ID)
     * @param string $objectType The object type ('post', 'term', or 'user')
     */
    private function saveStackDataFromPost(Stack $stack, int $objectId, string $objectType): void
    {
        $stackId = $stack->getId();

        // Check if we have data in the POST
        if (!isset($_POST['optstack_data'][$stackId])) {
            return;
        }

        // Get and decode the JSON data
        $jsonData = wp_unslash($_POST['optstack_data'][$stackId]);
        $data = json_decode($jsonData, true);

        if (!is_array($data)) {
            return;
        }

        // TODO: Add sanitization based on field types

        // Save based on object type
        switch ($objectType) {
            case 'post':
                update_post_meta($objectId, $stackId, $data);
                break;

            case 'term':
                update_term_meta($objectId, $stackId, $data);
                break;

            case 'user':
                update_user_meta($objectId, $stackId, $data);
                break;
        }

        /**
         * Action fired after stack data is saved.
         *
         * @param Stack $stack The stack that was saved
         * @param int $objectId The object ID
         * @param string $objectType The object type
         * @param array $data The saved data
         */
        do_action('optstack_data_saved', $stack, $objectId, $objectType, $data);
    }

    /**
     * Render the React mount point for a stack.
     */
    private function renderMountPoint(Stack $stack, array $extraData = []): void
    {
        $mountId = 'optstack-' . $stack->getId() . '-root';

        if (!$this->hasBuiltAssets()) {
            $this->renderBuildNotice();
            return;
        }

        // Convert underscore keys to hyphen for proper data attribute naming
        // data-object-id → dataset.objectId in JavaScript
        $dataAttrs = [];
        foreach ($extraData as $key => $value) {
            $attrName = str_replace('_', '-', $key);
            $dataAttrs[$attrName] = $value;
        }

        ?>
        <div 
            id="<?php echo esc_attr($mountId); ?>" 
            class="optstack-mount"
            data-stack="<?php echo esc_attr($stack->getId()); ?>"
            data-context="<?php echo esc_attr($stack->getContext()); ?>"
            <?php foreach ($dataAttrs as $key => $value): ?>
                data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
            <?php endforeach; ?>
        >
            <div style="padding: 20px; text-align: center; color: #666;">
                <?php esc_html_e('Loading...', 'optstack'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets.
     */
    private function enqueueAssets(Stack $stack, array $extraData = []): void
    {
        // Enqueue WordPress TinyMCE editor
        wp_enqueue_editor();
        wp_enqueue_media();

        // Enqueue WordPress component styles
        wp_enqueue_style('wp-components');

        // Check if dev mode is enabled
        $isDevMode = defined('OPTSTACK_DEV_MODE') && OPTSTACK_DEV_MODE;
        $devServer = defined('OPTSTACK_DEV_SERVER') ? OPTSTACK_DEV_SERVER : 'http://localhost:5173';

        if ($isDevMode) {
            // Load from Vite dev server
            $this->enqueueDevAssets($devServer);
        } else {
            // Load from built files
            $this->enqueueBuiltAssets();
        }

        // Pass data to JavaScript
        $localizeData = array_merge([
            'nonce' => wp_create_nonce('wp_rest'),
            'restUrl' => rest_url('optstack/v1/'),
            'adminUrl' => admin_url(),
            'stackId' => $stack->getId(),
            'context' => $stack->getContext(),
            'version' => OPTSTACK_VERSION,
            'devMode' => $isDevMode,
        ], $extraData);

        wp_localize_script('optstack-admin', 'optstack', $localizeData);
    }

    /**
     * Enqueue assets from Vite dev server (HMR mode).
     */
    private function enqueueDevAssets(string $devServer): void
    {
        // Vite client for HMR
        wp_enqueue_script(
            'vite-client',
            $devServer . '/@vite/client',
            [],
            null,
            false
        );

        // Add type="module" to vite client
        add_filter('script_loader_tag', function ($tag, $handle) {
            if ($handle === 'vite-client' || $handle === 'optstack-admin') {
                return str_replace(' src', ' type="module" src', $tag);
            }
            return $tag;
        }, 10, 2);

        // Main app from dev server
        wp_enqueue_script(
            'optstack-admin',
            $devServer . '/src/main.tsx',
            [
                'vite-client',
                'wp-element',
                'wp-components',
                'wp-data',
                'wp-api-fetch',
                'wp-i18n',
                'editor',
            ],
            null,
            true
        );
    }

    /**
     * Enqueue built/production assets.
     */
    private function enqueueBuiltAssets(): void
    {
        $distPath = OPTSTACK_DIR . 'frontend/dist/';
        $distUrl = OPTSTACK_URL . 'frontend/dist/';

        $jsFile = $distPath . 'optstack-admin.js';
        $cssFile = $distPath . 'optstack-main.css';

        if (!file_exists($jsFile)) {
            return;
        }

        $jsVersion = filemtime($jsFile);
        $cssVersion = file_exists($cssFile) ? filemtime($cssFile) : OPTSTACK_VERSION;

        // Enqueue CSS
        if (file_exists($cssFile)) {
            wp_enqueue_style(
                'optstack-admin',
                $distUrl . 'optstack-main.css',
                ['wp-components'],
                $cssVersion
            );
        }

        // Enqueue JS
        wp_enqueue_script(
            'optstack-admin',
            $distUrl . 'optstack-admin.js',
            [
                'wp-element',
                'wp-components',
                'wp-data',
                'wp-api-fetch',
                'wp-i18n',
                'editor',
            ],
            $jsVersion,
            true
        );
    }

    /**
     * Check if built assets exist.
     */
    private function hasBuiltAssets(): bool
    {
        return file_exists(OPTSTACK_DIR . 'frontend/dist/optstack-admin.js');
    }

    /**
     * Render notice when frontend is not built.
     */
    private function renderBuildNotice(): void
    {
        ?>
        <div class="notice notice-warning" style="margin: 10px 0;">
            <p>
                <strong><?php esc_html_e('OptStack frontend not built.', 'optstack'); ?></strong><br>
                <?php esc_html_e('Run:', 'optstack'); ?>
                <code>cd <?php echo esc_html(OPTSTACK_DIR); ?>frontend && npm install && npm run build</code>
            </p>
        </div>
        <?php
    }
}
