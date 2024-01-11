<?php
namespace OzoPanel\Ctrl\Assist\Type;

use OzoPanel\Helper\Info;

class Feedback {

    private $api = 'https://ozopanel.com/wp-json/feedback/v1/';

	public function __construct() {
		add_action( 'wp_ajax_ozopanel_deactivate_feedback', array( $this, 'deactivate' ) );
	}

	/**
     * When deactivate plugin get feedback from user
     *
     * @since 1.0.0
     * @access public
     */
    public function deactivate() {

        // Verify this came from our screen and with proper authorization.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_ozopanel_deactivate_nonce' ) ) {
            return new \WP_REST_Response(
                array(
                    'success' => false,
                    'message' => esc_html__( 'Nonce verification failed', 'ozopanel' )
                ), 403
            );
        }

        $reason_key = isset( $_POST['reason_key'] ) ? sanitize_text_field( $_POST['reason_key'] ) : '';
        $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
        $data_collect = isset( $_POST['data_collect'] ) ? sanitize_text_field( $_POST['data_collect'] ) : '';

        $data = array();
        if ( $data_collect ) {
            $info = new Info();
            $data = $info->name_email();
        }
        $data['reason_key'] = $reason_key;
        $data['reason'] = $reason;
        $data['version'] = OZOPANEL_VERSION;
        $data['package'] = 'free';

        wp_remote_post(
            $this->api . 'uninstaller', array(
				'timeout' => 0.01,
				'body' => $data,
				'blocking'  => false,
				'sslverify'   => false,
            )
        );

        return new \WP_REST_Response(
            array(
				'success'  => true,
            ), 200
        );
    }
}
