<?php

namespace Barn2\Plugin\WC_Variation_Prices;

use Barn2\WVP_Lib\Registerable,
	Barn2\WVP_Lib\Service,
	Barn2\WVP_Lib\Util as Lib_Util,
	Barn2\WVP_Lib\WooCommerce\Admin\Settings_Util as WC_Util;

/**
 * Loads the front-end scripts and styles.
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Frontend_Scripts implements Registerable, Service {

	private $file;
	private $version;

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The path of the plugin file
	 * @param string $version The version of the plugin
	 */
	public function __construct( $file, $version ) {
		$this->file    = $file;
		$this->version = $version;
	}

	/**
	 * Register the actions and filters related to the class.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
	}

	/**
	 * Load styles and scripts for the front end of the plugin
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {
		wp_enqueue_style( 'wc-variation-prices', plugins_url( 'assets/css/wc-variation-prices.min.css', $this->file ), [], $this->version );
		wp_enqueue_script( 'wc-variation-prices', plugins_url( 'assets/js/wc-variation-prices.min.js', $this->file ), [ 'jquery' ], $this->version, true );

		$types = [];
		if ( WC_Util::get_checkbox_option( 'variation_prices_settings_product_type_variable', true ) ) {
			$types[] = 'variable';
		}
		if ( WC_Util::get_checkbox_option( 'variation_prices_settings_product_type_grouped' ) ) {
			$types[] = 'grouped';
		}

		$price_range_handler = wvp()->get_service( 'handlers/price_range' );
		$hide                = WC_Util::get_checkbox_option( 'variation_prices_settings_hide_until_selected' );
		$show_selected       = $hide || WC_Util::get_checkbox_option( 'variation_prices_settings_show_selected_variation', true );

		wp_localize_script(
			'wc-variation-prices',
			'wcvp_params',
			[
				'product_types'      => $types,
				'format_type'        => get_option( 'variation_prices_settings_range_format', 'default' ),
				'show_selected'      => $show_selected,
				'hide'               => $hide,
				'list_item_template' => $price_range_handler->get_list_item_template_html()
			]
		);
	}

}
