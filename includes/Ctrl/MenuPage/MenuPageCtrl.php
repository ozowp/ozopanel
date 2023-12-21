<?php
namespace OzoPanel\Ctrl\MenuPage;

use OzoPanel\Ctrl\MenuPage\Type\{
	Dashboard,
};

class MenuPageCtrl {

	public function __construct() {
		if ( is_admin() ) {
			new Dashboard();
		}
	}
}