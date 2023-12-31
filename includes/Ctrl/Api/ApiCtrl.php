<?php

namespace OzoPanel\Ctrl\Api;

use OzoPanel\Ctrl\Api\Type\{
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
     * Class dir and class name mapping.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $class_map;

    /**
     * ApiCtrl constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Register custom REST API endpoints
        if (!class_exists('WP_REST_Server')) {
            return;
        }

        $this->class_map = apply_filters(
            'ozopanel_rest_api_class_map',
            [
                Restriction::class
            ]
        );

        // Init REST API routes.
        add_action('rest_api_init', array($this, 'register_rest_routes'), 10);

        // For plain permalink API support
        add_filter('rest_request_before_callbacks', [$this, 'rest_request_filter'], 10, 3);
    }

    /**
     * Register REST API routes.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_rest_routes(): void
    {
        foreach ($this->class_map as $controller) {
            $this->$controller = new $controller();
            $this->$controller->routes();
        }
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
    public function rest_request_filter($resp, $handler, $req)
    {
        $permalink_structure = get_option('permalink_structure');
        if ($permalink_structure === '') {
            $params = $req->get_params();
            if (isset($params['rest_route'])) {
                $query_string = wp_parse_url($params['rest_route']);
                // Parse the query string into an array of parameters
                parse_str($query_string, $param_form_args);
                foreach ($param_form_args as $key => $val) {
                    $req->set_param($key, $val);
                }
            }
        }
        return $req;
    }
}
