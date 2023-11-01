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

        add_submenu_page(
            'ozopanel',
            esc_html__('Admin Columns', 'ozopanel'),
            esc_html__('Admin Columns', 'ozopanel'),
            'manage_options',
            'ozopanel#/admin-columns',
            [$this, 'render']
        );

        add_submenu_page(
            'ozopanel',
            esc_html__('Settings', 'ozopanel'),
            esc_html__('Settings', 'ozopanel'),
            'manage_options',
            'ozopanel#/settings',
            [$this, 'render']
        );

        /* if ( !function_exists('ozopanelp') ) {
            global $submenu;
            $submenu['ozopanel'][] = [
                'Upgrade to Pro',
                'manage_options',
                'https://ozopanel.com',
            ];
        } */

        remove_submenu_page('ozopanel', 'ozopanel');

        /* global $menu;

        // Find the menu page you want to update
        foreach ($menu as $key => $item) {
            if ($item[2] == 'users.php') {
                // Update the capability required to access the menu page
                $menu[$key][1] = 'author'; // Replace 'new_capability' with your desired capability
            }
        } */
    /* $current_user = wp_get_current_user();
$caps = $current_user->caps;
error_log(print_r($caps, true)); */

        //Save admin menus
        $this->save_admin_menus();

        //restrict_menu
        // $this->restrict_menu();
    }

    /**
     * React js render tag
     *
     * @since 1.0.0
     */
    function render()
    {
        echo '<div class="wrap"><div id="ozopanel-dashboard"></div></div>';
    }

    /**
     * Save admin menus to option
     * In rest API request global $menu, $submenu not accessable, that's why saving in option
     * @since 1.0.0
     */
    protected function save_admin_menus()
    {
        if (!current_user_can('administrator')) return;

        global $menu, $submenu;

        //menu serial by menu priority
        ksort($menu);

        // Organize submenu items under their parent menu items
        $mergedMenu = [];
        foreach ($menu as $menuItem) {

            //Organize and add menu
            $modifyMenuItem = [];

            $cleanLabel = preg_replace('/\d+/', '', strip_tags($menuItem[0]));
            $modifyMenuItem['label'] = $cleanLabel;
            // $modifyMenuItem['label'] = $menuItem[0];
            $modifyMenuItem['capability'] = $menuItem[1];
            $modifyMenuItem['url'] = $menuItem[2];

            if (isset($menuItem[4])) {
                $modifyMenuItem['classes'] = $menuItem[4];
            }

            if (isset($menuItem[5])) {
                $modifyMenuItem['id'] = $menuItem[5];
            }

            if (isset($menuItem[6])) {
                $modifyMenuItem['icon'] = $menuItem[6];
            }

            //Organize and add submenu
            $modifyMenuItem['submenu'] = [];
            if (array_key_exists($menuItem[2], $submenu)) {
                foreach ($submenu[$menuItem[2]] as $subItem) {
                    $modifySubItem = [];
                    $cleanLabel = preg_replace('/\d+/', '', strip_tags($subItem[0]));
                    $modifySubItem['label'] = $cleanLabel;
                    $modifySubItem['capability'] = $subItem[1];
                    $modifySubItem['url'] = $subItem[2];
                    $modifyMenuItem['submenu'][] = $modifySubItem;
                }
            }

            //Escpae wp-menu-separator
            if (
                isset($menuItem[6]) &&
                ($menuItem[6] != 'wp-menu-separator' &&
                    $menuItem[2] != 'ozopanel-welcome' && //ozopanel welcome page
                    $menuItem[2] != 'ozopanel' //main ozopanel page
                )
            ) {
                $mergedMenu[] = $modifyMenuItem;
            }
        }

        update_option('ozopanel_admin_menu', $mergedMenu);
    }

    /**
     * Restrict menu for user or role
     *
     * @since 1.0.0
     */
    protected function restrict_menu()
    {

        $current_user = wp_get_current_user(); // Get the current user object

        if ($current_user instanceof \WP_User) {
            $user_roles = $current_user->roles; // Get the roles of the current user

            $current_user_role = $user_roles[0];
            $current_user_admin_menu = get_user_meta($current_user->ID, '_ozopanel_admin_menu', true);
            $current_role_admin_menu = get_option('ozopanel_admin_menu_role_' . $current_user_role);
            if ($current_user_admin_menu) {
                $this->check_restrict_menu($current_user_admin_menu);
            } else if ($current_role_admin_menu) {
                $this->check_restrict_menu($current_role_admin_menu);
            }
        }
    }

    /**
     * Check menu accesss
     *
     * @since 1.0.0
     */
    protected function check_restrict_menu($restrict_menu)
    {
        $admin_menu = get_option('ozopanel_admin_menu');

        if ($admin_menu) {
            foreach ($admin_menu as $menu) {
                if ($menu['submenu']) {
                    foreach ($menu['submenu'] as $submenu) {
                        if (array_key_exists($menu['url'], $restrict_menu)) {
                            if (!in_array($submenu['url'], $restrict_menu[$menu['url']])) {
                                $this->remove_admin_url_access($menu['url'], $submenu['url']);
                            }
                        } else {
                            $this->remove_admin_url_access($menu['url']);
                        }
                    }
                } else {
                    if (!array_key_exists($menu['url'], $restrict_menu)) {
                        $this->remove_admin_url_access($menu['url']);
                    }
                }
            }
        }
    }

    /**
     * Remove menu and url accesss
     *
     * @since 1.0.0
     */
    protected function remove_admin_url_access($menu, $submenu = '')
    {
        global $pagenow;
        global $parent_file;

        if ($submenu) {
            remove_submenu_page($menu, $submenu);
        } else {
            remove_menu_page($menu);
        }
        $url_to_hide = ($submenu) ? $submenu : $menu;

        if ($url_to_hide == $pagenow || $url_to_hide == $parent_file) {
            do_action('admin_page_access_denied');
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 'ozopanel'), 403);
        }
    }
}
