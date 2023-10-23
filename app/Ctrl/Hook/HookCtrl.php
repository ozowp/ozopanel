<?php

namespace OzoPanel\Ctrl\Hook;

use OzoPanel\Ctrl\Hook\Type\{
    Filter,
    Action
};

/**
 * Action & Filter hook
 *
 * @since 1.0.0
 */
class HookCtrl
{
    public function __construct()
    {
        new Filter();
        new Action();
    }
}
