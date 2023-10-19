<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class Business
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/businesses/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/businesses" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/businesses", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/businesses/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/businesses/(?P<id>[0-9,]+)", [
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

        if (isset($request["per_page"])) {
            $per_page = $request["per_page"];
        }

        if (isset($request["page"]) && $request["page"] > 1) {
            $offset = $per_page * $request["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_business",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if (isset($request["default"])) {
            $args["meta_query"][] = [
                [
                    "key" => "default",
                    "value" => 1,
                    "compare" => "LIKE",
                ],
            ];
        }

        $args["meta_query"][] = [
            [
                "key" => "ws_id",
                "value" => ndpv()->get_workspace(),
                "compare" => "LIKE",
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

            $query_data["name"] = get_post_meta($id, "name", true);
            $query_data["org_name"] = get_post_meta($id, "org_name", true);
            $query_data["email"] = get_post_meta($id, "email", true);
            $query_data["web"] = get_post_meta($id, "web", true);
            $query_data["mobile"] = get_post_meta($id, "mobile", true);
            $query_data["country"] = get_post_meta($id, "country", true);
            $query_data["region"] = get_post_meta($id, "region", true);
            $query_data["address"] = get_post_meta($id, "address", true);
            $query_data["city"] = get_post_meta($id, "city", true);
            $query_data["zip"] = get_post_meta($id, "zip", true);
            $query_data["default"] = (bool) get_post_meta($id, "default", true);

            $logo_id = get_post_meta($id, "logo", true);
            $logoData = null;
            if ($logo_id) {
                $logo_src = wp_get_attachment_image_src($logo_id, "thumbnail");
                if ($logo_src) {
                    $logoData = [];
                    $logoData["id"] = $logo_id;
                    $logoData["src"] = $logo_src[0];
                }
            }
            $query_data["logo"] = $logoData;

            $query_data["date"] = get_the_time(get_option("date_format"));
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

        $query_data["name"] = get_post_meta($id, "name", true);
        $query_data["org_name"] = get_post_meta($id, "org_name", true);
        $query_data["email"] = get_post_meta($id, "email", true);
        $query_data["web"] = get_post_meta($id, "web", true);
        $query_data["mobile"] = get_post_meta($id, "mobile", true); 
        $query_data["country"] = get_post_meta($id, "country", true);
        $query_data["region"] = get_post_meta($id, "region", true);
        $query_data["address"] = get_post_meta($id, "address", true);
        $query_data["city"] = get_post_meta($id, "city", true);
        $query_data["zip"] = get_post_meta($id, "zip", true);
        $query_data["default"] = (bool) get_post_meta($id, "default", true);

        $logo_id = get_post_meta($id, "logo", true);
        $logoData = null;
        if ($logo_id) {
            $logo_src = wp_get_attachment_image_src($logo_id, "thumbnail");
            if ($logo_src) {
                $logoData = [];
                $logoData["id"] = $logo_id;
                $logoData["src"] = $logo_src[0];
            }
        }
        $query_data["logo"] = $logoData;

        wp_send_json_success($query_data);
    }

    public function set_default()
    {
        $args = [
            "post_type" => "ndpv_business",
            "post_status" => "publish",
            "posts_per_page" => -1,
            "fields" => "ids",
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        $args["meta_query"][] = [
            [
                "key" => "default",
                "value" => 1,
                "compare" => "LIKE",
            ],
        ];

        $query = new \WP_Query($args);
        foreach ($query->posts as $id) {
            update_post_meta($id, "default", false);
        }
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;        
        $email = isset($param["email"])
            ? strtolower(sanitize_email($param["email"]))
            : null;
        $web = isset($param["web"]) ? esc_url_raw($param["web"]) : null;
        $mobile = isset($param["mobile"])
            ? sanitize_text_field($param["mobile"])
            : null;
        $country = isset($param["country"])
            ? sanitize_text_field($param["country"])
            : '';
        $region = isset($param["region"])
            ? sanitize_text_field($param["region"])
            : '';
        $address = isset($param["address"])
            ? sanitize_text_field($param["address"])
            : '';
        $city = isset($param["city"])
            ? sanitize_text_field($param["city"])
            : '';
        $zip = isset($param["zip"]) ? sanitize_text_field($param["zip"]) : '';
        $default = isset($param["default"])
            ? rest_sanitize_boolean($param["default"])
            : null;
        $logo = isset($param["logo"]["id"])
            ? absint($param["logo"]["id"])
            : null;

        if (empty($name)) {
            $reg_errors->add(
                "field",
                esc_html__("Name is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_business",
                "post_title" => $name,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());

                if ($name) {
                    update_post_meta($post_id, "name", $name);
                }

                if ($org_name) {
                    update_post_meta($post_id, "org_name", $org_name);
                }

                if ($web) {
                    update_post_meta($post_id, "web", $web);
                }

                if ($email) {
                    update_post_meta($post_id, "email", $email);
                }

                if ($mobile) {
                    update_post_meta($post_id, "mobile", $mobile);
                }

                if ($country) {
                    update_post_meta($post_id, "country", $country);
                }

                if ($region) {
                    update_post_meta($post_id, "region", $region);
                }

                if ($address) {
                    update_post_meta($post_id, "address", $address);
                }

                if ($city) {
                    update_post_meta($post_id, "city", $city);
                }

                if ($zip) {
                    update_post_meta($post_id, "zip", $zip);
                }

                if ($default) {
                    $this->set_default();
                    update_post_meta($post_id, "default", true);
                } else {
                    update_post_meta($post_id, "default", false);
                }

                if ($logo) {
                    update_post_meta($post_id, "logo", $logo);
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

        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;
        $web = isset($param["web"]) ? esc_url_raw($param["web"]) : null;
        $email = isset($param["email"])
            ? strtolower(sanitize_email($param["email"]))
            : null;
        $mobile = isset($param["mobile"])
            ? sanitize_text_field($param["mobile"])
            : null; 

        $country = isset($param["country"])
            ? sanitize_text_field($param["country"])
            : '';
        $region = isset($param["region"])
            ? sanitize_text_field($param["region"])
            : '';
        $address = isset($param["address"])
            ? sanitize_text_field($param["address"])
            : '';
        $city = isset($param["city"])
            ? sanitize_text_field($param["city"])
            : '';
        $zip = isset($param["zip"]) ? sanitize_text_field($param["zip"]) : '';

        $default = isset($param["default"])
            ? rest_sanitize_boolean($param["default"])
            : null;
        $logo = isset($param["logo"]["id"])
            ? absint($param["logo"]["id"])
            : null;

        if (empty($name)) {
            $reg_errors->add(
                "field",
                esc_html__("Name is missing", "propovoice")
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
                if ( isset($param['name']) ) {
                    update_post_meta($post_id, 'name', $name);
                }

                if ( isset($param['org_name']) ) {
                    update_post_meta($post_id, 'org_name', $org_name);
                }

                if ( isset($param['email']) ) {
                    update_post_meta($post_id, 'email', $email);
                }

                if ( isset($param['web']) ) {
                    update_post_meta($post_id, 'web', $web);
                }

                if ( isset($param['mobile']) ) {
                    update_post_meta($post_id, 'mobile', $mobile);
                }

                if ( isset($param['country']) ) {
                    update_post_meta($post_id, 'country', $country);
                }

                if ( isset($param['region']) ) {
                    update_post_meta($post_id, 'region', $region);
                }

                if ( isset($param['address']) ) {
                    update_post_meta($post_id, 'address', $address);
                }

                if ( isset($param['city']) ) {
                    update_post_meta($post_id, 'city', $city);
                }

                if ( isset($param['zip']) ) {
                    update_post_meta($post_id, 'zip', $zip);
                } 

                if ($default) {
                    $this->set_default();
                    update_post_meta($post_id, "default", true);
                } else {
                    update_post_meta($post_id, "default", false);
                }

                if ($logo) {
                    update_post_meta($post_id, "logo", $logo);
                } else {
                    delete_post_meta($post_id, "logo");
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
        return current_user_can("ndpv_business");
    }

    public function create_per()
    {
        return current_user_can("ndpv_business");
    }

    public function update_per()
    {
        return current_user_can("ndpv_business");
    }

    public function del_per()
    {
        return current_user_can("ndpv_business");
    }
}
