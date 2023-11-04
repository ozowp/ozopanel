<?php

namespace OzoPanel\Ctrl\Api\Type;

use OzoPanel\Helper\AdminColumn\Fns;
use OzoPanel\Model\AdminColumn as ModelAdminColumn;
use OzoPanel\Traits\Singleton;

class AdminColumn
{
    use Singleton;

    public function routes()
    {

        register_rest_route('ozopanel/v1', '/admin-columns/(?P<id>[a-z0-9_]+)', [
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

        register_rest_route('ozopanel/v1', '/admin-columns/(?P<id>[a-z0-9-]+)', [
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

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params['id'];

        $resp = [];
        $resp['screens'] = ModelAdminColumn::list_screens();
        $resp['columns'] = [];
        if ( $id ) {
            if ( post_type_exists( $id ) ) {
                $columns = get_option('ozopanel_admin_column_' . $id . '_default', []);
                $resp['columns'] = Fns::format_column( $columns );
            } elseif ( $id == 'wp_media' ) {
                $columns = get_option('ozopanel_admin_column_upload_default', []);
                $resp['columns'] = Fns::format_column( $columns );

            } elseif ( $id == 'wp_comments' ) {
                $columns = get_option('ozopanel_admin_column_edit-comments_default', []);
                $resp['columns'] = Fns::format_column( $columns );
            } elseif ( $id == 'wp_users' ) {
                $columns = get_option('ozopanel_admin_column_users_default', []);
                $resp['columns'] = Fns::format_column( $columns );
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
