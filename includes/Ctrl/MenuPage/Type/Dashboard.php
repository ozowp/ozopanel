<?php

namespace OzoPanel\Ctrl\MenuPage\Type;

use OzoPanel\Helper\Fns;
use OzoPanel\Helper\AdminColumn\WpListTableFactory;
class Dashboard
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_menu'], 9999);
    }

    public function add_settings_menu()
    {
        global $pagenow;

        //add_menu_page
        $this->add_menu_page();

        // load this condtion only ozopanel settings page
        // It getting from admin menu page URL, no need to check NonceVerification
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ($pagenow === 'admin.php' && isset($_GET['page']) && sanitize_text_field( $_GET['page'] ) === 'ozopanel') {
            //Save admin menus
            $this->save_admin_menus();

        }

        //restrict_menu
        $this->restrict_menu();
    }

    /**
     * React js render tag
     *
     * @since 1.0.0
     */
    function add_menu_page()
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

        if (ozopanel()->is_active_addon('restriction')) {
            add_submenu_page(
                'ozopanel',
                esc_html__('Restrict Roles', 'ozopanel'),
                esc_html__('Restrict Roles', 'ozopanel'),
                'manage_options',
                'ozopanel#/restrictions/roles',
                [$this, 'render']
            );

            add_submenu_page(
                'ozopanel',
                esc_html__('Restrict Users', 'ozopanel'),
                esc_html__('Restrict Users', 'ozopanel'),
                'manage_options',
                'ozopanel#/restrictions/users',
                [$this, 'render']
            );
        }

        remove_submenu_page('ozopanel', 'ozopanel');
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

            $cleanLabel = preg_replace('/\d+/', '', wp_strip_all_tags($menuItem[0]));
            $modifyMenuItem['label'] = $cleanLabel;
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
                    $cleanLabel = preg_replace('/\d+/', '', wp_strip_all_tags($subItem[0]));
                    $modifySubItem['label'] = $cleanLabel;
                    $modifySubItem['capability'] = $subItem[1];
                    $modifySubItem['url'] = $subItem[2];
                    $modifyMenuItem['submenu'][] = $modifySubItem;
                }
            }

            //Escpae wp-menu-separator
            if (
                $menuItem[2] != 'ozopanel-welcome' //ozopanel welcome page
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
