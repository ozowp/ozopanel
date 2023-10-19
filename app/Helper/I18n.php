<?php
namespace WAM\Helper;
/**
 * All Javascript files translate text
 *
 * @since 1.0.0
 */
class I18n
{
    static function dashboard()
    {
        return [
            //modules
            "db" => esc_html__("Dashboard", "wp-access-manager"),

            //alert
            "scf" => esc_html__("Successfully", "wp-access-manager"),
            "aAdd" => esc_html__("Successfully Added", "wp-access-manager"),
            "aUpd" => esc_html__("Successfully Updated", "wp-access-manager"),
            "aDel" => esc_html__("Successfully Deleted", "wp-access-manager"),
            "aThankM" => esc_html__("Thanks for your message", "wp-access-manager"),
            "aThankR" => esc_html__("Thanks for payment request", "wp-access-manager"),
            "aMail" => esc_html__("Mail successfully sent", "wp-access-manager"),
            "aConf" => esc_html__("Are you sure to delete it?", "wp-access-manager"),
            "cp" => esc_html__("Copied", "wp-access-manager"),
        ];
    }
}
