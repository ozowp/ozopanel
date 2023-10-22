<?php

namespace WAM\Ctrl\Api;

use WAM\Ctrl\Api\Type\{
    Action,
    Setting,
    Restriction
};

/**
 * Class ApiCtrl
 *
 * Controller for registering custom REST API endpoints.
 * @since 1.0.0
 */
class ApiCtrl
{

    /**
     * ApiCtrl constructor.
     * Registers custom REST API endpoints and handles plain permalink API support.
     * @since 1.0.0
     */
    public function __construct()
    {
        // Register custom REST API endpoints
        add_action('rest_api_init', function() {
            Restriction::init()->routes();
            Action::init()->routes();
            Setting::init()->routes();
        });

        // For plain permalink API support
        add_filter('rest_request_before_callbacks', [$this, 'rest_request_filter'], 10, 3);
    }

    /**
     * For plain permalink API support.
     *
     * @param \WP_REST_Response $resp The response to send.
     * @param \WP_REST_Server $handler Server instance.
     * @param \WP_REST_Request $req Request used to generate the response.
     * @return \WP_REST_Request Modified request.
     * @since 1.0.0
     */
    public function rest_request_filter($resp, $handler, $req) {
        $permalink_structure = get_option('permalink_structure');
        if ( $permalink_structure === '' ) {
            $params = $req->get_params();
            if ( isset($params['rest_route']) ) {
                $query_string = parse_url($params['rest_route'], PHP_URL_QUERY);
                // Parse the query string into an array of parameters
                parse_str($query_string, $param_form_args);
                foreach( $param_form_args as $key => $val) {
                    $req->set_param( $key, $val);
                }
            }
        }
        return $req;
    }
}
