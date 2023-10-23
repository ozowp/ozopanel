<?php

namespace OzoPanel\Ctrl\MenuPage\Type;

use OzoPanel\Helper\Fns;

class Dashboard
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_menu'], 9999);
    }

    public function add_settings_menu()
    {
        add_menu_page(
            esc_html__('OzoPanel', 'ozopanel'),
            esc_html__('OzoPanel', 'ozopanel'),
            'manage_options',
            'ozopanel',
            [$this, 'render'],
            'dashicons-shield',
            75
        );

        add_submenu_page(
            'ozopanel',
            esc_html__('Dashboard', 'ozopanel'),
            esc_html__('Dashboard', 'ozopanel'),
            'manage_options',
            'ozopanel#',
            [$this, 'render']
        );

        add_submenu_page(
            'ozopanel',
            esc_html__('Users', 'ozopanel'),
            esc_html__('Users', 'ozopanel'),
            'manage_options',
            'ozopanel#/restrictions/users',
            [$this, 'render']
        );

        add_submenu_page(
            'ozopanel',
            esc_html__('Roles', 'ozopanel'),
            esc_html__('Roles', 'ozopanel'),
            'manage_options',
            'ozopanel#/restrictions/roles',
            [$this, 'render']
        );

        /* add_submenu_page(
            'ozopanel',
            esc_html__('Settings', 'ozopanel'),
            esc_html__('Settings', 'ozopanel'),
            'manage_options',
            'ozopanel#/settings',
            [$this, 'render']
        ); */

        /* if ( !function_exists('ozopanelp') ) {
            global $submenu;
            $submenu['ozopanel'][] = [
                'Upgrade to Pro',
                'manage_options',
                'https://ozopanel.com',
            ];
        } */
        remove_submenu_page('ozopanel', 'ozopanel');

        //Save admin menus
        $this->save_admin_menus();

    }

    function render()
    {
        echo '<div class="wrap"><div id="ozopanel-dashboard"></div></div>';
    }

    /**
     * Save admin menus to option
     * In rest API request global $menu, $submenu not accessable, that's why saving in option
     * @since 1.0.0
     */
    protected function save_admin_menus() {
        global $menu, $submenu;

        //menu serial by menu priority
        ksort($menu);

        // Organize submenu items under their parent menu items
        $mergedMenu = [];
        foreach ($menu as $menuItem) {

            //Organize and add menu
            $modifyMenuItem = [];

            $cleanLabel = preg_replace('/\d+/', '', strip_tags( $menuItem[0] ));
            $modifyMenuItem['label'] = $cleanLabel;
            // $modifyMenuItem['label'] = $menuItem[0];
            $modifyMenuItem['capability'] = $menuItem[1];
            $modifyMenuItem['url'] = $menuItem[2];

            if ( isset( $menuItem[4] ) ) {
                $modifyMenuItem['classes'] = $menuItem[4];
            }

            if ( isset( $menuItem[5] ) ) {
                $modifyMenuItem['id'] = $menuItem[5];
            }

            if ( isset( $menuItem[6] ) ) {
                $modifyMenuItem['icon'] = $menuItem[6];
            }

            //Organize and add submenu
            $modifyMenuItem['submenu'] = [];
            if ( array_key_exists( $menuItem[2], $submenu ) ) {
                foreach( $submenu[$menuItem[2]] as $subItem ) {
                    $modifySubItem = [];
                    $cleanLabel = preg_replace('/\d+/', '', strip_tags( $subItem[0] ));
                    $modifySubItem['label'] = $cleanLabel;
                    $modifySubItem['capability'] = $subItem[1];
                    $modifySubItem['url'] = $subItem[2];
                    $modifyMenuItem['submenu'][] = $modifySubItem;
                }
            }

            //Escpae wp-menu-separator
            if (
                isset( $menuItem[6] ) &&
                (
                    $menuItem[6] != 'wp-menu-separator' &&
                    $menuItem[2] != 'ozopanel-welcome' && //ozopanel welcome page
                    $menuItem[2] != 'ozopanel' //main ozopanel page
                )
            ) {
                $mergedMenu[] = $modifyMenuItem;
            }
        }

        update_option( 'ozopanel_admin_menu', $mergedMenu);
    }
}

