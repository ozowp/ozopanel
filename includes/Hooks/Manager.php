<?php

namespace OzoPanel\Hooks;

use OzoPanel\Hooks\Types\{
    Action\ActionCtrl,
    Filter\FilterCtrl
};

/**
 * Action & Filter hook
 *
 * @since 0.1.0
 */
class Manager {

    public function __construct() {
        new ActionCtrl();
        new FilterCtrl();
    }
}
