<?php
namespace WAM\Ctrl\Integrate\Smtp;

class SmtpList
{
    public function __construct()
    {
        add_action("rest_api_init", [$this, "rest_routes"]);
    }

    public function rest_routes()
    {
        register_rest_route("wam/v1", "/intg-smtp", [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"],
        ]);
    }

    public function get($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $list = [
            [
                "name" => "Default",
                "slug" => null,
                "img" => "https://cdn.cdnlogo.com/logos/p/71/php.svg",
                "active" => false,
                "pro" => false,
            ],
            [
                "name" => "Other SMTP",
                "slug" => "other",
                "img" => "https://cdn.cdnlogo.com/logos/m/46/mail-ios.svg",
                "active" => false,
                "pro" => true,
            ],
            /* [ 
                'name' => 'Sendinblue',
                'slug' => 'sendinblue',
                'img' => 'https://www.sendinblue.com/wp-content/themes/sendinblue2019/assets/images/common/logo-color.svg',
                'active' => false,
                'pro' => true,
            ],
            [ 
                'name' => 'SendGrid',
                'slug' => 'sendgrid ',
                'img' => 'https://cdn.cdnlogo.com/logos/s/48/sendgrid.svg',
                'active' => false,
                'pro' => true,
            ],
            [ 
                'name' => 'Mailgun',
                'slug' => 'mailgun',
                'img' => 'https://img001.prntscr.com/file/img001/4cQw2CQQRUep5qE5vFuWpw.png',
                'active' => false,
                'pro' => true,
            ],
            [ 
                'name' => 'Mailtrap',
                'slug' => 'mailtrap',
                'img' => 'https://img001.prntscr.com/file/img001/msNOomOMTX28qqvOi4nEGg.png',
                'active' => false,
                'pro' => true,
            ] */
        ];

        $form_list = [];
        $smtp = get_option("wam_smtp");
        foreach ($list as $value) {
            if ($value["slug"] == $smtp) {
                $value["active"] = true;
            }
            $form_list[] = $value;
        }

        wp_send_json_success($form_list);
    }

    public function get_per()
    {
        return current_user_can("wam_setting");
    }
}
