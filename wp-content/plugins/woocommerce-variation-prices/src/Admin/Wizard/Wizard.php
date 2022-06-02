<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Interfaces\Restartable;
use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Setup_Wizard;

/**
 * WPS Setup wizard.
 */
class Wizard extends Setup_Wizard implements Restartable {

	/**
	 * On wizard restart, detect which pages should be automatically unhidden.
	 *
	 * @return void
	 */
	public function on_restart() {
		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'error_message' => __( 'You are not authorized.', 'woocommerce-variation-prices' ) ], 403 );
		}

		$toggle = [];

		$variable = get_option( 'variation_prices_settings_product_type_variable' ) === 'yes';
		$grouped  = get_option( 'variation_prices_settings_product_type_grouped' ) === 'yes';

		if ( $variable ) {
			$toggle[] = 'variable';
		}

		if ( $grouped ) {
			$toggle[] = 'grouped';
		}

		wp_send_json_success(
			[
				'toggle' => $toggle
			]
		);

	}

}
