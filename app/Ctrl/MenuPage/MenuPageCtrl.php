<?php
namespace WAM\Ctrl\MenuPage;

use WAM\Ctrl\MenuPage\Type\Dashboard;
use WAM\Ctrl\MenuPage\Type\Welcome;

class MenuPageCtrl {

	public function __construct() {
		if ( is_admin() ) {
			new Dashboard();
			new Welcome();
		}
	}
}