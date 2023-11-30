<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Setting class.
 *
 * @since 1.0.0
 */

class Setting extends RestCtrl
{
    /**
     * Route base.
     *
     * @var string
     * @since 1.0.0
     */
    protected $base = 'settings';

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
                'callback' => [$this, 'get'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'get');
                },
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . ozopanel()->plain_route(),
            [
                'methods' => 'POST',
                'callback' => [$this, 'create'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'add');
                }
            ]
        );
    }

    /**
     * Get settings data based on the provided tab.
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function get($req)
    {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $tab = isset($param['tab']) ? sanitize_text_field($param['tab']) : null;

        if (empty($tab)) {
            $wp_err->add(
                'field',
                esc_html__('Tab is missing', 'ozopanel')
            );
        }

        if ($wp_err->get_error_messages()) {
            return new \WP_REST_Response([
                'success'  => false,
                'data' => $wp_err->get_error_messages()
            ], 200);
        } else {
            $data = [];

            if ($tab == 'test_tab') {
                $option = get_option('ozopanel_' . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data['status'] = false;
                }
            }

            return new \WP_REST_Response([
                'success'  => true,
                'data' => $data
            ], 200);
        }
    }

    /**
     * Update settings data based on the provided tab.
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function create($req)
    {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $tab = isset($param['tab']) ? sanitize_text_field($param['tab']) : '';

        if (empty($tab)) {
            $wp_err->add(
                'field',
                esc_html__('Tab is missing', 'ozopanel')
            );
        }

        if ($wp_err->get_error_messages()) {
            return new \WP_REST_Response([
                'success'  => false,
                'data' => $wp_err->get_error_messages()
            ], 200);
        } else {
            $data = [];

            if ($tab == 'estimate_reminder') {
                $data['status'] = isset($param['status'])
                    ? rest_sanitize_boolean($param['status'])
                    : null;
                $data['due_date'] = isset($param['due_date'])
                    ? $param['due_date']
                    : null;
                $data['before'] = isset($param['before'])
                    ? $param['before']
                    : null;
                $data['after'] = isset($param['after'])
                    ? $param['after']
                    : null;

                $option = update_option('ozopanel_' . $tab, $data);
            }

            return new \WP_REST_Response([
                'success'  => true,
                'data' => null
            ], 200);
        }
    }
}
