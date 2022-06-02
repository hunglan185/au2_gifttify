<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Steps\Ready;

class Completed extends Ready {

	public function __construct() {
		parent::__construct();
		$this->set_name( esc_html__( 'Ready', 'woocommerce-variation-prices' ) );
		$this->set_title( esc_html__( 'Complete Setup', 'woocommerce-variation-prices' ) );
		$this->set_description( esc_html__( 'Congratulations, your configuration is now complete!', 'woocommerce-variation-prices' ) );
	}

}
