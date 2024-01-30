<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Restriction class
 *
 * @since 0.1.0
 */
class AdminMenu extends RestCtrl {

    /**
     * Route base.
     *
     * @var string
     * 
     * @since 0.1.0
     */
    protected $base = 'admin-menus';

    /**
     * Register all routes related with this api
     *
     * @since 0.1.0
     * 
     * @return void
     */

    public function routes() {

        register_rest_route(
            $this->namespace, '/' . $this->base,
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_single' ],
                'permission_callback' => [ $this, 'gate' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base,
            [
                'methods' => 'PUT',
                'callback' => [ $this, 'update' ],
                'permission_callback' => [ $this, 'gate' ],
            ]
        );
    }

    /**
     * Get single reqeust
     *
     * @since 0.1.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_single( $req ) {
        $resp = [];
        $admin_menu = get_option( 'ozopanel_admin_menu' );
        $admin_menu_editor = get_option( 'ozopanel_admin_menu_editor' );
        $menus = $admin_menu_editor ? $admin_menu_editor : $admin_menu;
        $resp['menus'] = $menus;
        $default_menus = [];
        if ( $admin_menu ) {
            foreach ( $admin_menu as $menu ) {
                $menu_t = [];
                $menu_t['label'] = $menu['label'];
                $menu_t['url'] = $menu['url'];
                $menu_t['isSubmenu'] = false;
                $default_menus[] = $menu_t;

                if ( isset( $menu['submenu'] ) && ! empty( $menu['submenu'] ) ) {
                    foreach ( $menu['submenu'] as $submenu ) {
                        $submenu_t = [];
                        $submenu_t['label'] = $submenu['label'];
                        $submenu_t['url'] = $submenu['url'];
                        $submenu_t['isSubmenu'] = true;
                        $default_menus[] = $submenu_t;
                    }
                }
            }
        }
        $resp['default_menus'] = $default_menus;

        return new \WP_REST_Response(
            [
				'success'  => true,
				'data' => $resp,
            ], 200
        );
    }

    /**
     * Update reqeust
     *
     * @since 0.1.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update( $req ) {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $admin_menu = isset( $param['admin_menu'] ) ? ( $param['admin_menu'] ) : '';

        if ( $wp_err->get_error_messages() ) {
            return new \WP_REST_Response(
                [
					'success'  => false,
					'data' => $wp_err->get_error_messages(),
                ], 200
            );
        } else {
            update_option( 'ozopanel_admin_menu_editor', $admin_menu );
            return new \WP_REST_Response(
                [
					'success'  => true,
					'data' => null,
                ], 200
            );
        }
    }
}
