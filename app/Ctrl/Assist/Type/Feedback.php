<?php
namespace OzoPanel\Ctrl\Assist\Type;

use OzoPanel\Helper\Info;

class Feedback {

    private $api = 'https://ozopanel.com/wp-json/ozopanela/v1/';

	public function __construct() {
		add_action('wp_ajax_ozopanel_deactivate_feedback', [ $this, 'deactivate' ]);
	}

	/**
     *
     * @since 1.0.0
     * @access public
     */
    public function deactivate()
    {
        if ( !isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], '_ozopanel_deactivate_nonce') ) {
            wp_send_json_error();
        }

        $reason_key = isset($_POST['reason_key']) ? sanitize_text_field($_POST['reason_key']) : '';
        $reason = isset($_POST["reason"]) ? sanitize_text_field($_POST["reason"]) : '';
        $data_collect = isset($_POST["data_collect"]) ? sanitize_text_field($_POST["data_collect"]) : '';

        $data = [];
        if ( $data_collect ) {
            $info = new Info;
            $data = $info->name_email();
        }
        $data['reason_key'] = $reason_key;
        $data['reason'] = $reason;
        $data['version'] = OZOPANEL_VERSION;
        $data['package'] = 'free';

        wp_remote_post( $this->api . 'uninstaller', [
            'timeout' => 0.01,
            'body' => $data,
            'blocking'  => false,
            'sslverify'   => false,
        ] );

        wp_send_json_success();
    }
}
