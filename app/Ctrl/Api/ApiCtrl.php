<?php

namespace WAM\Ctrl\Api;

use WAM\Ctrl\Api\Type\Action;
use WAM\Ctrl\Api\Type\Business;
use WAM\Ctrl\Api\Type\Client;
use WAM\Ctrl\Api\Type\Contact;
use WAM\Ctrl\Api\Type\Person;
use WAM\Ctrl\Api\Type\Dashbaord;
use WAM\Ctrl\Api\Type\Deal;
use WAM\Ctrl\Api\Type\Email;
use WAM\Ctrl\Api\Type\File;
use WAM\Ctrl\Api\Type\Form;
use WAM\Ctrl\Api\Type\EstInv;
use WAM\Ctrl\Api\Type\Lead;
use WAM\Ctrl\Api\Type\Media;
use WAM\Ctrl\Api\Type\Note;
use WAM\Ctrl\Api\Type\Org;
use WAM\Ctrl\Api\Type\Payment;
use WAM\Ctrl\Api\Type\PaymentProcess;
use WAM\Ctrl\Api\Type\Project;
use WAM\Ctrl\Api\Type\Setting;
use WAM\Ctrl\Api\Type\Task;
use WAM\Ctrl\Api\Type\Taxonomy;
use WAM\Ctrl\Api\Type\Team;
use WAM\Ctrl\Api\Type\Webhook;
use WAM\Ctrl\Api\Type\SaveForNext;

/**
 * Register all custom API
 * @since 1.0.0
 */

class ApiCtrl
{

	public function __construct()
	{
		add_action("rest_api_init", function() {
			// Lead::getInstance()->register_routes();
			// Deal::getInstance()->register_routes();
			// Task::getInstance()->register_routes();
			// Note::getInstance()->register_routes();
			// File::getInstance()->register_routes();
			// Client::getInstance()->register_routes();
			// Person::getInstance()->register_routes();
			// Org::getInstance()->register_routes();
			// Contact::getInstance()->register_routes();
			// Project::getInstance()->register_routes();
			// EstInv::getInstance()->register_routes();
			// Business::getInstance()->register_routes();
			// Email::getInstance()->register_routes();
			// Media::getInstance()->register_routes();
			// Payment::getInstance()->register_routes();
			// PaymentProcess::getInstance()->register_routes();
			// Dashbaord::getInstance()->register_routes();
			// Action::getInstance()->register_routes();
			// Taxonomy::getInstance()->register_routes();
			// Form::getInstance()->register_routes();
			// Webhook::getInstance()->register_routes();
			// Setting::getInstance()->register_routes();
			// Team::getInstance()->register_routes();
			// SaveForNext::getInstance()->register_routes();
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
