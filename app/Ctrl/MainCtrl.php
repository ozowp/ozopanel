<?php

namespace WAM\Ctrl;

use WAM\Traits\Singleton;
use WAM\Ctrl\Migration\MigrationCtrl;
use WAM\Ctrl\Api\ApiCtrl;
use WAM\Ctrl\Asset\AssetCtrl;
use WAM\Ctrl\Template\TemplateCtrl;
use WAM\Ctrl\Hook\HookCtrl;
use WAM\Ctrl\Integrate\IntegrateCtrl;
use WAM\Ctrl\Assist\AssistCtrl;
use WAM\Ctrl\Meta\MetaCtrl;
use WAM\Ctrl\MenuPage\MenuPageCtrl;
use WAM\Ctrl\Taxonomy\TaxonomyCtrl;
use WAM\Ctrl\Widget\WidgetCtrl;

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
        // new TaxonomyCtrl();
        new MenuPageCtrl();
        new AssetCtrl();
        new AssistCtrl();
        // new TemplateCtrl();
        // new WidgetCtrl();
        new HookCtrl();
        // new MetaCtrl();
        new ApiCtrl();
        // new IntegrateCtrl();
    }
}
