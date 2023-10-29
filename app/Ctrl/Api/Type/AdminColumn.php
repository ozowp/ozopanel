<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Helper\AdminColumn\ListScreens;
use OzoPanel\Model\AdminColumn as ModelAdminColumn;
use OzoPanel\Traits\Singleton;

class AdminColumn
{
    use Singleton;

    public function routes()
    {
        register_rest_route('ozopanel/v1', '/admin-columns/(?P<type>[a-z]+)', [
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
        ]);

        register_rest_route('ozopanel/v1', '/admin-columns' . ozopanel()->plain_route(), [
            'methods' => 'GET',
            'callback' => [$this, 'get'],
            'permission_callback' => [$this, 'get_per'],
        ]);

        register_rest_route('ozopanel/v1', '/admin-columns/(?P<id>[a-z0-9]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_single'],
            'permission_callback' => [$this, 'get_per'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
                ],
            ],
        ]);

        register_rest_route('ozopanel/v1', '/admin-columns/(?P<id>[a-z0-9]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update'],
            'permission_callback' => [$this, 'update_per'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
                ],
            ],
        ]);

        register_rest_route('ozopanel/v1', '/admin-columns/(?P<id>[a-z0-9,]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete'],
            'permission_callback' => [$this, 'del_per'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param) {
                        return is_string($param);
                    }
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
        if ( empty($id) ) {
            if ($type == 'users') {
                $reg_errors->add(
                    'select_id',
                    esc_html__('Please Select User', 'ozopanel')
                );
            } else {
                $reg_errors->add(
                    'select_id',
                    esc_html__('Please Select Role', 'ozopanel')
                );
            }
        }

        if ( $type == 'users' ) {
            $id_admin_menu = get_user_meta( $id, '_ozopanel_admin_menu', true);
            if ( $id_admin_menu ) {
                $reg_errors->add(
                    'user_exist',
                    esc_html__('User already exist!', 'ozopanel')
                );
            }
        } else {
            $role_admin_menu = get_option('ozopanel_admin_menu_role_' . $id);
            if ( $role_admin_menu ) {
                $reg_errors->add(
                    'role_exist',
                    esc_html__('Role already exist!', 'ozopanel')
                );
            }
        }

        if ( empty( $admin_menu ) ) {
            $reg_errors->add(
                'select_menu',
                esc_html__('Please select Menu', 'ozopanel')
            );
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

        $resp = $list = [];
        $total_list = 0;

        /* $list_screen = new ListScreens();
        $list = $list_screen->register_list_screens(); */
        // $list = ModelAdminColumn::get_post_types();

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

        if ($type == 'users') {
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

        if ( $id ) {
            $resp['form_data']['id'] = $id;
            if ( $type == 'users' ) {
                $resp['form_data']['admin_menu'] = get_user_meta($id, '_ozopanel_admin_menu', true);
            } else if ($type == 'roles') {
                $resp['form_data']['admin_menu'] = get_option('ozopanel_admin_menu_role_'. $id) ?? [];
            }
        }

        wp_send_json_success($resp);
    }

    public function update($req)
    {
        $param = $req->get_params();

        $url_param = $req->get_url_params();
        $id = $url_param["id"];
        $type = $url_param['type'];

        $reg_errors = new \WP_Error();

        $admin_menu = isset($param['admin_menu']) ? ($param['admin_menu']) : '';
        if ( empty($id) ) {
            if ($type == 'users') {
                $reg_errors->add(
                    'select_id',
                    esc_html__('Please Select User', 'ozopanel')
                );
            } else {
                $reg_errors->add(
                    'select_id',
                    esc_html__('Please Seleact Role', 'ozopanel')
                );
            }
        }

        if ( empty($admin_menu) ) {
            $reg_errors->add(
                'select_menu',
                esc_html__('Please select Menu', 'ozopanel')
            );
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

    public function delete($req)
    {
        $url_param = $req->get_url_params();
        $type = $url_param['type'];
        $ids = explode(",", $url_param["id"]);
        foreach ($ids as $id) {
            if ( $type == 'users' ) {
                delete_user_meta( $id, '_ozopanel_admin_menu');
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
