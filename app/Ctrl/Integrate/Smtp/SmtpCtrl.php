<?php 
namespace WAM\Ctrl\Integrate\Smtp;

use WAM\Ctrl\Integrate\Smtp\SmtpList; 

class SmtpCtrl
{ 
	public function __construct()
	{ 
		new SmtpList(); 
	}
}
