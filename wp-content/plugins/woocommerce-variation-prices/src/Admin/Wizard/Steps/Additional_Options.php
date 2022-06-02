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

class Additional_Options extends Step {

	/**
	 * Configure the step.
	 */
	public function __construct() {
		$this->set_id( 'additional-options' );
		$this->set_name( __( 'Additional Options', 'woocommerce-variation-prices' ) );
		$this->set_description( __( 'Fine tune the way the plugin works', 'woocommerce-variation-prices' ) );
		$this->set_title( __( 'Additional Options', 'woocommerce-variation-prices' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup_fields() {
		$settings = Settings::get_settings( $this->get_plugin(), 'variation-prices' );
		$fields   = array_combine(
			[
				'hide_until_selected',
				'show_selected_variation',
				'disable_on_shop',
			],
			Wizard_Util::pluck_wc_settings(
				$settings,
				[
					'variation_prices_settings_hide_until_selected',
					'variation_prices_settings_show_selected_variation',
					'variation_prices_settings_disable_on_shop',
				]
			)
		);

		$fields['hide_until_selected']['description'] = esc_html__( 'No price will appear when the product page first loads. When the customer selects a variation, its price will then appear.', 'woocommerce-variation-prices' );

		$fields['show_selected_variation']['description'] = esc_html__( 'Improve user experience by instantly updating the main product price with the price of the selected variation.', 'woocommerce-variation-prices' );
		$fields['show_selected_variation']['conditions']  = [
			'hide_until_selected' => [
				'op'    => 'eq',
				'value' => false,
			],
		];

		$fields['disable_on_shop']['description'] = esc_html__( 'Do not change the variation price format on the Shop and category pages.', 'woocommerce-variation-prices' );

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
			'show_selected_variation' => 'yes',
			'hide_until_selected'     => 'no',
			'disable_on_shop'         => 'no',
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

		foreach ( $option_defaults as $option_name => $default ) {
			$value = isset( $values[ $option_name ] ) && $values[ $option_name ] ? 'yes' : 'no';
			update_option( "variation_prices_settings_$option_name", $value );
		}

		wp_send_json_success();
	}

}
