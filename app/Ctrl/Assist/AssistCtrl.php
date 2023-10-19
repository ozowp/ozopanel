<?php 
namespace WAM\Ctrl\Assist;

use WAM\Ctrl\Assist\Type\Feedback;
use WAM\Ctrl\Assist\Type\Link;

class AssistCtrl {
	
	public function __construct() {   
		new Link(); 
		new Feedback();
	} 
}