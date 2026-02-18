<?php
/**
 * OptStack Gutenberg Block Examples
 *
 * Registers example blocks with OptStack-powered settings.
 * Use the optstack_render_block filter to provide frontend output.
 *
 * @package OptStack
 */

declare(strict_types=1);

use OptStack\OptStack;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('optstack_init', function (): void {
    // -------------------------------------------------------------------------
    // Example 1: Hero Block (text, textarea, color, media)
    // -------------------------------------------------------------------------
    OptStack::make('hero_block')
        ->forBlockType('optstack/hero')
        ->label('Hero Block')
        ->blockTitle('Hero')
        ->blockCategory('theme')
        ->blockIcon('cover-image')
        ->description('A configurable hero section block')
        ->define(function ($stack) {
            $stack->field('title', [
                'type'    => 'text',
                'label'   => 'Title',
                'default' => 'Welcome',
            ]);

            $stack->field('subtitle', [
                'type'    => 'textarea',
                'label'   => 'Subtitle',
                'default' => '',
            ]);

            $stack->field('background_color', [
                'type'    => 'color',
                'label'   => 'Background Color',
                'default' => '#2271b1',
            ]);

            $stack->field('image', [
                'type'  => 'media',
                'label' => 'Background Image',
            ]);
        })
        ->build();

    // -------------------------------------------------------------------------
    // Example 2: Call to Action Block (more field types: select, toggle, number, range, url, date)
    // -------------------------------------------------------------------------
    OptStack::make('cta_block')
        ->forBlockType('optstack/cta')
        ->label('Call to Action')
        ->blockTitle('Call to Action')
        ->blockCategory('theme')
        ->blockIcon('megaphone')
        ->description('Button or banner with heading, text, and link')
        ->define(function ($stack) {
            $stack->field('heading', [
                'type'    => 'text',
                'label'   => 'Heading',
                'default' => 'Ready to get started?',
            ]);

            $stack->field('description', [
                'type'    => 'textarea',
                'label'   => 'Description',
                'default' => 'Join us today and discover the difference.',
            ]);

            $stack->field('style', [
                'type'    => 'select',
                'label'   => 'Style',
                'default' => 'primary',
                'options' => [
                    ['value' => 'primary',   'label' => 'Primary'],
                    ['value' => 'secondary', 'label' => 'Secondary'],
                    ['value' => 'outline',   'label' => 'Outline'],
                ],
            ]);

            $stack->field('button_text', [
                'type'    => 'text',
                'label'   => 'Button Text',
                'default' => 'Learn more',
            ]);

            $stack->field('button_url', [
                'type'  => 'url',
                'label' => 'Button URL',
                'attributes' => ['placeholder' => 'https://'],
            ]);

            $stack->field('open_new_tab', [
                'type'    => 'toggle',
                'label'   => 'Open in new tab',
                'default' => false,
            ]);

            $stack->field('alignment', [
                'type'    => 'radio',
                'label'   => 'Alignment',
                'default' => 'center',
                'options' => [
                    ['value' => 'left', 'label' => 'Left'],
                    ['value' => 'center', 'label' => 'Center'],
                    ['value' => 'right', 'label' => 'Right'],
                ],
            ]);

            $stack->field('padding_size', [
                'type'    => 'range',
                'label'   => 'Padding',
                'default' => 24,
                'attributes' => [
                    'min'  => 8,
                    'max'  => 48,
                    'step' => 4,
                ],
            ]);

            $stack->field('accent_color', [
                'type'    => 'color',
                'label'   => 'Accent Color',
                'default' => '#3b82f6',
            ]);
        })
        ->build();

    // -------------------------------------------------------------------------
    // Example 3: Feature Card (group, media, toggle, number)
    // -------------------------------------------------------------------------
    OptStack::make('feature_card_block')
        ->forBlockType('optstack/feature-card')
        ->label('Feature Card')
        ->blockTitle('Feature Card')
        ->blockCategory('theme')
        ->blockIcon('grid-view')
        ->description('Icon or image with title and short description')
        ->define(function ($stack) {
            $stack->field('title', [
                'type'    => 'text',
                'label'   => 'Title',
                'default' => 'Feature title',
            ]);

            $stack->field('excerpt', [
                'type'    => 'textarea',
                'label'   => 'Excerpt',
                'default' => 'Short description of the feature.',
            ]);

            $stack->field('icon_or_image', [
                'type'    => 'select',
                'label'   => 'Visual type',
                'default' => 'icon',
                'options' => [
                    ['label' => 'Icon (emoji)', 'value' => 'icon'],
                    ['label' => 'Image', 'value' => 'image']
                ],
            ]);

            $stack->field('icon_emoji', [
                'type'    => 'text',
                'label'   => 'Icon (emoji)',
                'default' => '✨',
                'description' => 'Single emoji or character',
            ]);

            $stack->field('image', [
                'type'  => 'media',
                'label' => 'Image',
            ]);

            $stack->field('show_border', [
                'type'    => 'toggle',
                'label'   => 'Show border',
                'default' => true,
            ]);

            $stack->field('link_url', [
                'type'  => 'url',
                'label' => 'Link URL',
            ]);
        })
        ->build();

    // -------------------------------------------------------------------------
    // Example 4: Testimonials Block (repeatable group)
    // -------------------------------------------------------------------------
    OptStack::make('testimonials_block')
        ->forBlockType('optstack/testimonials')
        ->label('Testimonials')
        ->blockTitle('Testimonials')
        ->blockCategory('theme')
        ->blockIcon('format-quote')
        ->description('Repeating testimonial cards with name, quote, and role')
        ->define(function ($stack) {
            $stack->field('section_title', [
                'type'    => 'text',
                'label'   => 'Section Title',
                'default' => 'What people say',
            ]);

            $stack->group('testimonials', function ($group) {
                $group->group('items', function ($group_item) {
                    $group_item->repeatable(0, 5);
                    $group_item->field('testimonial', [
                        'type'    => 'textarea',
                        'label'   => 'Testimonial',
                        'default' => '',
                    ]);
                    $group_item->field('name', [
                        'type'    => 'text',
                        'label'   => 'Name',
                        'default' => '',
                    ]);
                    $group_item->field('role', [
                        'type'    => 'text',
                        'label'   => 'Role / Title',
                        'default' => '',
                    ]);
                });
            }, [
                'label' => 'Testimonials',
                'deferred' => true,
                'ui' => [
                    'triggerLabel' => 'Configure Testimonials',
                    'render' => 'modal',
                ],
            ]);
        })
        ->build();
});

