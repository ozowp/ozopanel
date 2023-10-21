<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class User
{
    use Singleton;

    public function routes()
    {
        register_rest_route("wam/v1", "/users/(?P<id>\d+)", [
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

        register_rest_route("wam/v1", "/users" . wam()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"],
        ]);

        register_rest_route("wam/v1", "/users", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"],
        ]);

        register_rest_route("wam/v1", "/users/(?P<id>\d+)", [
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

        register_rest_route("wam/v1", "/users/(?P<id>[0-9,]+)", [
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

        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : '';

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => "wam_user",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
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
            $item["id"] = $id;

            $meta = get_post_meta($id);
            $item["budget"] = isset($meta["budget"]) ? $meta["budget"][0] : "";
            $item["desc"] = get_the_content();

            $item["author"] = get_the_author();
            $item["date"] = get_the_time(get_option("date_format"));
            $list[] = $item;
        }
        wp_reset_postdata();

        $resp["list"] = $list;
        $resp["total"] = $total_list;

        wp_send_json_success($resp);
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $resp = [];
        $resp["id"] = absint($id);

        $meta = get_post_meta($id);
        $resp["ws_id"] = isset($meta["ws_id"]) ? $meta["ws_id"][0] : "";
        $resp["tab_id"] = isset($meta["tab_id"]) ? absint($meta["tab_id"][0]) : "";
        $resp["budget"] = isset($meta["budget"]) ? $meta["budget"][0] : "";
        $resp["currency"] = isset($meta["currency"]) ? $meta["currency"][0] : "";
        $resp["desc"] = get_post_field("post_content", $id);

        $resp["date"] = get_the_time(get_option("date_format"));

        wp_send_json_success($resp);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        //user
        $first_name = isset($param["first_name"]) ? sanitize_text_field($param["first_name"]) : null;
        $org_name = isset($param["org_name"]) ? sanitize_text_field($param["org_name"]) : null;
        $person_id = isset($param["person_id"]) ? absint($param["person_id"]) : null;
        $org_id = isset($param["org_id"]) ? absint($param["org_id"]) : null;
        $level_id = isset($param["level_id"]) ? absint($param["level_id"]) : null;
        $budget = isset($param["budget"]) ? sanitize_text_field($param["budget"]) : null;
        $currency = isset($param["currency"]) ? sanitize_text_field($param["currency"]) : null;
        $tags = isset($param["tags"]) ? array_map("absint", $param["tags"]) : null;
        $desc = isset($param["desc"]) ? nl2br($param["desc"]) : "";

        $email = isset($param['email']) ? strtolower(sanitize_email($param['email'])) : '';
        $mobile = isset($param['mobile']) ? sanitize_text_field($param['mobile']) : '';

        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Contact info is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            //insert user
            $data = [
                "post_type" => "wam_user",
                "post_title" => "Lead",
                "post_content" => $desc,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "tab_id", $post_id); //for task, note, file

                if ($level_id) {
                    wp_set_post_terms($post_id, [$level_id], "wam_user_level");
                }

                if ($person_id) {
                    update_post_meta($post_id, "person_id", $person_id);
                }

                if ($org_id) {
                    update_post_meta($post_id, "org_id", $org_id);
                }

                if ($budget) {
                    update_post_meta($post_id, "budget", $budget);
                }

                if ($currency) {
                    update_post_meta($post_id, "currency", $currency);
                }

                if ($tags) {
                    wp_set_post_terms($post_id, $tags, "wam_tag");
                }

                $param['id'] = $post_id;
                do_action("wamp/webhook", "user_add", $param);

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

        $url_params = $req->get_url_params();
        $post_id = $url_params["id"];

        //user
        $first_name = isset($param["first_name"]) ? sanitize_text_field($param["first_name"]) : null;
        $org_name = isset($param["org_name"]) ? sanitize_text_field($param["org_name"]) : null;
        $person_id = isset($param["person_id"]) ? absint($param["person_id"]) : null;
        $org_id = isset($param["org_id"]) ? absint($param["org_id"]) : null;
        $level_id = isset($param["level_id"]) ? absint($param["level_id"]) : null;
        $budget = isset($param["budget"]) ? sanitize_text_field($param["budget"]) : null;
        $currency = isset($param["currency"]) ? sanitize_text_field($param["currency"]) : null;
        $tags = isset($param["tags"]) ? array_map("absint", $param["tags"]) : null;

        $desc = $post_content = get_post_field('post_content', $post_id);


        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Contact info is missing", "propovoice")
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

                if ($person_id) {
                    update_post_meta($post_id, "person_id", $person_id);
                }

                if ($org_id && !$org_name) {
                    update_post_meta($post_id, "org_id", null);
                } else if ($org_id) {
                    update_post_meta($post_id, "org_id", $org_id);
                }

                update_post_meta($post_id, "budget", $budget);

                if ($currency) {
                    update_post_meta($post_id, "currency", $currency);
                }

                wp_set_post_terms($post_id, $tags, "wam_tag");

                do_action("wamp/webhook", "user_edit", $param);

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

        do_action("wamp/webhook", "user_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("administrator");
    }

    public function create_per()
    {
        return current_user_can("administrator");
    }

    public function update_per()
    {
        return current_user_can("administrator");
    }

    public function del_per()
    {
        return current_user_can("administrator");
    }
}
