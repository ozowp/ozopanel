<?php
namespace WAM\Ctrl\Hook\Type\Action;

class Role
{
    public static $wam_caps = [
        "wam_core" => true,
        "wam_dashboard" => true,
        "wam_lead" => true,
        "wam_deal" => true,
        "wam_estimate" => true,
        "wam_invoice" => true,
        "wam_client" => true,
        "wam_project" => true,
        "wam_action" => true,
        "wam_business" => true,
        "wam_contact" => true,
        "wam_email" => true,
        "wam_file" => true,
        "wam_form" => true,
        "ndvp_media" => true,
        "wam_note" => true,
        "wam_org" => true,
        "wam_payment" => true,
        "wam_person" => true,
        "wam_setting" => true,
        "wam_task" => true,
        "wam_taxonomy" => true,
        "wam_webhook" => true,
        "wam_workspace" => true,
    ];

    public function __construct()
    {
        add_action("init", [$this, "update_admin_caps"], 11);
        add_filter("woocommerce_prevent_admin_access", "__return_false");
    }

    public function update_admin_caps()
    {
        $admin_role = get_role("administrator");

        foreach (self::$wam_caps as $cap => $perm) {
            $admin_role->add_cap($cap, $perm);
        }
    }
}
