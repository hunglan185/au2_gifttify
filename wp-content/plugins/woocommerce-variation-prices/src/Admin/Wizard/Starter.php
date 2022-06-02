<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Starter as Setup_WizardStarter;

class Starter extends Setup_WizardStarter {

	/**
	 * Determine if the conditions to start the wizard are met.
	 *
	 * @return boolean
	 */
	public function should_start() {
		$missing_variable_option = ( false === get_option( 'variation_prices_settings_product_type_variable' ) );
		$missing_grouped_option  = ( false === get_option( 'variation_prices_settings_product_type_grouped' ) );

		// if both options are missing, the settings have never been saved
		// so start the Setup Wizard
		return $missing_variable_option && $missing_grouped_option;
	}

}
