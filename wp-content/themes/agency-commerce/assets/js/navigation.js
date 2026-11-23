( () => {
	const drawer = document.getElementById( 'ac-mobile-menu' );
	const menuToggle = document.querySelector( '.ac-menu-toggle' );
	const search = document.getElementById( 'ac-search-panel' );
	const searchToggle = document.querySelector( '.ac-search-toggle' );

	function closeSearch( restoreFocus = true ) {
		if ( ! search || ! searchToggle || search.hidden ) {
			return;
		}
		search.hidden = true;
		searchToggle.setAttribute( 'aria-expanded', 'false' );
		if ( restoreFocus ) {
			searchToggle.focus();
		}
	}

	if ( drawer && menuToggle && typeof drawer.showModal === 'function' ) {
		menuToggle.addEventListener( 'click', () => {
			closeSearch( false );
			drawer.showModal();
			menuToggle.setAttribute( 'aria-expanded', 'true' );
			document.documentElement.classList.add( 'has-mobile-menu-open' );
		} );
		drawer
			.querySelector( '[data-mobile-menu-close]' )
			?.addEventListener( 'click', () => drawer.close() );
		drawer.addEventListener( 'click', ( event ) => {
			const bounds = drawer.getBoundingClientRect();
			if (
				event.target === drawer &&
				( event.clientX < bounds.left || event.clientX > bounds.right )
			) {
				drawer.close();
			}
		} );
		drawer.addEventListener( 'keydown', ( event ) => {
			if ( event.key !== 'Tab' ) {
				return;
			}
			const links = Array.from(
				drawer.querySelectorAll( 'a[href], button:not([disabled])' )
			).filter( ( control ) => control.getClientRects().length );
			const first = links[ 0 ];
			const last = links.at( -1 );
			if ( event.shiftKey && event.target === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && event.target === last ) {
				event.preventDefault();
				first.focus();
			}
		} );
		drawer.addEventListener( 'close', () => {
			menuToggle.setAttribute( 'aria-expanded', 'false' );
			document.documentElement.classList.remove( 'has-mobile-menu-open' );
			const target = menuToggle.getClientRects().length
				? menuToggle
				: document.querySelector(
						'.ac-primary-navigation a, .ac-site-branding a'
				  );
			target?.focus();
		} );
		window
			.matchMedia( '(min-width: 70em)' )
			.addEventListener( 'change', ( event ) => {
				if ( event.matches && drawer.open ) {
					drawer.close();
				}
			} );
	}

	if ( search && searchToggle ) {
		searchToggle.closest( '.ac-site-header' )?.append( search );
		searchToggle.addEventListener( 'click', () => {
			if ( ! search.hidden ) {
				closeSearch();
				return;
			}
			search.hidden = false;
			searchToggle.setAttribute( 'aria-expanded', 'true' );
			search.querySelector( 'input[name="s"]' )?.focus();
		} );
		search
			.querySelector( '.ac-search-close' )
			?.addEventListener( 'click', () => closeSearch() );
		document.addEventListener( 'keydown', ( event ) => {
			if (
				event.key === 'Escape' &&
				( search.contains( event.target ) ||
					searchToggle === event.target )
			) {
				closeSearch();
			}
		} );
	}

	const disclosures = new Map();
	document
		.querySelectorAll( '.ac-menu--desktop, .ac-menu--mobile' )
		.forEach( ( menu, menuIndex ) => {
			menu.querySelectorAll( 'li' ).forEach( ( item, itemIndex ) => {
				const submenu = item.querySelector( ':scope > ul' );
				const link = item.querySelector( ':scope > a' );
				if ( ! submenu || ! link ) {
					return;
				}
				const toggle = document.createElement( 'button' );
				toggle.type = 'button';
				toggle.className = 'ac-submenu-toggle';
				toggle.setAttribute( 'aria-label', link.textContent.trim() );
				submenu.id ||= `ac-submenu-${ menuIndex }-${ itemIndex }`;
				toggle.setAttribute( 'aria-controls', submenu.id );
				item.insertBefore( toggle, submenu );
				disclosures.set( toggle, submenu );
				setExpanded( toggle, false );

				toggle.addEventListener( 'click', () => {
					const open = submenu.hidden;
					disclosures.forEach( ( panel, control ) => {
						if (
							control !== toggle &&
							! panel.contains( toggle )
						) {
							setExpanded( control, false );
						}
					} );
					setExpanded( toggle, open );
				} );
				item.addEventListener( 'keydown', ( event ) => {
					if ( event.key === 'Escape' && ! submenu.hidden ) {
						event.preventDefault();
						event.stopPropagation();
						setExpanded( toggle, false );
						toggle.focus();
					}
				} );
			} );
		} );

	function setExpanded( toggle, open ) {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		disclosures.get( toggle ).hidden = ! open;
	}

	document.addEventListener( 'click', ( event ) => {
		disclosures.forEach( ( submenu, toggle ) => {
			if ( ! toggle.parentElement.contains( event.target ) ) {
				setExpanded( toggle, false );
			}
		} );
	} );
} )();
