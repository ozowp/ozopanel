<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class Note
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/notes/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/notes" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/notes", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/notes/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/notes/(?P<id>[0-9,]+)", [
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

        $per_page = 10;
        $offset = 0;

        $tab_id = $request["tab_id"];

        if (isset($request["per_page"])) {
            $per_page = $request["per_page"];
        }

        if (isset($request["page"]) && $request["page"] > 1) {
            $offset = $per_page * $request["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_note",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        // if (!in_array("ndpv_manager", wp_get_current_user()->roles)) {
        //     $args["author"] = get_current_user_id();
        // }

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        $args["meta_query"][] = [
            [
                "key" => "tab_id",
                "value" => $tab_id,
                "compare" => "=",
            ],
        ];

        $query = new \WP_Query($args);
        $total_data = $query->found_posts; //use this for pagination
        $result = $data = [];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data["id"] = $id;

            $query_data["text"] = get_the_content();

            $author_id = get_post_field("post_author", $id);
            $query_data["by"] = get_the_author_meta("display_name", $author_id);

            $posted = get_the_time("U");
            $query_data["date"] =
                human_time_diff($posted, current_time("U")) .
                " " .
                esc_html__("ago", "propovoice");
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
        $query_data["id"] = $id;

        $query_data["text"] = get_post_field("post_content", $id);

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $tab_id = isset($param["tab_id"]) ? absint($req["tab_id"]) : null;
        $text = isset($param["text"]) ? nl2br($req["text"]) : null;

        if (empty($tab_id)) {
            $reg_errors->add(
                "field",
                esc_html__("Tab ID is missing", "propovoice")
            );
        }

        if (empty($text)) {
            $reg_errors->add(
                "field",
                esc_html__("Name field is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_note",
                "post_title" => "",
                "post_content" => $text,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());
                update_post_meta($post_id, "tab_id", $tab_id);

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

        $text = isset($param["text"])
            ? sanitize_text_field($req["text"])
            : null;

        if (empty($text)) {
            $reg_errors->add(
                "field",
                esc_html__("Name field is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => "",
                "post_content" => $text,
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
        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_note");
    }

    public function create_per()
    {
        return current_user_can("ndpv_note");
    }

    public function update_per()
    {
        return current_user_can("ndpv_note");
    }

    public function del_per()
    {
        return current_user_can("ndpv_note");
    }
}
