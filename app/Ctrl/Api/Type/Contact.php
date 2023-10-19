<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Model\Deal;
use WAM\Model\Org;
use WAM\Model\Person;
use WAM\Model\Project;
use WAM\Traits\Singleton;

class Contact
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/contacts/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/contacts" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/contacts", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/contacts/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/contacts/(?P<id>[0-9,]+)", [
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

        $s = isset($param["s"]) ? sanitize_text_field($param["s"]) : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        //TODO: tem load 5 person and org in estvoice
        $per_page = 5;

        $result = $data = [];

        $contact_types = ["person", "org"];

        foreach ($contact_types as $contact_type) {
            $args = [
                "post_type" => "ndpv_" . $contact_type,
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
                        "compare" => "LIKE",
                    ],
                ];

                $args["meta_query"][] = [
                    [
                        "key" => "name",
                        "value" => $s,
                        "compare" => "LIKE",
                    ],
                ];
            }

            $query = new \WP_Query($args);

            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();

                $query_data = [];
                $query_data["id"] = $id;
                $query_data["type"] = $contact_type;

                if ($contact_type == "org") {
                    $query_data["org_name"] = get_post_meta($id, "name", true);
                } else {
                    $query_data["first_name"] = get_post_meta(
                        $id,
                        "first_name",
                        true
                    );
                }
                $query_data["email"] = get_post_meta($id, "email", true);
                $query_data["web"] = get_post_meta($id, "web", true);
                $query_data["mobile"] = get_post_meta($id, "mobile", true);
                $query_data["country"] = get_post_meta($id, "country", true);
                $query_data["region"] = get_post_meta($id, "region", true);
                $query_data["address"] = get_post_meta($id, "address", true);
                $query_data["city"] = get_post_meta($id, "city", true);
                $query_data["zip"] = get_post_meta($id, "zip", true);

                $img_id = get_post_meta($id, "img", true);
                $imgData = null;
                if ($img_id) {
                    $img_src = wp_get_attachment_image_src(
                        $img_id,
                        "thumbnail"
                    );
                    if ($img_src) {
                        $imgData = [];
                        $imgData["id"] = $img_id;
                        $imgData["src"] = $img_src[0];
                    }
                }
                $query_data["img"] = $imgData;

                $query_data["date"] = get_the_time(get_option("date_format"));
                $data[] = $query_data;
            }
            wp_reset_postdata();
        }

        $result["result"] = $data;
        wp_send_json_success($result);
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = absint($id);

        $queryMeta = get_post_meta($id);
        $type = get_post_type($id) == "ndpv_person" ? "person" : "org";
        $query_data["ws_id"] = isset($queryMeta["ws_id"])
            ? $queryMeta["ws_id"][0]
            : "";
        $query_data["tab_id"] = $id;

        $project = new Project();
        $query_data["project"] = $project->total($id);

        $deal = new Deal();
        $query_data["deal"] = $deal->total($id);

        $query_data["person"] = null;
        if ($type == "person") {
            $person = new Person();
            $query_data["person"] = $person->single($id, true);
        }

        $query_data["org"] = null;
        if ($type == "org") {
            $org = new Org();
            $query_data["org"] = $org->single($id, true);
        } else {
            $org_id = isset($queryMeta["org_id"])
                ? $queryMeta["org_id"][0]
                : "";
            if ($org_id) {
                $org = new Org();
                $query_data["org"] = $org->single($org_id, true);
            }
        }

        $query_data["status_id"] = "";
        $status = get_the_terms($id, "ndpv_contact_status");

        //if not set any term, show active
        if (!$status) {
            $status[] = get_term_by("slug", "active", "ndpv_contact_status");
        }

        if ($status) {
            $term_id = $status[0]->term_id ?? null;
            $query_data["status_id"] = [
                "id" => $term_id,
                "label" => $status[0]->name ?? null,
                "color" => "#4a5568",
                "bg_color" => "#E2E8F0",
                "type" => get_term_meta($term_id, "type", true),
            ];

            $color = get_term_meta($term_id, "color", true);
            $bg_color = get_term_meta($term_id, "bg_color", true);

            if ($color) {
                $query_data["status_id"]["color"] = $color;
            }

            if ($bg_color) {
                $query_data["status_id"]["bg_color"] = $bg_color;
            }
        }

        $query_data["tags"] = [];
        $tags = get_the_terms($id, "ndpv_tag");
        if ($tags) {
            $tagList = [];
            foreach ($tags as $tag) {
                $tagList[] = [
                    "id" => $tag->term_id,
                    "label" => $tag->name,
                ];
            }
            $query_data["tags"] = $tagList;
        }

        $query_data["date"] = get_the_time(get_option("date_format"));

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();

        $first_name = isset($param["first_name"])
            ? sanitize_text_field($param["first_name"])
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;

        $person_id = null;
        $org_id = null;

        $person = new Person();
        $org = new Org();

        if ($first_name) {
            $person_id = $person->create($param);
        }

        if ($org_name) {
            $param = $person_id ? ["org_name" => $org_name] : $param;
            $org_id = $org->create($param);
        }

        if ($org_id) {
            $person->update(["org_id" => $org_id]);
        }

        if ($person_id) {
            $org->update(["person_id" => $person_id]);
        }

        if ($person_id || $org_id) {
            $post_id = $person_id ? $person_id : $org_id;
            $type = $person_id ? "person" : "org";
            wp_send_json_success([
                "id" => $post_id,
                "type" => $type,
            ]);
        } else {
            wp_send_json_error();
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset($param["first_name"])
            ? sanitize_text_field($param["first_name"])
            : null;
        $email = isset($param["email"])
            ? strtolower(sanitize_email($param["email"]))
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;
        $web = isset($param["web"]) ? esc_url_raw($param["web"]) : null;
        $mobile = isset($param["mobile"])
            ? sanitize_text_field($param["mobile"])
            : null;
        $country = isset($param["country"])
            ? sanitize_text_field($param["country"])
            : "";
        $region = isset($param["region"])
            ? sanitize_text_field($param["region"])
            : "";
        $address = isset($param["address"])
            ? sanitize_text_field($param["address"])
            : '';
        $city = isset($param["city"])
            ? sanitize_text_field($param["city"])
            : '';
        $zip = isset($param["zip"])
            ? sanitize_text_field($param["zip"])
            : '';
        $img =
            isset($param["img"]) && isset($param["img"]["id"])
                ? absint($param["img"]["id"])
                : null;

        if (empty($first_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Name field is missing", "propovoice")
            );
        }

        if (!is_email($email)) {
            $reg_errors->add(
                "email_invalid",
                esc_html__("Email id is not valid!", "propovoice")
            );
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
                 
                update_post_meta($post_id, "org_name", $org_name);
                
                if ( isset($param['web']) ) {
                    update_post_meta($post_id, 'web', $web);
                }

                if ( isset($param['email']) ) {
                    update_post_meta($post_id, 'email', $email);
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

                if ($img) {
                    update_post_meta($post_id, "img", $img);
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
        return current_user_can("ndpv_contact");
    }

    public function create_per()
    {
        return current_user_can("ndpv_contact");
    }

    public function update_per()
    {
        return current_user_can("ndpv_contact");
    }

    public function del_per()
    {
        return current_user_can("ndpv_contact");
    }
}
