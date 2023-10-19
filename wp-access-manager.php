<?php
/**
 * @package   Rakibul Hasan - WP Access Manager
 * @author    Rakibul Hasan <support@wp-access-manager.com>
 * @link      https://wp-access-manager.com
 * @copyright 2023 Rakibul Hasan
 *
 * Plugin Name: WP Access Manager
 * Plugin URI: https://wordpress.org/plugins/wp-access-manager
 * Author: Rakibul Hasan
 * Author URI: https://wp-access-manager.com
 * Version: 1.0.0
 * Description: Manager WP user access
 * Text Domain: wp-access-manager
 * Domain Path: /languages
 *
 */

/**
 * Don't allow call the file directly
 *
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * If WAM class exist return
 *
 * @since 1.0.0
 */
//TODO: this is not working need to check
// if ( function_exists('wam') ) return;

/**
 * WP Access Manager Final Class
 *
 * @since 1.0.0
 * @class WAM The class that holds the entire WAM plugin
 */

final class WAM {

    /**
     * Plugin version
     *
     * @since 1.0.0
     * @var string
     */
    public $version = '1.0.0';

	/**
     * Instance of self
     * @since 1.0.0
     * @var WAM
     */
    private static $instance = null;

    /**
     * Minimum PHP version required
     * @since 1.0.0
     * @var string
     */
    private $min_php = '7.2';

    /**
     * Constructor for the WAM class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {

        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        // new WAM\Ctrl\Install\InstallCtrl();
        add_action('plugins_loaded', [$this, 'on_plugins_loaded'], -1);
        add_action('init', [$this, 'initial'], 1);
    }

	/**
     * Initializes the WAM() class
     *
     * Checks for an existing WAM() instance
     * and if it doesn't find one, create it.
     */
    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define all constants
     * @since 1.0.0
     * @return void
     */
    public function define_constants() {
        $this->define( 'WAM_VERSION', $this->version );
        $this->define( 'WAM_FILE', __FILE__ );
        $this->define( 'WAM_PATH', plugin_dir_path(__FILE__) );
        $this->define( 'WAM_URL', plugins_url( '', __FILE__) );
        $this->define( 'WAM_SLUG', basename( dirname(__FILE__)) );
        $this->define( 'WAM_ASSEST', plugins_url( 'public', __FILE__ ) );
        $this->define( 'WAM_TEMPLATE_DEBUG_MODE', false );
    }

    /**
     * Define constant if not already defined
     *
     * @since 1.0.0
     *
     * @param string      $name
     * @param string|bool $value
     *
     * @return void
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Load in initial
     * @since 1.0.0
     * @return void
     */
    public function initial() {
        do_action('wam_before_init');

        $this->localization_setup();

        new WAM\Ctrl\MainCtrl();

        do_action('wam_init');
    }

    /**
     * Action hook when load WAM
     * @since 1.0.0
     */
    public function on_plugins_loaded() {
        do_action('wam_loaded');
    }

    /**
     * Initialize plugin for localization
     * @since 1.0.0
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wp-access-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or public.
     * @since 1.0.0
     * @return bool
     */
    public function is_request( $type ) {
        switch( $type ) {
            case 'admin':
                return is_admin();
            case 'public':
                return ( !is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
        }
    }

    /**
     * Get the plugin path.
     * @since 1.0.0
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Get the template path.
     * @since 1.0.0
     * @return string
     */
    public function get_template_path() {
        return apply_filters('wam_template_path', 'wam/templates/');
    }

    /**
     * Get the template partial path.
     * @since 1.0.0
     * @return string
     */
    public function get_partial_path( $path = null, $args = []) {
        WAM\Helper\Fns::get_template_part( 'partial/' . $path, $args );
    }

    /**
     * Get asset location
     *
     * @param $file
     * @since 1.0.0
     * @return string
     */
    public function get_asset_uri($file) {
        $file = ltrim($file, '/');

        return trailingslashit(WAM_URL . '/public') . $file;
    }

    /**
     * @param $file
     * @since 1.0.0
     * @return string
     */
    public function render($viewName, $args = array(), $return = false) {
        $path = str_replace(".", "/", $viewName);
        $viewPath = WAM_PATH . '/view/' . $path . '.php';
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
     * Get options field value
     *
     * @since 1.0.0
     * @return void
     */
    public function get_options() {

        $option_field = func_get_args()[0];
        $result = get_option( $option_field );
        $func_args = func_get_args();
        array_shift( $func_args );

        foreach ( $func_args as $arg ) {
            if ( is_array($arg) ) {
                if ( !empty( $result[$arg[0]] ) ) {
                    $result = $result[$arg[0]];
                } else {
                    $result = $arg[1];
                }
            } else {
                if ( !empty($result[$arg] ) ) {
                    $result = $result[$arg];
                } else {
                    $result = null;
                }
            }
        }
        return $result;
    }

    /**
     * Get preset data
     *
     * @since 1.0.0
     * @return void
     */
    public function get_default()
    {
        $preset = new WAM\Helper\Preset;
        $result = $preset->data();
        $func_args = func_get_args();

        foreach ($func_args as $arg) {
            if (is_array($arg)) {
                if (!empty($result[$arg[0]])) {
                    $result = $result[$arg[0]];
                } else {
                    $result = $arg[1];
                }
            } else {
                if (!empty($result[$arg])) {
                    $result = $result[$arg];
                } else {
                    $result = null;
                }
            }
        }
        return $result;
    }

    /**
     * Check pro version
     * @since 1.0.0
     * @return void
     */
    public function wage()
    {
        return function_exists('wamp') && wamp()->wage();
    }
}

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return WAM
 */
function wam() {
    return WAM::init();
}
wam(); // Run WAM Plugin