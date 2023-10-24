<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Traits\Singleton;

class Restriction
{
    use Singleton;

    public function routes()
    {
        register_rest_route('ozopanel/v1', '/restrictions/(?P<type>[a-z]+)', [
            'methods' => 'POST',
            'callback' => [$this, 'create'],
            'permission_callback' => [$this, 'create_per'],
            'args' => array(
                'type' => array(
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    },
                ),
            ),
        ]);

        register_rest_route('ozopanel/v1', '/restrictions/(?P<type>[a-z]+)' . ozopanel()->plain_route(), [
            'methods' => 'GET',
            'callback' => [$this, 'get'],
            'permission_callback' => [$this, 'get_per'],
            'args' => array(
                'type' => array(
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    },
                ),
            ),
        ]);

        register_rest_route('ozopanel/v1', '/restrictions/(?P<type>[a-z]+)/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_single'],
            'permission_callback' => [$this, 'get_per'],
            'args' => [
                'type' => array(
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    },
                ),
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route('ozopanel/v1', '/restrictions/(?P<id>\d+)/(?P<type>[a-z]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_single'),
            'permission_callback' => array($this, 'get_per'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ),
                'type' => array(
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    },
                ),
            ),
        ));

        register_rest_route('ozopanel/v1', '/restrictions/(?P<id>\d+)', [
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

        register_rest_route('ozopanel/v1', '/restrictions/(?P<id>[0-9,]+)', [
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

    public function create($req)
    {
        $param = $req->get_params();

        $url_param = $req->get_url_params();
        $type = $url_param['type'];

        $reg_errors = new \WP_Error();

        $id = isset($param['id']) ? sanitize_text_field($param['id']) : '';

        $admin_menu = isset($param['admin_menu']) ? ($param['admin_menu']) : '';
        if (empty($id)) {
            if ($type == 'users') {
                $reg_errors->add(
                    'select_id',
                    esc_html__('You must select a User', 'ozopanel')
                );
            } else {
                $reg_errors->add(
                    'select_id',
                    esc_html__('You must select a Select', 'ozopanel')
                );
            }
        }

        if ( $type == 'users' && user_can( $id, 'administrator' ) ) {
            $reg_errors->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($type == 'roles' && $id == 'administrator') {
            $reg_errors->add(
                'select_id',
                esc_html__('Administrator restriction not allowed!', 'ozopanel')
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            if ($type == 'users') {
                update_user_meta($id, '_ozopanel_admin_menu', $admin_menu);
            } else {
                update_option('ozopanel_admin_menu_role_' . $id, $admin_menu);
            }
            wp_send_json_success();
        }
    }

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
                // Do something with each user
                $user_id = $user->ID;
                $item = array();
                $item['id'] = $user_id;
                $item['name'] = $user->display_name;
                $item['email'] =  $user->user_email;

                $list[] = $item;
            }
            wp_reset_postdata();
        } else {
        }

        $resp['list'] = $list;
        $resp['total'] = $total_list;

        wp_send_json_success($resp);
    }

    public function get_single($req)
    {

        $url_params = $req->get_url_params();
        $type = $url_params['type'];
        $id = $url_params['id'];

        $resp = [];
        $admin_menu = get_option('ozopanel_admin_menu');
        $resp['admin_menu'] = $admin_menu;
        $resp['id_list'] = [];

        if ($type == 'roles') {
            global $wp_roles;
            foreach ($wp_roles->role_names as $key => $value) {
                //hide administrator
                if ($key == 'administrator') continue;
                $modify_roles = [];
                $modify_roles['id'] = $key;
                $modify_roles['label'] = $value;
                $resp['id_list'][] = $modify_roles;
            }
        } else if ($type == 'users') {
            //hide administrator
            $args = array(
                'role__not_in' => array( 'administrator' )
            );
            $users = get_users( $args );
            foreach ($users as $user) {
                $modify_users = [];
                $modify_users['id'] = $user->ID;
                $modify_users['label'] = "$user->display_name - $user->user_email";
                $resp['id_list'][] = $modify_users;
            }
        }

        wp_send_json_success($resp);
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $url_params = $req->get_url_params();
        $post_id = $url_params["id"];

        //user
        $first_name = isset($param["first_name"]) ? sanitize_text_field($param["first_name"]) : '';
        $org_name = isset($param["org_name"]) ? sanitize_text_field($param["org_name"]) : '';

        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Contact info is missing", "ozopanel")
            );
        }


        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {

            $data = [
                "ID" => $post_id,
                "post_title" => "Lead",
                "post_content" => $desc,
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function delete($req)
    {
        $url_params = $req->get_url_params();
        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action("ozopanelp/webhook", "user_del", $ids);

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
