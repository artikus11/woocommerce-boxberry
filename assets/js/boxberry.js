let boxberrySelectedPointAddress = false;
let boxberryPointCode            = false;
let boxberryPointName            = false;
let cdekData                     = '';

jQuery( document.body ).on( 'updated_checkout', function ( e, data ) {

	jQuery( e.target ).find( '#billing_city' ).on( 'input', function ( event ) {

		setTimeout( function () {
			jQuery( document.body ).trigger( 'update_checkout' );
		}, 1000 );

	} );

} );

jQuery( document ).on( 'click', function ( e ) {
	if ( e.target && (
		e.target instanceof HTMLElement
	) && e.target.getAttribute( 'data-boxberry-open' ) === 'true' ) {
		e.preventDefault();

		let selectPointLink = e.target;

		(
			function ( selectedPointLink ) {
				let city        = selectPointLink.getAttribute( 'data-boxberry-city' ) || undefined;
				let method      = selectPointLink.getAttribute( 'data-method' );
				let token       = selectPointLink.getAttribute( 'data-boxberry-token' );
				let targetStart = selectedPointLink.getAttribute( 'data-boxberry-target-start' );
				let weight      = selectPointLink.getAttribute( 'data-boxberry-weight' );
				let surch       = selectedPointLink.getAttribute( 'data-surch' );
				let paymentSum  = selectPointLink.getAttribute( 'data-paymentsum' );
				let orderSum    = selectPointLink.getAttribute( 'data-ordersum' );
				let height      = selectPointLink.getAttribute( 'data-height' );
				let width       = selectPointLink.getAttribute( 'data-width' );
				let depth       = selectPointLink.getAttribute( 'data-depth' );
				let api         = selectPointLink.getAttribute( 'data-api-url' );

				let boxberryPointSelectedHandler = function ( result ) {
					if ( typeof result === undefined ) {
						return;
					}

					boxberryPointCode            = result.id;
					boxberryPointName            = result.name.replace( 'Алма-Ата', 'Алматы' );
					boxberrySelectedPointAddress = result.address;
					let boxberrySelectedPointZip = result.zip;

					let addresSplit  = result.address.split( ',' );
					let insertAddres = 'ПВЗ: ' + addresSplit[ 2 ].trim() + (
						addresSplit[ 3 ] !== undefined ? addresSplit[ 3 ] : ''
					);

					if ( document.getElementById( 'shipping_address_1' ) ) {
						document.getElementById( 'shipping_address_1' ).value = insertAddres;
						if ( document.getElementById( 'billing_address_1' ) ) {
							document.getElementById( 'billing_address_1' ).value = insertAddres;
						}
					} else {
						if ( document.getElementById( 'billing_address_1' ) ) {
							document.getElementById( 'billing_address_1' ).value = insertAddres;
						}
					}

					if ( document.getElementById( 'shipping_state' ) ) {
						document.getElementById( 'shipping_state' ).value = insertAddres;
						if ( document.getElementById( 'billing_state' ) ) {
							document.getElementById( 'billing_state' ).value = insertAddres;
						}
					} else {
						if ( document.getElementById( 'billing_state' ) ) {
							document.getElementById( 'billing_state' ).value = insertAddres;
						}
					}

					if ( document.getElementById( 'shipping_postcode' ) ) {
						document.getElementById( 'shipping_postcode' ).value = boxberrySelectedPointZip;
						if ( document.getElementById( 'billing_postcode' ) ) {
							document.getElementById( 'billing_postcode' ).value = boxberrySelectedPointZip;
						}
					} else {
						if ( document.getElementById( 'billing_postcode' ) ) {
							document.getElementById( 'billing_postcode' ).value = boxberrySelectedPointZip;
						}
					}


					let formData = new FormData();
					formData.append( 'action', 'boxberry_update' );
					formData.append( 'method', method );
					formData.append( 'city', boxberryPointName );
					formData.append( 'cdekCityId', cdekData.code );
					formData.append( 'code', boxberryPointCode );
					formData.append( 'address', boxberrySelectedPointAddress );
					formData.append( 'price', result.price );

					bxbAjaxPost( formData ).then( function () {

						let cityData = jQuery( e.target ).closest( '.woocommerce-billing-fields' ).find( '#billing_city' );

						cityData.find( ':selected' ).val( boxberryPointName );
						cityData.find( ':selected' ).text( boxberryPointName );
						cityData.val( boxberryPointName );
						cityData.trigger( 'change' );

						jQuery( e.target ).closest( '.woocommerce-billing-fields' ).block( {
							message:    null,
							overlayCSS: {
								background: '#fff',
								'z-index':  1000000,
								opacity:    0.3
							}
						} );
						jQuery( document.body ).trigger( 'updated_shipping_method' );
						jQuery( document.body ).trigger( 'update_checkout' );

					} )

				};
				boxberry.versionAPI( api );
				boxberry.checkLocation( 0 );
				boxberry.sucrh( surch );
				boxberry.open( boxberryPointSelectedHandler, token, city, targetStart, orderSum, weight, paymentSum, height, width, depth );
			}
		)
		( selectPointLink )
	}
} );


