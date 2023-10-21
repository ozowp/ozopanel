<?php

namespace WAM\Ctrl\Api;

use WAM\Ctrl\Api\Type\Action;
use WAM\Ctrl\Api\Type\Setting;

/**
 * Register all custom API
 * @since 1.0.0
 */

class ApiCtrl
{

	public function __construct()
	{
		add_action("rest_api_init", function() {
			Action::init()->routes();
			Setting::init()->routes();
		});

		//For plain permalink API support
		add_filter('rest_request_before_callbacks', [$this, 'rest_request_filter'], 10, 3);
	}

	/**
	 * For plain permalink API support
	 * @since 1.0.0
	 */
	public function rest_request_filter($resp, $handler, $req) {
		$permalink_structure = get_option('permalink_structure');
        if ( $permalink_structure === '' ) {
			$params = $req->get_params();
			if ( isset($params['rest_route']) ) {
				$query_string = parse_url($params['rest_route'], PHP_URL_QUERY);
				// Parse the query string into an array of parameters
				parse_str($query_string, $param_form_args);
				foreach( $param_form_args as $key => $val) {
					$req->set_param( $key, $val);
				}
			}
		}
		return $req;
	}
}
