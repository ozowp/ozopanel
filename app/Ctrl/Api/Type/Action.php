<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class Action
{
    use Singleton;

    public function routes()
    {

        register_rest_route("wam/v1", "/actions", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("wam/v1", "/actions/(?P<id>[^/]+)", [
            "methods" => "PUT",
            "callback" => [$this, "update"],
            "permission_callback" => [$this, "update_per"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("wam/v1", "/actions/(?P<id>[0-9,]+)", [
            "methods" => "DELETE",
            "callback" => [$this, "delete"],
            "permission_callback" => [$this, "del_per"],
            "args" => [
                "id" => [
                    "sanitize_callback" => "sanitize_text_field",
                ],
            ],
        ]);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        // modified for multiple id support
        $str_id = isset($param["id"]) ? $param["id"] : null;
        $type = isset($param["type"]) ? sanitize_text_field($param["type"]) : '';

        $ids = explode(",", $str_id);

        foreach ($ids as $id) {
            $id = (int)$id;

            if (empty($id) || empty($type)) {
                $reg_errors->add(
                    "field",
                    esc_html__("Required field is missing", "wp-access-manager")
                );
            }

            if ($reg_errors->get_error_messages()) {
                wp_send_json_error($reg_errors->get_error_messages());
            } else {

            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $url_params = $req->get_url_params();
        $id = isset($url_params["id"]) ? $url_params["id"] : "";
        $ids = isset($params["ids"]) ? $params["ids"] : "";
        if ($id) {
            $ids = $ids ? $ids .= "," . $id : $id;
        }

        $type = isset($param["type"])? sanitize_text_field($param["type"]) : '';

        $ids_array = explode(",", $ids);

        foreach ($ids_array as $id) {
            $id = (int)$id;

            if (empty($id) || empty($type)) {
                $reg_errors->add(
                    "field",
                    esc_html__("Required field is missing", "wp-access-manager")
                );
            }

            if ($reg_errors->get_error_messages()) {
                wp_send_json_error($reg_errors->get_error_messages());
            }
        }

        wp_send_json_success($id);
    }

    public function delete($req)
    {
        $url_params = $req->get_url_params();
        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }
        wp_send_json_success($ids);
    }

    // check permission
    public function create_per()
    {
        return current_user_can("wam_action");
    }

    public function update_per()
    {
        return current_user_can("wam_action");
    }

    public function del_per()
    {
        return current_user_can("wam_action");
    }
}
