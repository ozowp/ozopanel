<?php
namespace OzoPanel\Hooks\Types\Action;

use OzoPanel\Hooks\Types\Action\Types\AdminColumn;
use OzoPanel\Hooks\Types\Action\Types\NavMenu;

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
