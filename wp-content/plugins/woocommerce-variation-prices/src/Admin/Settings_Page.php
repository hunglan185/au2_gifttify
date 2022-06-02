<?php

namespace Barn2\Plugin\WC_Variation_Prices\Admin;

use Barn2\WVP_Lib\Registerable,
	Barn2\Plugin\WC_Variation_Prices\Settings,
	Barn2\WVP_Lib\Plugin\Licensed_Plugin,
	Barn2\WVP_Lib\WooCommerce\Admin\Custom_Settings_Fields,
	Barn2\WVP_Lib\WooCommerce\Admin\Plugin_Promo;

/**
 * Provides functions for the plugin settings page in the WordPress admin.
 *
 * Settings are registered under: WooCommerce -> Settings -> Products -> Variation prices.
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings_Page implements Registerable {

	const SETTINGS_ID = 'variation_prices_settings';

	private $plugin;
	private $license_setting;

	/**
	 * Settings_Page class constructor
	 *
	 * @since 1.0.0
	 *
	 * @param Licensed_Plugin $plugin
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->id              = Settings::SECTION_SLUG;
		$this->label           = __( 'Variation Prices', 'woocommerce-variation-prices' );
		$this->plugin          = $plugin;
		$this->license_setting = $plugin->get_license_setting();
	}

	/**
	 * Register the actions and filters related to the class.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Register custom field types.
		$extra_field_types = new Custom_Settings_Fields();
		$extra_field_types->register();

		// Add sections & settings.
		add_filter( 'woocommerce_get_sections_products', [ $this, 'add_section' ] );
		add_filter( 'woocommerce_get_settings_products', [ $this, 'add_settings' ], 5, 2 );

		// Add plugin promo.
		$plugin_promo = new Plugin_Promo( $this->plugin->get_id(), $this->plugin->get_file(), $this->id );
		$plugin_promo->register();
	}

	/**
	 * Add a section to the WooCommerce -> Settings -> Products page
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections The sections of the current page
	 * @return array The filtered array with the custom section added
	 */
	public function add_section( $sections ) {
		$sections[ $this->id ] = __( 'Variation prices', 'woocommerce-variation-prices' );
		ksort( $sections );

		return $sections;
	}

	/**
	 * Add a section to the WooCommerce -> Settings -> Products page
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The settings of the current section
	 * @param string $current_section The id of the current section
	 * @return array The filtered array with the custom settings added
	 */
	public function add_settings( $settings, $current_section ) {
		// Check we're on the correct settings section.
		if ( $this->id !== $current_section ) {
			return $settings;
		}

		return Settings::get_settings( $this->plugin, $this->id );
	}


}
