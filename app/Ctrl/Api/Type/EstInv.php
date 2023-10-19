<?php

namespace WAM\Ctrl\Api\Type;

use WAM\Helper\Fns;
use WAM\Model\Invoice as ModelInvoice;
use WAM\Model\Contact;
use WAM\Traits\Singleton;

class EstInv
{
    use Singleton;
    
    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/invoices/(?P<id>\d+)", [
            "methods" => "GET",
            "callback" => [$this, "get_single"],
            "permission_callback" => [$this, "get_per_single"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("ndpv/v1", "/invoices" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/invoices", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/invoices/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/invoices/(?P<id>[0-9,]+)", [
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
        //this is the short solution to fix plain permalink issue
        $permalink_structure = get_option('permalink_structure');
        if ($permalink_structure === '' && isset($param['token'])) {
            $text = isset($param['args']) ? $param['args'] : '';

            preg_match('/\d+/', $text, $matches);
            if (!empty($matches)) {
                $req->set_url_params(['id' => $matches[0]]);
                $this->get_single($req);
            }
            return;
        }

        $per_page = 10;
        $offset = 0;

        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;
        $module_id = isset($param["module_id"])
            ? absint($param["module_id"])
            : null;
        $dashboard = isset($param["dashboard"]) ? true : false;
        $recurring = isset($param["recurring"]) ? true : false;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        if ($dashboard) {
            $per_page = 5;
        }

        $args = [
            "post_type" => "ndpv_estinv",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
            "orderby" => "ID",
            "order" => "DESC"
        ];

        $args["meta_query"] = [
            "relation" => "AND",
        ];

        if (current_user_can("ndpv_client_role")) {
            $user_id = get_current_user_id();
            $client_id = get_user_meta($user_id, 'ndpv_client_id', true);

            $args["meta_query"][] = [
                [
                    "key" => "to",
                    "value" => [$client_id],
                    "compare" => "IN",
                ],
            ];
        }

        if ($dashboard) {
            $args["meta_query"][] = [
                [
                    "key" => "status",
                    "value" => ['accept', 'decline', 'paid'],
                    "compare" => "NOT IN",
                ],
            ];
        }

        if ($recurring) {
            $args["meta_query"][] = [
                [
                    "key" => "recurring",
                    "value" => 1,
                ],
            ];
        }

        if ($s) {
            $contact_person = new Contact();
            $person_ids = $contact_person->query($s, "person");
            if ($person_ids) {
                $args["meta_query"][] = [
                    [
                        "key" => "to",
                        "value" => $person_ids,
                        "compare" => "IN",
                    ],
                ];
            }

            $org_ids = $contact_person->query($s, "org");
            if ($org_ids) {
                $args["meta_query"][] = [
                    [
                        "key" => "to",
                        "value" => $org_ids,
                        "compare" => "IN",
                    ],
                ];
            }
        }

        if (isset($param["path"])) {
            $args["meta_query"][] = [
                [
                    "key" => "path",
                    "value" => $param["path"],
                ],
            ];
        }

        /* if ( $s ) {
            $args['meta_query'][] = array(
                array(
                    'key'   => 'num',
                    'value' => $s
                )
            );
        } */

        if ( $module_id ) {
            //if query from client module search key as 'to'
            $module_key = isset($param["client_mod"]) ? 'to' : 'module_id';
            $args["meta_query"][] = [
                [
                    "key" => $module_key,
                    "value" => $module_id
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

            $invMeta = get_post_meta($id);
            $query_data["num"] = isset($invMeta["num"])
                ? $invMeta["num"][0]
                : "";
            $query_data["token"] = isset($invMeta["token"])
                ? $invMeta["token"][0]
                : "";
            $query_data["path"] = isset($invMeta["path"])
                ? $invMeta["path"][0]
                : "";
            $query_data["date"] = isset($invMeta["date"])
                ? $invMeta["date"][0]
                : "";
            $query_data["due_date"] = isset($invMeta["due_date"])
                ? $invMeta["due_date"][0]
                : "";

            $query_data["project"] = [
                "name" => "",
            ];

            $from_id = get_post_meta($id, "from", true);
            $fromData = [];
            if ($from_id) {
                $fromData["id"] = $from_id;
                $fromData["name"] = get_post_meta($from_id, "name", true);
            }
            $query_data["from"] = $fromData;

            $contact_id = get_post_meta($id, "to", true);
            $to_type = get_post_meta($id, "to_type", true);
            $contactData = [];

            if ($contact_id) {
                $contactData["id"] = absint($contact_id);
                $contactData["type"] = $to_type;
                $contactMeta = get_post_meta($contact_id);
                $contactData["first_name"] = isset($contactMeta["first_name"])
                    ? $contactMeta["first_name"][0]
                    : "";
                $contactData["org_name"] = isset($contactMeta["name"])
                    ? $contactMeta["name"][0]
                    : "";
                $contactData["email"] = isset($contactMeta["email"])
                    ? $contactMeta["email"][0]
                    : "";
            }
            $query_data["to"] = $contactData;

            $query_data["invoice"] = isset($invMeta["invoice"])
                ? maybe_unserialize($invMeta["invoice"][0])
                : "";
            $query_data["total"] = isset($invMeta["total"])
                ? $invMeta["total"][0]
                : "";

            if (!$query_data["total"]) {
                $query_data["total"] = 0;
            }

            $query_data["paid"] = isset($invMeta["paid"])
                ? $invMeta["paid"][0]
                : "";
            if (!$query_data["paid"]) {
                $query_data["paid"] = 0;
            }

            $query_data["due"] = isset($invMeta["due"])
                ? $invMeta["due"][0]
                : "";
            if (!$query_data["due"]) {
                $query_data["due"] = 0;
            }

            $query_data["feedback"] = isset($invMeta["feedback"])
                ? maybe_unserialize($invMeta["feedback"][0])
                : "";
            $query_data["payment_method"] = isset($invMeta["payment_method"])
                ? $invMeta["payment_method"][0]
                : "";
            $query_data["payment_info"] = isset($invMeta["payment_info"])
                ? maybe_unserialize($invMeta["payment_info"][0])
                : "";
            $query_data["status"] = isset($invMeta["status"])
                ? $invMeta["status"][0]
                : "";

            // $query_data['date'] = get_the_time( get_option('date_format') );
            $data[] = $query_data;
        }
        wp_reset_postdata();

        $path = $param["path"];
        $prefix = get_option("ndpv_" . $path . "_general");
        if ($prefix) {
            $result["prefix"] = $prefix["prefix"];
        } else {
            $result["prefix"] = $path == "invoice" ? "Inv-" : "Est-";
        }

        $result["result"] = $data;
        $result["total"] = $total_data;

        wp_send_json_success($result);
    }

    public function get_single($req)
    {
        $param = $req->get_params();

        $url_params = $req->get_url_params();

        $id = absint($url_params["id"]);
        $query_data = [];

        $subs_ref_id = get_post_meta($id, "subs_ref_id", true);
        if ($subs_ref_id) {
            $id = $subs_ref_id;
        }

        if ($id) {
            //edit
            $query_data["id"] = $id;

            $invMeta = get_post_meta($id);
            $query_data["token"] = isset($invMeta["token"])
                ? $invMeta["token"][0]
                : "";
            $query_data["date"] = isset($invMeta["date"])
                ? $invMeta["date"][0]
                : "";
            $query_data["due_date"] = isset($invMeta["due_date"])
                ? $invMeta["due_date"][0]
                : "";
            $query_data["module_id"] = isset($invMeta["module_id"])
                ? $invMeta["module_id"][0]
                : "";

            $from_id = isset($invMeta["from"]) ? $invMeta["from"][0] : "";

            $query_data["status"] = isset($invMeta["status"])
                ? $invMeta["status"][0]
                : "";
            $fromData = [];

            if ($from_id) {
                $fromData["id"] = $from_id;
                $fromMeta = get_post_meta($from_id);
                $fromData["name"] = isset($fromMeta["name"])
                    ? $fromMeta["name"][0]
                    : "";
                $fromData["org_name"] = isset($fromMeta["org_name"])
                    ? $fromMeta["org_name"][0]
                    : "";
                $fromData["email"] = isset($fromMeta["email"])
                    ? $fromMeta["email"][0]
                    : "";
                $fromData["web"] = isset($fromMeta["web"])
                    ? $fromMeta["web"][0]
                    : "";
                $fromData["mobile"] = isset($fromMeta["mobile"])
                    ? $fromMeta["mobile"][0]
                    : "";
                $fromData["country"] = isset($fromMeta["country"])
                    ? $fromMeta["country"][0]
                    : "";
                $fromData["region"] = isset($fromMeta["region"])
                    ? $fromMeta["region"][0]
                    : "";
                $fromData["address"] = isset($fromMeta["address"])
                    ? $fromMeta["address"][0]
                    : "";
                $fromData["city"] = isset($fromMeta["city"])
                    ? $fromMeta["city"][0]
                    : "";
                $fromData["zip"] = isset($fromMeta["zip"])
                    ? $fromMeta["zip"][0]
                    : "";
                $logo_id = get_post_meta($from_id, "logo", true);
                $logoData = null;
                if ($logo_id) {
                    $logoData = [];
                    $logoData["id"] = $logo_id;
                    $logo_src = wp_get_attachment_image_src(
                        $logo_id,
                        "thumbnail"
                    );
                    $logoData["src"] = $logo_src[0];
                }
                $fromData["logo"] = $logoData;
            }
            $query_data["fromData"] = $fromData;

            $to_id = isset($invMeta["to"]) ? $invMeta["to"][0] : "";
            $to_type = isset($invMeta["to_type"]) ? $invMeta["to_type"][0] : "";

            $toData = [];

            if ($to_id) {
                $toData["id"] = absint($to_id);
                $toMeta = get_post_meta($to_id);
                $toData["type"] = $to_type;
                $toData["first_name"] = isset($toMeta["first_name"])
                    ? $toMeta["first_name"][0]
                    : "";
                $toData["org_name"] = isset($toMeta["name"])
                    ? $toMeta["name"][0]
                    : "";
                $toData["email"] = isset($toMeta["email"])
                    ? $toMeta["email"][0]
                    : "";
                $toData["mobile"] = isset($toMeta["mobile"])
                    ? $toMeta["mobile"][0]
                    : "";
                $toData["web"] = isset($toMeta["web"]) ? $toMeta["web"][0] : "";
                $toData["country"] = isset($toMeta["country"])
                    ? $toMeta["country"][0]
                    : "";
                $toData["region"] = isset($toMeta["region"])
                    ? $toMeta["region"][0]
                    : "";
                $toData["address"] = isset($toMeta["address"])
                    ? $toMeta["address"][0]
                    : "";
            }
            $query_data["toData"] = $toData;

            $invoice = isset($invMeta["invoice"])
                ? maybe_unserialize($invMeta["invoice"][0])
                : "";

            $reminder = isset($invoice["reminder"])
                ? $invoice["reminder"]
                : null;
            if (!$reminder) {
                $reminderData = [];
                $reminderData["status"] = false;
                $reminderData["due_date"] = false;
                $reminderData["before"] = [];
                $reminderData["after"] = [15];
                $invoice["reminder"] = $reminderData;
            }

            $recurring = isset($invoice["recurring"])
                ? $invoice["recurring"]
                : null;
            if (!$recurring && !isset($param["client_view"])) {
                $recurringData = [];
                $recurringData["status"] = false;
                $recurringData["interval_type"] = "week";
                $recurringData["interval_in"] = "month";
                $recurringData["interval"] = 1;
                $recurringData["limit_type"] = 0;
                $recurringData["limit"] = 5;
                $recurringData["subscription"] = false;
                $recurringData["send_me"] = false;
                $recurringData["delivery"] = 1;
                $invoice["recurring"] = $recurringData;
            }

            $paymentData = null;
            if (isset($invoice["payment_methods"]["bank"])) {
                $paymentData["id"] = $invoice["payment_methods"]["bank"];
                $paymentMeta = get_post_meta(
                    $invoice["payment_methods"]["bank"]
                );
                $paymentData["name"] = isset($paymentMeta["name"])
                    ? $paymentMeta["name"][0]
                    : "";
                $paymentData["details"] = isset($paymentMeta["details"])
                    ? $paymentMeta["details"][0]
                    : "";
            }
            $query_data["paymentBankData"] = $paymentData;

            if (isset($param["client_view"])) {
                $token = isset($param["token"])
                    ? sanitize_text_field($param["token"])
                    : "";
                $post_token = get_post_meta($id, "token", true);

                if (
                    $recurring &&
                    (!$recurring["status"] || !$recurring["subscription"])
                ) {
                    unset($invoice["recurring"]);
                    $invoice["recurring"]['status'] = false;
                    $invoice["recurring"]['subscription'] = false;
                }

                $is_admin =
                    is_user_logged_in() &&
                    apply_filters(
                        "ndpv_admin",
                        current_user_can("administrator")
                    );

                $auth = false;
                if ($is_admin || $token == $post_token) {
                    $auth = true;
                }

                if (!$auth) {
                    wp_send_json_error();
                }

                $payment_methods = isset($invoice["payment_methods"])
                    ? $invoice["payment_methods"]
                    : null;
                if ($payment_methods) {
                    $new_payment_methods = [];

                    foreach ($payment_methods as $key => $payment_id) {
                        $payment_query_data = [];
                        $payment_query_data["id"] = $payment_id;

                        if ($key == "bank") {
                            $payment_query_data["name"] = get_post_meta(
                                $payment_id,
                                "name",
                                true
                            );
                            $payment_query_data["details"] = get_post_meta(
                                $payment_id,
                                "details",
                                true
                            );
                        } elseif ($key == "paypal") {
                            $payment_query_data["account_type"] = get_post_meta(
                                $payment_id,
                                "account_type",
                                true
                            );
                            $payment_query_data["client_id"] = get_post_meta(
                                $payment_id,
                                "client_id",
                                true
                            );
                        } elseif ($key == "stripe") {
                            $payment_query_data["public_key"] = get_post_meta(
                                $payment_id,
                                "public_key",
                                true
                            );
                        }

                        $new_payment_methods[$key] = $payment_query_data;
                    }

                    $invoice["payment_methods"] = $new_payment_methods;
                }

                $invoice_model = new ModelInvoice();
                $invoice["total"] = $invoice_model->getTotalAmount($invoice);
            }
        } else {
            //new
        }

        $path = $id ? get_post_meta($id, "path", true) : $param["path"];
        $prefix = get_option("ndpv_" . $path . "_general");
        if ($prefix) {
            $query_data["prefix"] = $prefix["prefix"];
        } else {
            $query_data["prefix"] = $path == "invoice" ? "Inv-" : "Est-";
        }

        $query_data["wc"] = false;
        if (ndpv()->wage()) {
            $wc = get_option("ndpv_payment_wc");
            if (isset($wc["status"])) {
                if ($wc["status"] && class_exists("woocommerce")) {
                    $query_data["wc"] = true;
                }
            }
        }

        $query_data["invoice"] = $invoice;

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();
        //TODO: sanitize later
        $invoice = isset($param) ? $param : null;
        $num = isset($param["num"]) ? $param["num"] : "";
        $module_id = isset($param["module_id"]) ? $param["module_id"] : null;
        $date = isset($param["date"]) ? $param["date"] : null;
        $path = isset($param["path"]) ? $param["path"] : "";
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;
        $payment_methods = isset($param["payment_methods"])
            ? $param["payment_methods"]
            : null;

        $total = 0;
        foreach ($param["items"] as $item) {
            $total += $item["qty"] * $item["price"];
        }

        $from = isset($param["from"]) ? $param["from"] : null;
        $to = isset($param["to"]) ? $param["to"] : null;
        $to_type = isset($param["to_type"]) ? $param["to_type"] : null;

        $reminder = isset($param["reminder"]) ? $param["reminder"] : null;
        $recurring = isset($param["recurring"]) ? $param["recurring"] : null;

        if (!$from) {
            $reg_errors->add(
                "field",
                esc_html__("Business is missing", "propovoice")
            );
        }

        if (!$path) {
            $reg_errors->add(
                "field",
                esc_html__("Module is missing", "propovoice")
            );
        }

        if (!$to) {
            $reg_errors->add(
                "field",
                esc_html__("Receiver is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            //TODO: give proper title
            $title = "";
            $data = [
                "post_type" => "ndpv_estinv",
                "post_title" => $title,
                "post_content" => "",
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());
                update_post_meta($post_id, "status", "draft");
                update_post_meta($post_id, "path", $path);

                $auto_id = '';
                if ($num) {
                    update_post_meta($post_id, "num", $num);
                } else {
                    //auto number
                    $prefix = get_option("ndpv_" . $path . "_general");
                    if ($prefix) {
                        $prefix = $prefix["prefix"];
                    } else {
                        $prefix = $path == "invoice" ? "Inv-" : "Est-";
                    }
                    $auto_id = $prefix . Fns::auto_id($path);
                    update_post_meta($post_id, "num", $auto_id);
                    $invoice['num'] = $auto_id;
                }

                if ($module_id) {
                    update_post_meta($post_id, "module_id", $module_id);
                }

                if ($date) {
                    update_post_meta($post_id, "date", $date);
                }

                if ($due_date) {
                    update_post_meta($post_id, "due_date", $due_date);
                }

                if ($from) {
                    update_post_meta($post_id, "from", $from);
                }

                if ($to) {
                    update_post_meta($post_id, "to", $to);
                }

                if ($to_type) {
                    update_post_meta($post_id, "to_type", $to_type);
                }

                if ($invoice) {
                    update_post_meta($post_id, "invoice", $invoice);
                }

                if ($total) {
                    update_post_meta($post_id, "total", $total);
                }

                if ($payment_methods) {
                    update_post_meta(
                        $post_id,
                        "payment_methods",
                        $payment_methods
                    );
                }

                if ($reminder) {
                    //save true or false
                    update_post_meta($post_id, "reminder", $reminder["status"]);
                }

                if ($recurring) {
                    //save true or false
                    update_post_meta(
                        $post_id,
                        "recurring",
                        $recurring["status"]
                    );
                    update_post_meta(
                        $post_id,
                        "subscription",
                        $recurring["subscription"]
                    );
                }

                //generate secret token
                $bytes = random_bytes(20);
                $token = bin2hex($bytes);
                update_post_meta($post_id, "token", $token);
                $param["id"] = $post_id;

                $hook = $path == "invoice" ? "inv" : "est";
                do_action("ndpvp/webhook", $hook . "_add", $param);


                wp_send_json_success([
                    "id" => $post_id,
                    "token" => $token,
                    "auto_id" => $auto_id
                ]);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();
        $invoice = isset($param) ? $param : null;

        $num = isset($param["num"]) ? $param["num"] : "";
        $module_id = isset($param["module_id"]) ? $param["module_id"] : null;
        $path = isset($param["path"]) ? $param["path"] : "";
        $date = isset($param["date"]) ? $param["date"] : null;
        $due_date = isset($param["due_date"]) ? $param["due_date"] : null;
        $payment_methods = isset($param["payment_methods"])
            ? $param["payment_methods"]
            : null;

        $from = isset($param["from"]) ? $param["from"] : null;
        $to = isset($param["to"]) ? $param["to"] : null;
        $to_type = isset($param["to_type"]) ? $param["to_type"] : null;

        $total = 0;
        foreach ($param["items"] as $item) {
            $total += $item["qty"] * $item["price"];
        }

        $reminder = isset($param["reminder"]) ? $param["reminder"] : null;
        $recurring = isset($param["recurring"]) ? $param["recurring"] : null;

        $attach = isset($param["attach"]) ? $param["attach"] : null;
        $sign = isset($param["sign"]) ? $param["sign"] : null;

        if (!$from) {
            $reg_errors->add(
                "field",
                esc_html__("Business is missing", "propovoice")
            );
        }

        if (!$to) {
            $reg_errors->add(
                "field",
                esc_html__("Receiver is missing", "propovoice")
            );
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => "",
                "post_content" => "",
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {
                if ($module_id) {
                    update_post_meta($post_id, "module_id", $module_id);
                }

                update_post_meta($post_id, "num", $num);
                update_post_meta($post_id, "date", $date);

                update_post_meta($post_id, "due_date", $due_date);

                if ($from) {
                    update_post_meta($post_id, "from", $from);
                }

                if ($to) {
                    update_post_meta($post_id, "to", $to);
                }

                if ($to_type) {
                    update_post_meta($post_id, "to_type", $to_type);
                }

                if ($invoice) {
                    update_post_meta($post_id, "invoice", $invoice);
                }

                if ($total) {
                    update_post_meta($post_id, "total", $total);
                }

                if ($reminder) {
                    update_post_meta($post_id, "reminder", $reminder["status"]);
                }

                if ($recurring) {
                    update_post_meta(
                        $post_id,
                        "recurring",
                        $recurring["status"]
                    );
                    update_post_meta(
                        $post_id,
                        "subscription",
                        $recurring["subscription"]
                    );
                }

                update_post_meta($post_id, "payment_methods", $payment_methods);

                $hook = $path == "invoice" ? "inv" : "est";
                do_action("ndpvp/webhook", $hook . "_edit", $param);


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

        do_action("ndpvp/webhook", "inv_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_invoice") ||
            current_user_can("ndpv_estimate");
    }

    public function get_per_single()
    {
        return true;
    }

    public function create_per()
    {
        return current_user_can("ndpv_invoice") ||
            current_user_can("ndpv_estimate");
    }

    public function update_per()
    {
        return current_user_can("ndpv_invoice") ||
            current_user_can("ndpv_estimate");
    }

    public function del_per()
    {
        return current_user_can("ndpv_invoice") ||
            current_user_can("ndpv_estimate");
    }
}
