<?php

namespace OzoPanel\Ctrl\Hook;

use OzoPanel\Ctrl\Hook\Type\Action\ActionCtrl;
use OzoPanel\Ctrl\Hook\Type\Filter\FilterCtrl;

/**
 * Action & Filter hook
 *
 * @since 1.0.0
 */
class HookCtrl {

    public function __construct() {
        new ActionCtrl();
        new FilterCtrl();
    }
}
