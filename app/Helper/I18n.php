<?php
namespace WAM\Helper;
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
            //settings
            "settings" => esc_html__("Settings", "wp-access-manager"),
            //settings > tabs
            "general" => esc_html__("General", "wp-access-manager"),
            "other" => esc_html__("Other", "wp-access-manager"),

            //alert
            /* "scf" => esc_html__("Successfully", "wp-access-manager"),
            "aAdd" => esc_html__("Successfully Added", "wp-access-manager"),
            "aUpd" => esc_html__("Successfully Updated", "wp-access-manager"),
            "aDel" => esc_html__("Successfully Deleted", "wp-access-manager"),
            "aThankM" => esc_html__("Thanks for your message", "wp-access-manager"),
            "aThankR" => esc_html__("Thanks for payment request", "wp-access-manager"),
            "aMail" => esc_html__("Mail successfully sent", "wp-access-manager"),
            "aConf" => esc_html__("Are you sure to delete it?", "wp-access-manager"),
            "cp" => esc_html__("Copied", "wp-access-manager"), */
        ];
    }
}
