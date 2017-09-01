<?php
/**
 * @package   EDD Mini API/User
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

class User {

	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * @var \WP_User|false
	 */
	public $user;

	/**
	 * @var string
	 */
	protected $public_key;

	/**
	 * @var string
	 */
	protected $private_key;

	/**
	 * User constructor.
	 *
	 * @param $user_id
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
		$this->set_user();
		$this->load_tokens();
	}

	/**
	 * Get the user object.
	 *
	 * @return void
	 */
	protected function set_user() {
		if ( is_email( $this->user_id ) ) {
			$this->user = get_user_by( 'email', $this->user_id );

			// Properly set the user ID var.
			if ( false !== $this->user ) {
				$this->user_id = $this->user->ID;
			}
		} else {
			$this->user = get_user_by( 'id', (int) $this->user_id );
		}
	}

	/**
	 * Load the user tokens.
	 *
	 * @return void
	 */
	protected function load_tokens() {

		// Try to load the tokens.
		$this->public_key  = get_user_meta( $this->user_id, '_ema_public_key', true );
		$this->private_key = get_user_meta( $this->user_id, '_ema_private_key', true );

		// If no tokens exist, create new ones for the user.
		if ( empty( $this->public_key ) || empty( $this->private_key ) ) {
			$this->create_tokens();
		}
	}

	/**
	 * Generate new tokens and save them to the user meta.
	 *
	 * @return void
	 */
	protected function create_tokens() {
		$this->generate_tokens();
		$this->save_tokens();
	}

	/**
	 * Generate tokens (public and private keys).
	 *
	 * @return void
	 */
	protected function generate_tokens() {
		$this->public_key  = ema_genetate_key( 20 );
		$this->private_key = ema_genetate_key( 20 );
	}

	/**
	 * Save new tokens to the user meta.
	 *
	 * @return bool
	 */
	protected function save_tokens() {
		$public_key  = add_user_meta( $this->user_id, '_ema_public_key', $this->public_key, true );
		$private_key = add_user_meta( $this->user_id, '_ema_private_key', $this->private_key, true );

		if ( false === ( $public_key || $private_key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get user hash.
	 *
	 * @param string $public_key
	 *
	 * @return string
	 */
	public function get_hash( $public_key ) {
		return hash( 'sha256', $public_key . $this->private_key );
	}

	/**
	 * Check token authentication.
	 *
	 * @param string $public_key
	 * @param string $try_hash
	 *
	 * @return bool True if the tokens match, false otherwise.
	 */
	public function authenticate( $public_key, $try_hash ) {

		$hash = $this->get_hash( $public_key );

		return $hash === $try_hash ? true : false;
	}

}

/**
 * Generates a unique key.
 *
 * @param int $length
 *
 * @return string
 */
function ema_genetate_key( $length = 10 ) {

	$characters        = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$string            = '';
	$characters_length = strlen( $characters ) - 1;

	for ( $i = 0; $i < $length; $i ++ ) {
		$string .= $characters[ mt_rand( 0, $characters_length ) ];
	}

	return $string;


}
