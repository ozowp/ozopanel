<?php
namespace OzoPanel\Helper;
/**
 * All Javascript files translate text
 *
 * @since 1.0.0
 */
class I18n
{
    static function app()
    {
        return [
            // general
            "select" => esc_html__("Select", "ozopanel"),
            "backTo" => esc_html__("Back to", "ozopanel"),
            "menu_select_guide" => esc_html__("Select menu and submenu which you want to allow for this", "ozopanel"),
            "submit" => esc_html__("Submit", "ozopanel"),
            "submiting" => esc_html__("Submitting...", "ozopanel"),
            "restrict" => esc_html__("Restrict", "ozopanel"),
            "restriction" => esc_html__("Restriction", "ozopanel"),
            // user
            "user" => esc_html__("User", "ozopanel"),
            "users" => esc_html__("Users", "ozopanel"),
            // role
            "role" => esc_html__("Role", "ozopanel"),
            "roles" => esc_html__("Roles", "ozopanel"),
            // settings
            "settings" => esc_html__("Settings", "ozopanel"),
            // settings > tabs
            "general" => esc_html__("General", "ozopanel"),
            "other" => esc_html__("Other", "ozopanel"),
            // toast
            "sucAdd" => esc_html__("Successfully Added", "ozopanel")
        ];
    }
}
