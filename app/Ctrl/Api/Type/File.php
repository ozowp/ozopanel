<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class File
{
    use Singleton;
    
    public function register_routes()
    {

        register_rest_route("ndpv/v1", "/files/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/files" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/files", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/files/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/files/(?P<id>[0-9,]+)", [
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

        $tab_id = $param["tab_id"];
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_file",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        // if (!in_array("ndpv_manager", wp_get_current_user()->roles)) {
        //     $args["author"] = get_current_user_id();
        // }

        $args["meta_query"] = [
            "relation" => "AND",
        ];

        $args["meta_query"][] = [
            [
                "key" => "tab_id",
                "value" => $tab_id,
                "compare" => "=",
            ],
        ];

        if ($type) {
            $args["meta_query"][] = [
                [
                    "key" => "type",
                    "value" => $type,
                    "compare" => "LIKE",
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
            $query_data["type"] = isset($queryMeta["type"])
                ? sanitize_text_field($queryMeta["type"][0])
                : "";

            $query_data["url"] = isset($queryMeta["url"])
                ? sanitize_text_field($queryMeta["url"][0])
                : "";

            $file_id = isset($queryMeta["file"])
                ? sanitize_text_field($queryMeta["file"][0])
                : "";

            $query_data["title"] = isset($queryMeta["title"])
                ? sanitize_text_field($queryMeta["title"][0])
                : get_the_title($file_id);

            $fileData = null;
            if ($file_id) {
                $attach_type = get_post_mime_type($file_id);
                if ($attach_type == "application/pdf") {
                    $file_src = wp_get_attachment_url($file_id);
                    if ($file_src) {
                        $query_data["type"] = 'pdf';
                        $query_data["url"] = $file_src;
                    }
                } else {
                    $file_src = wp_get_attachment_image_src($file_id, "thumbnail");
                    if ($file_src) {
                        $fileData = [];
                        $fileData["id"] = $file_id;
                        $fileData["src"] = $file_src[0];
                        $fileData["src_small"] = $file_src;
                    }
                }
            }
            $query_data["file"] = $fileData;

            $author_id = get_post_field("post_author", $id);
            $query_data["upload_by"] = get_avatar_url($author_id, [
                "size" => "36",
            ]);
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

        $query_data["title"] = get_post_meta($id, "title", true);

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();
        $tab_id = isset($param["tab_id"]) ? absint($req["tab_id"]) : null;
        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;
        $file =
            isset($param["file"]) && isset($param["file"]["id"])
            ? absint($param["file"]["id"])
            : null;

        $title = isset($param["title"])
            ? sanitize_text_field($param["title"])
            : null;
        $url = isset($param["url"]) ? sanitize_text_field($param["url"]) : null;

        if (empty($tab_id)) {
            $reg_errors->add(
                "field",
                esc_html__("Tab ID is missing", "propovoice")
            );
        }

        if (empty($type)) {
            $reg_errors->add(
                "field",
                esc_html__("Type is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_file",
                "post_title" => "",
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());
                update_post_meta($post_id, "tab_id", $tab_id);
                update_post_meta($post_id, "type", $type);

                if ($title) {
                    update_post_meta($post_id, "title", $title);
                }

                if ($url) {
                    update_post_meta($post_id, "url", $url);
                }

                if ($file) {
                    update_post_meta($post_id, "file", $file);
                }

                $param['id'] = $post_id;
                do_action("ndpvp/webhook", "file_add", $param);

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

        $title = isset($param["title"])
            ? sanitize_text_field($param["title"])
            : null;
        $url = isset($param["url"]) ? sanitize_text_field($param["url"]) : null;

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => "",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($title) {
                    update_post_meta($post_id, "title", $title);
                }

                if ($url) {
                    update_post_meta($post_id, "url", $url);
                }

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
        return current_user_can("ndpv_file");
    }

    public function create_per()
    {
        return current_user_can("ndpv_file");
    }

    public function update_per()
    {
        return current_user_can("ndpv_file");
    }

    public function del_per()
    {
        return current_user_can("ndpv_file");
    }
}
