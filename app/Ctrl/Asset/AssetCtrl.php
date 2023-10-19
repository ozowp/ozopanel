<?php

namespace WAM\Ctrl\Asset;

use WAM\Helper\Fns;
use WAM\Helper\I18n;

class AssetCtrl
{
    private $suffix;
    private $version;
    public $current_user_caps;

    public function __construct()
    {
        $this->suffix = defined('WAM_SCRIPT_DEBUG') && WAM_SCRIPT_DEBUG ? '' : '.min';
        $this->version = defined('WP_DEBUG') && WP_DEBUG ? time() : wam()->version;
        $this->current_user_caps = array_keys(wp_get_current_user()->allcaps);

        add_action('wp_enqueue_scripts', [$this, 'public_scripts'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 9999);

        //remove thank you text from wp-access-manager dashboard
        if (isset($_GET['page']) && $_GET['page'] == 'wam') {
            add_filter('admin_footer_text', '__return_empty_string', 11);
            add_filter('update_footer', '__return_empty_string', 11);
        }

        add_filter('show_admin_bar', [$this, 'hide_admin_bar']);

        add_filter('script_loader_tag', [$this, 'add_type_attribute'] , 10, 3);

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

    public function hide_admin_bar($show)
    {
        if (
            is_page_template([
                'test-template.php'
            ])
        ) {
            return false;
        }
        return $show;
    }

    private function admin_public_script()
    {
    }

    private function admin_script()
    {
        //font family
        if (
            (isset($_GET['page']) && $_GET['page'] == 'wam-welcome') ||
            (isset($_GET['page']) && $_GET['page'] == 'wam') ||
            is_page_template([
                'test-template.php'
            ]) ||
            $this->is_plugins_screen()
        ) {
            wp_enqueue_style(
                'wam-google-font',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                [],
                $this->version
            );
            wp_enqueue_style(
                'wam-main',
                wam()->get_asset_uri("css/main{$this->suffix}.css"),
                [],
                $this->version
            );
        }
        if (isset($_GET['page']) && $_GET['page'] == 'wam-welcome') {
            wp_enqueue_style(
                'wam-welcome',
                wam()->get_asset_uri("css/welcome{$this->suffix}.css"),
                [],
                $this->version
            );
            wp_enqueue_script(
                'wam-welcome',
                wam()->get_asset_uri("/js/welcome{$this->suffix}.js"),
                ['wp-element'],
                $this->version,
                true
            );
            wp_localize_script('wam-welcome', 'wam', [
                'apiUrl' => esc_url(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'dashboard' => menu_page_url('wam', false),
                'assetImgUri' => wam()->get_asset_uri('img/'),
                'i18n' => I18n::dashboard(),
            ]);
        }

        if (
            (isset($_GET['page']) && $_GET['page'] == 'wam') ||
            is_page_template([
                'test-template.php'
            ])
        ) {
            /* wp_enqueue_style(
                'wam-dashboard',
                wam()->get_asset_uri("css/dashboard{$this->suffix}.css"),
                [],
                $this->version
            ); */
            wp_enqueue_script(
                'wam-vite-client',
                // wam()->get_asset_uri('/js/dashboard{$this->suffix}.js'),
                'http://localhost:3000/@vite/client',
                ['wp-element'],
                $this->version,
                false
            );

            ob_start();
            ?>
                import { injectIntoGlobalHook } from 'http://localhost:3000/@react-refresh';
                injectIntoGlobalHook(window);
                window.$RefreshReg$ = () => {};
                window.$RefreshSig$ = () => (type) => type;
            <?php
            $script = ob_get_clean();

            wp_add_inline_script('wam-vite-client', $script);

            wp_enqueue_script(
                'wam-dashboard',
                // wam()->get_asset_uri('/js/dashboard{$this->suffix}.js'),
                'http://localhost:3000/src/main.tsx',
                ['wp-element'],
                $this->version,
                true
            );
            $current_user = wp_get_current_user();
            wp_localize_script('wam-dashboard', 'wam', [
                'apiUrl' => esc_url(rest_url()),
                'version' => wam()->version,
                'dashboard' => admin_url('admin.php?page=wam'),
                'nonce' => wp_create_nonce('wp_rest'),
                'date_format' => Fns::phpToMomentFormat(
                    get_option('date_format')
                ),
                'assetImgUri' => wam()->get_asset_uri('img/'),
                'assetUri' => WAM_ASSEST,
                'profile' => [
                    'name' => $current_user->display_name,
                    'img' => get_avatar_url($current_user->ID, [
                        'size' => '36',
                    ]),
                    'logout' => wp_logout_url(get_permalink()),
                ],
                'i18n' => I18n::dashboard(),
                'caps' => $this->current_user_caps,
            ]);
        }
    }

    public function public_scripts()
    {
        $this->admin_public_script();

        //wp_enqueue_style( 'wp-access-manager-main', wam()->get_asset_uri( 'public/css/main{$this->suffix}.css' ), array(), $this->version );

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
            'wam-dashboard' == $handle ||
            'wam-vite-client' == $handle
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
            wam()->render('uninstall/form');
        });

        wp_enqueue_script(
            'wam-uninstall',
            wam()->get_asset_uri("/js/uninstall{$this->suffix}.js"),
            [],
            $this->version,
            true
        );

        wp_localize_script('wam-uninstall', 'wam', [
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
