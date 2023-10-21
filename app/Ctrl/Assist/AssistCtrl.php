<?php
namespace WAM\Ctrl\Assist;

use WAM\Ctrl\Assist\Type\{
	Feedback,
	Link
};

class AssistCtrl {

	public function __construct() {
		new Link();
		new Feedback();
	}
}