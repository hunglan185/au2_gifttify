<?php

namespace Barn2\Plugin\WC_Variation_Prices\Admin;

use Barn2\WVP_Lib\Registerable,
	Barn2\WVP_Lib\Service,
	Barn2\WVP_Lib\Plugin\Licensed_Plugin,
	Barn2\WVP_Lib\Util as Lib_Util,
	Barn2\WVP_Lib\Plugin\Admin\Admin_Links,
	Barn2\WVP_Lib\Service_Container,
	Barn2\WVP_Lib\WooCommerce\Admin\Navigation;

/**
 * General admin functions for WooCommerce Variation Prices.
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Admin_Controller implements Registerable, Service {

	use Service_Container;

	private $plugin;

	/**
	 * Main class constructor
	 *
	 * @since 1.0.0
	 *
	 * @param Simple_Plugin|Premium_Plugin $plugin The current plugin object.
	 */
	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Get the list of services provided.
	 *
	 * @since 1.0.0
	 *
	 * @return array The list of service objects.
	 */
	public function get_services() {
		return [
			'admin_links'   => new Admin_Links( $this->plugin ),
			'settings_page' => new Settings_Page( $this->plugin ),
			'navigation'    => new Navigation( $this->plugin, 'variation-prices', __( 'Variation Prices', 'woocommerce-variation-prices' ) ),
			'wizard'        => new Wizard\Setup_Wizard( $this->plugin ),
		];
	}

	/**
	 * Register the services of the controller and
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->register_services();

		// Load admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
	}

	/**
	 * Load styles and scripts for the backend of the plugin
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The hook of the current page
	 */
	public function load_scripts( $hook ) {
		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}

		$min = Lib_Util::get_script_suffix();
		wp_enqueue_style( 'wcvp-admin', $this->plugin->get_dir_url() . "assets/css/admin/wcvp-admin{$min}.css", null, $this->plugin->get_version() );
		wp_enqueue_script( 'wcvp-admin', $this->plugin->get_dir_url() . "assets/js/admin/wcvp-admin{$min}.js", [ 'jquery' ], $this->plugin->get_version(), true );
	}

}
