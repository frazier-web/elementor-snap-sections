<?php
namespace TP_Skew_Slider;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * TP Skew Slider Widget
 *
 * Each slide is an "empty shell" — the background is either:
 *   (a) a background image/video, or
 *   (b) left fully transparent so an Elementor template rendered
 *       inside `.slide__content-area` shows through.
 *
 * The JS/animation layer is identical to the original theme widget.
 * Navigation, scroll-hijack, and sticky behaviour are all preserved.
 */
class Widget extends Widget_Base {

    public function get_name() {
        return 'tp-skew-slider';
    }

    public function get_title() {
        return __( 'TP Skew Slider', 'tp-skew-slider' );
    }

    public function get_icon() {
        return 'eicon-slides';
    }

    public function get_categories() {
        return [ 'tp-elements' ];
    }

    public function get_keywords() {
        return [ 'skew', 'slider', 'portfolio', 'fullscreen', 'scroll' ];
    }

    public function get_script_depends() {
        return [ 'tp-skew-slider' ];
    }

    public function get_style_depends() {
        return [ 'tp-skew-slider' ];
    }

    protected function register_controls() {

        /* ---------------------------------------------------------------
         * SECTION: SLIDES
         * ------------------------------------------------------------- */
        $this->start_controls_section(
            'section_slides',
            [
                'label' => __( 'Slides', 'tp-skew-slider' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        /* --- Background type --- */
        $repeater->add_control(
            'bg_type',
            [
                'label'   => __( 'Slide Background', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image'    => __( 'Image', 'tp-skew-slider' ),
                    'color'    => __( 'Solid Color', 'tp-skew-slider' ),
                    'none'     => __( 'None (Template fills slide)', 'tp-skew-slider' ),
                ],
            ]
        );

        $repeater->add_control(
            'bg_image',
            [
                'label'     => __( 'Background Image', 'tp-skew-slider' ),
                'type'      => Controls_Manager::MEDIA,
                'default'   => [ 'url' => Utils::get_placeholder_image_src() ],
                'condition' => [ 'bg_type' => 'image' ],
            ]
        );

        $repeater->add_control(
            'bg_color',
            [
                'label'     => __( 'Background Color', 'tp-skew-slider' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111111',
                'condition' => [ 'bg_type' => 'color' ],
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .slide__img' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        /* --- Overlay --- */
        $repeater->add_control(
            'overlay_color',
            [
                'label'   => __( 'Image Overlay', 'tp-skew-slider' ),
                'type'    => Controls_Manager::COLOR,
                'default' => 'rgba(0,0,0,0.35)',
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .slide__overlay' => 'background: {{VALUE}};',
                ],
            ]
        );

        /* --- Content area: choose template OR enter raw HTML/shortcode --- */
        $repeater->add_control(
            'content_source',
            [
                'label'   => __( 'Slide Content Source', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'custom'   => __( 'Custom HTML / Shortcode', 'tp-skew-slider' ),
                    'template' => __( 'Elementor Template', 'tp-skew-slider' ),
                    'none'     => __( 'None', 'tp-skew-slider' ),
                ],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'template_id',
            [
                'label'       => __( 'Choose Template', 'tp-skew-slider' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->_get_elementor_templates(),
                'label_block' => true,
                'condition'   => [ 'content_source' => 'template' ],
            ]
        );

        $repeater->add_control(
            'custom_content',
            [
                'label'       => __( 'Custom HTML / Shortcode', 'tp-skew-slider' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => '',
                'placeholder' => __( 'Paste an Elementor JSON section or any HTML/shortcode here.', 'tp-skew-slider' ),
                'condition'   => [ 'content_source' => 'custom' ],
                'label_block' => true,
            ]
        );

        /* --- Optional text overlay (legacy / quick-use) --- */
        $repeater->add_control(
            'show_text_overlay',
            [
                'label'     => __( 'Show Text Overlay', 'tp-skew-slider' ),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => 'yes',
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'label',
            [
                'label'       => __( 'Label', 'tp-skew-slider' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Category', 'tp-skew-slider' ),
                'label_block' => true,
                'condition'   => [ 'show_text_overlay' => 'yes' ],
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label'       => __( 'Title', 'tp-skew-slider' ),
                'type'        => Controls_Manager::TEXTAREA,
                'default'     => __( 'Slide Title', 'tp-skew-slider' ),
                'label_block' => true,
                'condition'   => [ 'show_text_overlay' => 'yes' ],
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label'       => __( 'Title Link', 'tp-skew-slider' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => 'https://',
                'default'     => [ 'url' => '' ],
                'condition'   => [ 'show_text_overlay' => 'yes' ],
            ]
        );

        $this->add_control(
            'slides',
            [
                'label'   => __( 'Slides', 'tp-skew-slider' ),
                'type'    => Controls_Manager::REPEATER,
                'fields'  => $repeater->get_controls(),
                'default' => [
                    [ 'label' => __( 'Digital Platform', 'tp-skew-slider' ), 'title' => "Project\nOne" ],
                    [ 'label' => __( 'Digital Platform', 'tp-skew-slider' ), 'title' => "Project\nTwo" ],
                    [ 'label' => __( 'Digital Platform', 'tp-skew-slider' ), 'title' => "Project\nThree" ],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------------------------------------
         * SECTION: NAVIGATION
         * ------------------------------------------------------------- */
        $this->start_controls_section(
            'section_nav',
            [
                'label' => __( 'Navigation', 'tp-skew-slider' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'prev_label',
            [
                'label'   => __( 'Prev Button Text', 'tp-skew-slider' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Prev', 'tp-skew-slider' ),
            ]
        );

        $this->add_control(
            'next_label',
            [
                'label'   => __( 'Next Button Text', 'tp-skew-slider' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Next', 'tp-skew-slider' ),
            ]
        );

        $this->add_control(
            'show_counter',
            [
                'label'   => __( 'Show Slide Counter', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_copyright',
            [
                'label'   => __( 'Show Footer Text', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'copyright_text',
            [
                'label'     => __( 'Footer Text', 'tp-skew-slider' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( "Have a project in mind? Let's Talk.", 'tp-skew-slider' ),
                'condition' => [ 'show_copyright' => 'yes' ],
            ]
        );

        $this->add_control(
            'copyright_link',
            [
                'label'     => __( 'Footer Link', 'tp-skew-slider' ),
                'type'      => Controls_Manager::URL,
                'default'   => [ 'url' => '#' ],
                'condition' => [ 'show_copyright' => 'yes' ],
            ]
        );

        $this->add_control(
            'show_social',
            [
                'label'   => __( 'Show Social Links', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'social_fb',
            [
                'label'     => __( 'Facebook URL', 'tp-skew-slider' ),
                'type'      => Controls_Manager::URL,
                'default'   => [ 'url' => '#' ],
                'condition' => [ 'show_social' => 'yes' ],
            ]
        );

        $this->add_control(
            'social_in',
            [
                'label'     => __( 'LinkedIn URL', 'tp-skew-slider' ),
                'type'      => Controls_Manager::URL,
                'default'   => [ 'url' => '#' ],
                'condition' => [ 'show_social' => 'yes' ],
            ]
        );

        $this->add_control(
            'social_be',
            [
                'label'     => __( 'Behance URL', 'tp-skew-slider' ),
                'type'      => Controls_Manager::URL,
                'default'   => [ 'url' => '#' ],
                'condition' => [ 'show_social' => 'yes' ],
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------------------------------------
         * SECTION: SCROLL BEHAVIOUR
         * ------------------------------------------------------------- */
        $this->start_controls_section(
            'section_scroll',
            [
                'label' => __( 'Scroll Behaviour', 'tp-skew-slider' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'scroll_mode',
            [
                'label'   => __( 'Scroll Mode', 'tp-skew-slider' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'hijack',
                'options' => [
                    'hijack'   => __( 'Hijack page scroll (sticky, release after last slide)', 'tp-skew-slider' ),
                    'freeflow' => __( 'Free-flow (slider scrolls with page)', 'tp-skew-slider' ),
                ],
                'description' => __( '"Hijack" replicates the original demo behaviour: slider sticks to top, each scroll tick advances one slide, page continues scrolling after the final slide.', 'tp-skew-slider' ),
            ]
        );

        $this->add_control(
            'wheel_tolerance',
            [
                'label'   => __( 'Wheel Tolerance (px)', 'tp-skew-slider' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 10,
                'min'     => 1,
                'max'     => 100,
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------------------------------------
         * SECTION: STYLE — TITLE
         * ------------------------------------------------------------- */
        $this->start_controls_section(
            'section_style_title',
            [
                'label' => __( 'Title Typography', 'tp-skew-slider' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Color', 'tp-skew-slider' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .skew-slider-content h4,
                     {{WRAPPER}} .skew-slider-content h4 a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_font_size',
            [
                'label'      => __( 'Font Size', 'tp-skew-slider' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vw' ],
                'range'      => [ 'px' => [ 'min' => 20, 'max' => 220 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 120 ],
                'selectors'  => [
                    '{{WRAPPER}} .skew-slider-content h4' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_padding_left',
            [
                'label'      => __( 'Content Left Offset', 'tp-skew-slider' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vw' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 600 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 160 ],
                'selectors'  => [
                    '{{WRAPPER}} .skew-slider-content' => 'padding-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => __( 'Label Color', 'tp-skew-slider' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'rgba(255,255,255,0.7)',
                'selectors' => [
                    '{{WRAPPER}} .skew-slider-content > span' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        /* ---------------------------------------------------------------
         * SECTION: STYLE — NAVIGATION
         * ------------------------------------------------------------- */
        $this->start_controls_section(
            'section_style_nav',
            [
                'label' => __( 'Navigation Style', 'tp-skew-slider' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'nav_color',
            [
                'label'     => __( 'Button Color', 'tp-skew-slider' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .skew-slider-arrow button,
                     {{WRAPPER}} .skew-slider-arrow button svg path' => 'color: {{VALUE}}; stroke: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $slides   = $settings['slides'] ?? [];

        if ( empty( $slides ) ) {
            echo '<p class="tp-skew-no-slides">' . esc_html__( 'Please add slides in the widget settings.', 'tp-skew-slider' ) . '</p>';
            return;
        }

        $total       = count( $slides );
        $scroll_mode = $settings['scroll_mode'] ?? 'hijack';
        $tolerance   = absint( $settings['wheel_tolerance'] ?? 10 );
        ?>
        <div class="skew-slider-area tp-skew-slider-widget"
             data-scroll-mode="<?php echo esc_attr( $scroll_mode ); ?>"
             data-tolerance="<?php echo esc_attr( $tolerance ); ?>">

            <div class="skew-slider-wrap">
                <?php foreach ( $slides as $index => $slide ) : ?>
                    <div class="skew-slider-item slide elementor-repeater-item-<?php echo esc_attr( $slide['_id'] ); ?>">

                        <?php $this->_render_slide_background( $slide ); ?>

                        <div class="slide__overlay"></div>

                        <div class="slide__content-area">
                            <?php $this->_render_slide_content( $slide ); ?>
                        </div>

                        <?php if ( 'yes' === ( $slide['show_text_overlay'] ?? 'yes' ) && ! empty( $slide['title'] ) ) : ?>
                            <div class="skew-slider-content">
                                <?php if ( ! empty( $slide['label'] ) ) : ?>
                                    <span><?php echo esc_html( $slide['label'] ); ?></span>
                                <?php endif; ?>
                                <h4>
                                    <?php if ( ! empty( $slide['link']['url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $slide['link']['url'] ); ?>"
                                           <?php echo ! empty( $slide['link']['is_external'] ) ? 'target="_blank"' : ''; ?>
                                           <?php echo ! empty( $slide['link']['nofollow'] ) ? 'rel="nofollow"' : ''; ?>>
                                            <?php echo wp_kses_post( nl2br( esc_html( $slide['title'] ) ) ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo wp_kses_post( nl2br( esc_html( $slide['title'] ) ) ); ?>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ( 'yes' === ( $settings['show_copyright'] ?? 'yes' ) ) : ?>
                <div class="tp-portfolio-slider__copyright">
                    <p>
                        <?php if ( ! empty( $settings['copyright_link']['url'] ) ) : ?>
                            <a href="<?php echo esc_url( $settings['copyright_link']['url'] ); ?>">
                                <?php echo esc_html( $settings['copyright_text'] ?? '' ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $settings['copyright_text'] ?? '' ); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ( 'yes' === ( $settings['show_social'] ?? 'yes' ) ) : ?>
                <div class="tp-portfolio-slider__social">
                    <?php if ( ! empty( $settings['social_fb']['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $settings['social_fb']['url'] ); ?>">Fb</a>
                    <?php endif; ?>
                    <?php if ( ! empty( $settings['social_in']['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $settings['social_in']['url'] ); ?>">In</a>
                    <?php endif; ?>
                    <?php if ( ! empty( $settings['social_be']['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $settings['social_be']['url'] ); ?>">Be</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="skew-slider-arrow slides-nav">
                <button class="skew-slider-prev d-flex align-items-center" aria-label="<?php esc_attr_e( 'Previous slide', 'tp-skew-slider' ); ?>">
                    <span class="icon-1">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 1L1 7L7 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="ml-5 tp-el-prev"><?php echo esc_html( $settings['prev_label'] ?? __( 'Prev', 'tp-skew-slider' ) ); ?></span>
                </button>

                <button class="skew-slider-next d-flex align-items-center" aria-label="<?php esc_attr_e( 'Next slide', 'tp-skew-slider' ); ?>">
                    <span class="slider-nav-text mr-5 tp-el-next"><?php echo esc_html( $settings['next_label'] ?? __( 'Next', 'tp-skew-slider' ) ); ?></span>
                    <span class="icon-2">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 13L7 7L1 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            </div>

            <?php if ( 'yes' === ( $settings['show_counter'] ?? 'yes' ) ) : ?>
                <div class="slides-numbers-wrap">
                    <div class="slides-numbers">
                        <span class="active text-1">1</span>
                        <span class="text-2">/</span>
                        <span class="text-3"><?php echo esc_html( $total ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }

    /**
     * Render the background layer for a single slide.
     */
    private function _render_slide_background( $slide ) {
        $bg_type = $slide['bg_type'] ?? 'image';

        if ( 'image' === $bg_type && ! empty( $slide['bg_image']['url'] ) ) {
            $url = esc_url( $slide['bg_image']['url'] );
            echo '<div class="slide__img" style="background-image:url(\'' . $url . '\')"></div>';
        } elseif ( 'color' === $bg_type ) {
            echo '<div class="slide__img"></div>';
        } else {
            echo '<div class="slide__img slide__img--transparent"></div>';
        }
    }

    /**
     * Render the per-slide content (template or custom HTML).
     */
    private function _render_slide_content( $slide ) {
        $source = $slide['content_source'] ?? 'none';

        if ( 'template' === $source && ! empty( $slide['template_id'] ) ) {
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display(
                (int) $slide['template_id'],
                true
            );

        } elseif ( 'custom' === $source && ! empty( $slide['custom_content'] ) ) {
            echo do_shortcode( wp_kses_post( $slide['custom_content'] ) );
        }
    }

    /**
     * Retrieve all Elementor templates for the template-picker control.
     */
    private function _get_elementor_templates() {
        $templates = [];
        $query = new \WP_Query( [
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ] );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $templates[ $post->ID ] = $post->post_title;
            }
        }

        return $templates;
    }
}
