<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Variation_Prices\Settings,
	Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Step,
	Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Util as Wizard_Util;

class Product_Types extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'product-types' );
		$this->set_name( __( 'Product Types', 'woocommerce-variation-prices' ) );
		$this->set_description( __( 'Which type of product do you want to affect?', 'woocommerce-variation-prices' ) );
		$this->set_title( __( 'Product Types', 'woocommerce-variation-prices' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {
		$settings = Settings::get_settings( $this->get_plugin(), 'variation-prices' );
		$fields   = array_combine(
			[
				'variable',
				'grouped',
			],
			Wizard_Util::pluck_wc_settings(
				$settings,
				[
					'variation_prices_settings_product_type_variable',
					'variation_prices_settings_product_type_grouped',
				]
			)
		);

		$fields['variable']['description'] = '';
		$fields['grouped']['description']  = '';

		return $fields;
	}

	/**
	 * Update options in the database if needed.
	 *
	 * @return void
	 */
	public function submit() {
		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( esc_html__( 'You are not authorized.', 'woocommerce-variation-prices' ) );
		}

		$option_defaults = [
			'variable' => 'yes',
			'grouped'  => 'no',
		];
		$values          = array_map(
			function( $v ) {
				return filter_var( $v, FILTER_VALIDATE_BOOLEAN );
			},
			array_merge(
				$option_defaults,
				$this->get_submitted_values()
			)
		);

		if ( false === $values['variable'] && false === $values['grouped'] ) {
			$this->send_error( esc_html__( 'If you do not select at least one option, the plugin will have no effect on your products', 'woocommerce-variation-prices' ) );
		}

		foreach ( $option_defaults as $option_name => $default ) {
			$value = isset( $values[ $option_name ] ) && $values[ $option_name ] ? 'yes' : 'no';
			update_option( "variation_prices_settings_product_type_$option_name", $value );
		}

		wp_send_json_success();
	}

}
