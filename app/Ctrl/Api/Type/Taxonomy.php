<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Traits\Singleton;

class Taxonomy
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/taxonomies/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/taxonomies" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/taxonomies", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/taxonomies/(?P<id>\d+)", [
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

        register_rest_route(
            "ndpv/v1",
            "/taxonomies/(?P<id>[0-9,]+)/(?P<tax>[a-z,_]+)",
            [
                "methods" => "DELETE",
                "callback" => [$this, "delete"],
                "permission_callback" => [$this, "del_per"],
                "args" => [
                    "id" => [
                        "sanitize_callback" => "sanitize_text_field",
                    ],
                    "tax" => [
                        "sanitize_callback" => "sanitize_text_field",
                    ],
                ],
            ]
        );
    }

    public function get($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $taxonomies = isset($param["taxonomy"])
            ? sanitize_text_field($param["taxonomy"])
            : null;
        $label_only = isset($param["label_only"]) ? true : false;
        $hide_bg = isset($param["hide_bg"]) ? true : false;
        $extra_amount_type = isset($param["extra_amount_type"])
            ? sanitize_text_field($param["extra_amount_type"])
            : null;
        $id = isset($param["id"]) ? sanitize_text_field($param["id"]) : null; //post id

        if (empty($taxonomies)) {
            $reg_errors->add(
                "field",
                esc_html__("Taxonomy is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [];

            $taxonomies = explode(",", $taxonomies);
            foreach ($taxonomies as $taxonomy) {
                $get_taxonomy = Fns::get_terms($taxonomy, $extra_amount_type);

                $format_taxonomy = [];
                foreach ($get_taxonomy as $single) {
                    $icon_id = get_term_meta($single->term_id, "icon", true);
                    $tax_pos = get_term_meta($single->term_id, "tax_pos", true);
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

                    $term_property = [
                        "id" => (string) $single->term_id,
                        "label" => $single->name,
                        "color" => "#4a5568",
                        "bg_color" => "#E2E8F0",
                        "tax_pos" => $tax_pos,
                        "icon" => $iconData ? $iconData : "",
                    ];

                    $color = get_term_meta($single->term_id, "color", true);
                    $bg_color = get_term_meta(
                        $single->term_id,
                        "bg_color",
                        true
                    );

                    if ($color) {
                        $term_property["color"] = $color;
                    }

                    if ($bg_color) {
                        $term_property["bg_color"] = $bg_color;
                    }

                    if ($taxonomy == "lead_source" && $hide_bg) {
                        $term_property["bg_color"] = "";
                        $term_property["color"] = "#718096";
                    }

                    if ($label_only) {
                        unset($term_property["bg_color"]);
                        unset($term_property["color"]);
                        unset($term_property["icon"]);
                    }

                    if (
                        $taxonomy == "deal_stage" ||
                        $taxonomy == "project_status" ||
                        $taxonomy == "contact_status" ||
                        $taxonomy == "task_status"
                    ) {
                        // for deal won, deal lost, project complted, task done, contact active, block
                        $term_property["type"] = get_term_meta(
                            $single->term_id,
                            "type",
                            true
                        );
                    }

                    if ($taxonomy == "extra_amount") {
                        // for, tax, fee, discount
                        $term_property["extra_amount_type"] = get_term_meta(
                            $single->term_id,
                            "extra_amount_type",
                            true
                        );
                        $term_property["tax_cal"] = get_term_meta(
                            $single->term_id,
                            "tax_cal",
                            true
                        );
                        $term_property["fee_cal"] = get_term_meta(
                            $single->term_id,
                            "fee_cal",
                            true
                        );
                        $term_property["val_type"] = get_term_meta(
                            $single->term_id,
                            "val_type",
                            true
                        );
                        $term_property["show"] = get_term_meta(
                            $single->term_id,
                            "show",
                            true
                        );
                        if (
                            $taxonomy == "extra_amount" &&
                            $extra_amount_type !=
                            $term_property["extra_amount_type"]
                        ) {
                            if (
                                $extra_amount_type &&
                                $extra_amount_type !=
                                $term_property["extra_amount_type"]
                            ) {
                                continue;
                            }
                        }
                    }

                    if ($taxonomy == "email_social") {
                        $term_property["url"] = get_term_meta(
                            $single->term_id,
                            "url",
                            true
                        );
                    }

                    $format_taxonomy[] = $term_property;
                }
                $data[$taxonomy] = $format_taxonomy;

                if ($id) {
                    $tags = get_the_terms($id, "ndpv_" . $taxonomy);
                    $tagList = [];
                    if ($tags) {
                        foreach ($tags as $tag) {
                            $color = get_term_meta(
                                $tag->term_id,
                                "color",
                                true
                            );
                            $bg_color = get_term_meta(
                                $tag->term_id,
                                "bg_color",
                                true
                            );

                            $single_tag = [
                                "id" => $tag->term_id,
                                "label" => $tag->name,
                                "color" => "#4a5568",
                                "bg_color" => "#E2E8F0",
                            ];

                            if ($color) {
                                $single_tag["color"] = $color;
                            }

                            if ($bg_color) {
                                $single_tag["bg_color"] = $bg_color;
                            }

                            if ($taxonomy == "lead_source") {
                                $single_tag["bg_color"] = "";
                                $single_tag["color"] = "#718096";
                            }

                            $tagList[] = $single_tag;
                        }
                    }
                    $data["single_" . $taxonomy] = $tagList;
                }
            }
            wp_send_json_success($data);
        }
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = $id;

        $query_data["label"] = get_term($id)->name;
        $query_data["color"] = get_term_meta($id, "color", true);
        $query_data["bg_color"] = get_term_meta($id, "bg_color", true);
        $icon_id = get_term_meta($id, "icon", true);
        $iconData = null;
        if ($icon_id) {
            $icon_src = wp_get_attachment_image_src($icon_id, "thumbnail");
            if ($icon_src) {
                $iconData = [];
                $iconData["id"] = $icon_id;
                $iconData["src"] = $icon_src[0];
            }
        }
        $query_data["icon"] = $iconData;

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $taxonomy = isset($param["taxonomy"])
            ? sanitize_text_field($param["taxonomy"])
            : null;
        $reorder = isset($param["reorder"])
            ? array_map("absint", $param["reorder"])
            : false;
        $label = isset($param["label"])
            ? sanitize_text_field($param["label"])
            : null;
        $color = isset($param["color"])
            ? sanitize_text_field($param["color"])
            : null;
        $bg_color = isset($param["bg_color"])
            ? sanitize_text_field($param["bg_color"])
            : null;
        $icon =
            isset($param["icon"]) && isset($param["icon"]["id"])
            ? absint($param["icon"]["id"])
            : null;
        $extra_amount_type = isset($param["extra_amount_type"])
            ? sanitize_text_field($param["extra_amount_type"])
            : null;
        $tax_cal = isset($param["tax_cal"])
            ? sanitize_text_field($param["tax_cal"])
            : null;
        $fee_cal = isset($param["fee_cal"])
            ? sanitize_text_field($param["fee_cal"])
            : null;
        $val_type = isset($param["val_type"])
            ? sanitize_text_field($param["val_type"])
            : null;
        $show = isset($param["show"])
            ? rest_sanitize_boolean($param["show"])
            : null;
        //email social
        $url = isset($param["url"]) ? esc_url_raw($param["url"]) : null;

        if (empty($taxonomy)) {
            $reg_errors->add(
                "field",
                esc_html__("Taxonomy is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            if ($reorder) {
                $this->reorder_taxonomies($reorder);
                wp_send_json_success();
            } else {
                $add_taxonomy = wp_insert_term(
                    $label, // the term
                    "ndpv_" . $taxonomy // the add_taxonomy
                );

                if (!is_wp_error($add_taxonomy)) {
                    $term_id = $add_taxonomy["term_id"];
                    update_term_meta($term_id, "tax_pos", $term_id);
                    update_term_meta($term_id, "color", $color);
                    update_term_meta($term_id, "bg_color", $bg_color);

                    if ($icon) {
                        update_term_meta($term_id, "icon", $icon);
                    }

                    if ($url) {
                        update_term_meta($term_id, "url", $url);
                    }

                    if ($taxonomy == "extra_amount" && $extra_amount_type) {
                        update_term_meta(
                            $term_id,
                            "extra_amount_type",
                            $extra_amount_type
                        );
                        update_term_meta($term_id, "tax_cal", $tax_cal);
                        update_term_meta($term_id, "fee_cal", $fee_cal);
                        update_term_meta($term_id, "val_type", $val_type);
                        update_term_meta($term_id, "show", $show);
                    }

                    wp_send_json_success($term_id);
                } else {
                    wp_send_json_error($add_taxonomy->get_error_messages());
                }
            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $taxonomy = isset($param["taxonomy"])
            ? sanitize_text_field($param["taxonomy"])
            : null;
        $post_id = isset($param["post_id"]) ? absint($param["post_id"]) : null;
        $add = isset($param["add"]) ? true : false;
        $append = isset($param["append"]) && $param["append"] ? true : false;
        $delete = isset($param["delete"]) ? true : false;
        $label = isset($param["label"])
            ? sanitize_text_field($param["label"])
            : null;
        $color = isset($param["color"])
            ? sanitize_text_field($param["color"])
            : null;
        $bg_color = isset($param["bg_color"])
            ? sanitize_text_field($param["bg_color"])
            : null;
        $icon =
            isset($param["icon"]) && isset($param["icon"]["id"])
            ? absint($param["icon"]["id"])
            : null;
        $tax_cal = isset($param["tax_cal"])
            ? sanitize_text_field($param["tax_cal"])
            : null;
        $fee_cal = isset($param["fee_cal"])
            ? sanitize_text_field($param["fee_cal"])
            : null;
        $val_type = isset($param["val_type"])
            ? sanitize_text_field($param["val_type"])
            : null;
        $show = isset($param["show"])
            ? rest_sanitize_boolean($param["show"])
            : null;
        //email social
        $url = isset($param["url"]) ? esc_url_raw($param["url"]) : null;

        if (empty($taxonomy)) {
            $reg_errors->add(
                "field",
                esc_html__("Taxonomy is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $term_id = absint($url_params["id"]);

            if ($add) {
                if ($append) {
                    wp_set_object_terms(
                        $post_id,
                        $term_id,
                        "ndpv_" . $taxonomy,
                        true
                    );
                } else {
                    wp_set_post_terms(
                        $post_id,
                        [$term_id],
                        "ndpv_" . $taxonomy
                    );
                }

                // Webhook trigger for lead_level, deal_stage, project_status change
                $param["id"] = $post_id;
                $taxonomy_actions = [
                    "lead_level" => "lead_level_change",
                    "deal_stage" => "deal_stage_change",
                    "project_status" => "project_status_change"
                ];
                $action = $taxonomy_actions[$taxonomy] ?? null;
                if ($action) {
                    do_action("ndpvp/webhook", $action, $param);
                }

                do_action("ndpvp/webhook", "taxonomy", $param);

                wp_send_json_success();
            } else if ($delete) {
                if ($post_id) { //delete term from post
                    wp_remove_object_terms($post_id, $term_id, 'ndpv_' . $taxonomy);
                }
                wp_send_json_success();
            } else {
                $add_taxonomy = wp_update_term(
                    $term_id,   // the term
                    'ndpv_' . $taxonomy, // the taxonomy
                    array(
                        'name' => $label,
                    )
                );

                if (!is_wp_error($add_taxonomy)) {
                    update_term_meta($term_id, "color", $color);
                    update_term_meta($term_id, "bg_color", $bg_color);

                    if ($icon) {
                        update_term_meta($term_id, "icon", $icon);
                    } else {
                        delete_term_meta($term_id, "icon");
                    }

                    if ($taxonomy == "extra_amount") {
                        update_term_meta($term_id, "tax_cal", $tax_cal);
                        update_term_meta($term_id, "fee_cal", $fee_cal);
                        update_term_meta($term_id, "val_type", $val_type);
                        update_term_meta($term_id, "show", $show);
                    }

                    if ($taxonomy == "email_social") {
                        if ($url) {
                            update_term_meta($term_id, "url", $url);
                        } else {
                            delete_term_meta($term_id, "url");
                        }
                    }

                    wp_send_json_success($term_id);
                } else {
                    wp_send_json_error();
                }
            }
        }
    }

    public function reorder_taxonomies($ids = [])
    {
        $i = 1;
        foreach ($ids as $id) :
            update_term_meta($id, "tax_pos", $i);
            $i++;
        endforeach;
    }

    public function delete($req)
    {
        $url_params = $req->get_url_params();
        $tax = $url_params["tax"];
        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_term($id, "ndpv_" . $tax);
        }
        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_taxonomy");
    }

    public function create_per()
    {
        return current_user_can("ndpv_taxonomy");
    }

    public function update_per()
    {
        return current_user_can("ndpv_taxonomy");
    }

    public function del_per()
    {
        return current_user_can("ndpv_taxonomy");
    }
}
