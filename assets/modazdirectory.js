jQuery( document ).ready( function( $ ) {	
	// trigger selection
	$( '#modazdirectory__select' ).on( 'change', function( e ) {
		var azLetter = $( this ).find( 'option:selected' ).text();
		azRequest( azLetter );
		azPush( azLetter );
	});
	
	// trigger click
	$( '.modazdirectory__link' ).on( 'click', function( e ) {
		e.preventDefault();
		var azLetter = this.rel;
		azRequest( azLetter );
		azPush( azLetter );
	});
});

var azPush = function( letter ) {
	// history API
	if( window.history && history.pushState ) {
		var azURL = '?lastletter=' + letter + '#modazdirectory';
		history.pushState( letter, '', azURL );
	}
};

window.addEventListener('popstate', function( e ) {
	if( e.state !== null ) azRequest( e.state );
});

var azRequest = function( letter ) {
	// Joomla Ajax request/call
	var request = {
		'option'	:	'com_ajax',
		'module'	:	'azdirectory',
		'method'	:	'getContacts',
		'data'		: 	letter,
		'format'	:	'json'
	};
	var jqxhr = jQuery.ajax({
		method		:	'get',
		data		:	request,
		dataType	:	'json'
	});
	jqxhr.done( function( msg ) {
		// empty the results
		jQuery( '.modazdirectory__results' ).empty();
		
		// generate Heading 1
		jQuery( '<h1>' )
			.text( msg['data']['lastletter'] )
			.appendTo( '.modazdirectory__results' );
		
		// get the module parameters
		var params = msg['data']['mod_params'];

		// get the contact information
		var response = msg['data']['json_array'];

		// create the individual results
		jQuery.each( response, function( k, v ) {

			var result = jQuery( '<div/>' ).addClass( 'modazdirectory__result' );
			
			if( params['show_image'] == 1 ) {
				jQuery( '<img />' )
					.attr({
						'src': msg['data']['juri_base'] + v['image'],
						'alt': v['name']
					})
					.addClass( 'modazdirectory__image' )
					.appendTo( result );
			}
			
			var contact = jQuery( '<div/>' );
			
			showContact( params, 'name', 'h3', v, contact );
			
			showContact( params, 'con_position', 'p', v, contact );
			
			showContact( params, 'address', 'p', v, contact );
			
			// start: exceptions for city, state zip
			var paraAddress = jQuery( '<p>' );

			showContact( params, 'suburb', 'span', v, paraAddress );

			if( ( azVerify( params, 'suburb', v ) == 1 ) && ( azVerify( params, 'state', v ) == 1 ) ) {
				jQuery( '<span>' )
					.text( ', ' + v['state'] )
					.appendTo( paraAddress );
			} else {
				showContact( params, 'state', 'span', v, paraAddress );
			}

			if( ( ( azVerify( params, 'suburb', v ) == 1 ) || ( azVerify( params, 'state', v ) == 1 ) ) && azVerify( params, 'postcode', v ) == 1 ) {
				jQuery( '<span>' )
					.text( ' ' + v['postcode'] )
					.appendTo( paraAddress );
			} else {
				showContact( params, 'postcode', 'span', v, paraAddress );
			}

			paraAddress.appendTo( contact );
			// end: exceptions for city, state zip
			
			showContact( params, 'telephone', 'p', v, contact, 't: ' );
			
			showContact( params, 'mobile', 'p', v, contact, 'm: ' );
			
			showContact( params, 'fax', 'p', v, contact, 'f: ' );
			
			showContact( params, 'email_to', 'p', v, contact );
			
			showContact( params, 'webpage', 'p', v, contact );
			
			contact.appendTo( result );

			result.appendTo( '.modazdirectory__results' );
		});
		
		// group results in rows of 2
		var results = jQuery( '.modazdirectory__result' );
		for( var i = 0; i < results.length; i += 2 ) {
			results.slice( i, i + 2 ).wrapAll( '<div class="modazdirectory__row"></div>' );
		};
		
		// broken image handling
		jQuery( 'img.modazdirectory__image' ).on( 'error', function () {
			var iconCamera = jQuery( '<span>' ).addClass( 'modazdirectory__icon-camera' );
			jQuery( this ).replaceWith( iconCamera );
		});

		// console.log( msg );
	});
	jqxhr.fail( function( jqXHR, textStatus ) {
		// console.log( jqXHR + textStatus );
	});
};

var showContact = function( params, key, tag, value, parent, label ) {
	// label is an optional parameter
	var label = label || '';
	// if the parameter is set to show and there is a value for key
	if( azVerify( params, key, value ) == 1 ) {
		// generate the DOM
		jQuery( '<' + tag + '>' )
			.text( label + value[key] )
			.appendTo( parent );
	} else {
		return false;
	}
};

var azVerify = function( params, key, value ) {
	return ( ( params['show_' + key] == 1 ) && ( value[key] ) ) ? 1 : 0;
};