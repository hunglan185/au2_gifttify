<?php
/**
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */

namespace Barn2\Plugin\WC_Variation_Prices\Admin\Wizard\Steps;

use Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Step,
	Barn2\Plugin\WC_Variation_Prices\Settings,
	Barn2\Plugin\WC_Variation_Prices\Dependencies\Barn2\Setup_Wizard\Util as Wizard_Util;

class Range_Format extends Step {

	public function __construct() {
		$this->set_id( 'range-format' );
		$this->set_name( esc_html__( 'Range Format', 'woocommerce-variation-prices' ) );
		$this->set_description( esc_html__( 'Customize the display format of the product price range.', 'woocommerce-variation-prices' ) );
		$this->set_title( esc_html__( 'Price range display format', 'woocommerce-variation-prices' ) );
	}

	public function setup_fields() {
		$settings = Settings::get_settings( $this->get_plugin(), 'variation-prices' );
		$fields   = array_combine(
			[
				'range_format',
				'range_separator',
				'range_custom_format',
				'range_custom_format_single',
			],
			Wizard_Util::pluck_wc_settings(
				$settings,
				[
					'variation_prices_settings_range_format',
					'variation_prices_settings_range_separator',
					'variation_prices_settings_range_custom_format',
					'variation_prices_settings_range_custom_format_single',
				]
			)
		);

		$fields['range_separator']['conditions'] = [
			'range_format' => [
				'op'    => 'eq',
				'value' => 'default',
			],
		];

		$fields['range_custom_format']['conditions'] = [
			'range_format' => [
				'op'    => 'eq',
				'value' => 'custom',
			],
		];

		$fields['range_custom_format_single']['conditions'] = [
			'range_format' => [
				'op'    => 'eq',
				'value' => 'custom',
			],
		];

		return $fields;
	}

	public function submit() {
		check_ajax_referer( 'barn2_setup_wizard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( esc_html__( 'You are not authorized.', 'woocommerce-variation-prices' ) );
		}

		$option_defaults = [
			'range_format'               => 'default',
			'range_separator'            => html_entity_decode( '&ndash;' ),
			'range_custom_format'        => html_entity_decode( 'from %min% to %max%' ),
			'range_custom_format_single' => html_entity_decode( '%min%' ),
		];
		$values          = array_merge(
			$option_defaults,
			$this->get_submitted_values()
		);

		$values = $this->get_submitted_values();

		foreach ( $option_defaults as $option_name => $default ) {
			$value = isset( $values[ $option_name ] ) ? $values[ $option_name ] : $default;
			update_option( "variation_prices_settings_$option_name", $value );
		}

		wp_send_json_success();

	}
}
