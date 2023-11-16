<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Traits\Singleton;

/**
 * Class Action
 * @package OzoPanel\Ctrl\Api\Type
 *
 * REST API endpoints related to actions.
 */
class Action
{
    use Singleton;

    /**
     * Register REST API routes.
     */
    public function routes()
    {
        register_rest_route('ozopanel/v1', '/actions', [
            'methods' => 'POST',
            'callback' => [$this, 'create'],
            'permission_callback' => [$this, 'create_per']
        ]);

        register_rest_route('ozopanel/v1', '/actions/(?P<id>[^/]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update'],
            'permission_callback' => [$this, 'update_per'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route('ozopanel/v1', '/actions/(?P<id>[0-9,]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete'],
            'permission_callback' => [$this, 'del_per'],
            'args' => [
                'id' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Create new action(s).
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function create($req)
    {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        // modified for multiple id support
        $str_id = isset($param['id']) ? $param['id'] : null;
        $type = isset($param['type']) ? sanitize_text_field($param['type']) : '';

        $ids = explode(',', $str_id);

        foreach ($ids as $id) {
            $id = (int)$id;

            if (empty($id) || empty($type)) {
                $wp_err->add(
                    'field',
                    esc_html__('Required field is missing', 'ozopanel')
                );
            }

            if ($wp_err->get_error_messages()) {
                wp_send_json_error($wp_err->get_error_messages());
            } else {
                // Your logic for creating action(s)
            }
        }
    }

    /**
     * Update existing action.
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function update($req)
    {
        $param = $req->get_params();
        $wp_err = new \WP_Error();

        $url_params = $req->get_url_params();
        $post_id = $url_params['id'];

        //user
        $first_name = isset($param['first_name']) ? sanitize_text_field($param['first_name']) : '';
        $org_name = isset($param['org_name']) ? sanitize_text_field($param['org_name']) : '';

        if (empty($first_name) && empty($org_name)) {
            $wp_err->add(
                'field',
                esc_html__('Contact info is missing', 'ozopanel')
            );
        }

        if ($wp_err->get_error_messages()) {
            wp_send_json_error($wp_err->get_error_messages());
        } else {
            $data = [
                'ID' => $post_id,
                'post_title' => 'Lead',
                'post_content' => $desc, // Note: $desc variable is not defined in the provided code
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    /**
     * Delete action(s) by ID(s).
     *
     * @param \WP_REST_Request $req Request object.
     */
    public function delete($req)
    {
        $url_params = $req->get_url_params();
        $ids = explode(',', $url_params['id']);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action('ozopanelp/webhook', 'user_del', $ids);

        wp_send_json_success($ids);
    }

    /**
     * Check permission for creating actions.
     *
     * @return bool Whether the current user can access the endpoint.
     */
    public function create_per()
    {
        return current_user_can('ozopanel_action');
    }

    /**
     * Check permission for updating actions.
     *
     * @return bool Whether the current user can access the endpoint.
     */
    public function update_per()
    {
        return current_user_can('ozopanel_action');
    }

    /**
     * Check permission for deleting actions.
     *
     * @return bool Whether the current user can access the endpoint.
     */
    public function del_per()
    {
        return current_user_can('ozopanel_action');
    }
}
