<?php
namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Model\Contact;
use WAM\Model\Invoice;
use WAM\Model\Org;
use WAM\Model\Person;
use WAM\Traits\Singleton;

class Project
{
    use Singleton;
    
    public function register_routes()
    {

        register_rest_route("ndpv/v1", "/projects/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/projects" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/projects", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/projects/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/projects/(?P<id>[0-9,]+)", [
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

        $board_view = true;
        $module_id = isset($param["module_id"])
            ? absint($param["module_id"])
            : null;
        $table_view = isset($param["table_view"]) ? true : false;
        $project_req = isset($param["project_req"]) ? true : false;
        $dashboard = isset($param["dashboard"]) ? true : false;
        if ($module_id || $table_view) {
            $board_view = false;
        }

        $result = [];
        if ($board_view) {
            $get_status = Fns::get_terms("project_status");
            $column = [];
            foreach ($get_status as $status):
                $status_id = $status->term_id;
                $status_name = $status->name;
                $items = $this->project_query($param, $status_id);
                $column[] = [
                    "id" => $status_id,
                    "name" => $status_name,
                    "color" => get_term_meta($status_id, "color", true),
                    "bg_color" => get_term_meta($status_id, "bg_color", true),
                    "type" => get_term_meta($status_id, "type", true),
                    "items" => $items,
                ];
                $result["result"] = $column;
            endforeach;
        } else {
            $result = $this->project_query($param, null, $project_req, $dashboard);
        }

        $result['extra'] = [
            'custom_field' => Fns::custom_field('project'),
        ];

        wp_send_json_success($result);
    }

    public function project_query($param, $status_id = null, $project_req = false, $dashboard = false)
    {
        $per_page = 10;
        $offset = 0;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $module_id = isset($param["module_id"])
            ? absint($param["module_id"])
            : null;
        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;

        $args = [];

        if ( $dashboard ) {
            $per_page = 5;
        }

        if ( $project_req ) {
            $args = [
                "post_type" => "ndpv_deal",
                "post_status" => "publish",
                "posts_per_page" => $per_page,
                "offset" => $offset,
            ];
        } else {
            $args = [
                "post_type" => "ndpv_project",
                "post_status" => "publish",
                "posts_per_page" => $per_page,
                "offset" => $offset,
            ];

        }

        if ( $dashboard ) {

            $term_args = array(
                'hide_empty' => false, // also retrieve terms which are not used yet
                'meta_query' => array(
                    array(
                        'key'     => 'type',
                        'value'   => 'completed',
                        'compare' => 'LIKE'
                    )
                ),
                'taxonomy'  => 'ndpv_project_status',
            );
            $terms = get_terms( $term_args );
            $term_id = $terms[0]->term_id;

            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'ndpv_project_status',
                    'terms' => array($term_id),
                    'field' => 'id',
                    'operator' => 'NOT IN',
                ),
            );
        }

        if ($status_id) {
            $args["orderby"] = "menu_order";
            $args["order"] = "ASC";
        }

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if ( $project_req ) {
            if ( current_user_can("ndpv_client_role") ) {
                $user_id = get_current_user_id();
                $id = get_user_meta($user_id, 'ndpv_client_id', true);
                $args['meta_query'][] = [
                    [
                        'relation' => 'AND',
                        [
                            'relation' => 'AND', 
                            [
                                "key" => "project_req",
                                "compare" => "EXISTS",
                            ]
                        ],
                        [
                            'relation' => 'OR',
                            [
                                'key'   => 'person_id',
                                'value' => $id
                            ],
                            [
                                'key'   => 'org_id',
                                'value' => $id
                            ]
                        ]
                    ]
                ];
            }
        } else {
            if ( current_user_can("ndpv_client_role") ) {
                $user_id = get_current_user_id();
                $id = get_user_meta($user_id, 'ndpv_client_id', true);
                $args['meta_query'][] = array(
                    array(
                        'key'   => 'person_id',
                        'value' => $id
                    )
                );

                $args['meta_query'][] = array(
                    array(
                        'key'   => 'org_id',
                        'value' => $id
                    )
                );
            }
        }

        if ($status_id) {
            $args["tax_query"] = [
                [
                    "taxonomy" => "ndpv_project_status",
                    "terms" => $status_id,
                    "field" => "term_id",
                ],
            ];
        }

        if ($module_id) {
            $args["meta_query"][] = [
                [
                    "key" => "person_id",
                    "value" => $module_id,
                ],
            ];

            $args["meta_query"][] = [
                [
                    "key" => "org_id",
                    "value" => $module_id,
                ],
            ];
        }

        if ($s) {
            if ( current_user_can("ndpv_client_role") ) {
                $args["s"] = $s;
            } else {
                $args["meta_query"][] = [
                    [
                        "key" => "title",
                        "value" => $s,
                        "compare" => "LIKE",
                    ],
                ];

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
        }

        if ( current_user_can("ndpv_staff") ) {
            $post_ids = Fns::get_posts_ids_by_type('ndpv_project');
            if ( !empty($post_ids) ) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
        }

        $query = new \WP_Query($args);
        $total_data = null;
        if (!$status_id) {
            $total_data = $query->found_posts; //use this for pagination
        }
        $result = $data = [];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data["id"] = (string) $id; //Invariant failed: Draggable requires a [string] draggableId.

            $queryMeta = get_post_meta($id);
            $query_data['title'] = get_the_title($id);
            $query_data['budget'] = isset($queryMeta['budget']) ? $queryMeta['budget'][0] : '';
            $query_data['currency'] = isset($queryMeta['currency']) ? $queryMeta['currency'][0] : '';
            $query_data['start_date'] = isset($queryMeta['start_date']) ? $queryMeta['start_date'][0] : '';
            $query_data['due_date'] = isset($queryMeta['due_date']) ? $queryMeta['due_date'][0] : '';
            $query_data['desc'] = get_the_content();
            //custom field
            foreach( Fns::custom_field('project') as $value ) {
                if ( $value['type'] == 'multi-select') {
                    $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                    ? maybe_unserialize( $queryMeta[$value['slug']][0] )
                    : "";
                } else {
                    $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                    ? $queryMeta[$value['slug']][0]
                    : "";
                }
            }

            if (!$status_id) {
                $query_data["status_id"] = "";
                $status = get_the_terms($id, "ndpv_project_status");
                if ($status) {
                    $term_id = $status[0]->term_id;
                    $query_data["status_id"] = [
                        "id" => $term_id,
                        "label" => $status[0]->name,
                        "color" => get_term_meta($term_id, "color", true),
                        "bg_color" => get_term_meta($term_id, "bg_color", true),
                    ];
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

        if ($status_id) {
            return $data;
        } else {
            $result["result"] = $data;
            $result["total"] = $total_data;
            return $result;
        }
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
        $query_data["title"] = get_the_title($id);
        $query_data["budget"] =
            isset($queryMeta["budget"]) && $queryMeta["budget"][0]
                ? $queryMeta["budget"][0]
                : 0;
        $query_data["currency"] = isset($queryMeta["currency"])
            ? $queryMeta["currency"][0]
            : "";
        $query_data["start_date"] = isset($queryMeta["start_date"])
            ? $queryMeta["start_date"][0]
            : "";
        $query_data["due_date"] = isset($queryMeta["due_date"])
            ? $queryMeta["due_date"][0]
            : "";
        $query_data["desc"] = get_post_field("post_content", $id);

        //custom field
        foreach( Fns::custom_field('project') as $value ) {
            if ( $value['type'] == 'multi-select') {
                $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                ? maybe_unserialize( $queryMeta[$value['slug']][0] )
                : "";
            } else {
                $query_data[$value['slug']] = isset($queryMeta[$value['slug']])
                ? $queryMeta[$value['slug']][0]
                : "";
            }
        }
        $query_data['custom_field'] = Fns::custom_field('project');

        $invoice = new Invoice();
        $query_data["invoice"] = $invoice->project_invoice($id);

        $query_data["status_id"] = "";
        $status = get_the_terms($id, "ndpv_project_status");
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

        $project_id = isset($param["project_id"])
            ? absint($param["project_id"])
            : null;
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
        $title = isset($param["title"])
            ? sanitize_text_field($param["title"])
            : null;
        $status_id = isset($param["status_id"])
            ? absint($param["status_id"])
            : null;
        $budget = isset($param["budget"])
            ? sanitize_text_field($param["budget"])
            : null;
        $currency = isset($param["currency"])
            ? sanitize_text_field($param["currency"])
            : null;
        $start_date = isset($param["start_date"]) ? $param["start_date"] : null;
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;
        $tags = isset($param["tags"])
            ? array_map("absint", $param["tags"])
            : null;
        $desc = isset($param["desc"]) ? nl2br($param["desc"]) : "";

        $deal_id = isset($param["deal_id"]) ? absint($param["deal_id"]) : false;

        if (empty($title)) {
            $reg_errors->add(
                "field",
                esc_html__("Please title is required", "propovoice")
            );
        }

        if (empty($status_id)) {
            $reg_errors->add(
                "field",
                esc_html__("Please select a status", "propovoice")
            );
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

        if (!$org_id && $org_name) {
            $org_id = $org->create($param);
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [
                "post_type" => "ndpv_project",
                "post_title" => $title,
                "post_content" => $desc,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());
                $tab_id = $post_id;
                if ($project_id) {
                    $tab_id = $project_id;
                    update_post_meta($post_id, "project_id", $project_id);
                    update_post_meta($project_id, "project_id", $post_id);
                }
                update_post_meta($post_id, "tab_id", $tab_id); //for task, note, file

                if ($title) {
                    update_post_meta($post_id, "title", $title);
                }

                if ($status_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$status_id],
                        "ndpv_project_status"
                    );
                }

                if ($project_id) {
                    $get_lead_person = get_post_meta($id, "person_id", true);
                    if ($get_lead_person) {
                        $person_id = $get_lead_person;
                    }

                    $get_lead_org = get_post_meta($id, "org_id", true);
                    if ($get_lead_org) {
                        $org_id = $get_lead_org;
                    }
                }

                if ($person_id) {
                    update_post_meta($post_id, "person_id", $person_id);
                }

                if ($org_id) {
                    update_post_meta($post_id, "org_id", $org_id);
                }

                //is_client
                if ($person_id && $org_id) {
                    update_post_meta($person_id, "is_client", 1);
                }

                if ($person_id && !$org_id) {
                    update_post_meta($person_id, "is_client", 1);
                }

                if ($org_id && !$person_id) {
                    update_post_meta($org_id, "is_client", 1);
                }
                //end is_client

                if ($budget) {
                    update_post_meta($post_id, "budget", $budget);
                }

                if ($currency) {
                    update_post_meta($post_id, "currency", $currency);
                }

                if ($start_date) {
                    update_post_meta($post_id, "start_date", $start_date);
                }

                if ($due_date) {
                    update_post_meta($post_id, "due_date", $due_date);
                }

                if ($tags) {
                    wp_set_post_terms($post_id, $tags, "ndpv_tag");
                }

                if ( $deal_id ) {
                    $project_req = get_post_meta($deal_id, 'project_req', true);
                    if ( $project_req ) {
                        delete_post_meta($deal_id, "project_req");
                    }
                }

                //custom field
                foreach(Fns::custom_field('project') as $value) {
                    $field = '';
                    if ( $value['type'] == 'multi-select') {
                        $field = isset($param[$value['slug']])
                        ? array_map("sanitize_text_field", $param[$value['slug']])
                        : "";
                    } else {
                        $field = isset($param[$value['slug']])
                        ? sanitize_text_field($param[$value['slug']])
                        : "";
                    }
                    if ( $field ) {
                        update_post_meta($post_id, $value['slug'], $field);
                    }
                }

                /* if ( $project_id ) { //when move to project //TODO: think it
                    wp_delete_post( $project_id );
                } */

                do_action("ndpvp/webhook", "project_add", $param);

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
        $title = isset($param["title"])
            ? sanitize_text_field($param["title"])
            : null;
        $reorder = isset($param["reorder"])
            ? array_map("absint", $param["reorder"])
            : false;
        $status_id = isset($param["status_id"])
            ? absint($param["status_id"])
            : null;
        $budget = isset($param["budget"])
            ? sanitize_text_field($param["budget"])
            : null;
        $currency = isset($param["currency"])
            ? sanitize_text_field($param["currency"])
            : null;
        $start_date = isset($param["start_date"]) ? $param["start_date"] : null;
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;
        $tags = isset($param["tags"])
            ? array_map("absint", $param["tags"])
            : null;
        $desc = isset($param["desc"]) ? nl2br($param["desc"]) : "";
        $change_tax = isset($param["change_tax"]) ? true : false;

        if (
            !$reorder &&
            !$change_tax &&
            (empty($first_name) && empty($org_name))
        ) {
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
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_author" => get_current_user_id(),
            ];

            if ($title) {
                $data["post_title"] = $title;
            }

            if ($desc) {
                $data["post_content"] = $desc;
            }

            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($status_id) {
                    wp_set_post_terms(
                        $post_id,
                        [$status_id],
                        "ndpv_project_status"
                    );
                }

                if ($reorder) {
                    $this->reorder_posts($reorder);
                }

                if ($person_id) {
                    update_post_meta($post_id, "person_id", $person_id);
                }

                if ($org_id && !$org_name) {
                    update_post_meta($post_id, "org_id", null);
                } else if ($org_id) {
                    update_post_meta($post_id, "org_id", $org_id);
                }

                update_post_meta($post_id, 'budget', $budget);
                update_post_meta($post_id, 'currency', $currency);
                update_post_meta($post_id, 'start_date', $start_date);
                update_post_meta($post_id, 'due_date', $due_date);
                wp_set_post_terms($post_id, $tags, "ndpv_tag");

                //custom field
                foreach( Fns::custom_field('project') as $value ) {
                    $field = '';
                    if ( $value['type'] == 'multi-select') {
                        $field = isset($param[$value['slug']])
                        ? array_map("sanitize_text_field", $param[$value['slug']])
                        : "";
                    } else {
                        $field = isset($param[$value['slug']])
                        ? sanitize_text_field($param[$value['slug']])
                        : "";
                    }
                    update_post_meta($post_id, $value['slug'], $field);
                }

                do_action('ndpvp/webhook', 'project_edit', $param);

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function reorder_posts($order = [])
    {
        global $wpdb;
        $list = join(", ", $order);
        $wpdb->query("SELECT @i:=-1");
        $result = $wpdb->query(
            "UPDATE wp_posts SET menu_order = ( @i:= @i+1 )
            WHERE ID IN ( $list ) ORDER BY FIELD( ID, $list );"
        );
        return $result;
    }

    public function delete($req)
    {
        $url_params = $req->get_url_params();

        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action("ndpvp/webhook", "project_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_project");
    }

    public function create_per()
    {
        return current_user_can("ndpv_project");
    }

    public function update_per()
    {
        return current_user_can("ndpv_project");
    }

    public function del_per()
    {
        return current_user_can("ndpv_project");
    }
}
