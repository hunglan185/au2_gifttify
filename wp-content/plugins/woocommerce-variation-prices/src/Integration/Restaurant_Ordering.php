<?php

namespace Barn2\Plugin\WC_Variation_Prices\Integration;

use Barn2\Plugin\WC_Variation_Prices,
	Barn2\WVP_Lib\Registerable,
	Barn2\WVP_Lib\Service;

use function Barn2\Plugin\WC_Variation_Prices\wvp;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the integration with the Restaurant Ordering Plugin
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Restaurant_Ordering implements Registerable, Service {

	/**
	 * Register the functionalities of the class
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_filter( 'wc_restaurant_ordering_product_price', [ $this, 'get_price_html' ], 10, 2 );
	}

	/**
	 * Return the price of the current restaurant ordering product
	 *
	 * @since 1.0.0
	 *
	 * @param string $price The price of restaurant ordering product
	 * @param WC_Product $product The current restaurant ordering product object
	 *
	 * @return string The restaurant ordering product price
	 */
	public function get_price_html( $price, $product ) {
		$price_range_handler = wvp()->get_service( 'handlers/price_range' );

		return $price_range_handler->get_price_html( $price, $product );
	}


}
