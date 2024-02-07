<?php

namespace OzoPanel\Controllers\Hook;

use OzoPanel\Controllers\Hook\Types\{
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
