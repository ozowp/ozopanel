<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Model\Contact;
use WAM\Model\Org;
use WAM\Model\Person;
use WAM\Model\Lead as LeadModel;
use WAM\Traits\Singleton;

class Lead
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/leads/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/leads" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"],
        ]);

        register_rest_route("ndpv/v1", "/leads", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/leads/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/leads/(?P<id>[0-9,]+)", [
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
            "post_type" => "ndpv_lead",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if ($s) {
            $contact_person = new Contact();
            $person_ids = $contact_person->query($s, "person");
            if ($person_ids) {
                $args["meta_query"][] = [
                    [
                        "key" => "person_id",
                        "value" => $person_ids,
                        "compare" => "IN",
                    ],
                ];
            }

            $org_ids = $contact_person->query($s, "org");
            if ($org_ids) {
                $args["meta_query"][] = [
                    [
                        "key" => "org_id",
                        "value" => $org_ids,
                        "compare" => "IN",
                    ],
                ];
            }
        }

        if (current_user_can("ndpv_staff")) {
            $post_ids = Fns::get_posts_ids_by_type("ndpv_lead");
            if (!empty($post_ids)) {
                $args["post__in"] = $post_ids;
                $args["orderby"] = "post__in";
            } else {
                $args["author"] = get_current_user_id();
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
            $query_data["budget"] = isset($queryMeta["budget"])
                ? $queryMeta["budget"][0]
                : "";
            $query_data["currency"] = isset($queryMeta["currency"])
                ? $queryMeta["currency"][0]
                : "";
            $query_data["desc"] = get_the_content();

            //custom field
            foreach (Fns::custom_field("lead") as $value) {
                if ($value['type'] == 'multi-select') {
                    $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                        ? maybe_unserialize($queryMeta[$value['slug']][0])
                        : "";
                } else {
                    $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                        ? $queryMeta[$value['slug']][0]
                        : "";
                }
            }

            $query_data["level_id"] = null;
            $level = get_the_terms($id, "ndpv_lead_level");
            if ($level) {
                $term_id = $level[0]->term_id;
                $query_data["level_id"] = [
                    "id" => $term_id,
                    "label" => $level[0]->name,
                    "color" => get_term_meta($term_id, "color", true),
                    "bg_color" => get_term_meta($term_id, "bg_color", true),
                ];
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

            $query_data["person"] = null;
            $person_id = get_post_meta($id, "person_id", true);
            if ($person_id) {
                $person = new Person();
                $query_data["person"] = $person->single($person_id);
            }

            $query_data["org"] = null;
            $org_id = get_post_meta($id, "org_id", true);
            if ($org_id) {
                $org = new Org();
                $query_data["org"] = $org->single($org_id);
            }

            $query_data["author"] = get_the_author();
            $query_data["date"] = get_the_time(get_option("date_format"));
            $data[] = $query_data;
        }
        wp_reset_postdata();

        $result["result"] = $data;
        $result["extra"] = [
            "custom_field" => Fns::custom_field("lead"),
        ];
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
        $query_data["budget"] = isset($queryMeta["budget"])
            ? $queryMeta["budget"][0]
            : "";
        $query_data["currency"] = isset($queryMeta["currency"])
            ? $queryMeta["currency"][0]
            : ""; 
        $query_data["desc"] = get_post_field("post_content", $id);

        //custom field
        foreach (Fns::custom_field("lead") as $value) {
            if ($value['type'] == 'multi-select') {
                $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                    ? maybe_unserialize($queryMeta[$value['slug']][0])
                    : "";
            } else {
                $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                    ? $queryMeta[$value['slug']][0]
                    : "";
            }
        }
        $query_data["custom_field"] = Fns::custom_field("lead");

        $query_data["level_id"] = "";
        $level = get_the_terms($id, "ndpv_lead_level");
        if ($level) {
            $term_id = $level[0]->term_id;
            $color = get_term_meta($term_id, "color", true);
            $bg_color = get_term_meta($term_id, "bg_color", true);
            $query_data["level_id"] = [
                "id" => $term_id,
                "label" => $level[0]->name,
                "color" => "#4a5568",
                "bg_color" => "#E2E8F0",
            ];

            if ($color) {
                $query_data["level_id"]["color"] = $color;
            }

            if ($bg_color) {
                $query_data["level_id"]["bg_color"] = $bg_color;
            }
        }

        $query_data["source_id"] = "";
        $source = get_the_terms($id, "ndpv_lead_source");
        if ($source) {
            $query_data["source_id"] = [
                "id" => $source[0]->term_id,
                "label" => $source[0]->name,
                "bg_color" => "",
                "color" => "#718096",
            ];
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

        $query_data["person"] = null;
        $person_id = isset($queryMeta["person_id"])
            ? $queryMeta["person_id"][0]
            : "";
        if ($person_id) {
            $person = new Person();
            $query_data["person"] = $person->single($person_id, true);
        }

        $query_data["org"] = null;
        $org_id = isset($queryMeta["org_id"]) ? $queryMeta["org_id"][0] : "";
        if ($org_id) {
            $org = new Org();
            $query_data["org"] = $org->single($org_id, true);
        }

        $query_data["date"] = get_the_time(get_option("date_format"));

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        //lead
        $first_name = isset($param["first_name"])
            ? sanitize_text_field($param["first_name"])
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;
        $person_id = isset($param["person_id"])
            ? absint($param["person_id"])
            : null;
        $org_id = isset($param["org_id"])
            ? absint($param["org_id"])
            : null;
        $level_id = isset($param["level_id"])
            ? absint($param["level_id"])
            : null;
        $budget = isset($param["budget"])
            ? sanitize_text_field($param["budget"])
            : null;
        $currency = isset($param["currency"])
            ? sanitize_text_field($param["currency"])
            : null;
        $tags = isset($param["tags"])
            ? array_map("absint", $param["tags"])
            : null;
        $desc = isset($param["desc"]) ? nl2br($param["desc"]) : "";

        $email   = isset($param['email']) ? strtolower(sanitize_email($param['email'])) : ''; 
        $mobile  = isset($param['mobile']) ? sanitize_text_field($param['mobile']) : '';

        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Contact info is missing", "propovoice")
            );
        }

        $lead = new LeadModel();
        $lead_id = $lead->is_lead_exists($email, $mobile);

        if ($lead_id) {
            $reg_errors->add(
                "lead_already_exist",
                esc_html__("Lead already exists!", "propovoice")
            );

            wp_send_json_error($reg_errors->get_error_messages());
        }

        $person = new Person();
        if ($person_id) {
            $person->update($param);
        }

        if (!$person_id && $first_name) {
            $person_id = $person->create($param);
        }

        $org = new Org();
        if (!$person_id && $org_id) {
            $org->update($param);
        }

        if (!$org_id && $org_name) {
            $org_id = $org->create($param);
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            //insert lead
            $data = [
                "post_type" => "ndpv_lead",
                "post_title" => "Lead",
                "post_content" => $desc,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());
                update_post_meta($post_id, "tab_id", $post_id); //for task, note, file

                if ($level_id) {
                    wp_set_post_terms($post_id, [$level_id], "ndpv_lead_level");
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
                    wp_set_post_terms($post_id, $tags, "ndpv_tag");
                }

                //custom field
                foreach (Fns::custom_field("lead") as $value) {
                    $field = '';
                    if ($value['type'] == 'multi-select') {
                        $field = isset($param[$value['slug']])
                            ? array_map("sanitize_text_field", $param[$value['slug']])
                            : "";
                    } else {
                        $field = isset($param[$value['slug']])
                            ? sanitize_text_field($param[$value['slug']])
                            : "";
                    }

                    if ($field) {
                        update_post_meta($post_id, $value["slug"], $field);
                    }
                }

                $param['id'] = $post_id;
                do_action("ndpvp/webhook", "lead_add", $param);

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

        //lead
        $first_name = isset($param["first_name"])
            ? sanitize_text_field($param["first_name"])
            : null;
        $org_name = isset($param["org_name"])
            ? sanitize_text_field($param["org_name"])
            : null;
        $person_id = isset($param["person_id"])
            ? absint($param["person_id"])
            : null;
        $org_id = isset($param["org_id"]) ? absint($param["org_id"]) : null;
        $level_id = isset($param["level_id"])
            ? absint($param["level_id"])
            : null;
        $budget = isset($param["budget"])
            ? sanitize_text_field($param["budget"])
            : null;
        $currency = isset($param["currency"])
            ? sanitize_text_field($param["currency"])
            : null;
        $tags = isset($param["tags"])
            ? array_map("absint", $param["tags"])
            : null;
        // $desc = isset($param["desc"]) ? nl2br($param["desc"]) : "";

        $desc = $post_content = get_post_field('post_content', $post_id);
        $post_content = get_post_field('post_content', $post_id);

        if (isset($param["desc"])) {
            if ($param["desc"] === $post_content) {
                $desc = $post_content;
            } else {
                $desc = nl2br($param["desc"]);
            }
        }


        if (empty($first_name) && empty($org_name)) {
            $reg_errors->add(
                "field",
                esc_html__("Contact info is missing", "propovoice")
            );
        }

        $person = new Person();
        if ($person_id) {
            $person->update($param);
        }

        if (!$person_id && $first_name) {
            $person_id = $person->create($param);
        }

        $org = new Org();
        if (!$person_id && $org_id) {
            $org->update($param);
        }

        if ($org_id && $org_name) {
            $org->update($param);
        }

        if (!$org_id && $org_name) {
            $org_id = $org->create($param);
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
                if ($level_id) {

                    $previous_lead_level = wp_get_post_terms($post_id, "ndpv_lead_level");
                    $level_ids = wp_set_post_terms($post_id, [$level_id], "ndpv_lead_level");

                    if (is_wp_error($level_ids)) {
                        throw new \Exception("There is an error to update lead level!!!");
                    }

                    if ($previous_lead_level["term_id"] != $level_id) {
                        do_action("ndpvp/webhook", "lead_level_change", $param);
                    }
                }

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

                wp_set_post_terms($post_id, $tags, "ndpv_tag");

                //custom field
                foreach (Fns::custom_field("lead") as $value) {
                    $field = '';
                    if ($value['type'] == 'multi-select') {
                        $field = isset($param[$value['slug']]) && is_array($param[$value['slug']]) ? array_map("sanitize_text_field", $param[$value['slug']])
                            : "";
                    } else {
                        $field = isset($param[$value['slug']])
                            ? sanitize_text_field($param[$value['slug']])
                            : "";
                    }

                    update_post_meta($post_id, $value['slug'], $field);
                }

                do_action("ndpvp/webhook", "lead_edit", $param);

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function delete($req)
    {
        //TODO: when delete lead delete task note file, if not exist in deal project
        $url_params = $req->get_url_params();
        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action("ndpvp/webhook", "lead_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_lead");
    }

    public function create_per()
    {
        return current_user_can("ndpv_lead");
    }

    public function update_per()
    {
        return current_user_can("ndpv_lead");
    }

    public function del_per()
    {
        return current_user_can("ndpv_lead");
    }
}
