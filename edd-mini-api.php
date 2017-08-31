<?php
/**
 * @package   EDD
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 *
 * @wordpress-plugin
 * Plugin Name:       EDD Mini API
 * Plugin URI:        https://julienliabeuf.com
 * Description:       Provides API access to EDD customer data. For internal use only.
 * Version:           0.1.0
 * Author:            Julien Liabeuf
 * Author URI:        https://julienliabeuf.com
 * Text Domain:       edd-mini-api
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class EDD_Mini_API {

	/**
	 * @var EDD_Mini_API Holds the unique instance of the class.
	 * @since 3.2.5
	 */
	private static $instance;

	/**
	 * Instantiate and return the unique object.
	 *
	 * @since     3.2.5
	 * @return object EDD_Mini_API Unique instance of the class.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Mini_API ) ) {
			self::$instance = new EDD_Mini_API;
			self::$instance->init();
		}

		return self::$instance;

	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	private function init() {

		// Include our files.
		self::$instance->includes();

		// Create our custom REST API endpoint.
		$routes = new EMA\Endpoint();
		$routes->create_routes();
	}

	/**
	 * Load required files.
	 *
	 * @return void
	 */
	private function includes() {
		require_once( 'includes/class-endpoint.php' );
		require_once( 'includes/class-user.php' );
		require_once( 'includes/class-purchases.php' );
	}

}

/**
 * The main function responsible for returning the unique instance
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 0.1.0
 * @return object EDD_Mini_API
 */
function EMA() {
	return EDD_Mini_API::instance();
}

// Get EMA Running
EMA();
