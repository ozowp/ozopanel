<?php

namespace OzoPanel\Ctrl\Asset;

use OzoPanel\Helper\I18n;

/**
 * AssetCtrl class.
 *
 * Responsible for managing all of the assets (CSS, JS, Images, Locales).
 */
class AssetCtrl {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_all_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Register all scripts and styles.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_all_scripts() {
        $this->register_styles( $this->get_styles() );
        $this->register_scripts( $this->get_scripts() );
    }

    /**
     * Get all styles.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_styles(): array {
        return array(
            'ozopanel-dashboard' => array(
                'src'     => OZOPANEL_BUILD . '/index.css',
                'version' => OZOPANEL_VERSION,
                'deps'    => array(),
            ),
        );
    }

    /**
     * Get all scripts.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_scripts(): array {
        $dependency = require_once OZOPANEL_DIR . '/build/index.asset.php';

        return array(
            'ozopanel-dashboard' => array(
                'src'       => OZOPANEL_BUILD . '/index.js',
                'version'   => $dependency['version'],
                'deps'      => $dependency['dependencies'],
                'in_footer' => true,
            ),
        );
    }

    /**
     * Register styles.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_styles( array $styles ) {
        foreach ( $styles as $handle => $style ) {
            wp_register_style( $handle, $style['src'], $style['deps'], $style['version'] );
        }
    }

    /**
     * Register scripts.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_scripts( array $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            wp_register_script( $handle, $script['src'], $script['deps'], $script['version'], $script['in_footer'] );
        }
    }

    /**
     * Enqueue admin styles and scripts.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_admin_assets() {
        // Check if we are on the admin page and page=ozopanel.

        // It getting from admin menu page URL, no need to check NonceVerification
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        /* if ( ! is_admin() || ! isset( $_GET['page'] ) || sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== 'ozopanel' ) {
            return;
        } */

        wp_enqueue_style( 'ozopanel-dashboard' );
        wp_enqueue_script( 'ozopanel-dashboard' );

        wp_localize_script(
            'ozopanel-dashboard', 'ozopanel', array(
				'i18n' => I18n::app(),
            )
        );
    }
}
