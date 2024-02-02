<?php

namespace OzoPanel\Abstracts;

use WP_Error;
use WP_REST_Controller;

/**
 * Rest Controller base class
 *
 * @since 0.1.0
 */
abstract class RestCtrl extends WP_REST_Controller {


    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'ozopanel/v1';

    /**
     * Endpoint base
     *
     * @var string
     */
    protected $base = '';

    /**
     * Check default permission for rest routes.
     *
     * @since 0.1.0
     *
     * @return bool
     */

    public function gate( $req ) {
        // You can access parameters from the $req object
        // $param = $req->get_param('param');

        // Implement your permission check logic here
        if ( current_user_can( 'administrator' ) ) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__( 'Sorry, you are not allowed to do that.', 'ozopanel' ),
            [
                'status' => is_user_logged_in() ? 403 : 401,
            ]
        );
    }
}
