<?php

namespace Barn2\Plugin\WC_Variation_Prices;

use Barn2\WVP_Lib\Plugin\Licensed_Plugin,
	Barn2\WVP_Lib\Util as Lib_Util;

/**
 * Handles the settings of the plugin
 *
 * @since 1.0.2
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Settings {

	const SECTION_SLUG = 'variation-prices';

	/**
	 * Build the array with the WooCommerce settings of the plugin
	 *
	 * @since 1.0.2
	 *
	 * @param Licensed_Plugin $plugin The plugin instance
	 * @param string $id The slug of the plugin
	 * @return array The array with the plugin settings
	 */
	public static function get_settings( $plugin ) {
		$symbols   = get_woocommerce_currency_symbols();
		$currency  = $symbols[ get_woocommerce_currency() ];
		$separator = get_option( 'variation_prices_settings_range_separator', '&ndash;' );

		$license_setting = $plugin->get_license_setting();

		$settings = [
			[
				'id'    => 'variation_prices_settings',
				'type'  => 'settings_start',
				'class' => 'barn2-plugins-settings',
			],
			[
				'title' => __( 'Variation prices', 'woocommerce-variation-prices' ),
				'desc'  => '<p>' . __( 'The following options control the WooCommerce Variation Prices extension.', 'woocommerce-variation-prices' ) . '<p>'
				. '<p>'
				. Lib_Util::format_link( $plugin->get_documentation_url(), __( 'Documentation', 'woocommerce-variation-prices' ), true ) . ' | '
				. Lib_Util::format_link( $plugin->get_support_url(), __( 'Support', 'woocommerce-variation-prices' ), true )
				. '</p>',
				'type'  => 'title',
				'id'    => 'variation_prices_settings_format',
			],
			$license_setting->get_license_key_setting(),
			$license_setting->get_license_override_setting(),
			[
				'title'             => __( 'Price display format', 'woocommerce-variation-prices' ),
				'type'              => 'select',
				'id'                => 'variation_prices_settings_range_format',
				'options'           => [
					/* translators: 1: currency symbol 2: separator character */
					'default' => sprintf( __( 'Price range e.g. %1$s50 %2$s %1$s100', 'woocommerce-variation-prices' ), $currency, $separator ),
					/* translators: currency symbol */
					'from'    => sprintf( __( 'Starting price (long) e.g. From %s50', 'woocommerce-variation-prices' ), $currency ),
					/* translators: currency symbol */
					'plus'    => sprintf( __( 'Starting price (short) e.g. %s50+', 'woocommerce-variation-prices' ), $currency ),
					/* translators: currency symbol */
					'upto'    => sprintf( __( 'Maximum price e.g. Up to %s100', 'woocommerce-variation-prices' ), $currency ),
					'list'    => __( 'List all variation prices', 'woocommerce-variation-prices' ),
					'custom'  => __( 'Custom', 'woocommerce-variation-prices' ),
				],
				'default'           => 'default',
				'class'             => 'wvp-toggle-parent',
				'custom_attributes' => [
					'data-child-class' => 'selected-option',
				],
			],
			[
				'title'   => __( 'Price range separator', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Choose what to display between the high and low prices.', 'woocommerce-variation-prices' ),
				'type'    => 'text',
				'id'      => 'variation_prices_settings_range_separator',
				'default' => '&ndash;',
				'class'   => 'selected-option selected-option-default',
			],
			[
				'title'   => __( 'Custom format for products with different variation prices', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Use %min% and %max% for the minimum and maximum prices.', 'woocommerce-variation-prices' ),
				'type'    => 'text',
				'id'      => 'variation_prices_settings_range_custom_format',
				'default' => 'from %min% to %max%',
				'class'   => 'selected-option selected-option-custom',
			],
			[
				'title'   => __( 'Custom format when all prices are the same', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Use either %min% or %max% for the price.', 'woocommerce-variation-prices' ),
				'type'    => 'text',
				'id'      => 'variation_prices_settings_range_custom_format_single',
				'default' => '%min%',
				'class'   => 'selected-option selected-option-custom',
			],
			[
				'title'         => __( 'Product type', 'woocommerce-variation-prices' ),
				'desc'          => __( 'Variable products', 'woocommerce-variation-prices' ),
				'type'          => 'checkbox',
				'id'            => 'variation_prices_settings_product_type_variable',
				'default'       => 'yes',
				'checkboxgroup' => 'start',
			],
			[
				'desc'          => __( 'Grouped products', 'woocommerce-variation-prices' ),
				'type'          => 'checkbox',
				'id'            => 'variation_prices_settings_product_type_grouped',
				'default'       => 'no',
				'checkboxgroup' => '',
			],
			[
				'title'             => __( 'Hide the price until a variation is selected', 'woocommerce-variation-prices' ),
				'desc'              => __( 'Hide the main price on the single product page until a variation is selected.', 'woocommerce-variation-prices' ),
				'type'              => 'checkbox',
				'id'                => 'variation_prices_settings_hide_until_selected',
				'default'           => 'no',
				'class'             => 'wvp-toggle-parent',
				'custom_attributes' => [
					'data-child-class' => 'show-selected-option',
					'data-toggle-val'  => 0,
				],
			],
			[
				'title'   => __( 'Show selected variation price', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Update the main price when a variation is selected.', 'woocommerce-variation-prices' ),
				'type'    => 'checkbox',
				'id'      => 'variation_prices_settings_show_selected_variation',
				'class'   => 'show-selected-option',
				'default' => 'yes',
			],
			[
				'title'   => __( 'Disable on Shop page', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Don\'t change the variation price format on the Shop and category pages.', 'woocommerce-variation-prices' ),
				'type'    => 'checkbox',
				'id'      => 'variation_prices_settings_disable_on_shop',
				'default' => 'no',
			],
			[
				'title'   => __( 'Use in admin', 'woocommerce-variation-prices' ),
				'desc'    => __( 'Also change the price display format in the WordPress admin.', 'woocommerce-variation-prices' ),
				'type'    => 'checkbox',
				'id'      => 'variation_prices_settings_use_in_admin',
				'default' => 'no',
			],
			[
				'id'   => 'variation_prices_settings_format_end',
				'type' => 'sectionend',
			],
			[
				'id'   => 'variation_prices_settings_end',
				'type' => 'settings_end',
			],
		];

		$section_id = self::SECTION_SLUG;

		return apply_filters( "woocommerce_get_settings_{$section_id}", $settings );
	}
}
