<?php
namespace OzoPanel\Ctrl\Hook\Type\Filter;

use OzoPanel\Ctrl\Hook\Type\Filter\Type\NavMenu;

/**
 * WP Filter hook
 *
 * @since 1.0.0
 */
class FilterCtrl
{
    public function __construct()
    {
        new NavMenu();
        add_filter("admin_body_class", [$this, "admin_body_class"]);
    }

    function admin_body_class($classes)
    {
        if (
            (isset($_GET["page"]) && sanitize_text_field( $_GET["page"] ) == "ozopanel") 
        ) {
            $classes .= " ozopanel ";
        }

        return $classes;
    }

}
