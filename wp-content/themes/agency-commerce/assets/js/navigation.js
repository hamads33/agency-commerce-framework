( () => {
	'use strict';

	const focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join( ',' );

	const menuToggle = document.querySelector( '.ac-menu-toggle' );
	const mobileMenu = document.getElementById( 'ac-mobile-menu' );
	const mobileOverlay = document.querySelector( '.ac-mobile-overlay' );
	const closeButtons = document.querySelectorAll(
		'[data-mobile-menu-close]'
	);
	const searchToggle = document.querySelector( '.ac-search-toggle' );
	const searchPanel = document.getElementById( 'ac-search-panel' );
	const searchClose = document.querySelector( '.ac-search-close' );
	let previousFocus = null;

	const getFocusable = ( container ) => {
		if ( ! container ) {
			return [];
		}

		return Array.from(
			container.querySelectorAll( focusableSelector )
		).filter( ( element ) => {
			return (
				! element.hasAttribute( 'hidden' ) &&
				element.offsetParent !== null
			);
		} );
	};

	const openMenu = () => {
		if ( ! menuToggle || ! mobileMenu ) {
			return;
		}

		previousFocus = menuToggle.ownerDocument.activeElement;
		menuToggle.setAttribute( 'aria-expanded', 'true' );
		mobileMenu.hidden = false;
		mobileMenu.setAttribute( 'aria-hidden', 'false' );

		if ( mobileOverlay ) {
			mobileOverlay.hidden = false;
		}

		document.documentElement.classList.add( 'has-mobile-menu-open' );

		const focusable = getFocusable( mobileMenu );
		if ( focusable.length ) {
			focusable[ 0 ].focus();
		}
	};

	const closeMenu = () => {
		if ( ! menuToggle || ! mobileMenu ) {
			return;
		}

		menuToggle.setAttribute( 'aria-expanded', 'false' );
		mobileMenu.setAttribute( 'aria-hidden', 'true' );
		mobileMenu.hidden = true;

		if ( mobileOverlay ) {
			mobileOverlay.hidden = true;
		}

		document.documentElement.classList.remove( 'has-mobile-menu-open' );

		if ( previousFocus && typeof previousFocus.focus === 'function' ) {
			previousFocus.focus();
		}
	};

	const trapMenuFocus = ( event ) => {
		if ( event.key !== 'Tab' || ! mobileMenu || mobileMenu.hidden ) {
			return;
		}

		const focusable = getFocusable( mobileMenu );
		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];

		if (
			event.shiftKey &&
			mobileMenu.ownerDocument.activeElement === first
		) {
			event.preventDefault();
			last.focus();
		} else if (
			! event.shiftKey &&
			mobileMenu.ownerDocument.activeElement === last
		) {
			event.preventDefault();
			first.focus();
		}
	};

	const openSearch = () => {
		if ( ! searchToggle || ! searchPanel ) {
			return;
		}

		searchToggle.setAttribute( 'aria-expanded', 'true' );
		searchPanel.hidden = false;
		const input = searchPanel.querySelector(
			'input[type="search"], input[name="s"]'
		);
		if ( input ) {
			input.focus();
		}
	};

	const closeSearch = () => {
		if ( ! searchToggle || ! searchPanel ) {
			return;
		}

		searchToggle.setAttribute( 'aria-expanded', 'false' );
		searchPanel.hidden = true;
		searchToggle.focus();
	};

	if ( menuToggle && mobileMenu ) {
		menuToggle.addEventListener( 'click', () => {
			if ( mobileMenu.hidden ) {
				openMenu();
			} else {
				closeMenu();
			}
		} );

		closeButtons.forEach( ( button ) => {
			button.addEventListener( 'click', closeMenu );
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && ! mobileMenu.hidden ) {
				closeMenu();
			}
		} );

		mobileMenu.addEventListener( 'keydown', trapMenuFocus );
	}

	if ( searchToggle && searchPanel ) {
		searchToggle.addEventListener( 'click', () => {
			if ( searchPanel.hidden ) {
				openSearch();
			} else {
				closeSearch();
			}
		} );

		if ( searchClose ) {
			searchClose.addEventListener( 'click', closeSearch );
		}

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && ! searchPanel.hidden ) {
				closeSearch();
			}
		} );
	}
} )();
