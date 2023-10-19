<?php

namespace WAM\Ctrl\Hook;

use WAM\Ctrl\Hook\Type\Filter;
use WAM\Ctrl\Hook\Type\Action\ActionCtrl;

class HookCtrl
{
    public function __construct()
    {
        new Filter();
        new ActionCtrl();
    }
}
