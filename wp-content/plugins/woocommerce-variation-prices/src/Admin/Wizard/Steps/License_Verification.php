<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Steps\Welcome;

class License_Verification extends Welcome {

	public function __construct() {
		$this->set_id( 'license_activation' );
		$this->set_name( esc_html__( 'Welcome', 'woocommerce-variation-prices' ) );
		$this->set_description( esc_html__( 'Take control over the display of variation prices in your store.', 'woocommerce-variation-prices' ) );
		$this->set_tooltip( esc_html__( 'Use this setup wizard to quickly configure the options of your variation prices. You can easily change these options later on the plugin settings page or by relaunching the setup wizard.', 'woocommerce-variation-prices' ) );
	}

}
