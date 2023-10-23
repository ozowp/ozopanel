<?php 
namespace OzoPanel\Ctrl\Integrate;

use OzoPanel\Ctrl\Integrate\Form\FormCtrl;
use OzoPanel\Ctrl\Integrate\Smtp\SmtpCtrl;

class IntegrateCtrl
{
    public function __construct()
    {
        new FormCtrl();
        new SmtpCtrl();
    } 
}
