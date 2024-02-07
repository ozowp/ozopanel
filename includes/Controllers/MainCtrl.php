<?php

namespace OzoPanel\Controllers;

use OzoPanel\Traits\Singleton;

use OzoPanel\Controllers\{
    Api\ApiCtrl,
    Asset\AssetCtrl,
    Template\TemplateCtrl,
    Hook\HookCtrl,
    Assist\AssistCtrl,
    MenuPage\MenuPageCtrl
};

/**
 * Main controller
 *
 * @since 0.1.0
 *
 * All the others controller load here
 */
class MainCtrl {

    use Singleton;

    public function __construct() {
        new MenuPageCtrl();
        new AssetCtrl();
        new AssistCtrl();
        new HookCtrl();
        new ApiCtrl();
    }
}
