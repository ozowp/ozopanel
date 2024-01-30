<?php
namespace OzoPanel\Ctrl\MenuPage;

use OzoPanel\Ctrl\MenuPage\Type\{
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
