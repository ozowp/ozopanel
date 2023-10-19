<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Model\Business;
use WAM\Traits\Singleton;
use WAM\Helper\Fns;

class Setting
{
    use Singleton;
    public $tabs = [
        "system_email_template" => [
            "email_lead_add_notif" => [
                "keys" => [
                    "email_template",
                    "lead",
                    "add_notif"
                ]
            ],
            "email_lead_assign_notif" => [
                "keys" => [
                    "email_template",
                    "lead",
                    "assign_notif"
                ]
            ],
            "email_lead_level_change_notif" => [
                "keys" => [
                    "email_template",
                    "lead",
                    "level_change_notif"
                ]
            ],
            "email_lead_to_deal_notif" => [
                "keys" => [
                    "email_template",
                    "lead",
                    "to_deal_notif"
                ]
            ],
            "email_deal_add_notif" => [
                "keys" => [
                    "email_template",
                    "deal",
                    "add_notif"
                ]
            ],
            "email_deal_assign_notif" => [
                "keys" => [
                    "email_template",
                    "deal",
                    "assign_notif"
                ]
            ],
            "email_deal_stage_change_notif" => [
                "keys" => [
                    "email_template",
                    "deal",
                    "stage_change_notif"
                ]
            ],
            "email_estimate_default" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "default"
                ]
            ],
            "email_estimate_reminder" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "reminder"
                ]
            ],
            "email_est_add_notif" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "add_notif"
                ]
            ],
            "email_est_edit_notif" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "edit_notif"
                ]
            ],
            "email_est_accept_notif" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "accept_notif"
                ]
            ],
            "email_est_reject_notif" => [
                "keys" => [
                    "email_template",
                    "estimate",
                    "reject_notif"
                ]
            ],
            "email_invoice_default" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "default"
                ]
            ],
            "email_invoice_reminder" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "reminder"
                ]
            ],
            "email_invoice_recurring" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "recurring"
                ]
            ],
            "email_inv_add_notif" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "add_notif"
                ]
            ],
            "email_inv_edit_notif" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "edit_notif"
                ]
            ],
            "email_inv_paid_notif" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "paid_notif"
                ]
            ],
            "email_inv_paid_req_notif" => [
                "keys" => [
                    "email_template",
                    "invoice",
                    "paid_req_notif"
                ]
            ],
            "email_client_portal_password" => [
                "keys" => [
                    "email_template",
                    "client_portal",
                    "password"
                ]
            ],
            "email_team_password" => [
                "keys" => [
                    "email_template",
                    "team",
                    "password"
                ]
            ],
            "email_task_add_notif" => [
                "keys" => [
                    "email_template",
                    "task",
                    "add_notif"
                ]
            ],
            "email_task_assign_notif" => [
                "keys" => [
                    "email_template",
                    "task",
                    "assign_notif"
                ]
            ],
            "email_task_status_change_notif" => [
                "keys" => [
                    "email_template",
                    "task",
                    "status_change_notif"
                ]
            ],
            "email_staff_add_notif" => [
                "keys" => [
                    "email_template",
                    "staff",
                    "add_notif"
                ]
            ],
            "email_project_discussion_add_notif" => [
                "keys" => [
                    "email_template",
                    "project",
                    "discussion_add_notif"
                ]
            ],
            "email_file_add_notif" => [
                "keys" => [
                    "email_template",
                    "file",
                    "add_notif"
                ]
            ],
        ]
    ];

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/settings" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"],
        ]);

        register_rest_route("ndpv/v1", "/settings", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"],
        ]);
    }

    public function get($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $tab = isset($param["tab"]) ? sanitize_text_field($param["tab"]) : null;

        if (empty($tab)) {
            $reg_errors->add(
                "field",
                esc_html__("Tab is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [];

            if ($tab == "general_module") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["deactivate"] = [];
                }
            }

            if ($tab == "subscription") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    if (!isset($option["deactivate"])) {
                        $option["deactivate"] = [];
                    }
                    $data = $option;
                } else {
                    $data["deactivate"] = [];
                }
            }

            if ($tab == "email_footer") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                    $white_label = get_option("ndpv_white_label");
                    $data["logo"] = null;
                    if ($white_label && isset($white_label["logo"])) {
                        $data["logo"] = $white_label["logo"];
                        $logo_id = $white_label["logo"];
                        $logoData = null;
                        if ($logo_id) {
                            $logo_src = wp_get_attachment_image_src(
                                $logo_id,
                                "thumbnail"
                            );
                            if ($logo_src) {
                                $logoData = [];
                                $logoData["id"] = $logo_id;
                                $logoData["src"] = $logo_src[0];
                            }
                        }
                        $data["logo"] = $logoData;
                    }
                } else {
                    $data["active"] = true;
                    $data["text"] = "<p>Powered by</p>
<h3>Propovoice</h3>";
                    $data["logo"] = null;
                }
            }

            if ($tab == "estinv_currency") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["currency"] = "USD";
                    $data["lang"] = "en";
                }
            }

            if ($tab == "estimate_general") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["prefix"] = "Est-";
                }
            }

            if ($tab == "estimate_reminder") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["status"] = false;
                    $data["due_date"] = false;
                    $data["before"] = [];
                    $data["after"] = [];
                }
            }

            if ($tab == "invoice_general") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["prefix"] = "Inv-";
                }
            }

            if ($tab == "invoice_reminder") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["status"] = false;
                    $data["due_date"] = false;
                    $data["before"] = [];
                    $data["after"] = [];
                }
            }

            if ($tab == "payment_wc") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["status"] = false;
                }
            }

            if ($tab == "email_smtp_test") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $business = new Business;
                    $business_info = $business->info();
                    $name = $business_info['name'];
                    $data["from"] = $business_info['email'];
                    $data["to"] = '';

                    $subject = str_replace(
                        array(
                            '{org_name}'
                        ),
                        array(
                            $name
                        ),
                        ndpv()->get_default(
                            "email_template",
                            "smtp",
                            "test",
                            "subject"
                        )
                    );

                    $msg = str_replace(
                        array(
                            '{org_name}'
                        ),
                        array(
                            $name
                        ),
                        ndpv()->get_default(
                            "email_template",
                            "smtp",
                            "test",
                            "msg"
                        )
                    );

                    $data["subject"] = $subject;
                    $data["msg"] = $msg;
                }
            }

            // Get email template
            if (array_key_exists($tab, $this->tabs["system_email_template"])) {
                $data = $this->get_email_template($this->tabs["system_email_template"], $tab, $data);
            }

            if ($tab == "estvoice_tax") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["item_tax"] = false;
                    // $data['item_tax_val_type'] = 'fixed';
                }
            }

            if ($tab == "smtp_other") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["host"] = "";
                    $data["port"] = "";
                    $data["secure"] = "";
                    $data["auth"] = false;
                    $data["user"] = "";
                    $data["pass"] = "";
                }
            }

            if ($tab == "smtp_sendinblue") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["key"] = "";
                    $data["web"] = "";
                }
            }

            if ($tab == "google_api_oauth2") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["client_id"] = "";
                    $data["client_secret"] = "";
                    $data["redirect_uri"] = "";
                    $data["api_key"] = "";
                }
            }

            if ($tab == "automation_zapier") {
                $option = get_option("ndpv_" . $tab);

                if ($option) {
                    $data = $option;
                } else {
                    $data["status"] = false;
                    $data["name"] = "";
                    $data["url"] = "";
                    $data["actions"] = [
                        /* [
                            "id" => "new_lead",
                            "method" => "post",
                            "custom" => false,
                            "name" => '',
                            "url" => ''
                        ], */];
                }
            }

            wp_send_json_success($data);
        }
    }

    public function get_email_template($tabs, $current_tab, $data)
    {
        // Fetch template from database
        $option = get_option("ndpv_" . $current_tab);
        if ($option) {
            return $option;
        }

        // Fetch template from preset
        $keys = $tabs[$current_tab]["keys"];
        $fields = ["subject", "msg"];
        foreach ($fields as $field) {
            $data[$field] = call_user_func_array([ndpv(), "get_default"], [...$keys, $field]);
        }

        return $data;
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $tab = isset($param["tab"]) ? sanitize_text_field($param["tab"]) : null;

        if (empty($tab)) {
            $reg_errors->add(
                "field",
                esc_html__("Tab is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $data = [];

            if ($tab == "general_module") {
                $data["deactivate"] = isset($param["deactivate"])
                    ? array_map("sanitize_text_field", $param["deactivate"])
                    : [];
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "subscription") {
                $data = $param;
                $data["deactivate"] = isset($param["deactivate"])
                    ? array_map("sanitize_text_field", $param["deactivate"])
                    : [];
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "email_footer") {
                $data["active"] = isset($param["active"])
                    ? rest_sanitize_boolean($param["active"])
                    : null;
                $data["text"] = isset($param["text"]) ? $param["text"] : null;

                $option = update_option("ndpv_" . $tab, $data);

                $white_label["logo"] = isset($param["logo"])
                    ? $param["logo"]["id"]
                    : null;

                update_option("ndpv_white_label", $white_label);
            }

            if ($tab == "estinv_currency") {
                $data["currency"] = isset($param["currency"])
                    ? sanitize_text_field($param["currency"])
                    : null;
                $data["lang"] = isset($param["lang"])
                    ? sanitize_text_field($param["lang"])
                    : null;
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "estimate_general") {
                $data["prefix"] = isset($param["prefix"])
                    ? sanitize_text_field($param["prefix"])
                    : "";
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "invoice_general") {
                $data["prefix"] = isset($param["prefix"])
                    ? sanitize_text_field($param["prefix"])
                    : "";
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "estimate_reminder" || $tab == "invoice_reminder") {
                //TODO: sanitization
                $data["status"] = isset($param["status"])
                    ? rest_sanitize_boolean($param["status"])
                    : null;
                $data["due_date"] = isset($param["due_date"])
                    ? $param["due_date"]
                    : null;
                $data["before"] = isset($param["before"])
                    ? $param["before"]
                    : null;
                $data["after"] = isset($param["after"])
                    ? $param["after"]
                    : null;

                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "payment_wc") {
                $data["status"] = isset($param["status"])
                    ? rest_sanitize_boolean($param["status"])
                    : null;

                if ($data["status"] && !class_exists("woocommerce")) {
                    $reg_errors->add(
                        "field",
                        esc_html__(
                            "Please install and activate WooCommerce plugin.",
                            "propovoice"
                        )
                    );
                }

                if ($reg_errors->get_error_messages()) {
                    wp_send_json_error($reg_errors->get_error_messages());
                }
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "email_smtp_test") {
                $data["from"] = isset($param["from"])
                    ? sanitize_text_field($param["from"])
                    : '';
                $data["to"] = isset($param["to"])
                    ? sanitize_textarea_field($param["to"])
                    : '';
                $data["subject"] = isset($param["subject"])
                    ? sanitize_text_field($param["subject"])
                    : '';
                $data["msg"] = isset($param["msg"])
                    ? sanitize_textarea_field($param["msg"])
                    : '';
                $option = update_option("ndpv_" . $tab, $data);

                $business = new Business;
                $business_info = $business->info();
                $name = $business_info['name'];

                $headers = ["Content-Type: text/html; charset=UTF-8"];
                $headers[] = "From: " .  $name . " <" . $data["from"] . ">";

                $send_mail = wp_mail($data["to"], $data["subject"], nl2br($data["msg"]), $headers, []);

                if ($send_mail) {
                    wp_send_json_success();
                } else {
                    wp_send_json_error(["Something wrong: Email not sent"]);
                }
            }

            // Create email template
            if (array_key_exists($tab, $this->tabs["system_email_template"])) {
                $this->create_email_template($tab, $param, $data);
            }


            if ($tab == "password_change") {
                $user_id = get_current_user_id();

                $data["current_password"] = isset($param["current_password"])
                    ? ($param["current_password"])
                    : '';
                $data["new_password"] = isset($param["new_password"])
                    ? sanitize_textarea_field($param["new_password"])
                    : '';
                $data["confirm_password"] = isset($param["confirm_password"])
                    ? sanitize_textarea_field($param["confirm_password"])
                    : '';

                $user = get_user_by('id', $user_id);
                if (!wp_check_password($data["current_password"], $user->data->user_pass, $user_id)) {
                    $reg_errors->add(
                        "field",
                        esc_html__("Current password not matched!!!", "propovoice")
                    );
                }

                if ($data["new_password"] != $data["confirm_password"]) {
                    $reg_errors->add(
                        "field",
                        esc_html__("Confirm password not matched!!!", "propovoice")
                    );
                }

                if ($reg_errors->get_error_messages()) {
                    wp_send_json_error($reg_errors->get_error_messages());
                } else {
                    wp_set_password($data["new_password"], $user_id);

                    // Log-in again.
                    wp_set_auth_cookie($user_id);
                    wp_set_current_user($user_id);
                }
            }

            if ($tab == "estvoice_tax") {
                $data["item_tax"] = isset($param["item_tax"])
                    ? rest_sanitize_boolean($param["item_tax"])
                    : null;
                // $data['item_tax_val_type'] = isset( $param['item_tax_val_type'] ) ? sanitize_textarea_field( $param['item_tax_val_type'] ) : null;
                $option = update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "smtp_default") {
                update_option("ndpv_smtp", null);
            }

            if ($tab == "smtp_other") {
                $data["host"] = isset($param["host"])
                    ? sanitize_text_field($param["host"])
                    : null;
                $data["port"] = isset($param["port"])
                    ? absint($param["port"])
                    : null;
                $data["secure"] = isset($param["secure"])
                    ? sanitize_text_field($param["secure"])
                    : null;
                $data["auth"] = isset($param["auth"])
                    ? rest_sanitize_boolean($param["auth"])
                    : null;
                $data["user"] = isset($param["user"])
                    ? sanitize_text_field($param["user"])
                    : null;
                $data["pass"] = isset($param["pass"])
                    ? sanitize_text_field($param["pass"])
                    : null;
                update_option("ndpv_" . $tab, $data);
                update_option("ndpv_smtp", "other");
            }

            if ($tab == "smtp_sendinblue") {
                //Check valid key here
                $data["key"] = isset($param["key"])
                    ? sanitize_text_field($param["key"])
                    : null;
                $data["web"] = isset($param["web"])
                    ? esc_url_raw($param["web"])
                    : null;
                update_option("ndpv_" . $tab, $data);
                update_option("ndpv_smtp", "sendinblue");
            }

            if ($tab == "google_api_oauth2") {
                //Check valid key here
                $data["client_id"] = isset($param["client_id"])
                    ? sanitize_text_field($param["client_id"])
                    : null;
                $data["client_secret"] = isset($param["client_secret"])
                    ? sanitize_text_field($param["client_secret"])
                    : null;
                $data["redirect_uri"] = isset($param["redirect_uri"])
                    ? sanitize_text_field($param["redirect_uri"])
                    : null;
                $data["api_key"] = isset($param["api_key"])
                    ? sanitize_text_field($param["api_key"])
                    : null;
                update_option("ndpv_" . $tab, $data);
            }

            if ($tab == "automation_zapier") {
                $data["status"] = isset($param["status"])
                    ? rest_sanitize_boolean($param["status"])
                    : null;
                $data["name"] = isset($param["name"])
                    ? sanitize_text_field($param["name"])
                    : null;
                $data["url"] = isset($param["url"])
                    ? esc_url_raw($param["url"])
                    : null;
                $data["actions"] = isset($param["actions"])
                    ? $param["actions"]
                    : null;
                update_option("ndpv_" . $tab, $data);
            }

            wp_send_json_success();
        }
    }

    public function create_email_template($current_tab, $param, $data)
    {

        $data["subject"] = isset($param["subject"])
            ? sanitize_text_field($param["subject"])
            : null;
        $data["msg"] = isset($param["msg"])
            ? sanitize_textarea_field($param["msg"])
            : null;
        update_option("ndpv_" . $current_tab, $data);
    }

    public function get_per()
    {
        return current_user_can("ndpv_setting");
    }

    public function create_per()
    {
        return current_user_can("ndpv_setting");
    }
}
