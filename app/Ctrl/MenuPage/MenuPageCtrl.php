<?php
namespace OzoPanel\Ctrl\MenuPage;

use OzoPanel\Ctrl\MenuPage\Type\{
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