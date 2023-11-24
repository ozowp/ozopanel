<?php
namespace OzoPanel\Ctrl\Hook\Type\Action;

use OzoPanel\Ctrl\Hook\Type\Action\Type\AdminColumn;
use OzoPanel\Ctrl\Hook\Type\Action\Type\NavMenu;

/**
 * WP Action hook
 *
 * @since 1.0.0
 */
class ActionCtrl
{
    public function __construct()
    {
        new NavMenu();
        new AdminColumn();
    }
}
