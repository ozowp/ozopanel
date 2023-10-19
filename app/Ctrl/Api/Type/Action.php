<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Traits\Singleton;

class Action
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/actions" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/actions", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/actions/(?P<id>\d+)", [
            "methods" => "GET",
            "callback" => [$this, "get_single"],
            "permission_callback" => [$this, "get_per"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("ndpv/v1", "/actions/(?P<id>[^/]+)", [
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

        register_rest_route("ndpv/v1", "/actions/(?P<id>[0-9,]+)", [
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

    public function get($req)
    {
        $request = $req->get_params();
        wp_send_json_success();
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = $id;

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        // modified for multiple id support
        $str_id = isset($param["id"]) ? $param["id"] : null;
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;

        $ids = explode(",", $str_id);

        foreach ($ids as $id) {
            $id = (int)$id;

            if (empty($id) || empty($type)) {
                $reg_errors->add(
                    "field",
                    esc_html__("Required field is missing", "propovoice")
                );
            }

            if ($reg_errors->get_error_messages()) {
                wp_send_json_error($reg_errors->get_error_messages());
            } else {

                $title = get_the_title($id);
                $oldpost = get_post($id);
                $post = [
                    "post_title" => $title,
                    "post_status" => "publish",
                    "post_type" => $oldpost->post_type,
                    "post_author" => get_current_user_id(),
                ];
                $new_post_id = wp_insert_post($post);

                // Copy post metadata

                $data = get_post_meta($id);

                //auto number
                $auto_id = '';
                $path = get_post_meta($id, "path", true);
                if ($type == "copy-to-inv") {
                    $path = "invoice";
                }
                $prefix = get_option("ndpv_" . $path . "_general");
                if ($prefix) {
                    $prefix = $prefix["prefix"];
                } else {
                    $prefix = $path == "invoice" ? "Inv-" : "Est-";
                }
                $auto_id = $prefix . Fns::auto_id($path);

                foreach ($data as $key => $values) {
                    foreach ($values as $value) {
                        if ($key == "status") {
                            $value = "draft";
                        }

                        if ($key == "path" && $type == "copy-to-inv") {
                            $value = "invoice";
                        }

                        if ($key == "num") {
                            $value = $auto_id;
                        }

                        if ($key == "invoice") { //key name invoice, but estimate and invoice stored here in obj
                            $value = maybe_unserialize($value);

                            $value["id"] = $new_post_id;
                            $value["num"] = $auto_id;

                            if ($type == "copy-to-inv") {
                                $value["path"] = "invoice";
                            }
                        }

                        add_post_meta(
                            $new_post_id,
                            $key,
                            maybe_unserialize($value)
                        );
                    }
                }
            }
        }

        if (!is_wp_error($new_post_id)) {
            wp_send_json_success($new_post_id);
        } else {
            wp_send_json_error();
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
        error_log(print_r($ids, true));
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;

        $ids_array = explode(",", $ids);

        foreach ($ids_array as $id) {
            $id = (int)$id;

            if (empty($id) || empty($type)) {
                $reg_errors->add(
                    "field",
                    esc_html__("Required field is missing", "propovoice")
                );
            }

            if ($reg_errors->get_error_messages()) {
                wp_send_json_error($reg_errors->get_error_messages());
            }
            update_post_meta($id, "status", $type);
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
    public function get_per()
    {
        return current_user_can("ndpv_action");
    }

    public function create_per()
    {
        return current_user_can("ndpv_action");
    }

    public function update_per()
    {
        return current_user_can("ndpv_action");
    }

    public function del_per()
    {
        return current_user_can("ndpv_action");
    }
}
