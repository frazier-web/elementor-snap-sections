<?php
/**
 * Plugin Name: TP Skew Slider
 * Plugin URI:  https://github.com/your-repo/tp-skew-slider
 * Description: A full-viewport skew slider for Elementor. Each slide is an empty shell that accepts a custom Elementor template or pasted section JSON. Butter-smooth scroll, sticky behaviour, infinite loop.
 * Version:     1.0.0
 * Author:      Your Agency
 * Author URI:  #
 * License:     GPL-2.0-or-later
 * Text Domain: tp-skew-slider
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Elementor tested up to: 3.25
 * Elementor Pro tested up to: 3.25
 */

defined( 'ABSPATH' ) || exit;

define( 'TP_SKEW_SLIDER_VERSION', '1.0.0' );
define( 'TP_SKEW_SLIDER_FILE',    __FILE__ );
define( 'TP_SKEW_SLIDER_DIR',     plugin_dir_path( __FILE__ ) );
define( 'TP_SKEW_SLIDER_URL',     plugin_dir_url( __FILE__ ) );

final class TP_Skew_Slider_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            return;
        }

        add_action( 'elementor/widgets/register',      [ $this, 'register_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'wp_enqueue_scripts',              [ $this, 'enqueue_frontend_assets' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
        add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_preview_assets' ] );
    }

    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'tp-elements',
            [
                'title' => __( 'TP Elements', 'tp-skew-slider' ),
                'icon'  => 'fa fa-plug',
            ]
        );
    }

    public function register_widgets( $widgets_manager ) {
        require_once TP_SKEW_SLIDER_DIR . 'widgets/class-skew-slider-widget.php';
        $widgets_manager->register( new \TP_Skew_Slider\Widget() );
    }

    public function enqueue_frontend_assets() {
        if ( ! is_singular() && ! is_archive() ) {
            return;
        }

        $this->_register_assets();

        if ( \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
            $this->_enqueue_assets();
        }
    }

    public function enqueue_editor_assets() {
        $this->_register_assets();
        $this->_enqueue_assets();
    }

    public function enqueue_preview_assets() {
        $this->_register_assets();
        $this->_enqueue_assets();
    }

    private function _register_assets() {
        wp_register_style(
            'tp-skew-slider',
            TP_SKEW_SLIDER_URL . 'assets/css/tp-skew-slider.css',
            [],
            TP_SKEW_SLIDER_VERSION
        );

        wp_register_script(
            'tp-skew-slider-slideshow',
            TP_SKEW_SLIDER_URL . 'assets/js/slideshow.js',
            [],
            TP_SKEW_SLIDER_VERSION,
            true
        );

        wp_register_script(
            'tp-skew-slider',
            TP_SKEW_SLIDER_URL . 'assets/js/tp-skew-slider.js',
            [ 'jquery', 'tp-skew-slider-slideshow' ],
            TP_SKEW_SLIDER_VERSION,
            true
        );
    }

    private function _enqueue_assets() {
        wp_enqueue_style( 'tp-skew-slider' );
        wp_enqueue_script( 'tp-skew-slider-slideshow' );
        wp_enqueue_script( 'tp-skew-slider' );
    }

    public function admin_notice_missing_elementor() {
        $message = sprintf(
            '<strong>%s</strong> %s <strong>%s</strong>.',
            __( 'TP Skew Slider', 'tp-skew-slider' ),
            __( 'requires', 'tp-skew-slider' ),
            __( 'Elementor', 'tp-skew-slider' )
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message );
    }
}

TP_Skew_Slider_Plugin::instance();
