( () => {
	const config = window.agencyCommerceRecentlyViewed;
	if ( ! config || ! window.localStorage ) {
		return;
	}

	const key = 'agencyCommerceRecentlyViewed';
	const limit = Number.parseInt( config.limit, 10 ) || 4;
	const currentId = Number.parseInt( config.productId, 10 ) || 0;
	let ids = [];

	try {
		ids = JSON.parse( window.localStorage.getItem( key ) || '[]' );
	} catch {
		ids = [];
	}

	ids = ids.map( Number ).filter( Boolean );

	if ( currentId ) {
		ids = [ currentId, ...ids.filter( ( id ) => id !== currentId ) ];
		window.localStorage.setItem(
			key,
			JSON.stringify( ids.slice( 0, limit ) )
		);
	}

	const containers = document.querySelectorAll(
		'[data-agency-commerce-recently-viewed]'
	);
	if ( ! containers.length || ! ids.length ) {
		return;
	}

	window
		.fetch( config.endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( {
				ids: ids.slice( currentId ? 1 : 0, limit + 1 ),
			} ),
		} )
		.then( ( response ) => response.json() )
		.then( ( payload ) => {
			if ( ! payload.html ) {
				return;
			}
			containers.forEach( ( container ) => {
				const target = container.querySelector(
					'[data-agency-commerce-recently-viewed-items]'
				);
				if ( target ) {
					target.innerHTML = payload.html;
					container.hidden = false;
				}
			} );
		} )
		.catch( () => {} );
} )();
