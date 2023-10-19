<?php 
namespace WAM\Ctrl\Integrate;

use WAM\Ctrl\Integrate\Form\FormCtrl;
use WAM\Ctrl\Integrate\Smtp\SmtpCtrl;

class IntegrateCtrl
{
    public function __construct()
    {
        new FormCtrl();
        new SmtpCtrl();
    } 
}
