(function( $, document ) {
    "use strict";

	const updatePriceRange = ( $form ) => {

		if ( '1' !== wcvp_params.show_selected ) {
			return false;
		}

		const attributes = {},
			  unchosenAttributes = [],
			  $wvp = $form.closest('.product[id^="product"], .product.product-row').find( '.wvp-price-range' ),
			  rangeFormat = $wvp.data('format'),
			  productVariations = $form.data('product_variations');

		if ( ! productVariations ) {
			$wvp.html( $wvp.data('default') )
			.toggleClass( 'price', ! $wvp.find( '.price' ).length );

			return false;
		}

		$form.find('select').each( (i, e) => {
			if ( $(e).val() ) {
				attributes[$(e).data('attribute_name')] = $(e).val();
			} else {
				unchosenAttributes.push( $(e).data('attribute_name') );
			}
		});

		if ( unchosenAttributes.length === $form.find('select').length ) {
			$wvp.html( $wvp.data('default') )
			.toggleClass( 'price', ! $wvp.find( '.price' ).length );

			return false;
		}

		const selectedVariations = productVariations.filter( pv => Object.entries(attributes).reduce( (s, e) => s + 1 * ( e[1] === pv.attributes[e[0]] ), 0 ) === Object.values(attributes).length );
		const min = selectedVariations.reduce( (min, v) => Math.min( min, v.display_price ), Infinity ),
			  max = selectedVariations.reduce( (max, v) => Math.max( max, v.display_price ), 0 ),
			  minVariation = selectedVariations.find( v => v.display_price === min ),
			  maxVariation = selectedVariations.find( v => v.display_price === max );

		if ( 'list' === wcvp_params.format_type ) {

			// when the list of variation is selected, update the price only when all the attributes have been defined
			if ( '1' === wcvp_params.show_selected && selectedVariations.length > 1 ) {
				return false;
			}

			const list = selectedVariations.map( v => {
				const name = unchosenAttributes.map( k => v.attribute_names[k] ).join(', ');

				return wcvp_params.list_item_template.replace( '%1$s', v.variation_id )
													 .replace( '%2$s', name )
													 .replace( '%3$s', v.price_html || v.display_price_html );
			}).join('');
			$wvp.html( `<span class="wvp-list">${list}</span>` )
			.toggleClass( 'price', ! $wvp.find( '.price' ).length );
		} else if ( selectedVariations.length === 1 || min === max ) {
			const $price = $('<span>', { class: 'wvp-delimiter' } ).append(
				$( minVariation.price_html || minVariation.display_price_html ),
				' '
			);
			$wvp.html( $price.html() )
			.toggleClass( 'price', ! $wvp.find( '.price' ).length );
		} else {
			$wvp.html(
				rangeFormat
				.replace( '%1$s', minVariation.price_html || minVariation.display_price_html )
				.replace( '%2$s', maxVariation.price_html || maxVariation.display_price_html )
			).toggleClass( 'price', ! $wvp.find( '.price' ).length );

			$wvp.find('del').remove();

			if ( $wvp.data('suffix') ) {
				// Fix the price suffix duplication
				$wvp.find( '.woocommerce-price-suffix' ).slice(0,-1).remove();

				const $suffix = $wvp.find( '.woocommerce-price-suffix' ).detach();
				$wvp.append( $suffix )
					.toggleClass( 'price', ! $wvp.find( '.price' ).length );
			} else {
				$wvp.find( '.woocommerce-price-suffix' ).remove();
			}
		}
	};

	$( (event) => {

		if ( ! wcvp_params.product_types.includes( 'variable' ) ) {
			return false;
		}
		// All the variations are listed as a JSON object
		// in the data-product_variations attribute
		$('[data-product_variations]').each( (i, e) => {
			if ( $( e ).data('product_variations') !== 'false' ) {
				updatePriceRange( $( e ) );
			}
		});

		// The number of variations exceeds the variation threshold
		// and the selected variation is loaded via AJAX
		// only after all attributes have been selected
		$( document ).on( 'found_variation', (event, variation) => {
			if ( '1' !== wcvp_params.show_selected ) {
				return false;
			}

			const $wvp = $(event.target).closest('.product').find( '.wvp-price-range' ),
				  $price = $( variation.price_html || variation.display_price_html );

			if ( 'list' !== wcvp_params.format_type ) {
				$price.find('.woocommerce-price-suffix').remove();
			}

			$wvp.html( variation.price_html || variation.display_price_html )
			.toggleClass( 'price', ! $wvp.find( '.price' ).length );

		});

		$('[data-product_variations] select').on('change', (event) => {
			const $form = $( event.target ).closest('form');

			updatePriceRange( $form );
		});

	} );


})( jQuery, document );
