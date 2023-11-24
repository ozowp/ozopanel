<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Abstracts\RestCtrl;

/**
 * API Restriction class.
 *
 * @since 1.0.0
 */
class Restriction extends RestCtrl
{

    /**
     * Route base.
     *
     * @var string
     * @since 1.0.0
     */
    protected $base = 'restrictions';

    /**
     * Register all routes related with carts.
     *
     * @return void
     * @since 1.0.0
     */

    public function routes()
    {
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<type>[a-z]+)',
            [
                'methods' => 'POST',
                'callback' => [$this, 'create'],
                'permission_callback' => [$this, 'create_per'],
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ]
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<type>[a-z]+)' . ozopanel()->plain_route(),
            [
                'methods' => 'GET',
                'callback' => [$this, 'get'],
                'permission_callback' => [$this, 'get_per'],
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ]
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<type>[a-z]+)/(?P<id>[a-z0-9]+)',
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_single'],
                'permission_callback' => [$this, 'get_per'],
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<type>[a-z]+)/(?P<id>[a-z0-9]+)',
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update'],
                'permission_callback' => [$this, 'update_per'],
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<type>[a-z]+)/(?P<id>[a-z0-9,]+)',
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete'],
                'permission_callback' => [$this, 'del_per'],
                'args' => [
                    'type' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_string($param);
                        }
                    ],
                ],
            ]
        );
    }

    /**
     * Create reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create($req)
    {
        $param = $req->get_params();

        $url_param = $req->get_url_params();
        $type = $url_param['type'];

        $wp_err = new \WP_Error();

        $id = isset($param['id']) ? sanitize_text_field($param['id']) : '';

        $admin_menu = isset($param['admin_menu']) ? ($param['admin_menu']) : '';
        if (empty($id)) {
            if ($type == 'users') {
                $wp_err->add(
                    'select_id',
                    esc_html__('Please Select User', 'ozopanel')
                );
            } else {
                $wp_err->add(
                    'select_id',
                    esc_html__('Please Select Role', 'ozopanel')
                );
            }
        }

        if ($type == 'users') {
            $id_admin_menu = get_user_meta($id, '_ozopanel_admin_menu', true);
            if ($id_admin_menu) {
                $wp_err->add(
                    'user_exist',
                    esc_html__('User already exist!', 'ozopanel')
                );
            }
        } else {
            $role_admin_menu = get_option('ozopanel_admin_menu_role_' . $id);
            if ($role_admin_menu) {
                $wp_err->add(
                    'role_exist',
                    esc_html__('Role already exist!', 'ozopanel')
                );
            }
        }

        if (empty($admin_menu)) {
            $wp_err->add(
                'select_menu',
                esc_html__('Please select Menu', 'ozopanel')
            );
        }

        if ($type == 'users' && user_can($id, 'administrator')) {
            $wp_err->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($type == 'roles' && $id == 'administrator') {
            $wp_err->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($wp_err->get_error_messages()) {
            wp_send_json_error($wp_err->get_error_messages());
        } else {
            if ($type == 'users') {
                update_user_meta($id, '_ozopanel_admin_menu', $admin_menu);
            } else {
                update_option('ozopanel_admin_menu_role_' . $id, $admin_menu);
            }
            wp_send_json_success();
        }
    }

    /**
     * Get reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get($req)
    {
        $param = $req->get_params();

        $per_page = 10;
        $offset = 0;

        $s = isset($param['text']) ? sanitize_text_field($param['text']) : '';

        if (isset($param['per_page'])) {
            $per_page = $param['per_page'];
        }

        if (isset($param['page']) && $param['page'] > 1) {
            $offset = $per_page * $param['page'] - $per_page;
        }

        $url_params = $req->get_url_params();
        $type = $url_params['type'];

        $resp = $list = [];
        $total_list = 0;

        if ($type == 'users') {
            $args = [
                'number' => $per_page,
                'offset' => $offset,
            ];

            $args['meta_query'] = [
                [
                    'key' => '_ozopanel_admin_menu',
                    'compare' => 'EXISTS',
                ]
            ];

            $query = new \WP_User_Query($args);
            $total_list = $query->get_total(); //use this for pagination

            foreach ($query->get_results() as $user) {
                $item = [];
                $item['id'] = $user->ID;
                $item['name'] = $user->display_name;
                $item['email'] =  $user->user_email;

                $list[] = $item;
            }
            wp_reset_postdata();
        } else {
            global $wp_roles;
            foreach ($wp_roles->role_names as $key => $value) {
                //hide administrator
                if ($key == 'administrator') continue;

                if (get_option('ozopanel_admin_menu_role_' . $key)) {
                    $item = [];
                    $item['id'] = $key;
                    $item['label'] = $value;

                    $list[] = $item;
                    $total_list++;
                }
            }
        }

        $resp['list'] = $list;
        $resp['total'] = $total_list;

        wp_send_json_success($resp);
    }

    /**
     * Get single reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $type = $url_params['type'];
        $id = $url_params['id'];

        $resp = [];
        $admin_menu = get_option('ozopanel_admin_menu');
        $resp['admin_menu'] = $admin_menu;
        $resp['id_list'] = [];

        if ($type == 'users') {
            //hide administrator
            $args = array(
                'role__not_in' => array('administrator')
            );
            $users = get_users($args);
            foreach ($users as $user) {
                $modify_users = [];
                $modify_users['id'] = $user->ID;
                $modify_users['label'] = "$user->display_name - $user->user_email";
                $resp['id_list'][] = $modify_users;
            }
        } else if ($type == 'roles') {
            global $wp_roles;
            foreach ($wp_roles->role_names as $key => $value) {
                //hide administrator
                if ($key == 'administrator') continue;
                $modify_roles = [];
                $modify_roles['id'] = $key;
                $modify_roles['label'] = $value;
                $resp['id_list'][] = $modify_roles;
            }
        }

        if ($id) {
            $resp['form_data']['id'] = $id;
            if ($type == 'users') {
                $resp['form_data']['admin_menu'] = get_user_meta($id, '_ozopanel_admin_menu', true);
            } else if ($type == 'roles') {
                $resp['form_data']['admin_menu'] = get_option('ozopanel_admin_menu_role_' . $id) ?? [];
            }
        }

        wp_send_json_success($resp);
    }

    /**
     * Update reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update($req)
    {
        $param = $req->get_params();

        $url_param = $req->get_url_params();
        $id = $url_param["id"];
        $type = $url_param['type'];

        $wp_err = new \WP_Error();

        $admin_menu = isset($param['admin_menu']) ? ($param['admin_menu']) : '';
        if (empty($id)) {
            if ($type == 'users') {
                $wp_err->add(
                    'select_id',
                    esc_html__('Please Select User', 'ozopanel')
                );
            } else {
                $wp_err->add(
                    'select_id',
                    esc_html__('Please Seleact Role', 'ozopanel')
                );
            }
        }

        if (empty($admin_menu)) {
            $wp_err->add(
                'select_menu',
                esc_html__('Please select Menu', 'ozopanel')
            );
        }

        if ($type == 'users' && user_can($id, 'administrator')) {
            $wp_err->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($type == 'roles' && $id == 'administrator') {
            $wp_err->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($wp_err->get_error_messages()) {
            wp_send_json_error($wp_err->get_error_messages());
        } else {
            if ($type == 'users') {
                update_user_meta($id, '_ozopanel_admin_menu', $admin_menu);
            } else {
                update_option('ozopanel_admin_menu_role_' . $id, $admin_menu);
            }
            wp_send_json_success();
        }
    }

    /**
     * Delete reqeust
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $req request object
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete($req)
    {
        $wp_err = new \WP_Error();

        /* $wp_err->add(
            'select_id',
            esc_html__('Wrong ID', 'ozopanel')
        ); */

        if ($wp_err->get_error_messages()) {
            wp_send_json_error($wp_err->get_error_messages());
        }

        $url_param = $req->get_url_params();
        $type = $url_param['type'];
        $ids = explode(",", $url_param["id"]);
        foreach ($ids as $id) {
            if ($type == 'users') {
                delete_user_meta($id, '_ozopanel_admin_menu');
            } else {
                delete_option('ozopanel_admin_menu_role_' . $id);
            }
        }

        wp_send_json_success($ids);
    }

    // check permission
    public function create_per()
    {
        return current_user_can('administrator');
    }

    public function get_per()
    {
        return current_user_can('administrator');
    }

    public function update_per()
    {
        return current_user_can('administrator');
    }

    public function del_per()
    {
        return current_user_can('administrator');
    }
}
