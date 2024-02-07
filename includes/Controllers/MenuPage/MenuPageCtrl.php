<?php
namespace OzoPanel\Controllers\MenuPage;

use OzoPanel\Controllers\MenuPage\Types\{
	AdminMenu,
	Welcome
};

class MenuPageCtrl {

	public function __construct() {
		if ( is_admin() ) {
			new AdminMenu();
			new Welcome();
		}
	}
}
