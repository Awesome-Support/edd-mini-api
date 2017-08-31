<?php
/**
 * @package   EDD Mini API/Endpoint
 * @author    Julien Liabeuf <julien@liabeuf.fr>
 * @license   GPL-2.0+
 * @link      https://julienliabeuf.com
 * @copyright 2017 Julien Liabeuf
 */

namespace EMA;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Endpoint {

	/**
	 * @var User
	 */
	public $user;

	/**
	 * Check user authentication.
	 *
	 * @return \WP_Error|bool
	 */
	protected function is_user_authenticated( $data ) {

		// Extract credentials from the API request.
		$creds = $this->get_credentials( $data );

		if ( false === ( $creds['email'] || $creds['api_key'] || $creds['api_token'] ) ) {
			return new \WP_Error( 'unauthorized_access', 'Unauthorized access' );
		}

		// Get our user object.
		$this->user = new User( $creds['email'] );

		// Make sure that the user exists.
		if ( is_wp_error( $this->user ) ) {
			return $this->user;
		}

		return $this->user->authenticate( $creds['api_key'], $creds['api_token'] );

	}

	/**
	 * Extract user credentials from the API request.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function get_credentials( $data ) {
		$creds = array();

		$creds['email']     = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : false;
		$creds['api_key']   = isset( $data['api_key'] ) ? sanitize_key( $data['api_key'] ) : false;
		$creds['api_token'] = isset( $data['api_token'] ) ? sanitize_key( $data['api_token'] ) : false;

		return $creds;
	}

	/**
	 * Register hook to create the endpoint routes.
	 *
	 * @return void
	 */
	public function create_routes() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Get the custom endpoint routes.
	 *
	 * @return array
	 */
	private function get_rest_routes() {
		return array(
			array(
				'route'    => '/addons',
				'callback' => array( $this, 'get_customer_addons' ),
			),
		);
	}

	/**
	 * Register the endpoint/routes with the WP REST API.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		foreach ( $this->get_rest_routes() as $r ) {
			register_rest_route( 'as-client', $r['route'], array(
				'methods'  => 'GET',
				'callback' => $r['callback'],
			) );
		}
	}

	/**
	 * Get all addons purchased by the customer.
	 *
	 *
	 * @return array
	 */
	public function get_customer_addons() {

		$auth = $this->is_user_authenticated( $_GET );
		if ( true !== $auth ) {
			$message = is_wp_error( $auth ) ? $auth : 'unauthorized_access';

			return $this->prepare_response( $message, true );
		}

		$addons = new Purchases( $this->user->user_id );
		return $addons->get_customer_purchases();

		return array();
	}

	/**
	 * Preare the endpoint response in a standardized JSON format.
	 *
	 * @param mixed $data
	 * @param bool  $error
	 */
	protected function prepare_response( $data = null, $error = false ) {
		$response = array( 'error' => $error, 'data' => array() );

		if ( isset( $data ) ) {
			if ( is_wp_error( $data ) ) {
				$response['errors'] = array();
				foreach ( $data->errors as $code => $messages ) {
					foreach ( $messages as $message ) {
						$response['errors'] = array( 'code' => $code, 'message' => $message );
					}
				}
			} else {
				$response['data'] = $data;
			}
		}

		return $response;
	}

}