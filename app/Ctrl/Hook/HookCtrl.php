<?php

namespace WAM\Ctrl\Hook;

use WAM\Ctrl\Hook\Type\{
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
