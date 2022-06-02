<?php

namespace Barn2\Plugin\WC_Variation_Prices\Handlers;

use Barn2\WVP_Lib\Registerable,
	Barn2\WVP_Lib\Service,
	Barn2\WVP_Lib\WooCommerce\Admin\Settings_Util as WC_Util,
	Barn2\WVP_Lib\Util as Lib_Util;

defined( 'ABSPATH' ) || exit;

/**
 * Price Range Format Handler
 *
 * @package   Barn2\woocommerce-variation-prices
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Price_Range implements Registerable, Service {

	/**
	 * The product object currently being handled
	 *
	 * @since 1.0.5
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 * An array with the minimum and maximum price of the product
	 *
	 * @since 1.0.5
	 *
	 * @var array[float]
	 */
	private $range;

	/**
	 * An associative array of prices
	 *
	 * This array contains the prices of all the products
	 * that are children of the product currently being handled
	 * The `keys` of the array are the ID of each child product
	 * Each `value` is an array of floats with all the prices of a child product
	 * (raw price, regular price, sale price)
	 *
	 * @var array[array[float]]
	 */
	private $prices;

	/**
	 * The format used for the price range
	 *
	 * @var string
	 */
	private $format;

	/**
	 * The string used as a separator between minimum and maximum prices
	 *
	 * @var string
	 */
	private $separator;

	/**
	 * Register the functionalities of the class
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_filter( 'woocommerce_get_price_html', [ $this, 'get_price_html' ], 10, 2 );

		add_filter( 'woocommerce_available_variation', [ $this, 'get_available_variation' ], 10, 3 );
	}

	/**
	 * Return the price of a variable or grouped product
	 *
	 * @since 1.0.0
	 *
	 * @param string $price The price of product
	 * @param WC_Product $product The current product object
	 *
	 * @return string The product price
	 */
	public function get_price_html( $price, $product ) {
		$this->set_product( $product );

		if ( ! Lib_Util::is_front_end() && ! WC_Util::get_checkbox_option( 'variation_prices_settings_use_in_admin' ) ) {
			return $price;
		}

		if ( ! $this->is_product_affected() ) {
			return $price;
		}

		if ( ! in_array( $product->get_type(), [ 'variable', 'grouped' ], true ) ) {
			$single_price_format = get_option( 'variation_prices_settings_range_custom_format_single', '%min%' );

			$price = str_replace( [ '%min%', '%max%' ], wc_price( $product->get_price() ), $single_price_format );

			/**
			 * Filter the HTML markup of the price
			 *
			 * @since 1.0.5
			 *
			 * @param array $price The HTML output
			 * @param WC_Product $product The current variable or grouped product
			 */
			return apply_filters( 'wc_variation_prices_single_price_html', $price, $product );
		}

		$range = $this->range;

		if ( empty( $range ) ) {
			/**
			* This is a WooCommerce filter that is documented
			* in /woocommerce/includes/class-wc-product-variable.php
			*/
			return apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
		}

		$min = $range['min'];
		$max = $range['max'];

		$price_suffix = $product->get_price_suffix();
		$classes      = [ 'price' ];

		if ( 'list' === $this->format ) {
			if ( WC_Util::get_checkbox_option( 'variation_prices_settings_hide_until_selected' ) ) {
				$price_suffix = '';
			}

			$classes = [];
		}

		$price_format = $this->get_price_range_format();

		$classes = array_merge(
			[ 'wvp-price-range' ],
			/**
			 * Filter the array of classes for the main element enclosing the price range markup
			 *
			 * @since 1.0.0
			 *
			 * @param array $classes The class array
			 * @param WC_Product $product The current variable or grouped product
			 */
			apply_filters( 'wc_variation_prices_price_range_classes', $classes, $product )
		);

		$price_range = sprintf(
			$price_format . $price_suffix,
			is_numeric( $min ) ? wc_price( $min ) : $min,
			is_numeric( $max ) ? wc_price( $max ) : $max
		);

		$price = sprintf(
			'<span class="%1$s" data-suffix="%2$s" data-format="%3$s" data-default="%4$s">%5$s</span>',
			implode( ' ', $classes ),
			esc_attr( $price_suffix ),
			esc_attr( $price_format ),
			esc_attr( $price_range ),
			$price_range
		);

		/**
		 * Filter the HTML markup of the price
		 *
		 * @since 1.0.0
		 *
		 * @param array $price The HTML output
		 * @param WC_Product $product The current variable or grouped product
		 */
		return apply_filters( 'wc_variation_prices_price_range_html', $price, $product );
	}

	/**
	 * Filter the array with the properties of each variation
	 *
	 * @since 1.0.0
	 *
	 * @param array $available_variations The array of properties
	 * @param WC_Product $product The current product object
	 * @param WC_Product $variation The current product variation
	 *
	 * @return array The filtered array of variations
	 */
	public function get_available_variation( $available_variations, $product, $variation ) {
		$this->set_product( $product );

		if ( $this->is_product_affected() ) {
			$available_variations['attribute_names'] = $available_variations['attributes'];
			array_walk(
				$available_variations['attribute_names'],
				function( &$t, $a ) {
					$a = str_replace( 'attribute_', '', $a );
					if ( taxonomy_exists( $a ) ) {
						$term = get_term_by( 'slug', $t, $a );
						if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
							$t = $term->name;
						}
					}
				}
			);

			$available_variations['display_price_html'] = sprintf(
				'<span class="price">%1$s</span>',
				wc_price( wc_get_price_to_display( $variation ) ) . $variation->get_price_suffix()
			);
		}

		return $available_variations;
	}

	/**
	 * Set the product currently being handled
	 *
	 * @since 1.0.5
	 *
	 * @param  WC_Product $product
	 */
	public function set_product( $product ) {
		if ( $this->product && $this->product->get_id() === $product->get_id() ) {
			return;
		}

		$this->product = $product;
		$this->reset_properties();
	}

	/**
	 * Reset price range properties
	 *
	 * @since 1.0.5
	 */
	private function reset_properties() {
		$this->range  = null;
		$this->prices = [];
		$this->define_dependent_product_prices();
		$this->define_price_range();
		$this->define_display_format();
	}

	/**
	 * Set the price range format
	 *
	 * @since 1.0.5
	 */
	private function define_display_format() {
		$format = get_option( 'variation_prices_settings_range_format', 'default' );

		/**
		 * Filter the selected display format
		 * so that the default settings can be overridden
		 *
		 * @since 1.0.0
		 *
		 * @param string $format The selected display format
		 * @param WC_Product $product The current variable or grouped product
		 */
		$this->format = apply_filters( 'wc_variation_prices_display_format', $format, $this->product );

		$this->separator = get_option( 'variation_prices_settings_range_separator', '&ndash;' );
	}

	/**
	 * Determines whether a product is affected by the plugin
	 * based on the current setting configuration
	 *
	 * @since 1.0.0
	 * @since 1.0.5 Removed the $product argument that is now a class property
	 *
	 * @return bool Whether the passed product is affected
	 */
	private function is_product_affected() {
		$product             = $this->product;
		$is_product_affected = false;
		$product_type        = $product->get_type();

		if ( in_array( $product_type, [ 'variable', 'grouped' ], true ) ) {
			$is_product_affected = WC_Util::get_checkbox_option( "variation_prices_settings_product_type_$product_type" );
		} else {
			$is_product_affected = apply_filters( 'wc_variation_prices_apply_single_price_format', false, $product );
		}

		$disable_on_shop = WC_Util::get_checkbox_option( 'variation_prices_settings_disable_on_shop' );
		if ( $disable_on_shop && ( is_shop() || is_product_taxonomy() ) ) {
			$is_product_affected = false;
		}

		// if `did_action` returns the same values for both hook on the single product page
		// the function was invoked outside of the single product summary template
		// then we are conditionally excluding the related products
		if ( $disable_on_shop && is_product() && did_action( 'woocommerce_before_single_product_summary' ) === did_action( 'woocommerce_after_single_product_summary' ) ) {
			$is_product_affected = false;
		}

		/**
		 * Filter whether the price range format will be used for the current product
		 *
		 * @since 1.0.3
		 *
		 * @param bool $is_product_affected Whether the plugin
		 * @param WC_Product $product The current product object
		 */
		return apply_filters( 'wc_variation_prices_is_product_affected', $is_product_affected, $product );
	}

	/**
	 * Return the price range of variable or grouped products
	 * as an array of 'min', 'max' prices
	 *
	 * @since 1.0.0
	 * @since 1.0.5 Removed the $product argument that is now a class property
	 *
	 * @return array The price range
	 */
	private function define_price_range() {
		$prices = $this->prices;

		if ( empty( $prices['price'] ) ) {
			return [];
		}

		$min = current( $prices['price'] );
		$max = end( $prices['price'] );

		$this->range = compact( 'min', 'max' );
	}

	/**
	 * Return true if all children products have the same price
	 *
	 * @since 1.0.5
	 *
	 * @return bool
	 */
	private function is_same_price() {
		return $this->range['min'] === $this->range['max'];
	}

	/**
	 * Return the string with a template for the price range format
	 *
	 * @since 1.0.0
	 * @since 1.0.5 Removed the `$format` argument that is now a class property
	 *
	 * @return string The price range format
	 */
	private function get_price_range_format() {
		$price_range_format = "%1\$s $this->separator %2\$s";

		switch ( $this->format ) {
			case 'from':
				// translators: 1: minimum price
				$price_range_format = __( 'From %1$s', 'woocommerce-variation-prices' );
				break;
			case 'plus':
				// translators: 1: minimum price
				$price_range_format = __( '%1$s+', 'woocommerce-variation-prices' );
				break;
			case 'upto':
				// translators: 2: maximum price
				$price_range_format = __( 'Up to %2$s', 'woocommerce-variation-prices' );
				break;
			case 'custom':
				if ( $this->is_same_price() ) {
					$price_range_format = get_option( 'variation_prices_settings_range_custom_format_single', '%min%' );
				} else {
					$price_range_format = get_option( 'variation_prices_settings_range_custom_format', 'from %min% to %max%' );
				}

				if ( $price_range_format ) {
					$price_range_format = str_replace( [ '%min%', '%max%' ], [ '%1$s', '%2$s' ], $price_range_format );
				}

				break;
		}

		if ( 'custom' !== $this->format && $this->is_same_price() ) {
			$price_range_format = '%1$s';
		}

		$price_range_format = $this->add_delimiter_tags( $price_range_format );

		if ( 'list' === $this->format ) {
			$price_range_format = $this->get_subproduct_list();
		}

		if ( WC_Util::get_checkbox_option( 'variation_prices_settings_hide_until_selected' ) && is_product() ) {
			$price_format = '';
		}

		/**
		 * Filter the string used for the price range format
		 *
		 * The string can contain two placeholders `%1$s` and `%2$s`
		 * that will be replaced with the minimum and maximum prices respectively.
		 * If all the children products share the same price,
		 * then only `%1$s` will be used.
		 *
		 * @since 1.0.5
		 *
		 * @param bool $is_product_affected Whether the plugin
		 * @param WC_Product $product The current product object
		 */
		$price_format = apply_filters( 'wc_variation_prices_price_range_format', $price_range_format, $this->product );

		return $price_format;
	}

	/**
	 * Return the price format with the price elements
	 * surrounded by <span> tags for further CSS customization
	 *
	 * @since 1.0
	 *
	 * @param string $price_format The string format for the price range
	 * @return string The string with the <span> tags added for the delimiters
	 */
	private function add_delimiter_tags( $price_format ) {
		if ( $price_format ) {
			$replacements = [
				'</span>%1$s<span class="wvp-range-delimiter">',
				'</span>%2$s<span class="wvp-range-delimiter">',
			];
			$price_format = str_replace( [ '%1$s', '%2$s' ], $replacements, $price_format );
			$price_format = '<span class="wvp-range-delimiter">' . $price_format . ' </span>';
		}

		return $price_format;
	}

	/**
	 * Return the HTML markup of the list of variations or children
	 *
	 * @since 1.0.0
	 * @since 1.0.5 Removed the $product argument that is now a class property
	 *
	 * @return string The HTML markup of the product list
	 */
	private function get_subproduct_list() {
		$html   = '';
		$prices = $this->prices;

		if ( ! empty( $prices ) ) {

			/**
			 * Filter the tagname used to enclose the list of variations
			 * The default tagname is 'span'. The filter allows to use
			 * a 'div' or a 'ul' instead
			 *
			 * @since 1.0.0
			 *
			 * @param string $tagname The HTML markup of the list with all the variations
			 * @param WC_Product $product The current variable or grouped product
			 */
			$tag_name = apply_filters( 'wc_variation_prices_list_tagname', 'span' );

			ob_start();

			?>
			<<?php echo esc_attr( $tag_name ); ?> class="wvp-list">
			<?php
			foreach ( $prices['price'] as $id => $price ) {
				$child = wc_get_product( $id );

				if ( $child ) {
					$name = $child->get_name();

					if ( $child->is_type( 'variation' ) ) {
						$name = wc_get_formatted_variation( $child->get_variation_attributes(), true, false );
					}

					if ( $name ) {
						if ( isset( $prices['regular_price'] ) && (float) $price !== (float) $prices['regular_price'][ $id ] ) {
							$price = wc_format_sale_price( wc_price( $prices['regular_price'][ $id ] ), wc_price( $prices['price'][ $id ] ) );
						}

						$price  = is_numeric( $price ) ? wc_price( $price ) : $price;
						$price .= $child->get_price_suffix();

						$list_item_template = $this->get_list_item_template_html();
						$list_item          = str_replace(
							[
								'%1$s',
								'%2$s',
								'%3$s'
							],
							[
								esc_attr( $id ),
								$name,
								$price
							],
							$list_item_template
						);

						echo $list_item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			}
			?>
			</<?php echo esc_attr( $tag_name ); ?>>
			<?php
			$html = ob_get_clean();
		}

		/**
		 * Filter the HTML markup of the list of variations
		 * when the 'List of all variations' is selected in the settings
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The HTML markup of the list with all the variations
		 * @param WC_Product $product The current variable or grouped product
		 */
		return apply_filters( 'wc_variation_prices_list_html', $html, $product );
	}

	/**
	 * Return the HTML template of the single list item
	 * The string placeholders will be replaced
	 * with the product id, the name and the price respectively
	 *
	 * @since 1.0.0
	 *
	 * @return string The HTML template
	 */
	public function get_list_item_template_html() {
		ob_start();
		?>
			<span class="wvp-list-item" data-product_id="%1$s">
				<span class="wvp-list-item-name">%2$s</span>
				<span class="price wvp-list-item-price">%3$s</span>
			</span>
		<?php
		/**
		 * Filter the HTML template of the single variation list item
		 * The template must contain three string placeholders:
		 *     %1$s will be replaced with the variation id,
		 *     %2$s will be replaced with the variation name
		 *     %3$s will be replaced with the variation price
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The HTML template of the list item
		 */
		$html = ob_get_clean();

		$html = apply_filters( 'wc_variation_prices_list_item_template', $html );

		return preg_replace( [ "/\t/", "/\n/" ], '', $html );
	}

	/**
	 * Get an array with the prices of all the variations (for variable products)
	 * or the children (for grouped products)
	 * of the current product in the loop
	 *
	 * @since 1.0.0
	 * @since 1.0.5 Removed the $product argument that is now a class property
	 *
	 * @return array The array with the prices of all the variations or children products
	 */
	public function define_dependent_product_prices() {
		if ( $this->prices ) {
			return $this->prices;
		}

		$prices = [];

		$product      = $this->product;
		$product_type = $product->get_type();

		switch ( $product_type ) {
			case 'variable':
				$prices = $product->get_variation_prices( true );
				break;
			case 'grouped':
				$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
				$children         = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
				foreach ( $children as $child ) {
					if ( '' !== $child->get_price() ) {
						$prices['price'][ $child->get_id() ] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child ) : wc_get_price_excluding_tax( $child );
					}
					if ( '' !== $child->get_regular_price() ) {
						$prices['regular_price'][ $child->get_id() ] = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child, [ 'price' => $child->get_regular_price() ] ) : wc_get_price_excluding_tax( $child, [ 'price' => $child->get_regular_price() ] );
					}
				}

				if ( isset( $prices['price'] ) ) {
					asort( $prices['price'] );
				}

				if ( isset( $prices['regular_price'] ) ) {
					asort( $prices['regular_price'] );
				}

				break;
		}

		$this->prices = $prices;
	}

}
