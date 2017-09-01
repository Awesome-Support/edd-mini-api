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
 * Connects with the various EDD functions and methods for retrieving information related to addons purchased,
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
	 * @var User
	 */
	public $user;

	/**
	 * The list of products purchased by the customer.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	public $products = array();

	/**
	 * Purchases constructor.
	 *
	 * @since 0.2.0
	 *
	 * @param User $user
	 */
	public function __construct( $user ) {
		$this->user = $user;
	}

	/**
	 * Get the customer purchases.
	 *
	 * @since 0.2.0
	 * @return array
	 */
	public function get_customer_purchases() {
		$purchases = $this->get_customer_payments();

		if ( false === $purchases ) {
			return array();
		}

		foreach ( $purchases as $purchase ) {
			$this->products[] = array(
				'ID'        => $purchase->ID,
				'name'      => $purchase->post_title,
				'slug'      => $purchase->post_name,
				'guid'      => $purchase->guid,
				'downloads' => $this->get_customer_downloads( $purchase->ID ),
			);
		}

		return $this->products;
	}

	/**
	 * Get the customer payments.
	 *
	 * @since 0.2.0
	 * @return bool|object
	 */
	protected function get_customer_payments() {
		return edd_get_users_purchases( $this->user->user_id );
	}

	/**
	 * Get the downloads for a specific payment.
	 *
	 * @since 0.2.0
	 *
	 * @param int $purchase_id Purchase for which to retrieve the downloads.
	 *
	 * @return array
	 */
	protected function get_customer_downloads( $purchase_id ) {
		$downloads = edd_get_payment_meta_cart_details( $purchase_id );

		foreach ( $downloads as $key => $download ) {
			$downloads[ $key ]['product_link']   = get_permalink( $download['id'] );
			$downloads[ $key ]['download_links'] = $this->get_download_links( $purchase_id, $download['id'], $download['item_number']['options']['price_id'] );
		}

		return $downloads;
	}

	/**
	 * Get the download links for a purchase.
	 *
	 * @since 0.2.0
	 *
	 * @param int $purchase_id
	 * @param int $download_id
	 * @param int $price_id
	 *
	 * @return array
	 */
	protected function get_download_links( $purchase_id, $download_id, $price_id ) {

		$links          = array();
		$download_files = edd_get_download_files( $download_id );
		$key            = edd_get_payment_key( $purchase_id );

		if ( ! empty( $download_files ) && is_array( $download_files ) ) {
			foreach ( $download_files as $filekey => $file ) {
				$filename           = edd_get_file_name( $file );
				$links[ $filename ] = edd_get_download_file_url( $key, $this->user->user->user_email, $filekey, $download_id, $price_id );
			}
		}

		return $links;
	}

}
