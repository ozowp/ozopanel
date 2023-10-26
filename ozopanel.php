<?php
/**
 * @package   WpOzo - OzoPanel
 * @author    WpOzo <contact@wpozo.com>
 * @link      https://wpozo.com
 * @copyright 2023 WpOzo
 *
 * Plugin Name: OzoPanel
 * Plugin URI: https://wordpress.org/plugins/ozopanel
 * Author: WpOzo
 * Author URI: https://wpozo.com
 * Version: 1.0.0
 * Description: Manager WP Access
 * Text Domain: ozopanel
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
 * OzoPanel Final Class
 *
 * @since 1.0.0
 * @class OzoPanel The class that holds the entire OzoPanel plugin
 */

use OzoPanel\Helper\Fns;

final class OzoPanel {

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
     * @var OzoPanel
     */
    private static $instance = null;

    /**
     * Minimum PHP version required
     * @since 1.0.0
     * @var string
     */
    private $min_php = '7.2';

    /**
     * Constructor for the OzoPanel class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {

        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        // new OzoPanel\Ctrl\Install\InstallCtrl();
        add_action('plugins_loaded', [$this, 'on_plugins_loaded'], -1);
        add_action('init', [$this, 'initial'], 1);
    }

	/**
     * Initializes the OzoPanel() class
     *
     * Checks for an existing OzoPanel() instance
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
        $this->define( 'OZOPANEL_VERSION', $this->version );
        $this->define( 'OZOPANEL_FILE', __FILE__ );
        $this->define( 'OZOPANEL_PATH', plugin_dir_path(__FILE__) );
        $this->define( 'OZOPANEL_URL', plugins_url( '', __FILE__) );
        $this->define( 'OZOPANEL_SLUG', basename( dirname(__FILE__)) );
        $this->define( 'OZOPANEL_ASSEST', plugins_url( 'dist', __FILE__ ) );
        $this->define( 'OZOPANEL_SCRIPT_DEBUG', false );
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
        do_action('ozopanel_before_init');

        $this->localization();

        OzoPanel\Ctrl\MainCtrl::init();

        do_action('ozopanel_init');
    }

    /**
     * Action hook when load OzoPanel
     * @since 1.0.0
     */
    public function on_plugins_loaded() {
        do_action('ozopanel_loaded');
    }

    /**
     * Initialize plugin for localization
     * @since 1.0.0
     * @uses load_plugin_textdomain()
     */
    public function localization() {
        load_plugin_textdomain( 'ozopanel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
        return apply_filters('ozopanel_template_path', 'ozopanel/templates/');
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

        return trailingslashit(OZOPANEL_URL . '/dist') . $file;
    }

    /**
     * Load template from plugin view folder by path
     *
     * @param $file
     * @since 1.0.0
     * @return string
     */
    public function render($path, $args = array(), $return = false) {
        return Fns::render( $path, $args, $return);
    }

    /**
     * Get options data
     *
     * @since 1.0.0
     * @return void
     */
    public function get_option() {
        return Fns::option_value( func_get_args() );
    }

    /**
     * Get preset data
     *
     * @since 1.0.0
     * @return void
     */
    public function get_preset() {
        return Fns::preset_value( func_get_args() );
    }

    /**
     * If plain permalink pass args in different way otherwise API not working
     * @since 1.0.0
     * @return string
     */
    public function plain_route() {
        $permalink_structure = get_option('permalink_structure');
        return $permalink_structure === '' ? '/(?P<args>.*)' : '';
    }

    /**
     * Check pro version
     * @since 1.0.0
     * @return void
     */
    public function gate()
    {
        return function_exists('ozopanelp') && ozopanelp()->gate();
    }
}

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return OzoPanel
 */
function ozopanel() {
    return OzoPanel::init();
}
ozopanel(); // Run OzoPanel Plugin