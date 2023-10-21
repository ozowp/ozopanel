<?php
namespace WAM\Ctrl\Hook\Type;

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
            $classes[] = "wam";
            $classes[] = get_option("template") . "-theme";
        }
        return $classes;
    }

    function admin_body_class($classes)
    {
        if (
            (isset($_GET["page"]) && $_GET["page"] == "wam") ||
            (isset($_GET["page"]) && $_GET["page"] == "wam-welcome")
        ) {
            $classes .= " wam " . get_option("template") . "-theme";
        }

        return $classes;
    }

}
