<?php

namespace OzoPanel\Ctrl;

use OzoPanel\Traits\Singleton;
use OzoPanel\Ctrl\Migration\MigrationCtrl;
use OzoPanel\Ctrl\Api\ApiCtrl;
use OzoPanel\Ctrl\Asset\AssetCtrl;
use OzoPanel\Ctrl\Template\TemplateCtrl;
use OzoPanel\Ctrl\Hook\HookCtrl;
use OzoPanel\Ctrl\Assist\AssistCtrl;
use OzoPanel\Ctrl\MenuPage\MenuPageCtrl;

/**
 * Main controller
 * All the others controller load here
 * @since 1.0.0
 */
class MainCtrl
{
    use Singleton;

    public function __construct()
    {
        // new MigrationCtrl();
        new MenuPageCtrl();
        new AssetCtrl();
        new AssistCtrl();
        // new TemplateCtrl();
        new HookCtrl();
        new ApiCtrl();
    }
}
