<?php

declare(strict_types=1);

namespace OptStack\WordPress\Customizer;

use OptStack\Core\Stack\Stack;
use WP_Customize_Control;

/**
 * Customizer control that renders the OptStack React admin UI for a stack.
 * Uses PHP-based rendering (render_content), not JS templates.
 */
class OptStackControl extends WP_Customize_Control
{
    /**
     * Control type identifier.
     *
     * @var string
     */
    public $type = 'optstack';

    /**
     * The OptStack stack instance.
     *
     * @var Stack|null
     */
    public $stack = null;

    /**
     * Constructor.
     *
     * @param \WP_Customize_Manager $manager
     * @param string                $id
     * @param array<string, mixed>  $args Must include 'stack' => Stack.
     */
    public function __construct($manager, $id, array $args = [])
    {
        if (isset($args['stack']) && $args['stack'] instanceof Stack) {
            $this->stack = $args['stack'];
        }
        unset($args['stack']);
        parent::__construct($manager, $id, $args);
    }

    /**
     * Render the control markup.
     * Outputs the OptStack React mount point inside the Customizer sidebar.
     */
    public function render_content(): void
    {
        if (!$this->stack instanceof Stack) {
            echo '<p>' . esc_html__('OptStack: stack not found.', 'optstack') . '</p>';
            return;
        }

        $stack = $this->stack;
        $mount_id = 'optstack-' . $stack->getId() . '-root';
        ?>
        <label>
            <?php if ($this->label): ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            <?php if ($this->description): ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
        </label>
        <div
            id="<?php echo esc_attr($mount_id); ?>"
            class="optstack-mount optstack-customizer-mount"
            data-stack="<?php echo esc_attr($stack->getId()); ?>"
            data-context="<?php echo esc_attr($stack->getContext()); ?>"
        >
            <div style="padding: 12px 0; color: #666;">
                <?php esc_html_e('Loading OptStack editor...', 'optstack'); ?>
            </div>
        </div>
        <?php
    }
}
