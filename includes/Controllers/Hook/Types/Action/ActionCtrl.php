<?php
namespace OzoPanel\Controllers\Hook\Types\Action;

use OzoPanel\Controllers\Hook\Types\Action\Types\AdminColumn;
use OzoPanel\Controllers\Hook\Types\Action\Types\NavMenu;

/**
 * WP Action hook
 *
 * @since 0.1.0
 */
class ActionCtrl {

    public function __construct() {
        new NavMenu();
        new AdminColumn();
    }
}
