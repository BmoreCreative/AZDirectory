document.addEventListener( "DOMContentLoaded", function() {	
	azTriggerSelect();	// trigger selection
	azTriggerClick(); // trigger click
	azModal(); // trigger modal
	azPagination(); // trigger pagination
});

var azPagination = function() {
	var azPageNav = document.querySelectorAll( '.modazdirectory__pagination a' );
	for( var i = 0; i < azPageNav.length; i++ ){
		azPageNav[i].addEventListener( 'click', function( e ){
			// disable default behavior
			e.preventDefault();
			
			// get the parameters to pass to azRequest
			var azUrlParams = new URLSearchParams( e.target.href );
			var azLetter = 'All';
			if( azUrlParams.get( 'lastletter' ) ){
				azLetter = azUrlParams.get( 'lastletter' );
			}
			var azStart = azUrlParams.get( 'start' );
			azRequest( azLetter, azStart );
			
			// anchor
			var modazdirectory = document.getElementById( 'modazdirectory' );
			modazdirectory.scrollIntoView();
		});
	}
};

var azPush = function( letter ) {
	// history API
	if( window.history && history.pushState ) {
		var azURL = '?lastletter=' + letter + '#modazdirectory';
		history.pushState( letter, '', azURL );
	}
};

var azTriggerClick = function() {
	var azLink = document.querySelectorAll( '.modazdirectory__link' );
	for( var i = 0; i < azLink.length; i++ ){
		azLink[i].addEventListener( 'click', function( e ){
			e.preventDefault();
			var azLetter = this.rel;
			azRequest( azLetter );
			azPush( azLetter );
		});
	}
};

var azTriggerSelect = function() {
	var azSelect = document.getElementById( 'modazdirectory__select' );
	azSelect.addEventListener( 'change', function( e ){
		var azLetter;
		var azSelectedIndex = e.target.selectedIndex;
		var azOptions = e.target.options;
		
		// if the first option is selected => "Last Name"
		if( azOptions[azSelectedIndex].index == 0 ){
			// select the second option => "All"
			azLetter = azOptions[1].text;
		} else {
			azLetter = azOptions[azSelectedIndex].text;
		}
		
		azRequest( azLetter );
		azPush( azLetter );
	});
};

var azSelectDefault = function( letter ) {
	var azSelect = document.getElementById( 'modazdirectory__select' );
	for( var i = 0; i < azSelect.options.length; i++ ){
		if( azSelect.options[i].text == letter ){
			azSelect.options[i].selected = true;
		}
	}
};

window.addEventListener( 'popstate', function( e ) {
	if( e.state !== null ) azRequest( e.state );
});

var azRequest = function( letter, start ) {
	start = start || "0";
	
	if( letter == 'All' ) letter = Joomla.JText._( 'JALL' );
	
	var azUrl = '?option=com_ajax&module=azdirectory&method=getContacts&data[letter]=' + letter + '&data[start]=' + start + '&data[title]=' + encodeURIComponent( modazModuleTitle ) + '&format=json';
	fetch( azUrl )
		.then( function( azResp ){
			if( azResp.ok ){
				return azResp.text();
			} else {
				return Promise.reject( azResp );
			}
		})
		.then( function( azHtml ){
			// replace with the DOM with the response
			var azDirectory = document.getElementById( 'modazdirectory' );
			azDirectory.innerHTML = azHtml;
			azSelectDefault( letter );
		})
		.then( azEmailCloak )
		.then( azTriggerSelect )
		.then( azTriggerClick )
		.then( azModal )
		.then( azPagination )
		.catch( function( azErr ){
			console.warn( azErr );
		});
};

var azEmailCloak = function() {
	var azDirectory = document.getElementById( 'modazdirectory' );
	
	// look for embedded scripts within the HTML
	var azScripts = azDirectory.querySelectorAll( 'script' ), azScript, azFixedScript;
	
	// replace them with newly created <script> elements
	for( var i = 0; i < azScripts.length; i++ ){
		azScript = azScripts[i];
		azFixedScript = document.createElement( 'script' );
		azFixedScript.type = azScript.type;
		if( azScript.innerHTML ){
			azFixedScript.innerHTML = azScript.innerHTML;
		} else {
			azFixedScript.src = azScript.src;
		}
		azFixedScript.async = false;
		
		azScript.replaceWith( azFixedScript );
	}
}

var azModal = function() {

	if( modazNameHyperlink == 0 ) return;
	
	var modazModal = document.getElementById( 'modazdirectory__modal' );
	// fire immediately
	modazModal.addEventListener( 'show.bs.modal', function( e ){
		// get the data-remote attribute
		var modazRemoteUrl = e.relatedTarget.dataset.remote;
		// jQuery .load replacement
		fetch( modazRemoteUrl )
		.then( function( modazResp ){
			return modazResp.text();
		})
		.then( function( modazBody ){
			document.getElementById( 'modazdirectory__modal-body' ).innerHTML = modazBody;
		});
	});
};