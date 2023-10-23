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
        $reg_errors = new \WP_Error();

        //user
        $first_name = isset($param['first_name']) ? sanitize_text_field($param['first_name']) : null;
        $org_name = isset($param['org_name']) ? sanitize_text_field($param['org_name']) : null;
        $person_id = isset($param['person_id']) ? absint($param['person_id']) : null;
        $org_id = isset($param['org_id']) ? absint($param['org_id']) : null;
        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                'field',
                esc_html__('Contact info is missing', 'ozopanel')
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            wp_send_json_success();
            //insert user
            $data = [
                'post_type' => 'ozopanel_user',
                'post_title' => 'Lead',
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                if ($org_id) {
                    update_post_meta($post_id, 'org_id', $org_id);
                }

                $param['id'] = $post_id;
                do_action('ozopanelp/webhook', 'user_add', $param);

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
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

        $args = [
            'post_type' => 'ozopanel_user',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'offset' => $offset,
        ];

        $args['meta_query'] = [
            'relation' => 'OR',
        ];

        if ($s) {
        }

        $query = new \WP_Query($args);
        $total_list = $query->found_posts; //use this for pagination
        $resp = $list = [];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $item = [];
            $item['id'] = $id;

            $meta = get_post_meta($id);
            $item['budget'] = isset($meta['budget']) ? $meta['budget'][0] : '';
            $item['desc'] = get_the_content();

            $item['author'] = get_the_author();
            $item['date'] = get_the_time(get_option('date_format'));
            $list[] = $item;
        }
        wp_reset_postdata();

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
        $admin_menu = get_option( 'ozopanel_admin_menu' );
        $resp['admin_menu'] = $admin_menu;
        $resp['id_list'] = [];

        if ( $type == 'roles' ) {
            global $wp_roles;
            foreach( $wp_roles->role_names as $key => $value ) {
                $modify_roles = [];
                $modify_roles['id'] = $key;
                $modify_roles['label'] = $value;
                $resp['id_list'][] = $modify_roles;
            }
        } else if ( $type == 'users' ) {
            $users = get_users();
            foreach( $users as $user ) {
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
