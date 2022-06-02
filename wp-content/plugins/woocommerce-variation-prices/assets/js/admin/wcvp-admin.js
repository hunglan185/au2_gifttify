
(function( $ ) {
    "use strict";

    var toggleChildSettings = function( $parent ) {
		let show = false;
		const toggleVal = $parent.data( 'toggleVal' ),
			  closestAncestorTag = $parent.data( 'ancestorTag' ) || 'tr',
			  $children = $parent.closest( '.form-table' ).find( '.' + $parent.data( 'child-class' ) ).closest( closestAncestorTag );

		$children.each( function() {
			if ( 'radio' === $parent.attr( 'type' ) ) {
				show = $parent.prop( 'checked' ) && toggleVal == $parent.val();
			} else if ( 'checkbox' === $parent.attr( 'type' ) ) {
				if ( typeof toggleVal === 'undefined' || 1 == toggleVal ) {
					show = $parent.prop( 'checked' );
				} else {
					show = !$parent.prop( 'checked' );
				}
			} else if ( 'select' === $parent.prop( 'tagName' ).toLowerCase() ) {
				var foundOption = `.${$parent.data( 'child-class' )}-${$parent.val()}`;
				show = ! ! $( this ).find( foundOption ).length;
			} else {
				show = ( toggleVal == $parent.val() );
			}
			$( this ).toggle( show );
		});
	};

	$( () => {

		$( '.form-table .wvp-toggle-parent' ).each( function() {

			toggleChildSettings( $( this ) );

			$( this ).on( 'change', function() {
				toggleChildSettings( $( this ) );
			} );

		} );

	} );

})( jQuery );
