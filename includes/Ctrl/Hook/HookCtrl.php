<?php

namespace OzoPanel\Ctrl\Hook;

use OzoPanel\Ctrl\Hook\Type\{
    Action\ActionCtrl,
    Filter\FilterCtrl
};

/**
 * Action & Filter hook
 *
 * @since 0.1.0
 */
class HookCtrl {

    public function __construct() {
        new ActionCtrl();
        new FilterCtrl();
    }
}
