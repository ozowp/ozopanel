<?php
namespace OzoPanel\Controllers\Assist;

use OzoPanel\Controllers\Assist\Types\{
	Feedback,
	Link
};

/**
 * Plugin user helper link and data
 *
 * @since 0.1.0
 */
class AssistCtrl {

	public function __construct() {
		new Link();
		new Feedback();
	}
}
