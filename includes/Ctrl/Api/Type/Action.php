<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Action class.
 *
 * @since 1.0.0
 */

class Action extends RestCtrl {


    /**
     * Route base.
     *
     * @since 1.0.0
     *
     * @var string
     */
    protected $base = 'actions';

    /**
     * Register all routes related with carts.
     * @since 1.0.0
     * @return void
     */
    public function routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base,
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'create' ),
                'permission_callback' => function () {
                    return Fns::gate( $this->base, 'add' );
                },
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

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[0-9,]+)',
            array(
                'methods' => 'DELETE',
                'callback' => array( $this, 'delete' ),
                'permission_callback' => function () {
                    return Fns::gate( $this->base, 'del' );
                },
                'args' => array(
                    'id' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Create new action(s).
     * @since 1.0.0
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
                    array(
						'success'  => false,
						'data' => $wp_err->get_error_messages(),
                    ), 200
                );
            } else {
                // Your logic for creating action(s)
            }
        }
    }

    /**
     * Update existing action.
     * @since 1.0.0
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
                array(
					'success'  => false,
					'data' => $wp_err->get_error_messages(),
                ), 200
            );
        } else {
            $data = array(
                'ID' => $post_id,
                'post_title' => '',
                'post_content' => '', // Note: $desc variable is not defined in the provided code
                'post_author' => get_current_user_id(),
            );
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                return new \WP_REST_Response(
                    array(
						'success'  => true,
						'data' => $post_id,
                    ), 200
                );
            }
        }
    }

    /**
     * Delete action(s) by ID(s).
     * @since 1.0.0
     * @param \WP_REST_Request $req Request object.
     */
    public function delete( $req ) {
        $url_params = $req->get_url_params();
        $ids = explode( ',', $url_params['id'] );
        foreach ( $ids as $id ) {
            wp_delete_post( $id );
        }

        do_action( 'ozopanelp_webhook', 'user_del', $ids );

        wp_send_json_success( $ids );
    }
}
