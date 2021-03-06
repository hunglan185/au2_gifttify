<?php
defined( 'ABSPATH' ) or die( 'Keep Silent' );

/** @var $args */

$product = $args['product'];

$currency       = get_woocommerce_currency();
$use_transient  = wc_string_to_bool( woo_variation_swatches()->get_option( 'use_transient' ) );
$transient_name = sprintf( 'wvs_archive_template_%s_%s', $product->get_id(), $currency );
$cache          = new Woo_Variation_Swatches_Cache( $transient_name, 'wvs_archive_template' );

// Clear cache
if ( isset( $_GET['wvs_clear_transient'] ) ) {
	$cache->delete_transient();
}

// Return cache
if ( $use_transient ) {
	$transient_html = $cache->get_transient( $transient_name );
	if ( ! empty( $transient_html ) ) {
		echo $transient_html . '<!-- from wvs_pro_archive_variation_template  -->';

		return;
	}
}

$attributes = $product->get_variation_attributes();

$attribute_keys = array_keys( $attributes );

// Get Available variations?
if ( wc_string_to_bool( woo_variation_swatches()->get_option( 'ajax_load_archive_variation' ) ) ) {
	$variations_attr = 'false';
} else {
	$get_variations = count( $product->get_children() ) <= apply_filters( 'woo_variation_swatches_archive_ajax_variation_threshold', 30, $product );

/*	if ( $get_variations ) {
		$available_variations     = $product->get_available_variations();
		$new_available_variations = array();
		$a                        = array(
			'attributes',
			'is_in_stock',
			'image',
			'variation_id',
			'price_html',
			'variation_is_visible',
			'max_qty',
			'wvs_stock_left'
		);


		foreach ( $available_variations as $key => $variation ) {
			$new_available_variations[ $key ] = array(
				'attributes'           => $variation['attributes'],
				'is_in_stock'          => $variation['is_in_stock'],
				'variation_id'         => $variation['variation_id'],
				'price_html'           => $variation['price_html'],
				'variation_is_visible' => $variation['variation_is_visible'],
				'max_qty'              => $variation['max_qty'],
				'image_id'              => $variation['image_id'],
			);

			if ( isset( $variation['image'] ) && ! empty( $variation['image'] ) ) {
				if ( isset( $variation['image']['thumb_src'] ) && ! empty( $variation['image']['thumb_src'] ) ) {
					$new_available_variations[ $key ]['image']['thumb_src'] = $variation['image']['thumb_src'];
				}
				if ( isset( $variation['image']['thumb_src_w'] ) && ! empty( $variation['image']['thumb_src_w'] ) ) {
					$new_available_variations[ $key ]['image']['thumb_src_w'] = $variation['image']['thumb_src_w'];
				}
				if ( isset( $variation['image']['thumb_src_h'] ) && ! empty( $variation['image']['thumb_src_h'] ) ) {
					$new_available_variations[ $key ]['image']['thumb_src_h'] = $variation['image']['thumb_src_h'];
				}
			}
		}

	}

	print_r( $product->get_available_variations());
	print_r( $new_available_variations);

	die;*/


	 $available_variations = $get_variations ? $product->get_available_variations() : false;
	//$available_variations = $get_variations ? $new_available_variations : false;
	$variations_json      = wp_json_encode( $available_variations );
	$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
}

$show_clear   = wc_string_to_bool( woo_variation_swatches()->get_option( 'show_clear_on_archive' ) );
$catalog_mode = wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_catalog_mode' ) );
// $catalog_attribute     = woo_variation_swatches()->get_option( 'catalog_mode_attribute' );

// Global Catalog Attribute
$catalog_attribute     = wc_variation_attribute_name( woo_variation_swatches()->get_option( 'catalog_mode_attribute' ) );
$has_catalog_attribute = false;

if ( $catalog_mode ) {
	$product_settings = wvs_pro_get_product_option( $product->get_id() );
	if ( isset( $product_settings['catalog_attribute'] ) && ! empty( $product_settings['catalog_attribute'] ) ) {
		$catalog_attribute = wc_variation_attribute_name( $product_settings['catalog_attribute'] );
	}

	foreach ( $attributes as $attribute_name => $options ) {
		if ( $catalog_attribute == wc_variation_attribute_name( $attribute_name ) ) {
			$has_catalog_attribute = true;
		}
	}

	if ( ! $has_catalog_attribute ) {
		return;
	}
}

$single_variation_preview_attribute = '';
if ( wc_string_to_bool( woo_variation_swatches()->get_option( 'enable_single_variation_preview_archive' ) ) ) {
	$single_variation_preview_attribute = str_ireplace( '%', '\\%', sanitize_title( wvs_pro_get_product_option( $product->get_id(), 'single_variation_preview_attribute' ) ) );
}

ob_start();

?>

	<div class="variations_form wvs-archive-variation-wrapper" data-single_variation_preview_attribute="<?php echo esc_attr( $single_variation_preview_attribute ) ?>" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
		<ul class="variations">
			<?php

			foreach ( $attributes as $attribute_name => $options ) :

				$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );

				if ( $catalog_mode ) {
					if ( $catalog_attribute == wc_variation_attribute_name( $attribute_name ) ) {
						echo '<li>';
						wc_dropdown_variation_attribute_options( array(
							'options'    => $options,
							'attribute'  => $attribute_name,
							'product'    => $product,
							'selected'   => $selected,
							'is_archive' => true
						) );
						echo '</li>';
					}
				} else {
					echo '<li>';
					wc_dropdown_variation_attribute_options( array(
						'options'    => $options,
						'attribute'  => $attribute_name,
						'product'    => $product,
						'selected'   => $selected,
						'is_archive' => true
					) );
					echo '</li>';
				}
			endforeach;

			if ( $show_clear && ! $catalog_mode ):
				echo apply_filters( 'woocommerce_reset_variations_link', '<li class="reset_variations woo_variation_swatches_archive_reset_variations"><a href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a></li>' );
			endif;
			?>
		</ul>
	</div>

<?php

$data = ob_get_clean();
// Set cache
if ( $use_transient ) {
	$cache->set_transient( $data, DAY_IN_SECONDS );
}
echo $data;
