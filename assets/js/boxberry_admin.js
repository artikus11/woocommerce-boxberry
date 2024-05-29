document.addEventListener( 'click', function ( e ) {

	function sendPointData( data_id, result ) {
        const xhr = new XMLHttpRequest();

        xhr.open( 'POST', woocommerce_admin_meta_boxes.ajax_url, true );

        const fd = new FormData;

        fd.append( 'action', 'boxberry_admin_update' );
		fd.append( 'id', data_id );
		fd.append( 'code', result.id );
		fd.append( 'address', result.address );
		xhr.onreadystatechange = function () {
			if ( xhr.readyState === 4 && xhr.status === 200 ) {
				location.reload();
			}
		}
		xhr.send( fd );
	}


	if ( e.target && (e.target instanceof HTMLElement) && e.target.getAttribute( 'data-boxberry-open' ) === 'true' ) {
		e.preventDefault();

		var selectPointLink = e.target;

		( function ( selectedPointLink ) {
                const token   = '1$DCIlCpOeh0NkfiVjTUQNEQ8fPbjnIldR';
                const weight  = '5';
                const city    = selectPointLink.getAttribute( 'data-boxberry-city' ) || undefined;
                const data_id = selectPointLink.getAttribute( 'data-id' );

                const boxberryPointSelectedHandler = function ( result ) {
                    selectedPointLink.textContent = result.id;
                    sendPointData( data_id, result );
                };

                boxberry.open( boxberryPointSelectedHandler, token, city, '', '', weight );
			}
		)( selectPointLink );
	}
}, true );

jQuery( document ).ready( function () {
	if ( jQuery( location ).attr( 'href' ).indexOf( 'shipping&instance_id' ) >= 0 ) {
		let getBxbId     = jQuery( "input:visible[id*='woocommerce_boxberry']" );
		let getBxbSelect = jQuery( "select:visible[id*='woocommerce_boxberry']" );
		if ( getBxbId.length ) {
			getBxbId[ 3 ].id.indexOf( 'boxberry_courier' ) >= 0 ? jQuery( getBxbId[ 3 ] ).closest( 'tr' ).hide() : '';
			getBxbSelect[ 4 ].id.indexOf( 'boxberry_courier' ) >= 0 ? jQuery( getBxbSelect[ 4 ] ).closest( 'tr' ).hide() : '';
			getBxbSelect[ 5 ].id.indexOf( 'boxberry_self' ) >= 0 ? jQuery( getBxbSelect[ 5 ] ).closest( 'tr' ).hide() : '';
			let setBxbId = '#' + getBxbId[ 1 ].id;
			jQuery( setBxbId ).on( 'change', function () {
				jQuery( this ).val().trim().length !== 32 ? alert( 'Токен указан с ошибкой' ) : '';
			} );
		}
	}
} );

