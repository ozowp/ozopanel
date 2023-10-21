<?php
namespace WAM\Ctrl\MenuPage;

use WAM\Ctrl\MenuPage\Type\{
	Dashboard,
	Welcome
};

class MenuPageCtrl {

	public function __construct() {
		if ( is_admin() ) {
			new Dashboard();
			new Welcome();
		}
	}
}