<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Traits\Singleton;

class Payment
{
    use Singleton;
    
    public function register_routes()
    {

        register_rest_route("ndpv/v1", "/payments/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/payments" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/payments", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/payments/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/payments/(?P<id>[0-9,]+)", [
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
            "post_type" => "ndpv_payment",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if (isset($request["type"])) {
            $args["meta_query"][] = [
                [
                    "key" => "type",
                    "value" => $request["type"],
                    "compare" => "LIKE",
                ],
            ];
        }

        if (isset($request["name"])) {
            $args["meta_query"][] = [
                [
                    "key" => "name",
                    "value" => $request["name"],
                    "compare" => "LIKE",
                ],
            ];
        }

        if (isset($request["default"])) {
            $args["meta_query"][] = [
                [
                    "key" => "default",
                    "value" => 1,
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

            $type = get_post_meta($id, "type", true);
            $query_data["type"] = $type;

            if ($type == "bank") {
                $query_data["name"] = get_post_meta($id, "name", true);
                $query_data["details"] = get_post_meta($id, "details", true);
                $query_data["default"] = (bool) get_post_meta(
                    $id,
                    "default",
                    true
                );
            } elseif ($type == "paypal") {
                $query_data["account_type"] = get_post_meta(
                    $id,
                    "account_type",
                    true
                );
                $query_data["account_name"] = get_post_meta(
                    $id,
                    "account_name",
                    true
                );
                $query_data["account_email"] = get_post_meta(
                    $id,
                    "account_email",
                    true
                );
                $query_data["client_id"] = get_post_meta(
                    $id,
                    "client_id",
                    true
                );
                $query_data["secret_id"] = get_post_meta(
                    $id,
                    "secret_id",
                    true
                );
                $query_data["default"] = (bool) get_post_meta(
                    $id,
                    "default",
                    true
                );
            } elseif ($type == "stripe") {
                $query_data["account_name"] = get_post_meta(
                    $id,
                    "account_name",
                    true
                );
                $query_data["public_key"] = get_post_meta(
                    $id,
                    "public_key",
                    true
                );
                $query_data["secret_key"] = get_post_meta(
                    $id,
                    "secret_key",
                    true
                );
                $query_data["default"] = (bool) get_post_meta(
                    $id,
                    "default",
                    true
                );
            }

            $query_data["date"] = get_the_time(get_option("date_format"));
            $data[] = $query_data;
        }
        wp_reset_postdata();

        $data_from = isset($request["data_from"])
            ? sanitize_text_field($request["data_from"])
            : null;

        if ($data_from == "single_invoice") {
            $data = $this->formatArray($data, "type");
        }

        $result["result"] = $data;
        $result["total"] = $total_data;

        wp_send_json_success($result);
    }

    function formatArray($array, $key)
    {
        $custom_array = $new_array = [];
        foreach ($array as $v) {
            $custom_array[$v[$key]][] = $v;
        }

        foreach ($custom_array as $key => $value) {
            $temp_array = [];
            $method_name = "";
            switch ($key) {
                case "paypal":
                    $method_name = esc_html__("Paypal", "propovoice");
                    break;

                case "stripe":
                    $method_name = esc_html__("Stripe", "propovoice");
                    break;

                case "bank":
                    $method_name = __("Bank & Others", "propovoice");
                    break;

                default:
                    $method_name = ucfirst($key);
                    break;
            }

            $temp_array["method_name"] = $method_name;
            $temp_array["method_id"] = $key;
            $temp_array["list"] = $value;

            $new_array[] = $temp_array;
        }
        return $new_array;
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = $id;

        $query_data["type"] = get_post_meta($id, "type", true);
        $query_data["name"] = get_post_meta($id, "name", true);
        $query_data["details"] = get_post_meta($id, "details", true);
        $query_data["default"] = (bool) get_post_meta($id, "default", true);

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;
        //bank form
        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : null;
        $details = isset($param["details"])
            ? sanitize_textarea_field($param["details"])
            : null;
        $default = isset($param["default"])
            ? rest_sanitize_boolean($param["default"])
            : null;

        //paypal form
        $account_type = isset($param["account_type"])
            ? sanitize_text_field($param["account_type"])
            : null;
        $account_name = isset($param["account_name"])
            ? sanitize_text_field($param["account_name"])
            : null;
        $account_email = isset($param["account_email"])
            ? sanitize_email($param["account_email"])
            : null;
        $client_id = isset($param["client_id"])
            ? sanitize_text_field($param["client_id"])
            : null;
        $secret_id = isset($param["secret_id"])
            ? sanitize_text_field($param["secret_id"])
            : null;

        //stripe form
        $account_name = isset($param["account_name"])
            ? sanitize_text_field($param["account_name"])
            : null;
        $public_key = isset($param["public_key"])
            ? sanitize_text_field($param["public_key"])
            : null;
        $secret_key = isset($param["secret_key"])
            ? sanitize_text_field($param["secret_key"])
            : null;

        /* if (
            empty($name)
        ) {
            $reg_errors->add('field', esc_html__('Bank name is missing', 'propovoice'));
        } */

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_payment",
                "post_title" => $type,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());

                if ($type) {
                    update_post_meta($post_id, "type", $type);
                }

                if ($type == "bank") {
                    if ($name) {
                        update_post_meta($post_id, "name", $name);
                    }

                    if ($details) {
                        update_post_meta($post_id, "details", $details);
                    }
                } elseif ($type == "paypal") {
                    if ($account_type) {
                        update_post_meta(
                            $post_id,
                            "account_type",
                            $account_type
                        );
                    }

                    if ($account_name) {
                        update_post_meta(
                            $post_id,
                            "account_name",
                            $account_name
                        );
                    }

                    if ($account_email) {
                        update_post_meta(
                            $post_id,
                            "account_email",
                            $account_email
                        );
                    }

                    if ($client_id) {
                        update_post_meta($post_id, "client_id", $client_id);
                    }

                    if ($secret_id) {
                        update_post_meta($post_id, "secret_id", $secret_id);
                    }
                } elseif ($type == "stripe") {
                    if ($account_name) {
                        update_post_meta(
                            $post_id,
                            "account_name",
                            $account_name
                        );
                    }
                    if ($public_key) {
                        update_post_meta($post_id, "public_key", $public_key);
                    }

                    if ($secret_key) {
                        update_post_meta($post_id, "secret_key", $secret_key);
                    }
                }

                if ($default) {
                    update_post_meta($post_id, "default", true);
                } else {
                    update_post_meta($post_id, "default", false);
                }

                //TODO: when add new bank, but not for all payment
                $paymentData = [];
                $paymentData["id"] = $post_id;
                $paymentData["type"] = "bank";
                $paymentMeta = get_post_meta($post_id);
                $paymentData["name"] = isset($paymentMeta["name"])
                    ? $paymentMeta["name"][0]
                    : "";
                $paymentData["details"] = isset($paymentMeta["details"])
                    ? $paymentMeta["details"][0]
                    : "";

                wp_send_json_success($paymentData);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $type = isset($param["type"])
            ? sanitize_text_field($param["type"])
            : null;
        //bank form
        $name = isset($param["name"])
            ? sanitize_text_field($param["name"])
            : null;
        $details = isset($param["details"])
            ? sanitize_textarea_field($param["details"])
            : null;
        $default = isset($param["default"])
            ? rest_sanitize_boolean($param["default"])
            : null;

        //paypal form
        $account_type = isset($param["account_type"])
            ? sanitize_text_field($param["account_type"])
            : null;
        $account_name = isset($param["account_name"])
            ? sanitize_text_field($param["account_name"])
            : null;
        $account_email = isset($param["account_email"])
            ? sanitize_email($param["account_email"])
            : null;
        $client_id = isset($param["client_id"])
            ? sanitize_text_field($param["client_id"])
            : null;
        $secret_id = isset($param["secret_id"])
            ? sanitize_text_field($param["secret_id"])
            : null;

        //stripe form
        $account_name = isset($param["account_name"])
            ? sanitize_text_field($param["account_name"])
            : null;
        $public_key = isset($param["public_key"])
            ? sanitize_text_field($param["public_key"])
            : null;
        $secret_key = isset($param["secret_key"])
            ? sanitize_text_field($param["secret_key"])
            : null;

        /* if (empty($name)) {
            $reg_errors->add('field', esc_html__('Bank name is missing', 'propovoice'));
        } */

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => $type,
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($type) {
                    update_post_meta($post_id, "type", $type);
                }

                if ($type == "bank") {
                    if ($name) {
                        update_post_meta($post_id, "name", $name);
                    }

                    if ($details) {
                        update_post_meta($post_id, "details", $details);
                    }
                } elseif ($type == "paypal") {
                    if ($account_type) {
                        update_post_meta(
                            $post_id,
                            "account_type",
                            $account_type
                        );
                    }

                    if ($account_name) {
                        update_post_meta(
                            $post_id,
                            "account_name",
                            $account_name
                        );
                    }

                    if ($account_email) {
                        update_post_meta(
                            $post_id,
                            "account_email",
                            $account_email
                        );
                    }

                    if ($client_id) {
                        update_post_meta($post_id, "client_id", $client_id);
                    }

                    if ($secret_id) {
                        update_post_meta($post_id, "secret_id", $secret_id);
                    }
                } elseif ($type == "stripe") {
                    if ($account_name) {
                        update_post_meta(
                            $post_id,
                            "account_name",
                            $account_name
                        );
                    }

                    if ($public_key) {
                        update_post_meta($post_id, "public_key", $public_key);
                    }

                    if ($secret_key) {
                        update_post_meta($post_id, "secret_key", $secret_key);
                    }
                }

                if ($default) {
                    update_post_meta($post_id, "default", true);
                } else {
                    update_post_meta($post_id, "default", false);
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
        return current_user_can("ndpv_payment");
    }

    public function create_per()
    {
        return current_user_can("ndpv_payment");
    }

    public function update_per()
    {
        return current_user_can("ndpv_payment");
    }

    public function del_per()
    {
        return current_user_can("ndpv_payment");
    }
}
