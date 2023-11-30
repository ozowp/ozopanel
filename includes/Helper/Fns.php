<?php
namespace OzoPanel\Helper;

/**
 * Helper functions
 *
 * @since 1.0.0
 * @class Fns
 */
class Fns
{

    /**
     *  API request permission Check
     *
     * @since 1.0.0
     */
    public static function gate($string, $method = '')
    {
        // when run php test
        //return true;
        return current_user_can('administrator');
    }

    /**
     *  String to slug convert
     *
     * @since 1.0.0
     */
    public static function slugify($string)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }

    /**
     * Get gravatar image by email
     * @since 1.0.0
     */
    public static function get_gravatar($email, $size = 40)
    {
        $hash = md5(strtolower(trim($email)));
        return sprintf('https://www.gravatar.com/avatar/%s?d=blank&s=%s', $hash, $size);
    }

    /**
     * Get option value
     *
     * @since 1.0.0
     */
    public static function option_value($func_get_args)
    {
        $option_field = $func_get_args[0];
        $data = get_option($option_field);
        $func_args = $func_get_args;
        array_shift($func_args);

        return self::nested_array($data, $func_args);
    }

    /**
     * Get preset value
     *
     * @since 1.0.0
     */
    public static function preset_value($func_get_args)
    {
        $preset = new \OzoPanel\Helper\Preset;
        $data = $preset->data();
        return self::nested_array($data, $func_get_args);
    }

    /**
     * Access Nested Array
     * By this function can access nested array like: get_preset('key', 'associate_key');
     * @since 1.0.0
     */
    public static function nested_array($data, $func_args)
    {
        foreach ($func_args as $arg) {
            if (is_array($arg)) {
                if (!empty($data[$arg[0]])) {
                    $data = $data[$arg[0]];
                } else {
                    $data = $arg[1];
                }
            } else {
                if (!empty($data[$arg])) {
                    $data = $data[$arg];
                } else {
                    $data = null;
                }
            }
        }
        return $data;
    }

    /**
     * Sanitize input data
     *
     * @since 1.0.0
     */
    public function sanitizeInput($value, $type = 'text', $array_map = 'text')
    {
        $new_value = null;
        if (isset($value)) {
            if ($type == 'text') {
                $new_value = sanitize_text_field($value);
            } elseif ($type == 'email') {
                $new_value = strtolower(sanitize_email($value));
            } elseif ($type == 'url') {
                $new_value = esc_url_raw($value);
            } elseif ($type == 'textarea') {
                // Allowing some basic HTML tags in the textarea
                $allowed_tags = array(
                    'a' => array(
                        'href' => array(),
                        'title' => array()
                    ),
                    'br' => array(),
                    'em' => array(),
                    'strong' => array()
                );
                $new_value = wp_kses($value, $allowed_tags);
            } elseif ($type == 'array_map') {
                $sanitize = '';
                switch ($array_map) {
                    case 'text':
                        $sanitize = 'sanitize_text_field';
                        break;

                    case 'boolean':
                        $sanitize = 'rest_sanitize_boolean';
                        break;
                }
                $new_value = array_map($sanitize, $value);
            }
        }
        return $new_value;
    }

    /**
     * Sanitize output data
     *
     * @since 1.0.0
     */
    public function sanitizeOutput($value, $type = 'text')
    {
        $new_value = null;
        if ($value) {
            if ($type == 'text') {
                $new_value = esc_html(stripslashes($value));
            } elseif ($type == 'url') {
                $new_value = esc_url(stripslashes($value));
            } elseif ($type == 'textarea') {
                $new_value = esc_textarea(stripslashes($value));
            } else {
                $new_value = esc_html(stripslashes($value));
            }
        }
        return $new_value;
    }

    /**
     * Get any custom template name by slug
     * @since 1.0.0
     */
    public static function template_page_url_by_slug($slug)
    {
        $page = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $slug . '-template.php'
        ));
        if (!empty($page)) {
            return get_permalink($page[0]->ID);
        } else {
            return '';
        }
    }

    /**
     * Load template from plugin view folder by path
     *
     * @param $file
     * @since 1.0.0
     * @return string
     */
    public static function render($file_name, $args = array(), $return = false) {
        $path = str_replace(".", "/", $file_name);
        $viewPath = OZOPANEL_PATH . '/view/' . $path . '.php';
        if ( !file_exists($viewPath) ) {
            return;
        }

        if ( $args ) {
            extract($args);
        }

        if ( $return ) {
            ob_start();
            include $viewPath;

            return ob_get_clean();
        }
        include $viewPath;
    }

    /**
     * Convert PHP to JS moment format
     * @since 1.0.0
     */
    public static function phpToMomentFormat($format)
    {
        $replacements = [
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'o',
            'w' => 'e',
            'z' => 'DDD',
            'W' => 'W',
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '', // no equivalent
            'L' => '', // no equivalent
            'o' => 'YYYY',
            'Y' => 'YYYY',
            'y' => 'YY',
            'a' => 'a',
            'A' => 'A',
            'B' => '', // no equivalent
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'SSS',
            'e' => 'zz', // deprecated since version 1.6.0 of moment.js
            'I' => '', // no equivalent
            'O' => '', // no equivalent
            'P' => '', // no equivalent
            'T' => '', // no equivalent
            'Z' => '', // no equivalent
            'c' => '', // no equivalent
            'r' => '', // no equivalent
            'U' => 'X',
        ];
        $momentFormat = strtr($format, $replacements);
        return $momentFormat;
    }

    /**
     * Get template part (for templates like the shop-loop).
     *
     * @param mixed  $slug Template slug.
     * @param string $name Template name (default: '').
     * @since 1.0.0
     */
    public static function get_template_part($slug, $args = null, $include = true)
    {
        // load template from theme if exist
        $template = locate_template(
            array(
                "{$slug}.php",
                ozopanel()->get_template_path() . "{$slug}.php"
            )
        );

        // load template from pro plugin if exist
        if (!$template && function_exists('ozopanelp')) {
            $fallback = ozopanel()->plugin_path() . "-pro" . "/templates/{$slug}.php";
            $template = file_exists($fallback) ? $fallback : '';
        }

        // load template from current plugin if exist
        if (!$template) {
            $fallback = ozopanel()->plugin_path() . "/templates/{$slug}.php";
            $template = file_exists($fallback) ? $fallback : '';
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('ozopanel_get_template_part', $template, $slug);

        if ($template) {
            if (!empty($args) && is_array($args)) {
                extract($args); // @codingStandardsIgnoreLine
            }

            // load_template($template, false, $args);
            if ($include) {
                include $template;
            } else {
                return $template;
            }
        }
    }
}
