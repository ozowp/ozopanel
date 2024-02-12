<?php

namespace OzoPanel\Assets;

use OzoPanel\Helpers\I18n;

/**
 * Asset Manager class.
 *
 * Responsible for managing all of the assets (CSS, JS, Images, Locales).
 * 
 * @since 0.1.0
 */
class Manager {

    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_all_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Register all scripts and styles.
     *
     * @since 0.1.0
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
     * @since 0.1.0
     *
     * @return array
     */
    public function get_styles(): array {
        return [
            'ozopanel' => [
                'src'     => OZOPANEL_BUILD . '/index.css',
                'version' => OZOPANEL_VERSION,
                'deps'    => [],
            ],
        ];
    }

    /**
     * Get all scripts.
     *
     * @since 0.1.0
     *
     * @return array
     */
    public function get_scripts(): array {
        $dependency = require_once OZOPANEL_DIR . '/build/index.asset.php';

        return [
            'ozopanel' => [
                'src'       => OZOPANEL_BUILD . '/index.js',
                'version'   => $dependency['version'],
                'deps'      => $dependency['dependencies'],
                'in_footer' => true,
            ],
        ];
    }

    /**
     * Register styles.
     *
     * @since 0.1.0
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
     * @since 0.1.0
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
     * @since 0.1.0
     *
     * @return void
     */
    public function enqueue_admin_assets() {

        /**
         * Show/Hide nav menu roles selections option
         *
         * @since 0.1.0
         */

        // It getting from admin menu page URL, no need to check NonceVerification
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_admin() && isset( $GLOBALS['pagenow'] ) && sanitize_text_field( wp_unslash( $GLOBALS['pagenow'] ) ) === 'nav-menus.php' ) {
            ob_start();
			?>
                document.addEventListener("DOMContentLoaded", function() {
                    var navMenuContainer = document.getElementById('menu-to-edit');

                    // Function to toggle the display of roles fields
                    function toggleRolesFields(select) {
                        var rolesField = select.closest('li').querySelector('.field-ozopanel-roles');
                        if (select.value === 'roles') {
                            rolesField.style.display = 'block';
                        } else {
                            rolesField.style.display = 'none';
                        }
                    }

                    // Set initial state for existing menu items
                    navMenuContainer.querySelectorAll('.edit-menu-item-ozopanel-who-can-see').forEach(function(select) {
                        toggleRolesFields(select);
                    });

                    // Event delegation for dynamically added menu items
                    navMenuContainer.addEventListener('change', function(event) {
                        if (event.target && event.target.classList.contains('edit-menu-item-ozopanel-who-can-see')) {
                            toggleRolesFields(event.target);
                        }
                    });
                });                
            <?php
            $script = ob_get_clean();

            wp_add_inline_script( 'nav-menu', $script );
        }

        // It getting from admin menu page URL, no need to check NonceVerification
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! is_admin() || ! isset( $_GET['page'] ) || sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== 'ozopanel' ) {
            return;
        }

        wp_enqueue_style( 'ozopanel' );
        wp_enqueue_script( 'ozopanel' );

        wp_localize_script(
            'ozopanel', 'ozopanel', [
				'i18n' => I18n::app(),
            ]
        );
    }
}