async function bxbAjaxPost( data ) {
	await fetch( boxberry_handle.ajax_url,
		{
			method: 'POST',
			body:   data
		} );
}


function blockDelivery() {
	jQuery( '.woocommerce-billing-fields' ).block( {
		message:    null,
		overlayCSS: {
			background: '#fff',
			'z-index':  1000000,
			opacity:    0.3
		}
	} );
}


function getCityField() {
	if ( jQuery( '#billing_city' ).length && ! jQuery( '#ship-to-different-address-checkbox' ).prop( 'checked' ) ) {
		return jQuery( '#billing_city' );
	}

	if ( jQuery( '#shipping_city' ).length ) {
		return jQuery( '#shipping_city' );
	}

	return false;
}


/*
 jQuery( document.body ).on( 'updated_checkout', function ( e, xhr, data ) {

 let cityData;
 if ( boxberryPointName && getCityField() ) {
 cityData = jQuery( e.target ).find( '#billing_city' );
 cityData.find( ':selected' ).val( boxberryPointName );
 cityData.find( ':selected' ).text( boxberryPointName );
 cityData.val( boxberryPointName )
 cityData.trigger( 'change' );

 jQuery( document.body ).trigger( 'updated_checkout_city' );

 if ( getCityField().val().trim().toLowerCase().replace( 'ё', 'е' ).indexOf( boxberryPointName.toLowerCase().replace( 'ё', 'е' ) ) === -1 ) {

 boxberryPointCode            = false;
 boxberrySelectedPointAddress = false;
 boxberryPointName            = false;
 jQuery( document.body ).trigger( 'update_checkout' );
 console.log( getCityField().val() );
 }
 }

 } );
 */


jQuery( document ).on( 'change', 'input[name="payment_method"]', function () {
	jQuery( document.body ).trigger( 'update_checkout' );
} );


/*

 jQuery(document).ready(function () {
 let upd = 0;
 if (location.pathname === '/checkout/' && upd === 0) {
 upd = 1;
 if (getCityField().val().length) {
 jQuery(document.body).trigger('update_checkout');
 }
 } else {
 upd = 0;
 }
 console.log(jQuery( document.body ).find('#billing_city'));
 jQuery('#billing_postcode').on('blur',function(){
 if (!jQuery('#ship-to-different-address-checkbox').prop('checked')){
 jQuery( document.body ).trigger( 'update_checkout' );
 }
 });
 jQuery('#billing_state').on('blur',function(){
 if (!jQuery('#ship-to-different-address-checkbox').prop('checked')){
 jQuery( document.body ).trigger( 'update_checkout' );
 }
 });
 jQuery( document.body ).find('#billing_city').on('change',function(e){
 console.log(e);
 //if (!jQuery('#ship-to-different-address-checkbox').prop('checked')){
 jQuery( document.body ).trigger( 'update_checkout' );
 //	}
 });
 jQuery('#shipping_city').on('focusout',function(){
 jQuery( document.body ).trigger( 'update_checkout' );
 });
 jQuery('#shipping_state').on('focusout',function(){
 jQuery( document.body ).trigger( 'update_checkout' );
 });
 jQuery('#shipping_postcode').on('focusout',function(){
 jQuery( document.body ).trigger( 'update_checkout' );
 });


 });
 */
