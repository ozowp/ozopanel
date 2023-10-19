<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Traits\Singleton;

class Task
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/tasks/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/tasks" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/tasks", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/tasks/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/tasks/(?P<id>[0-9,]+)", [
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

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        $tab_id = isset($param["tab_id"]) ? absint($req["tab_id"]) : false;
        $dashboard = isset($param["dashboard"])
            ? sanitize_text_field($req["dashboard"])
            : false;
        $status_id = isset($param["status_id"])
            ? absint($req["status_id"])
            : false;

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        if ($dashboard) {
            $per_page = 3;
        }

        $args = [
            "post_type" => "ndpv_task",
            "post_status" => "publish",
            "posts_per_page" => -1,
        ];

        if (!$tab_id) {
            $args["posts_per_page"] = $per_page;
            $args["offset"] = $offset;
        }

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if ($tab_id) {
            $args["meta_query"][] = [
                [
                    "key" => "tab_id",
                    "value" => $tab_id,
                    "compare" => "=",
                ],
            ];
        }

        if (!$status_id) {
            $taxonomy = "task_status";
            $get_taxonomy = Fns::get_terms($taxonomy);
            $status_id = $get_taxonomy[0]->term_id;
        }

        if ($dashboard) {
            $tax_args = array(
                'hide_empty' => false, // also retrieve terms which are not used yet
                'meta_query' => array(
                    array(
                        'key'      => 'type',
                        'value'    => 'done',
                        'compare'  => 'LIKE'
                    )
                ),
                'taxonomy'  => 'ndpv_task_status',
            );
            $terms = get_terms($tax_args);
            $status_id = $terms[0]->term_id;

            $args["tax_query"] = [
                [
                    "taxonomy" => "ndpv_task_status",
                    "terms" => $status_id,
                    "field" => "term_id",
                    'operator'  => 'NOT IN'
                ],
            ];
        } else {
            $args["tax_query"] = [
                [
                    "taxonomy" => "ndpv_task_status",
                    "terms" => $status_id,
                    "field" => "term_id",
                ],
            ];
        }

        if (current_user_can("ndpv_staff")) {

            $post_ids = Fns::get_posts_ids_by_type('ndpv_task');
            if (!empty($post_ids)) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
        }

        $query = new \WP_Query($args);
        $total_data = $query->found_posts; //use this for pagination
        $result = [];

        $data = [
            "task_status" => [],
            "latest" => [],
            "today" => [],
            "other" => [],
            "unschedule" => [],
        ];

        $taxonomy = "task_status";
        $get_taxonomy = Fns::get_terms($taxonomy);

        $format_taxonomy = [];
        foreach ($get_taxonomy as $single) {
            $format_taxonomy[] = [
                "id" => $single->term_id,
                "label" => $single->name,
            ];
        }
        $data[$taxonomy] = $format_taxonomy;

        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data["id"] = $id;
            $query_data["title"] = html_entity_decode( get_the_title() );

            $query_data["status_id"] = "";
            $status = get_the_terms($id, "ndpv_task_status");
            if ($status) {
                $term_id = $status[0]->term_id;
                $query_data["status_id"] = [
                    "id" => $term_id,
                    "label" => $status[0]->name,
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

            $query_data["type_id"] = "";
            $type = get_the_terms($id, "ndpv_task_type");
            if ($type) {
                $icon_id = get_term_meta($type[0]->term_id, "icon", true);
                $iconData = null;
                if ($icon_id) {
                    $icon_src = wp_get_attachment_image_src(
                        $icon_id,
                        "thumbnail"
                    );
                    if ($icon_src) {
                        $iconData = [];
                        $iconData["id"] = $icon_id;
                        $iconData["src"] = $icon_src[0];
                    }
                }
                $query_data["type_id"] = [
                    "id" => $type[0]->term_id,
                    "label" => $type[0]->name,
                    "icon" => $iconData ? $iconData : "",
                    "color" => "#4a5568",
                    "bg_color" => "#E2E8F0",
                ];
            }

            $query_data["priority_id"] = "";
            $priority = get_the_terms($id, "ndpv_task_priority");
            if ($priority) {
                $term_id = $priority[0]->term_id;
                $query_data["priority_id"] = [
                    "id" => $term_id,
                    "label" => $priority[0]->name,
                    "color" => "#4a5568",
                    "bg_color" => "#E2E8F0",
                ];

                $color = get_term_meta($term_id, "color", true);
                $bg_color = get_term_meta($term_id, "bg_color", true);

                if ($color) {
                    $query_data["priority_id"]["color"] = $color;
                }

                if ($bg_color) {
                    $query_data["priority_id"]["bg_color"] = $bg_color;
                }
            }

            $query_data["desc"] = get_the_content();
            $query_data["note"] = get_post_meta($id, "note", true);
            $query_data["google_meet"] = get_post_meta(
                $id,
                "google_meet",
                true
            );
            $query_data["start_date"] = get_post_meta($id, "start_date", true);
            $query_data["due_date"] = get_post_meta($id, "due_date", true);
            $query_data["checklist"] = get_post_meta($id, "checklist", true);
            $query_data["author"] = get_the_author(); 
            $query_data["date"] = get_the_time(get_option("date_format"));

            $query_data["tab_title"] = '';
            $query_data["tab_url"] = '';
            $tab_id = get_post_meta($id, "tab_id", true);
            if ( $tab_id ) {
                $name = '';
                $post_type = get_post_type( $tab_id );
                $person_id = get_post_meta($tab_id, "person_id", true);
                $org_id = get_post_meta($tab_id, "org_id", true);
                if ( $person_id ) {
                    $name = get_post_meta($person_id, "first_name", true);
                } else if ( $org_id ) {
                    $name = get_post_meta($person_id, "org_name", true);
                }
                switch ($post_type) {
                    case 'ndpv_lead':
                        $query_data["tab_title"] = esc_html__('Lead: ', 'propovoice') . $name;
                        $query_data["tab_url"] = 'lead/' . $tab_id;
                        break;

                    case 'ndpv_deal':
                        $name = get_the_title($tab_id);
                        $query_data["tab_title"] = esc_html__('Deal: ', 'propovoice') . $name;
                        $query_data["tab_url"] = 'deal/' . $tab_id;
                        break;

                    case 'ndpv_person':
                        $name = get_post_meta($tab_id, "first_name", true);
                        $query_data["tab_title"] = esc_html__('Contact: ', 'propovoice') . $name;
                        $query_data["tab_url"] = 'contact/' . $tab_id;
                        break;
                        
                    case 'ndpv_org':
                        $name = get_post_meta($tab_id, "name", true);
                        $query_data["tab_title"] = esc_html__('Contact: ', 'propovoice') . $name;
                        $query_data["tab_url"] = 'contact/' . $tab_id;
                        break;
                    
                    case 'ndpv_project':
                        $name = get_the_title($tab_id);
                        $query_data["tab_title"] = esc_html__('Project: ', 'propovoice') . $name;
                        $query_data["tab_url"] = 'project/' . $tab_id;
                        break;
                    
                    default:
                        # code...
                        break;
                }
            } 

            if ($dashboard) {
                $data["latest"][] = $query_data;
            } else {
                $start_date = get_post_meta($id, "start_date", true);
                if ($start_date) {
                    $current_date = date("Y-m-d", current_time("timestamp"));
                    $format_start_date = date("Y-m-d", strtotime($start_date));

                    if ($current_date == $format_start_date) {
                        $data["today"][] = $query_data;
                    } else {
                        $data["other"][] = $query_data;
                    }
                } else {
                    $data["unschedule"][] = $query_data;
                }
            }
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

        $query_data["title"] = get_the_title();
        $query_data["desc"] = get_the_content();

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $tab_id = isset($param["tab_id"]) ? absint($req["tab_id"]) : null;
        $title = isset($param["title"])
            ? sanitize_text_field($req["title"])
            : null;
        $status_id = isset($param["status_id"])
            ? absint($param["status_id"])
            : null;
        $type_id = isset($param["type_id"]) ? absint($param["type_id"]) : null;
        $priority_id = isset($param["priority_id"])
            ? absint($param["priority_id"])
            : null;

        $google_meet = isset($param["google_meet"])
            ? $param["google_meet"]
            : null;
        $start_date = isset($param["start_date"]) ? $param["start_date"] : null;
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;

        if (empty($title)) {
            $reg_errors->add(
                "field",
                esc_html__("Title field is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_task",
                "post_title" => $title,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());

                if ($tab_id) {
                    update_post_meta($post_id, "tab_id", $tab_id);
                }

                if ($status_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$status_id],
                        "ndpv_task_status"
                    );
                }

                if ($type_id) {
                    wp_set_post_terms($post_id, [$type_id], "ndpv_task_type");
                }

                if ($priority_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$priority_id],
                        "ndpv_task_priority"
                    );
                }

                if ($google_meet) {
                    wp_set_post_terms($post_id, "google_meet", $google_meet);
                }
                if ($start_date) {
                    update_post_meta($post_id, "start_date", $start_date);
                }

                if ($due_date) {
                    update_post_meta($post_id, "due_date", $due_date);
                }
                $param['id'] = $post_id;
                do_action("ndpvp/webhook", "task_add", $param);

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

        $status_id = isset($param["status_id"])
            ? absint($param["status_id"])
            : null;
        $type_id = isset($param["type_id"]) ? absint($param["type_id"]) : null;
        $priority_id = isset($param["priority_id"])
            ? absint($param["priority_id"])
            : null;

        $google_meet = isset($param["google_meet"])
            ? $param["google_meet"]
            : null;
        $note = isset($param["note"]) ? nl2br($param["note"]) : "";
        $start_date = isset($param["start_date"]) ? $param["start_date"] : '';
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;
        $checklist = isset($param["checklist"]) ? $param["checklist"] : null;

        /* if (empty($title)) {
            $reg_errors->add('field', esc_html__('Title field is missing', 'propovoice'));
        } */

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_author" => get_current_user_id(),
            ];

            if (isset($param["title"])) {
                $data["post_title"] = sanitize_text_field($req["title"]);
            }

            if (isset($param["desc"])) {
                $data["post_content"] = sanitize_text_field($req["desc"]);
            }

            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($status_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$status_id],
                        "ndpv_task_status"
                    );
                }

                if ($type_id) {
                    wp_set_post_terms($post_id, [$type_id], "ndpv_task_type");
                }

                if ($priority_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$priority_id],
                        "ndpv_task_priority"
                    );
                }

                if ($google_meet) {
                    update_post_meta($post_id, "google_meet", $google_meet);
                }

                update_post_meta($post_id, "note", $note);

                if ($start_date) {
                    update_post_meta($post_id, "start_date", $start_date);
                }

                if ($due_date) {
                    update_post_meta($post_id, "due_date", $due_date);
                }

                if ($checklist) {
                    update_post_meta($post_id, "checklist", $checklist);
                }

                do_action("ndpvp/webhook", "task_edit", $param);

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

        do_action("ndpvp/webhook", "task_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_task");
    }

    public function create_per()
    {
        return current_user_can("ndpv_task");
    }

    public function update_per()
    {
        return current_user_can("ndpv_task");
    }

    public function del_per()
    {
        return current_user_can("ndpv_task");
    }
}
