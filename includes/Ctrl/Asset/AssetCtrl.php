<?php

namespace OzoPanel\Ctrl\Asset;

use OzoPanel\Helper\Fns;
use OzoPanel\Helper\I18n;

/**
 * All plugin asset
 *
 * @since 1.0.0
 */
class AssetCtrl
{
    private $version;

    public function __construct()
    {
        $this->version = defined('WP_DEBUG') && WP_DEBUG ? time() : ozopanel()->version;

        add_action('wp_enqueue_scripts', [$this, 'public_scripts'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 9999);

        add_filter('script_loader_tag', [$this, 'add_type_attribute'] , 10, 3);
    }

    private function admin_public_script()
    {
    }

    private function admin_script()
    {
        //font family
        if (
            // It getting from admin menu page URL, no need to check NonceVerification
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            (isset($_GET['page']) && sanitize_text_field( $_GET['page'] ) == 'ozopanel')
        ) {
            wp_enqueue_style(
                'ozopanel-google-font',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                [],
                $this->version
            );
        }

        if (
            // It getting from admin menu page URL, no need to check NonceVerification
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            (isset($_GET['page']) && sanitize_text_field( $_GET['page'] ) == 'ozopanel')
        ) {
            wp_enqueue_style(
                'ozopanel-dashboard',
                ozopanel()->get_asset_uri("main.min.css"),
                [],
                $this->version
            );

            wp_enqueue_script(
                'ozopanel-dashboard',
                ozopanel()->get_asset_uri('/main.min.js'),
                ['wp-api-fetch'],
                null,
                true
            );

            wp_localize_script('ozopanel-dashboard', 'ozopanel', [
                'version' => ozopanel()->version,
                'dashboard' => admin_url('admin.php?page=ozopanel'),
                'i18n' => I18n::app()
            ]);
        }

        /**
         * Show/Hide nav menu roles selections option
         *
         * @since 1.0.0
         */
        if ( is_admin() && isset($GLOBALS['pagenow']) && sanitize_text_field( $GLOBALS['pagenow'] ) === 'nav-menus.php' ) {
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

            wp_add_inline_script('nav-menu', $script);
        }
    }

    public function public_scripts()
    {
        $this->admin_public_script();

        $this->admin_script();
    }

    public function admin_scripts()
    {
        $this->admin_public_script();
        $this->admin_script();
    }

    public function add_type_attribute($tag, $handle) {

        // Check if the script handle matches the one you want to modify
        if (
            'ozopanel-dashboard' == $handle
        ) {
            // Add the type='module' attribute to the script tag
            $tag = str_replace( '<script', '<script type="module"', $tag );
        }

        return $tag;
    }

}
