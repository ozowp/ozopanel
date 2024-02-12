<?php

namespace OzoPanel\Api\Types;

use OzoPanel\Abstracts\RestApi;

/**
 * API Action class
 *
 * @since 0.1.0
 */
class Action extends RestApi {


    /**
     * Route base.
     *
     * @since 0.1.0
     *
     * @var string
     */
    protected $base = 'actions';

    /**
     * Register all routes related with this api
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->base,
            [
                'methods' => 'POST',
                'callback' => [ $this, 'create' ],
                'permission_callback' => [ $this, 'permission' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/(?P<id>[^/]+)',
            [
                'methods' => 'PUT',
                'callback' => [ $this, 'update' ],
                'permission_callback' => [ $this, 'permission' ],
                'args' => [
                    'id' => [
                        'validate_callback' => function ( $param ) {
                            return is_numeric( $param );
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/(?P<id>[0-9,]+)',
            [
                'methods' => 'DELETE',
                'callback' => [ $this, 'delete' ],
                'permission_callback' => [ $this, 'permission' ],
                'args' => [
                    'id' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Create new action(s).
     *
     * @since 0.1.0
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function create( $req ) {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        // modified for multiple id support
        $str_id = isset( $param['id'] ) ? $param['id'] : null;
        $type = isset( $param['type'] ) ? sanitize_text_field( $param['type'] ) : '';

        $ids = explode( ',', $str_id );

        foreach ( $ids as $id ) {
            $id = (int) $id;

            if ( empty( $id ) || empty( $type ) ) {
                $wp_err->add(
                    'field',
                    esc_html__( 'Required field is missing', 'ozopanel' )
                );
            }

            if ( $wp_err->get_error_messages() ) {
                return new \WP_REST_Response(
                    [
                        'success'  => false,
                        'data' => $wp_err->get_error_messages(),
                    ],
                    200
                );
            }
        }
    }

    /**
     * Update existing action
     *
     * @since 0.1.0
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function update( $req ) {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $url_params = $req->get_url_params();
        $post_id = $url_params['id'];

        $type = isset( $param['type'] ) ? sanitize_text_field( $param['type'] ) : '';

        if ( empty( $type ) ) {
            $wp_err->add(
                'field',
                esc_html__( 'Type is missing', 'ozopanel' )
            );
        }

        if ( $wp_err->get_error_messages() ) {
            return new \WP_REST_Response(
                [
                    'success'  => false,
                    'data' => $wp_err->get_error_messages(),
                ],
                200
            );
        } else {
            $data = [
                'ID' => $post_id,
                'post_title' => '',
                'post_content' => '', // Note: $desc variable is not defined in the provided code
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                return new \WP_REST_Response(
                    [
                        'success'  => true,
                        'data' => $post_id,
                    ],
                    200
                );
            }
        }
    }

    /**
     * Delete action(s) by ID(s)
     *
     * @since 0.1.0
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function delete( $req ) {
        $url_params = $req->get_url_params();
        $ids = explode( ',', $url_params['id'] );
        foreach ( $ids as $id ) {
            wp_delete_post( $id );
        }

        do_action( 'ozopanelp_webhook', 'user_delete', $ids );

        wp_send_json_success( $ids );
    }
}
