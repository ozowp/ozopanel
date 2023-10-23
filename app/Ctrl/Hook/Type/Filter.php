<?php
namespace OzoPanel\Ctrl\Hook\Type;
/**
 * WP Filter hook
 *
 * @since 1.0.0
 */
class Filter
{
    public function __construct()
    {
        add_filter("body_class", [$this, "body_class"]);
        add_filter("admin_body_class", [$this, "admin_body_class"]);
    }

    function body_class($classes)
    {
        if (
            is_page_template([
                "test-template.php"
            ])
        ) {
            $classes[] = "ozopanel";
            $classes[] = get_option("template") . "-theme";
        }
        return $classes;
    }

    function admin_body_class($classes)
    {
        if (
            (isset($_GET["page"]) && $_GET["page"] == "ozopanel") ||
            (isset($_GET["page"]) && $_GET["page"] == "ozopanel-welcome")
        ) {
            $classes .= " ozopanel " . get_option("template") . "-theme";
        }

        return $classes;
    }

}
