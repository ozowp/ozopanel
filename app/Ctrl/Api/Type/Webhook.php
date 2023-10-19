<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class Webhook
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/webhooks/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/webhooks" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/webhooks", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/webhooks/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/webhooks/(?P<id>[0-9,]+)", [
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
        $param = $req->get_params();

        $per_page = 10;
        $offset = 0;

        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : "";
        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_webhook",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "AND",
        ];

        if ($type) {
            $args["meta_query"][] = [
                [
                    "key" => "type",
                    "value" => $type,
                ],
            ];
        }

        $query = new \WP_Query($args);
        $total_data = $query->found_posts; //use this for pagination
        $result = $data = [];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data["id"] = $id;

            $queryMeta = get_post_meta($id);
            $query_data["name"] = get_the_title();
            $query_data["active"] = false;
            if (isset($queryMeta["active"]) && $queryMeta["active"][0]) {
                $query_data["active"] = true;
            }

            $query_data["url"] = isset($queryMeta["url"])
                ? $queryMeta["url"][0]
                : "";
            $query_data["method"] = isset($queryMeta["method"])
                ? $queryMeta["method"][0]
                : "";
            $query_data["actions"] = isset($queryMeta["actions"])
                ? maybe_unserialize($queryMeta["actions"][0])
                : [];

            $data[] = $query_data;
        }
        wp_reset_postdata();

        $result["result"] = $data;
        $result["total"] = $total_data;

        wp_send_json_success($result);
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = absint($id);

        $queryMeta = get_post_meta($id);
        $query_data["ws_id"] = isset($queryMeta["ws_id"])
            ? $queryMeta["ws_id"][0]
            : "";
        $query_data["tab_id"] = isset($queryMeta["tab_id"])
            ? absint($queryMeta["tab_id"][0])
            : "";

        $query_data["date"] = get_the_time(get_option("date_format"));

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        //webhook
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : "";
        $active = isset($param["active"])
            ? rest_sanitize_boolean($param["active"])
            : false;
        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : "";
        $url = isset($param["url"]) ? esc_url_raw($param["url"]) : "";
        $method = isset($param["method"])
            ? sanitize_text_field($param["method"])
            : "";
        $actions = isset($param["actions"])
            ? array_map("sanitize_text_field", $param["actions"])
            : [];

        if (empty($type)) {
            $reg_errors->add(
                "field",
                esc_html__("Type is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            //insert webhook
            $data = [
                "post_type" => "ndpv_webhook",
                "post_title" => $name,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());

                if ($type) {
                    update_post_meta($post_id, "type", $type);
                }

                if ($active) {
                    update_post_meta($post_id, "active", $active);
                }

                if ($url) {
                    update_post_meta($post_id, "url", $url);
                }

                if ($method) {
                    update_post_meta($post_id, "method", $method);
                }

                if ($actions) {
                    update_post_meta($post_id, "actions", $actions);
                }

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        //webhook
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : "";
        $active = isset($param["active"])
            ? rest_sanitize_boolean($param["active"])
            : false;
        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : "";
        $url = isset($param["url"]) ? esc_url_raw($param["url"]) : "";
        $method = isset($param["method"])
            ? sanitize_text_field($param["method"])
            : "";
        $actions = isset($param["actions"])
            ? array_map("sanitize_text_field", $param["actions"])
            : [];

        if (empty($type)) {
            $reg_errors->add(
                "field",
                esc_html__("Type is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => $name,
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "active", $active);

                update_post_meta($post_id, "url", $url);

                update_post_meta($post_id, "method", $method);

                update_post_meta($post_id, "actions", $actions);

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
        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_webhook");
    }

    public function create_per()
    {
        return current_user_can("ndpv_webhook");
    }

    public function update_per()
    {
        return current_user_can("ndpv_webhook");
    }

    public function del_per()
    {
        return current_user_can("ndpv_webhook");
    }
}
