<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstract\RestCtrl;
use OzoPanel\Helper\Fns;

/**
 * API Action class.
 *
 * @since 1.0.0
 */

class Action extends RestCtrl
{

    /**
     * Route base.
     *
     * @var string
     * @since 1.0.0
     */
    protected $base = 'actions';

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
                'methods' => 'POST',
                'callback' => [$this, 'create'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'add');
                },
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[^/]+)',
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'edit');
                },
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[0-9,]+)',
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete'],
                'permission_callback' => function() {
                    return Fns::gate($this->base, 'del');
                },
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
}
