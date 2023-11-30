<?php

namespace OzoPanel\Ctrl\Asset;

use OzoPanel\Helper\Fns;
use OzoPanel\Helper\I18n;
use OzoPanel\Vite;

/**
 * All plugin asset
 *
 * @since 1.0.0
 */
class AssetCtrl
{
    private $suffix;
    private $version;

    public function __construct()
    {
        $this->suffix = ozopanel()->is_debug() ? '' : '.min';
        $this->version = defined('WP_DEBUG') && WP_DEBUG ? time() : ozopanel()->version;

        add_action('wp_enqueue_scripts', [$this, 'public_scripts'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 9999);

        //remove thank you text from ozopanel dashboard
        if (isset($_GET['page']) && $_GET['page'] == 'ozopanel') {
            add_filter('admin_footer_text', '__return_empty_string', 11);
            add_filter('update_footer', '__return_empty_string', 11);
        }

        // add_filter('script_loader_tag', [$this, 'add_type_attribute'] , 10, 3);

        add_action('current_screen', function () {
            if (!$this->is_plugins_screen()) {
                return;
            }

            add_action('admin_enqueue_scripts', [
                $this,
                'enqueue_uninstall_dialog',
            ]);
        });
    }

    private function admin_public_script()
    {
    }

    private function admin_script()
    {
        //font family
        if (
            (isset($_GET['page']) && $_GET['page'] == 'ozopanel-welcome') ||
            (isset($_GET['page']) && $_GET['page'] == 'ozopanel') ||
            is_page_template([
                'test-template.php'
            ]) ||
            $this->is_plugins_screen()
        ) {
            wp_enqueue_style(
                'ozopanel-google-font',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                [],
                $this->version
            );
            /* wp_enqueue_style(
                'ozopanel-main',
                ozopanel()->get_asset_uri("css/main{$this->suffix}.css"),
                [],
                $this->version
            ); */
        }
        if (isset($_GET['page']) && $_GET['page'] == 'ozopanel-welcome') {
            wp_enqueue_style(
                'ozopanel-welcome',
                ozopanel()->get_asset_uri("css/welcome{$this->suffix}.css"),
                [],
                $this->version
            );
            wp_enqueue_script(
                'ozopanel-welcome',
                ozopanel()->get_asset_uri("/js/welcome{$this->suffix}.js"),
                ['wp-element'],
                $this->version,
                true
            );
            wp_localize_script('ozopanel-welcome', 'ozopanel', [
                'apiUrl' => esc_url(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'dashboard' => menu_page_url('ozopanel', false),
                'assetImgUri' => ozopanel()->get_asset_uri('img/'),
                'i18n' => I18n::dashboard(),
            ]);
        }

        if (
            (isset($_GET['page']) && $_GET['page'] == 'ozopanel') ||
            is_page_template([
                'test-template.php'
            ])
        ) {
            /* if ( ! ozopanel()->is_debug() ) {
                wp_enqueue_style(
                    'ozopanel-dashboard',
                    ozopanel()->get_asset_uri("main.css"),
                    [],
                    $this->version
                );
            }

            if ( ozopanel()->is_debug() ) {
                wp_enqueue_script(
                    'ozopanel-vite-client',
                    ozopanel()->dev_path() . '/@vite/client',
                    [],
                    $this->version,
                    false
                );
                ob_start();
                ?>
                    import { injectIntoGlobalHook } from '<?php echo ozopanel()->dev_path(); ?>/@react-refresh';
                    injectIntoGlobalHook(window);
                    window.$RefreshReg$ = () => {};
                    window.$RefreshSig$ = () => (type) => type;
                <?php
                $script = ob_get_clean();
                wp_add_inline_script('ozopanel-vite-client', $script);
            }

            wp_enqueue_script(
                'ozopanel-dashboard',
                ozopanel()->is_debug() ? ozopanel()->dev_path() . '/src/main.tsx' : ozopanel()->get_asset_uri('/main.js'),
                ['wp-api-fetch'],
                null,
                true
            );

            wp_localize_script('ozopanel-dashboard', 'ozopanel', [
                'version' => ozopanel()->version,
                'dashboard' => admin_url('admin.php?page=ozopanel'),
                'date_format' => Fns::phpToMomentFormat( get_option('date_format') ),
                'assetImgUri' => ozopanel()->get_asset_uri('img/'),
                'assetUri' => OZOPANEL_ASSEST,
                'i18n' => I18n::app()
            ]); */

            //$allowed_screens = ['toplevel_page_coldmailar', 'coldmailar_page_coldmailar-campaigns'];
            //if (in_array($screen, $allowed_screens)) {
                Vite\enqueue_asset(
                    COLDMAILAR_DIR . '/dist',
                    'src/main.tsx',
                    [
                        'dependencies' => ['react', 'react-dom'],
                        'handle' => 'ozopanel-dashboard',
                        'in-footer' => true,
                    ]
                );
                wp_localize_script('ozopanel-dashboard', 'ozopanel', [
                    'version' => ozopanel()->version,
                    'dashboard' => admin_url('admin.php?page=ozopanel'),
                    'date_format' => Fns::phpToMomentFormat( get_option('date_format') ),
                    'assetImgUri' => ozopanel()->get_asset_uri('img/'),
                    'assetUri' => OZOPANEL_ASSEST,
                    'i18n' => I18n::app()
                ]);
            //}
        }

        /**
         * Show/Hide nav menu roles selections option
         *
         * @since 1.0.0
         */
        if ( is_admin() && isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'nav-menus.php' ) {
            ob_start();
                ?>
                document.addEventListener("DOMContentLoaded", function() {
                    var whoCanSeeSelects = document.querySelectorAll(".edit-menu-item-ozopanel-who-can-see");
                    var rolesFields = document.querySelectorAll(".field-ozopanel-roles");

                    // Hide roles fields by default
                    rolesFields.forEach(function(field) {
                        field.style.display = "none";
                    });

                    // Show/hide roles field based on the selected option for each menu item
                    whoCanSeeSelects.forEach(function(select, index) {
                        if (select.value === "roles") {
                            rolesFields[index].style.display = "block";
                        }
                        select.addEventListener("change", function() {
                            if (select.value === "roles") {
                                rolesFields[index].style.display = "block";
                            } else {
                                rolesFields[index].style.display = "none";
                            }
                        });
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

        //wp_enqueue_style( 'ozopanel-main', ozopanel()->get_asset_uri( 'public/css/main{$this->suffix}.css' ), array(), $this->version );

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
            'ozopanel-dashboard' == $handle ||
            'ozopanel-vite-client' == $handle
        ) {
            // Add the type='module' attribute to the script tag
            $tag = str_replace( '<script', '<script type="module"', $tag );
        }

        return $tag;
    }

    /**
     * Enqueue uninstall dialog scripts.
     *
     * Registers the uninstall dialog scripts and enqueues them.
     *
     * @since 1.0.0
     */
    public function enqueue_uninstall_dialog()
    {
        add_action('admin_footer', function () {
            ozopanel()->render('uninstall/form');
        });

        wp_enqueue_script(
            'ozopanel-uninstall',
            ozopanel()->get_asset_uri("/js/uninstall{$this->suffix}.js"),
            [],
            $this->version,
            true
        );

        wp_localize_script('ozopanel-uninstall', 'ozopanel', [
            'ajaxurl' => esc_url( admin_url('admin-ajax.php') ),
        ]);
    }


    /**
     * @since 1.0.0
     */
    private function is_plugins_screen()
    {
        if ( !function_exists('get_current_screen') ) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }

        if ( is_admin() ) {
            return in_array(get_current_screen()->id, [
                'plugins',
                'plugins-network',
            ]);
        } else {
            return false;
        }
    }
}
