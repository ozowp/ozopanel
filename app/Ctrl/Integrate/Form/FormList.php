<?php
namespace OzoPanel\Ctrl\Integrate\Form;

use OzoPanel\Helper\Fns;

class FormList
{
    public function __construct()
    {
        add_action("rest_api_init", [$this, "rest_routes"]);
    }

    public function rest_routes()
    {
        register_rest_route('ozopanel/v1', '/intg-form', [
            'methods' => 'GET',
            'callback' => [$this, 'get'],
            'permission_callback' => [$this, 'get_per']
        ]);
    }

    public function get($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $ninja_from = false;
        if (class_exists("Ninja_Forms")) {
            $ninja_forms_version = get_option("ninja_forms_version", "0.0.0");
            if (version_compare($ninja_forms_version, "3", ">=")) {
                $ninja_from = true;
            }
        }

        $form_list = [
            [
                "active" => class_exists("WPCF7_ContactForm"),
                "name" => "Contact Form 7",
                "slug" => "contact_form_7",
                "img" => "https://ps.w.org/contact-form-7/assets/icon.svg",
                "pro" => true,
            ],
            [
                "active" => class_exists("WPForms"),
                "name" => "WPForms",
                "slug" => "wpforms",
                "img" => "https://ps.w.org/wpforms-lite/assets/icon.svg",
                "pro" => true,
            ],
            [
                "active" => $ninja_from,
                "name" => "Ninja Forms",
                "slug" => "ninja_forms",
                "img" => "https://ps.w.org/ninja-forms/assets/icon-128x128.png",
                "pro" => true,
            ],
            [
                "active" => class_exists("GFForms"),
                "name" => "Gravity Forms",
                "slug" => "gravity_forms",
                "img" =>
                    "https://s38924.pcdn.co/wp-content/themes/gfcom-voyager/dist/images/logo-black.svg",
                "pro" => true,
            ],
            [
                "active" => defined("FLUENTFORM"),
                "name" => "Fluent Forms",
                "slug" => "fluent_forms",
                "img" => "https://ps.w.org/fluentform/assets/icon-128x128.png",
                "pro" => true,
            ],

            /* [
                'active' => class_exists( 'Popup_Maker' ),
                'name' => 'Popup Maker',
                'slug' => 'popup_maker',
                'img' => 'https://ps.w.org/popup-maker/assets/icon-128x128.jpg',
                'pro' => true,
            ],
            [
                'active' => defined( 'SG_POPUP_TEXT_DOMAIN' ),
                'name' => 'Popup Builder',
                'slug' => 'popup_builder',
                'img' => 'https://ps.w.org/popup-builder/assets/icon-128x128.gif',
                'pro' => true,
            ],
            [
                'active' => class_exists( 'Forminator' ),
                'name' => 'Forminator Forms',
                'slug' => 'forminator_forms',
                'img' => 'https://ps.w.org/forminator/assets/icon-128x128.png',
                'pro' => true,
            ],
            [
                'active' => defined( 'HAPPYFORMS_VERSION' ),
                'name' => 'Happy Forms',
                'slug' => 'happy_forms',
                'img' => 'https://ps.w.org/happyforms/assets/icon-128x128.png',
                'pro' => true,
            ] */
        ];
        $data = [];
        $data['form'] = $form_list;
        $data['lead_field'] = Fns::custom_field('lead');
        wp_send_json_success($data);
    }

    public function get_per()
    {
        return current_user_can("ozopanel_setting");
    }
}
