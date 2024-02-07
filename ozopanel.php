<?php
/**
 * OzoPanel - A customization plugin for WordPress
 *
 * @package   WpOzo - OzoPanel
 * @author    WpOzo <contact@wpozo.com>
 * @link      https://wpozo.com
 * @copyright 2023 WpOzo
 *
 * @wordpress-plugin
 * Plugin Name:       OzoPanel
 * Plugin URI:        https://wordpress.org/plugins/ozopanel
 * Description:       A customization plugin for WordPress
 * Version:           0.1.0
 * Author:            WpOzo
 * Author URI:        https://wpozo.com
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Tested up to:      6.4
 * Text Domain:       ozopanel
 * Domain Path:       /languages
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Don't allow call the file directly
 *
 * @since 0.1.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * OzoPanel Final Class
 *
 * @since 0.1.0
 *
 * @class OzoPanel The class that holds the entire OzoPanel plugin
 */

final class OzoPanel {


    /**
     * Plugin version
     *
     * @since 0.1.0
     *
     * @var string
     */
    private const VERSION = '0.1.0';

    /**
     * Instance of self
     *
     * @since 0.1.0
     *
     * @var OzoPanel
     */
    private static $instance = null;

    /**
     * Minimum PHP version required
     *
     * @since 0.1.0
     *
     * @var string
     */
    private const MIN_PHP = '7.4';

    /**
     * Constructor for the OzoPanel class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {

        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        add_action( 'wp_loaded', [ $this, 'flush_rewrite_rules' ] );

        $this->init_plugin();
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
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'OZOPANEL_VERSION', self::VERSION );
        define( 'OZOPANEL_FILE', __FILE__ );
        define( 'OZOPANEL_DIR', __DIR__ );
        define( 'OZOPANEL_PATH', plugin_dir_path( OZOPANEL_FILE ) );
        define( 'OZOPANEL_URL', plugins_url( '', OZOPANEL_FILE ) );
        define( 'OZOPANEL_SLUG', basename( OZOPANEL_DIR ) );
        define( 'OZOPANEL_TEMPLATE_PATH', OZOPANEL_PATH . '/templates' );
        define( 'OZOPANEL_BUILD', OZOPANEL_URL . '/build' );
        define( 'OZOPANEL_ASSETS', OZOPANEL_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugins are loaded.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function init_plugin() {

        do_action( 'ozopanel_before_init' );

        $this->includes();
        $this->init_hooks();

        // Fires after the plugin is loaded.
        do_action( 'ozopanel_init' );
    }

    /**
     * Include the required files.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function includes() {
        // Common classes
        OzoPanel\Controllers\MainCtrl::init();
    }

    /**
     * Initialize the hooks.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function init_hooks() {

        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @since 0.1.0
     *
     * @uses load_plugin_textdomain()
     *
     * @return void
     */
    public function localization_setup() {
        load_plugin_textdomain( 'ozopanel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        if ( is_admin() ) {
            // Load wp-script translation for ozopanel-dashboard
            wp_set_script_translations( 'ozopanel-dashboard', 'ozopanel', plugin_dir_path( __FILE__ ) . 'languages' );
        }
    }

    /**
     * Activating the plugin.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function activate() {
        // Run the installer to create necessary migrations and seeders.
    }

    /**
     * Placeholder for deactivation function.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function deactivate() {
        // Remove unnecessary data when deactivate
    }

    /**
     * Flush rewrite rules after plugin is activated.
     *
     * Nothing being added here yet.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        // fix rewrite rules
    }

    /**
     * What type of request is this?
     *
     * @since 0.1.0
     *
     * @param string $type admin, ajax, cron or public.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined( 'DOING_AJAX' );

            case 'rest':
                return defined( 'REST_REQUEST' );

            case 'cron':
                return defined( 'DOING_CRON' );

            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Get options data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_option() {
        return Fns::option_value( func_get_args() );
    }

    /**
     * Check active addons
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public function is_active_addon( $id ) {
        $addons = get_option( 'ozopanel_addons', [] );
        return ( array_key_exists( $id, $addons ) ) ? $addons[ $id ] : true;
    }
}

/**
 * Initialize the main plugin.
 *
 * @since 0.1.0
 *
 * @return OzoPanel
 */
function ozopanel() {
    return OzoPanel::init();
}
ozopanel(); // Run OzoPanel Plugin
