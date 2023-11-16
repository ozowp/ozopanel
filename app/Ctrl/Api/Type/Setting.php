<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Traits\Singleton;

/**
 * Class Setting
 * @package OzoPanel\Ctrl\Api\Type
 *
 * REST API endpoints related to settings.
 */
class Setting
{
    use Singleton;

    /**
     * Register REST API routes.
     */
    public function routes()
    {
        register_rest_route('ozopanel/v1', '/settings' . ozopanel()->plain_route(), [
            'methods' => 'GET',
            'callback' => [$this, 'get'],
            'permission_callback' => [$this, 'get_per'],
        ]);

        register_rest_route('ozopanel/v1', '/settings', [
            'methods' => 'POST',
            'callback' => [$this, 'create'],
            'permission_callback' => [$this, 'create_per'],
        ]);
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
            wp_send_json_error($wp_err->get_error_messages());
        } else {
            $data = [];

            if ($tab == 'estimate_reminder') {
                $option = get_option('ozopanel_' . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data['status'] = false;
                    $data['due_date'] = false;
                    $data['before'] = [];
                    $data['after'] = [];
                }
            }

            wp_send_json_success($data);
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
            wp_send_json_error($wp_err->get_error_messages());
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

            wp_send_json_success();
        }
    }

    /**
     * Check permission for getting settings data.
     *
     * @return bool Whether the current user can access the endpoint.
     */
    public function get_per()
    {
        return current_user_can('administrator');
    }

    /**
     * Check permission for updating settings data.
     *
     * @return bool Whether the current user can access the endpoint.
     */
    public function create_per()
    {
        return current_user_can('administrator');
    }
}
