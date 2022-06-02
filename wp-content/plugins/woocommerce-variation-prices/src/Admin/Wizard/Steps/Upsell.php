<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Steps\Cross_Selling;

class Upsell extends Cross_Selling {

	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'More', 'woocommerce-variation-prices' ) );
		$this->set_description(
			sprintf(
				// translators: 1: URL to All Access Pass page, 2: URL to the KB about the upgrading process
				__( 'Enhance your store with these fantastic plugins from Barn2, or get them all by upgrading to an <a href="%1$s" target="_blank">All Access Pass<a/>! <a href="%2$s" target="_blank">(learn how here)</a>', 'woocommerce-variation-prices' ),
				'https://barn2.com/wordpress-plugins/bundles/',
				'https://barn2.com/kb/how-to-upgrade-license/'
			)
		);
		$this->set_title( esc_html__( 'Extra features', 'woocommerce-variation-prices' ) );
	}

	public function get_upsells() {
		$this->get_wizard()->set_as_completed();
		parent::get_upsells();
	}

}
