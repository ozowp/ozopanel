<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Model\Client as ModelClient;
use WAM\Model\Org;
use WAM\Model\Person;
use WAM\Traits\Singleton;

class Client
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/clients/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/clients" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/clients", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/clients/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/clients/(?P<id>[0-9,]+)", [
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
        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => ["ndpv_person", "ndpv_org"],
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if ($s) {
            $args["meta_query"][] = [
                [
                    "key" => "first_name",
                    "value" => $s,
                    "compare" => "Like",
                ],
            ];
            $args["meta_query"][] = [
                [
                    "key" => "email",
                    "value" => $s,
                    "compare" => "Like",
                ],
            ];
        }

        $args["meta_query"][] = [
            [
                "key" => "is_client",
                "value" => 1,
                "compare" => "=",
            ],
        ];

        if (current_user_can("ndpv_staff")) {
            $post_ids = Fns::get_posts_ids_by_type(["ndpv_person", "ndpv_org"]);
            if (!empty($post_ids)) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
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
            $type = get_post_type($id) == "ndpv_person" ? "person" : "org";
            $query_data["type"] = $type;
            $query_data["first_name"] = isset($queryMeta["first_name"])
                ? $queryMeta["first_name"][0]
                : "";   
            $query_data["org_name"] = isset($queryMeta["name"])
                ? $queryMeta["name"][0]
                : "";            
            $query_data["email"] = isset($queryMeta["email"])
                ? $queryMeta["email"][0]
                : "";
            $query_data["web"] = isset($queryMeta["web"])
                ? $queryMeta["web"][0]
                : "";
            $query_data["mobile"] = isset($queryMeta["mobile"])
                ? $queryMeta["mobile"][0]
                : "";
            $query_data["country"] = isset($queryMeta["country"])
                ? $queryMeta["country"][0]
                : "";
            $query_data["region"] = isset($queryMeta["region"])
                ? $queryMeta["region"][0]
                : "";
            $query_data["address"] = isset($queryMeta["address"])
                ? $queryMeta["address"][0]
                : "";
            $query_data["img"] = isset($queryMeta["img"])
                ? $queryMeta["img"][0]
                : "";

            $query_data["org"] = null;
            $org_id = get_post_meta($id, "org_id", true);
            if ($org_id) {
                $org = new Org();
                $query_data["org"] = $org->single($org_id);
            }

            $query_data["client_portal"] = isset($queryMeta["client_portal"])
                ? $queryMeta["client_portal"][0]
                : false;

            $img_id = $query_data["img"];
            $imgData = null;
            if ($img_id) {
                $img_src = wp_get_attachment_image_src($img_id, "thumbnail");
                if ($img_src) {
                    $imgData = [];
                    $imgData["id"] = $img_id;
                    $imgData["src"] = $img_src[0];
                }
            }
            $query_data["img"] = $imgData;

            $query_data["author"] = get_the_author();
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
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset($param["first_name"])
            ? sanitize_text_field($param["first_name"])
            : '';
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : '';

        $email = isset($param["email"])
            ? strtolower(sanitize_email($req["email"]))
            : '';

        $mobile = isset($param["mobile"])
            ? sanitize_text_field($param["mobile"])
            : '';

        $person_id = isset($param["person_id"])
            ? absint($param["person_id"])
            : null;
        $org_id = isset($param["org_id"]) ? absint($param["org_id"]) : null;

        $client_portal = isset($param["client_portal"])
            ? rest_sanitize_boolean($param["client_portal"])
            : false;

        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "contact_field",
                esc_html__("Contact info is missing", "propovoice")
            );
        }

        $client_id = $this->is_client_exists($email, $mobile);
        if ($client_id) {
            $reg_errors->add(
                "already_exist",
                esc_html__("Client already exists! Email or Mobile should be unique", "propovoice")
            );

            wp_send_json_error($reg_errors->get_error_messages());
        }

        //check if team exist
        $user_id = email_exists($email);
        if ($user_id) {
            $user_data = new \WP_User($user_id);
            $user_roles = $user_data->roles;
            $check_roles = array('administrator', 'ndpv_admin', 'ndpv_manager', 'ndpv_staff');
            $role_exist = false;
            foreach ($check_roles as $role) {
                if (in_array($role, $user_roles)) {
                    $role_exist = true;
                }
            }
            if ($role_exist) {
                $reg_errors->add(
                    "already_exist",
                    esc_html__("You can not add a Team member as client", "propovoice")
                );
            }
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $person = new Person();
            if ($person_id) {
                $param["is_client"] = true;
                $person->update($param);
            }

            if (!$person_id && $first_name) {
                $param["is_client"] = true;
                $person_id = $person->create($param);
            }

            $org = new Org();
            if (!$person_id && $org_id) {
                $org->update($param);
            }

            if ($org_id) {
                $org->update($param);
            }

            if (!$org_id && $org_name) {
                if ( $person_id ) {
                    $param["person_id"] = $person_id;
                }
                $org_id = $org->create($param);
            }

            $post_id = ($person_id) ? $person_id : $org_id;

            if ($org_id) {
                update_post_meta($post_id, "org_id", $org_id);
            }

            $client_model = new ModelClient();
            $name = ($person_id) ? $first_name : $org_name;
            $client_model->set_user_if_not($post_id, $name, $email, $client_portal);
            update_post_meta($post_id, "client_portal", $client_portal);

            wp_send_json_success();
        }
    }

    public function is_client_exists($email, $mobile)
    {
        $args = array(
            "post_type" => ["ndpv_person", "ndpv_org"],
            "post_status" => "publish",
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'email',
                        'value'   => $email,
                        'compare' => '=',
                    )
                ),
                array(
                    'key'     => 'is_client',
                    'value'   => 1,
                    'compare' => '=',
                ),
            ),
            'fields' => 'ids',
            'posts_per_page' => 1,
        );

        if ( $mobile ) {
            $args['meta_query'][] = [
                'key'     => 'mobile',
                'value'   => $mobile,
                'compare' => '='
            ];
        }

        $posts = get_posts($args);

        if ($posts) {
            $post_id = $posts[0];
            return $post_id;
        } else {
            return false;
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset($param["first_name"])
            ? sanitize_text_field($req["first_name"])
            : null; 
        $email = isset($param["email"])
            ? strtolower(sanitize_email($req["email"]))
            : null;
        $org_id = isset($param["org_id"]) ? absint($param["org_id"]) : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($req["org_name"])
            : null;
        $web = isset($param["web"]) ? esc_url_raw($req["web"]) : null;
        $mobile = isset($param["mobile"])
            ? sanitize_text_field($req["mobile"])
            : null;
        $country = isset($param["country"])
            ? sanitize_text_field($req["country"])
            : null;
        $region = isset($param["region"])
            ? sanitize_text_field($req["region"])
            : null;
        $address = isset($param["address"])
            ? sanitize_text_field($req["address"])
            : null;
        $img = isset($param["img"]) && isset($param["img"]["id"])
            ? absint($param["img"]["id"])
            : null;

        $client_portal = isset($param["client_portal"])
            ? rest_sanitize_boolean($param["client_portal"])
            : false;

        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "contact_field",
                esc_html__("Contact info is missing", "propovoice")
            );
        }

        if (!is_email($email)) {
            $reg_errors->add(
                "email_invalid",
                esc_html__("Email id is not valid!", "propovoice")
            );
        }

        //check if team exist
        $user_id = email_exists($email);
        if ($user_id) {
            $user_data = new \WP_User($user_id);
            $user_roles = $user_data->roles;
            $check_roles = array('administrator', 'ndpv_admin', 'ndpv_manager', 'ndpv_staff');
            $role_exist = false;
            foreach ($check_roles as $role) {
                if (in_array($role, $user_roles)) {
                    $role_exist = true;
                }
            }
            if ($role_exist) {
                $reg_errors->add(
                    "already_exist",
                    esc_html__("You can not add a Team member as client", "propovoice")
                );
            }
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => $first_name,
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($first_name) {
                    update_post_meta($post_id, "first_name", $first_name);
                } 

                if ($email) {
                    update_post_meta($post_id, "email", $email);
                }

                update_post_meta($post_id, "org_name", $org_name);
                update_post_meta($post_id, "web", $web);
                update_post_meta($post_id, "mobile", $mobile);
                update_post_meta($post_id, "country", $country);
                update_post_meta($post_id, "region", $region);
                update_post_meta($post_id, "address", $address);

                $org = new Org();
                if (!$org_id && $org_name) {
                    $org_id = $org->create([
                        "org_name" => $org_name,
                        "person_id" => $post_id,
                    ]);
                } else if ($org_id && $org_name) {
                    $org->update([
                        "org_id" => $org_id,
                        "org_name" => $org_name
                    ]);
                }

                if ($org_id && !$org_name) {
                    update_post_meta($post_id, "org_id", null);
                } else if ($org_id) {
                    update_post_meta($post_id, "org_id", $org_id);
                }

                if ( isset($param['img']) ) { 
                    if ($img) {
                        update_post_meta($post_id, "img", $img);
                    } else {
                        delete_post_meta($post_id, "img");
                    }
                }

                $client_model = new ModelClient();
                $client_model->set_user_if_not($post_id, $first_name, $email, $client_portal);
                update_post_meta($post_id, "client_portal", $client_portal);

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

        $client_model = new ModelClient(); 
            
        foreach ($ids as $id) {
            delete_post_meta($id, 'is_client');
            $client_model->delete_client($id);
        }
        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_client");
    }

    public function create_per()
    {
        return current_user_can("ndpv_client");
    }

    public function update_per()
    {
        return current_user_can("ndpv_client");
    }

    public function del_per()
    {
        return current_user_can("ndpv_client");
    }
}