// -----------------------------------------------------------------------------
// Frontend render: Hero Block
// -----------------------------------------------------------------------------
add_filter('optstack_render_block', function (string $html, string $stackId, array $attributes, $block): string {
    if ($stackId !== 'hero_block') {
        return $html;
    }

    $title = $attributes['title'] ?? 'Welcome';
    $subtitle = $attributes['subtitle'] ?? '';
    $bgColor = $attributes['background_color'] ?? '#2271b1';
    $image = $attributes['image'] ?? [];
    $imageUrl = is_array($image) && !empty($image['url']) ? $image['url'] : '';

    ob_start();
    ?>
    <div class="optstack-hero" style="background-color: <?php echo esc_attr($bgColor); ?>; min-height: 200px; padding: 40px; text-align: center; position: relative;">
        <?php if ($imageUrl): ?>
            <div style="position: absolute; inset: 0; background-image: url(<?php echo esc_url($imageUrl); ?>); background-size: cover; opacity: 0.3;"></div>
        <?php endif; ?>
        <div style="position: relative; z-index: 1;">
            <h2 style="color: #fff; margin: 0 0 16px;"><?php echo esc_html($title); ?></h2>
            <?php if ($subtitle): ?>
                <p style="color: rgba(255,255,255,0.9); margin: 0;"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}, 10, 4);

// -----------------------------------------------------------------------------
// Frontend render: Call to Action Block
// -----------------------------------------------------------------------------
add_filter('optstack_render_block', function (string $html, string $stackId, array $attributes, $block): string {
    if ($stackId !== 'cta_block') {
        return $html;
    }

    $heading = $attributes['heading'] ?? 'Ready to get started?';
    $description = $attributes['description'] ?? '';
    $style = $attributes['style'] ?? 'primary';
    $buttonText = $attributes['button_text'] ?? 'Learn more';
    $buttonUrl = $attributes['button_url'] ?? '';
    $openNewTab = !empty($attributes['open_new_tab']);
    $alignment = $attributes['alignment'] ?? 'center';
    $padding = (int) ($attributes['padding_size'] ?? 24);
    $accentColor = $attributes['accent_color'] ?? '#3b82f6';

    $target = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';
    $btnStyles = [
        'primary'   => 'background:' . $accentColor . '; color:#fff; border:none; padding:10px 20px; border-radius:6px; text-decoration:none; display:inline-block;',
        'secondary' => 'background:#e5e7eb; color:#374151; border:none; padding:10px 20px; border-radius:6px; text-decoration:none; display:inline-block;',
        'outline'   => 'background:transparent; color:' . $accentColor . '; border:2px solid ' . $accentColor . '; padding:8px 18px; border-radius:6px; text-decoration:none; display:inline-block;',
    ];
    $btnStyle = $btnStyles[$style] ?? $btnStyles['primary'];

    ob_start();
    ?>
    <div class="optstack-cta" style="padding: <?php echo (int) $padding; ?>px; text-align: <?php echo esc_attr($alignment); ?>; border-left: 4px solid <?php echo esc_attr($accentColor); ?>;">
        <h3 style="margin: 0 0 8px;"><?php echo esc_html($heading); ?></h3>
        <?php if ($description): ?>
            <p style="margin: 0 0 16px; color: #374151;"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <?php if ($buttonUrl): ?>
            <a href="<?php echo esc_url($buttonUrl); ?>" style="<?php echo esc_attr($btnStyle); ?>"<?php echo $target; ?>><?php echo esc_html($buttonText); ?></a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}, 10, 4);

