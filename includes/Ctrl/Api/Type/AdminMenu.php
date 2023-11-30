<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Restriction class.
 *
 * @since 1.0.0
 */
class AdminMenu extends RestCtrl
{

    /**
     * Route base.
     *
     * @var string
     * @since 1.0.0
     */
    protected $base = 'admin-menus';

    /**
     * Register all routes related with carts.
     *
     * @return void
     * @since 1.0.0
     */

    public function routes()
    {

        register_rest_route(
            $this->namespace, '/' . $this->base,
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_single'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'get');
                },
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base,
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'edit');
                },
            ]
        );

    }

    /**
     * Get single reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_single($req)
    {
        $resp = [];
        $admin_menu = get_option('ozopanel_admin_menu');
        $admin_menu_editor = get_option('ozopanel_admin_menu_editor');
        $menus = $admin_menu_editor ? $admin_menu_editor : $admin_menu;
        $resp['menus'] = $menus;

        return new \WP_REST_Response([
            'success'  => true,
            'data' => $resp,
        ], 200);
    }

    /**
     * Update reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update($req)
    {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $admin_menu = isset($param['admin_menu']) ? ($param['admin_menu']) : '';

        if ($wp_err->get_error_messages()) {
            return new \WP_REST_Response([
                'success'  => false,
                'data' => $wp_err->get_error_messages()
            ], 200);
        } else {
            update_option('ozopanel_admin_menu_editor', $admin_menu);
            return new \WP_REST_Response([
                'success'  => true,
                'data' => null
            ], 200);
        }
    }
}
