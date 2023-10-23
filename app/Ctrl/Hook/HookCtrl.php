<?php

namespace OzoPanel\Ctrl\Hook;

use OzoPanel\Ctrl\Hook\Type\{
    Filter,
    Action
};

class HookCtrl
{
    public function __construct()
    {
        new Filter();
        new Action();
    }
}