// -----------------------------------------------------------------------------
// Frontend render: Feature Card Block
// -----------------------------------------------------------------------------
add_filter('optstack_render_block', function (string $html, string $stackId, array $attributes, $block): string {
    if ($stackId !== 'feature_card_block') {
        return $html;
    }

    $title = $attributes['title'] ?? 'Feature title';
    $excerpt = $attributes['excerpt'] ?? '';
    $visualType = $attributes['icon_or_image'] ?? 'icon';
    $iconEmoji = $attributes['icon_emoji'] ?? '✨';
    $image = $attributes['image'] ?? [];
    $imageUrl = is_array($image) && !empty($image['url']) ? $image['url'] : '';
    $showBorder = !empty($attributes['show_border']);
    $linkUrl = $attributes['link_url'] ?? '';

    $borderStyle = $showBorder ? ' border: 1px solid #e5e7eb;' : '';
    $content = '';
    if ($visualType === 'image' && $imageUrl) {
        $content = '<img src="' . esc_url($imageUrl) . '" alt="" style="max-width: 64px; height: auto; display: block; margin-bottom: 12px;" />';
    } else {
        $content = '<span style="font-size: 2rem; line-height: 1; display: block; margin-bottom: 12px;">' . esc_html($iconEmoji) . '</span>';
    }

    ob_start();
    ?>
    <div class="optstack-feature-card" style="padding: 20px; background: #f9fafb; border-radius: 8px;<?php echo $borderStyle; ?>">
        <?php echo $content; ?>
        <h4 style="margin: 0 0 8px;"><?php echo esc_html($title); ?></h4>
        <?php if ($excerpt): ?>
            <p style="margin: 0; font-size: 14px; color: #6b7280;"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        <?php if ($linkUrl): ?>
            <p style="margin: 12px 0 0;"><a href="<?php echo esc_url($linkUrl); ?>">Read more</a></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}, 10, 4);

// -----------------------------------------------------------------------------
// Frontend render: Testimonials Block (repeatable group)
// -----------------------------------------------------------------------------
add_filter('optstack_render_block', function (string $html, string $stackId, array $attributes, $block): string {
    if ($stackId !== 'testimonials_block') {
        return $html;
    }

    $sectionTitle = $attributes['section_title'] ?? 'What people say';
    $itemsData = $attributes['testimonials'] ?? [];
    $items = (is_array($itemsData) && isset($itemsData['items']) && is_array($itemsData['items']))
        ? $itemsData['items']
        : (is_array($itemsData) && !isset($itemsData['items']) ? $itemsData : []);

    ob_start();
    ?>
    <div class="optstack-testimonials" style="padding: 32px 0;">
        <?php if ($sectionTitle): ?>
            <h3 style="margin: 0 0 24px; font-size: 1.5rem;"><?php echo esc_html($sectionTitle); ?></h3>
        <?php endif; ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($items as $item): ?>
                <?php
                $name = $item['name'] ?? '';
                $quote = $item['testimonial'] ?? $item['quote'] ?? '';
                $role = $item['role'] ?? '';
                if (!$quote && !$name) {
                    continue;
                }
                ?>
                <div style="padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <?php if ($quote): ?>
                        <blockquote style="margin: 0 0 12px; font-style: italic; color: #374151;"><?php echo esc_html($quote); ?></blockquote>
                    <?php endif; ?>
                    <?php if ($name || $role): ?>
                        <footer style="font-size: 0.875rem; color: #6b7280;">
                            <strong><?php echo esc_html($name); ?></strong><?php echo $role ? ' – ' . esc_html($role) : ''; ?>
                        </footer>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}, 10, 4);
