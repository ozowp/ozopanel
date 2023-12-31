<?php
namespace OzoPanel\Ctrl\Assist;

use OzoPanel\Ctrl\Assist\Type\{
	Feedback,
	Link
};

/**
 * Plugin user helper link and data
 *
 * @since 1.0.0
 */
class AssistCtrl {

	public function __construct() {
		new Link();
		new Feedback();
	}
}