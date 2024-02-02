<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;

/**
 * API Addon class
 *
 * @since 0.1.0
 */
class Addon extends RestCtrl {

    /**
     * Route base.
     *
     * @var string
     * 
     * @since 0.1.0
     */
    protected $base = 'addons';

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
                'callback' => [ $this, 'get' ],
                'permission_callback' => [ $this, 'gate' ],
                'args' => [
                    'type' => [
                        'validate_callback' => function ( $param ) {
                            return is_string( $param );
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[^/]+)',
            [
                'methods' => 'PUT',
                'callback' => [ $this, 'update' ],
                'permission_callback' => [ $this, 'gate' ],
                'args' => [
                    'id' => [
                        'validate_callback' => function ( $param ) {
                            return is_numeric( $param );
                        },
                    ],
                ],
            ]
        );
    }

    /**
     * Get reqeust
     *
     * @since 0.1.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get( $req ) {
        $list = [
            [
                'id' => 'admin_menu_editor',
                'title' => esc_html__( 'Admin Menu Editor', 'ozopanel' ),
                'description' => 'You can customize admin menu and submenu using this',
                'is_active' => true,
            ],
            [
                'id' => 'admin_column_editor',
                'title' => esc_attr__( 'Admin Column Editor', 'ozopanel' ),
                'description' => esc_attr__( 'You can customize table column using this', 'ozopanel' ),
                'is_active' => true,
            ],
            [
                'id' => 'restriction',
                'title' => esc_attr__( 'Restriction', 'ozopanel' ),
                'description' => esc_attr__( 'You can restrict admin menu and submenu using this', 'ozopanel' ),
                'is_active' => true,
            ],
            [
                'id' => 'nav_menu_restriction',
                'title' => esc_attr__( 'Nav Menu Restriction', 'ozopanel' ),
                'description' => esc_attr__( 'You can restrict nav menu using this', 'ozopanel' ),
                'is_active' => false,
            ],
        ];

        $addons = get_option( 'ozopanel_addons', [] );

        foreach ( $list as $key => $item ) {
            if ( array_key_exists( $item['id'], $addons ) ) {
                $list[ $key ]['is_active'] = $addons[ $item['id'] ];
            } else {
                $list[ $key ]['is_active'] = true;
            }
        }

        $resp['list'] = $list;

        return new \WP_REST_Response(
            [
				'success'  => true,
				'data' => $resp,
            ], 200
        );
    }

    /**
     * Update existing action.
     *
     * @since 0.1.0
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function update( $req ) {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $url_params = $req->get_url_params();
        $addon = $url_params['id'];

        $is_active = isset( $param['is_active'] ) ? rest_sanitize_boolean( $param['is_active'] ) : false;

        if ( empty( $addon ) ) {
            $wp_err->add(
                'field',
                esc_html__( 'Addon is missing', 'ozopanel' )
            );
        }

        if ( $wp_err->get_error_messages() ) {
            return new \WP_REST_Response(
                [
					'success'  => false,
					'data' => $wp_err->get_error_messages(),
                ], 200
            );
        } else {
            $addons = get_option( 'ozopanel_addons', [] );
            $addons[ $addon ] = $is_active;
            update_option( 'ozopanel_addons', $addons );

            return new \WP_REST_Response(
                [
					'success'  => true,
					'data' => null,
                ], 200
            );
        }
    }
}
