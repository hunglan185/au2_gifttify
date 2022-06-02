<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard;

use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\Completed;
use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\License_Verification;
use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\Additional_Options;
use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\Range_Format;
use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\Product_Types;
use Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps\Upsell;
use Barn2\WVP_Lib\Plugin\License\EDD_Licensing;
use Barn2\WVP_Lib\Plugin\License\Plugin_License;
use Barn2\WVP_Lib\Plugin\Licensed_Plugin;
use Barn2\WVP_Lib\Registerable;

class Setup_Wizard implements Registerable {

	private $plugin;

	private $wizard;

	public function __construct( Licensed_Plugin $plugin ) {

		$this->plugin = $plugin;

		$steps = [
			new License_Verification(),
			new Product_Types(),
			new Range_Format(),
			new Additional_Options(),
			new Upsell(),
			new Completed(),
		];

		$wizard = new Wizard( $this->plugin, $steps );

		$wizard->configure(
			[
				'skip_url'        => admin_url( 'admin.php?page=wc-settings&tab=products&section=variation-prices' ),
				'license_tooltip' => esc_html__( 'The licence key is contained in your order confirmation email.', 'woocommerce-variation-prices' ),
			]
		);

		$wizard->add_edd_api( EDD_Licensing::class );
		$wizard->add_license_class( Plugin_License::class );
		$wizard->add_restart_link( 'variation-prices', 'variation_prices_settings_format' );

		// $wizard->add_custom_asset(
		// 	$plugin->get_dir_url() . 'assets/js/admin/wizard.min.js',
		// 	Lib_Util::get_script_dependencies( $this->plugin, 'admin/wizard.min.js' )
		// );

		$this->wizard = $wizard;
	}

	public function register() {
		$this->wizard->boot();

		add_action( 'admin_enqueue_scripts', [ $this, 'add_inline_style' ], 21 );
	}

	public function add_inline_style() {
		$custom_css = '.components-checkbox-control label.components-checkbox-control__label{font-size:16px}';

		wp_add_inline_style( $this->wizard->get_slug(), $custom_css );
	}

}
