<?php

namespace Barn2\Plugin\WC_Variation_Prices;

use Barn2\Plugin\WC_Variation_Prices\Admin\Plugin_Setup,
	Barn2\WVP_Lib\Registerable,
	Barn2\WVP_Lib\Translatable,
	Barn2\WVP_Lib\Service_Provider,
	Barn2\WVP_Lib\Plugin\Premium_Plugin,
	Barn2\WVP_Lib\Plugin\Licensed_Plugin,
	Barn2\WVP_Lib\Util as Lib_Util,
	Barn2\WVP_Lib\Service_Container;

/**
 * The main plugin class. Responsible for setting up to core plugin services.
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Licensed_Plugin, Registerable, Translatable, Service_Provider {

	use Service_Container;

	const NAME    = 'WooCommerce Variation Prices';
	const ITEM_ID = 349483;

	/**
	 * Constructs and initalizes the main plugin class.
	 *
	 * @param string $file The main plugin file.
	 * @param string $version The current plugin version.
	 */
	public function __construct( $file = null, $version = '1.0' ) {
		parent::__construct(
			[
				'name'               => self::NAME,
				'item_id'            => self::ITEM_ID,
				'version'            => $version,
				'file'               => $file,
				'is_woocommerce'     => true,
				'settings_path'      => 'admin.php?page=wc-settings&tab=products&section=variation-prices',
				'documentation_path' => 'kb-categories/wvp-kb/',
			]
		);
	}

	/**
	 * Registers the plugin with WordPress.
	 */
	public function register() {
		parent::register();

		add_action( 'plugins_loaded', [ $this, 'load_services' ] );
		add_action( 'init', [ $this, 'load_textdomain' ] );

		$plugin_setup = new Plugin_Setup( $this->get_file(), $this );
		$plugin_setup->register();
	}

	/**
	 * Load the services related to this plugin.
	 *
	 * @since 1.0.0
	 */
	public function load_services() {
		// Don't load anything if WooCommerce not active.
		if ( ! Lib_Util::is_woocommerce_active() ) {
			$this->add_missing_woocommerce_notice();
			return;
		}

		$this->register_services();
	}

	/**
	 * Get the list of services provided.
	 *
	 * @since 1.0.0
	 *
	 * @return array The list of service objects.
	 */
	public function get_services() {
		$services = [];

		// Create the admin service.
		if ( Lib_Util::is_admin() ) {
			$services['admin'] = new Admin\Admin_Controller( $this );
		}

		// Create core services if license is valid.
		if ( $this->get_license()->is_valid() ) {
			$services['frontend_scripts']                = new Frontend_Scripts( $this->get_file(), $this->get_version() );
			$services['handlers/price_range']            = new Handlers\Price_Range();
			$services['integration/restaurant_ordering'] = new Integration\Restaurant_Ordering();
		}

		return $services;
	}

	/**
	 * Load the textdomain for the plugin
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-variation-prices', false, $this->get_slug() . '/languages' );
	}

	/**
	 * Add a notice in case WooCommerce is not active
	 *
	 * @since 1.0.0
	 */
	private function add_missing_woocommerce_notice() {
		if ( Lib_Util::is_admin() ) {
			$admin_notice = new \Barn2\WVP_Lib\Admin\Notices();
			$admin_notice->add(
				'wvp_woocommerce_missing',
				'',
				sprintf(
					// translators: 1: opening anchor tag, 2: closing anchor tag
					__( 'Please %1$sinstall WooCommerce%2$s in order to use WooCommerce Variation Prices.', 'woocommerce-variation-prices' ),
					Lib_Util::format_link_open( 'https://woocommerce.com/', true ),
					'</a>'
				),
				[
					'type'       => 'error',
					'capability' => 'install_plugins'
				]
			);
			$admin_notice->boot();
		}
	}

}
