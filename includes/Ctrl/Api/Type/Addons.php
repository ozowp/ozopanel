<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Addons class.
 *
 * @since 1.0.0
 */

class Addons extends RestCtrl {


    /**
     * Route base.
     *
     * @var string
     * @since 1.0.0
     */
    protected $base = 'addons';

    /**
     * Register all routes related with carts.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function routes() {

        register_rest_route(
            $this->namespace, '/' . $this->base,
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get' ),
                'permission_callback' => function () {
                    return Fns::gate( $this->base, 'get' );
                },
                'args' => array(
                    'type' => array(
                        'validate_callback' => function ( $param ) {
                            return is_string( $param );
                        },
                    ),
                ),
            )
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[^/]+)',
            array(
                'methods' => 'PUT',
                'callback' => array( $this, 'update' ),
                'permission_callback' => function () {
                    return Fns::gate( $this->base, 'edit' );
                },
                'args' => array(
                    'id' => array(
                        'validate_callback' => function ( $param ) {
                            return is_numeric( $param );
                        },
                    ),
                ),
            )
        );
    }

    /**
     * Get reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get( $req ) {
        $list = array(
            array(
                'id' => 'admin_menu_editor',
                'title' => esc_html__( 'Admin Menu Editor', 'ozopanel' ),
                'desc' => 'You can customize admin menu and submenu using this',
                'isActive' => true,
            ),
            array(
                'id' => 'admin_column_editor',
                'title' => esc_attr__( 'Admin Column Editor', 'ozopanel' ),
                'desc' => esc_attr__( 'You can customize table column using this', 'ozopanel' ),
                'isActive' => true,
            ),
            array(
                'id' => 'restriction',
                'title' => esc_attr__( 'Restriction', 'ozopanel' ),
                'desc' => esc_attr__( 'You can restrict admin menu and submenu using this', 'ozopanel' ),
                'isActive' => true,
            ),
            array(
                'id' => 'nav_menu_restriction',
                'title' => esc_attr__( 'Nav Menu Restriction', 'ozopanel' ),
                'desc' => esc_attr__( 'You can restrict nav menu using this', 'ozopanel' ),
                'isActive' => false,
            ),
        );

        $addons = get_option( 'ozopanel_addons', array() );

        foreach ( $list as $key => $item ) {
            if ( array_key_exists( $item['id'], $addons ) ) {
                $list[ $key ]['isActive'] = $addons[ $item['id'] ];
            } else {
                $list[ $key ]['isActive'] = true;
            }
        }

        $resp['list'] = $list;

        return new \WP_REST_Response(
            array(
				'success'  => true,
				'data' => $resp,
            ), 200
        );
    }

    /**
     * Update existing action.
     *
     * @since 1.0.0
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function update( $req ) {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $url_params = $req->get_url_params();
        $addon = $url_params['id'];

        $is_active = isset( $param['isActive'] ) ? rest_sanitize_boolean( $param['isActive'] ) : false;

        if ( empty( $addon ) ) {
            $wp_err->add(
                'field',
                esc_html__( 'Addon is missing', 'ozopanel' )
            );
        }

        if ( $wp_err->get_error_messages() ) {
            return new \WP_REST_Response(
                array(
					'success'  => false,
					'data' => $wp_err->get_error_messages(),
                ), 200
            );
        } else {
            $addons = get_option( 'ozopanel_addons', array() );
            $addons[ $addon ] = $is_active;
            update_option( 'ozopanel_addons', $addons );

            return new \WP_REST_Response(
                array(
					'success'  => true,
					'data' => null,
                ), 200
            );
        }
    }
}
