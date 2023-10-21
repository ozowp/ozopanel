<?php

namespace WAM\Ctrl\MenuPage\Type;

use WAM\Helper\Fns;

class Dashboard
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_menu'], 99);
    }

    public function add_settings_menu()
    {
        add_menu_page(
            esc_html__('WAM', 'wp-access-manager'),
            esc_html__('WAM', 'wp-access-manager'),
            'manage_options',
            'wam',
            [$this, 'render'],
            'dashicons-shield',
            75
        );

        add_submenu_page(
            'wam',
            esc_html__('Dashboard', 'wp-access-manager'),
            esc_html__('Dashboard', 'wp-access-manager'),
            'manage_options',
            'wam#',
            [$this, 'render']
        );


        add_submenu_page(
            'wam',
            esc_html__('Settings', 'wp-access-manager'),
            esc_html__('Settings', 'wp-access-manager'),
            'manage_options',
            'wam#/settings',
            [$this, 'render']
        );

        if ( !function_exists('wamp') ) {
            global $submenu;
            $submenu['wam'][] = [
                'Upgrade to Pro',
                'manage_options',
                'https://wp-access-manager.com',
            ];
        }

        remove_submenu_page('wam', 'wam');
    }

    function render()
    {
        echo '<div class="wrap"><div id="wam-dashboard"></div></div>';
    }
}

