<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\AdminColumn\Fns as ColumnFns;
use OzoPanel\Helper\Fns;
use OzoPanel\Model\AdminColumn as ModelAdminColumn;

/**
 * API AdminColumn class
 *
 * @since 0.1.0
 */
class AdminColumn extends RestCtrl {

    /**
     * Route base.
     *
     * @var string
     * @since 0.1.0
     */
    protected $base = 'admin-columns';

    /**
     * Register all routes related with this api
     *
     * @return void
     * @since 0.1.0
     */
    public function routes() {

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[a-z0-9_]+)',
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_single' ],
                'permission_callback' => [ $this, 'gate' ],
                'args' => [
                    'id' => [
                        'validate_callback' => function ( $param ) {
                            return is_string( $param );
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[a-z0-9_]+)',
            [
                'methods' => 'PUT',
                'callback' => [ $this, 'update' ],
                'permission_callback' => [ $this, 'gate' ],
                'args' => [
                    'id' => [
                        'validate_callback' => function ( $param ) {
                            return is_string( $param );
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[a-z0-9,]+)',
            [
                'methods' => 'DELETE',
                'callback' => [ $this, 'delete' ],
                'permission_callback' => function () {
                    return Fns::gate( $this->base, 'del' );
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function ( $param ) {
                            return is_string( $param );
                        },
                    ],
                ],
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
        $url_params = $req->get_url_params();
        $id = $url_params['id'];

        $wp_err = new \WP_Error();

        if ( ! $id ) {
            $wp_err->add(
                'select_id',
                esc_html__( 'Screen ID is required!', 'ozopanel' )
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
            $resp = [];
            $resp['screens'] = ModelAdminColumn::screens();
            $columns_default = [];
            if ( $id ) {
                if ( post_type_exists( $id ) ) {
                    $columns = get_option( 'ozopanel_admin_column_' . $id . '_default', [] );
                    $columns_default = ColumnFns::format_column( $columns );
                } elseif ( $id === 'wp_media' ) {
                    $columns = get_option( 'ozopanel_admin_column_upload_default', [] );
                    $columns_default = ColumnFns::format_column( $columns );
                } elseif ( $id === 'wp_comments' ) {
                    $columns = get_option( 'ozopanel_admin_column_edit-comments_default', [] );
                    $columns_default = ColumnFns::format_column( $columns );
                } elseif ( $id === 'wp_users' ) {
                    $columns = get_option( 'ozopanel_admin_column_users_default', [] );
                    $columns_default = ColumnFns::format_column( $columns );
                }
            }

            $resp['columns_default'] = $columns_default;
            $custom_columns = get_option( 'ozopanel_admin_column_' . $id, [] );
            $resp['columns'] = $custom_columns ? $custom_columns : $columns_default; //custom column otherwise default column

            return new \WP_REST_Response(
                [
					'success'  => true,
					'data' => $resp,
                ], 200
            );
        }
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

        $url_param = $req->get_url_params();
        $id = $url_param['id'];

        $wp_err = new \WP_Error();

        $admin_column = isset( $param['admin_column'] ) ? ( $param['admin_column'] ) : '';

        if ( ! $id ) {
            $wp_err->add(
                'select_id',
                esc_html__( 'Screen ID is required!', 'ozopanel' )
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
            update_option( 'ozopanel_admin_column_' . $id, $admin_column );
            return new \WP_REST_Response(
                [
					'success'  => true,
					'data' => null,
                ], 200
            );
        }
    }

    /**
     * Delete reqeust
     *
     * @since 0.1.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete( $req ) {
        $url_param = $req->get_url_params();
        $type = $url_param['type'];
        $ids = explode( ',', $url_param['id'] );
        foreach ( $ids as $id ) {
            if ( $type === 'users' ) {
                delete_user_meta( $id, '_ozopanel_admin_menu' );
            } else {
                delete_option( 'ozopanel_admin_menu_role_' . $id );
            }
        }

        return new \WP_REST_Response(
            [
				'success'  => true,
				'data' => $ids,
            ], 200
        );
    }
}
