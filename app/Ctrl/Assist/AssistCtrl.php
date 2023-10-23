<?php
namespace OzoPanel\Ctrl\Assist;

use OzoPanel\Ctrl\Assist\Type\{
	Feedback,
	Link
};

class AssistCtrl {

	public function __construct() {
		new Link();
		new Feedback();
	}
}