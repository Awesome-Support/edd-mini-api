<?php
/**
 * @package   EDD Mini API/Purchases
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

/**
 * Class Purchases.
 *
 * Connects with the various EDD functions and methods for retrieving informations related to addons purchased,
 * licenses and download links.
 *
 * @since   0.2.0
 * @package EMA
 */
class Purchases {

	/**
	 * Current user ID.
	 *
	 * @since 0.2.0
	 * @var int
	 */
	public $user_id;

	/**
	 * Purchases constructor.
	 */
	public function __construct( $user_id ) {
		$this->user_id = (int) $user_id;
	}

	/**
	 * Get the customer purchases.
	 *
	 * @since 0.2.0
	 * @return array
	 */
	public function get_customer_purchases() {
		$purchases = edd_get_users_purchased_products( $this->user_id );

		if ( false === $purchases ) {
			return array();
		}

		$products = array();

		foreach ( $purchases as $purchase ) {
			$products[] = array(
				'ID'   => $purchase->ID,
				'name' => $purchase->post_title,
				'slug' => $purchase->post_name,
				'guid' => $purchase->guid,
			);
		}

		return $products;
	}

}