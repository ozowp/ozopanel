<?php

namespace OzoPanel\Ctrl;

use OzoPanel\Traits\Singleton;
use OzoPanel\Ctrl\Migration\MigrationCtrl;
use OzoPanel\Ctrl\Api\ApiCtrl;
use OzoPanel\Ctrl\Asset\AssetCtrl;
use OzoPanel\Ctrl\Template\TemplateCtrl;
use OzoPanel\Ctrl\Hook\HookCtrl;
use OzoPanel\Ctrl\Integrate\IntegrateCtrl;
use OzoPanel\Ctrl\Assist\AssistCtrl;
use OzoPanel\Ctrl\Meta\MetaCtrl;
use OzoPanel\Ctrl\MenuPage\MenuPageCtrl;
use OzoPanel\Ctrl\Taxonomy\TaxonomyCtrl;
use OzoPanel\Ctrl\Widget\WidgetCtrl;

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
