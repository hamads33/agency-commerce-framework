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

	const filterToggle = document.querySelector( '.ac-filter-toggle' );
	const filterDrawer = document.getElementById( 'ac-filter-drawer' );
	const filterOverlay = document.querySelector( '.ac-filter-overlay' );
	const closeButtons = document.querySelectorAll( '[data-filter-close]' );
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

	const openDrawer = () => {
		if ( ! filterToggle || ! filterDrawer ) {
			return;
		}

		previousFocus = filterToggle.ownerDocument.activeElement;
		filterToggle.setAttribute( 'aria-expanded', 'true' );
		filterDrawer.hidden = false;
		filterDrawer.setAttribute( 'aria-hidden', 'false' );

		if ( filterOverlay ) {
			filterOverlay.hidden = false;
		}

		document.documentElement.classList.add( 'has-filter-drawer-open' );

		const focusable = getFocusable( filterDrawer );
		if ( focusable.length ) {
			focusable[ 0 ].focus();
		}
	};

	const closeDrawer = () => {
		if ( ! filterToggle || ! filterDrawer ) {
			return;
		}

		filterToggle.setAttribute( 'aria-expanded', 'false' );
		filterDrawer.setAttribute( 'aria-hidden', 'true' );
		filterDrawer.hidden = true;

		if ( filterOverlay ) {
			filterOverlay.hidden = true;
		}

		document.documentElement.classList.remove( 'has-filter-drawer-open' );

		if ( previousFocus && typeof previousFocus.focus === 'function' ) {
			previousFocus.focus();
		}
	};

	const trapDrawerFocus = ( event ) => {
		if ( event.key !== 'Tab' || ! filterDrawer || filterDrawer.hidden ) {
			return;
		}

		const focusable = getFocusable( filterDrawer );
		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];

		if (
			event.shiftKey &&
			filterDrawer.ownerDocument.activeElement === first
		) {
			event.preventDefault();
			last.focus();
		} else if (
			! event.shiftKey &&
			filterDrawer.ownerDocument.activeElement === last
		) {
			event.preventDefault();
			first.focus();
		}
	};

	if ( filterToggle && filterDrawer ) {
		filterToggle.addEventListener( 'click', () => {
			if ( filterDrawer.hidden ) {
				openDrawer();
			} else {
				closeDrawer();
			}
		} );

		closeButtons.forEach( ( button ) => {
			button.addEventListener( 'click', closeDrawer );
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && ! filterDrawer.hidden ) {
				closeDrawer();
			}
		} );

		filterDrawer.addEventListener( 'keydown', trapDrawerFocus );
	}

	const whatsappButton = document.querySelector( '.ac-whatsapp-enquiry' );
	const variationForm = document.querySelector( '.variations_form' );

	const buildVariationLabel = ( form ) => {
		if ( ! form ) {
			return '';
		}

		const labels = Array.from( form.querySelectorAll( 'select[name^="attribute_"]' ) )
			.map( ( select ) => {
				const currentValue = select.value;
				if ( ! currentValue ) {
					return '';
				}

				const option = select.options[ select.selectedIndex ];
				const fieldLabel = select
					.closest( 'tr' )
					?.querySelector( 'label' )
					?.textContent?.trim()
					?.replace( /:\s*$/, '' ) || select.name.replace( /^attribute_/, '' );
				const optionLabel = option ? option.textContent.trim() : currentValue;

				return `${ fieldLabel }: ${ optionLabel }`;
			} )
			.filter( Boolean );

		return labels.join( ', ' );
	};

	const updateWhatsAppButton = ( forceDisable = false ) => {
		if ( ! whatsappButton ) {
			return;
		}

		const phone = ( whatsappButton.dataset.whatsappPhone || '' ).replace( /\D+/g, '' );

		if ( ! phone ) {
			return;
		}

		const template = whatsappButton.dataset.whatsappTemplate || '';
		const productName = whatsappButton.dataset.productName || '';
		const productUrl = whatsappButton.dataset.productUrl || window.location.href;
		const sku = whatsappButton.dataset.productSku || '';
		const variation = buildVariationLabel( variationForm );
		const messageTemplate = template || 'Hello, I am interested in {product_name}.\nProduct: {product_url}\nSKU: {sku}\nVariation: {variation}';
		const message = messageTemplate
			.replace( /\{product_name\}/g, productName )
			.replace( /\{product_url\}/g, productUrl )
			.replace( /\{sku\}/g, sku )
			.replace( /\{variation\}/g, variation || 'Not selected' );

		if ( forceDisable || ( whatsappButton.dataset.whatsappNeedsVariation === 'true' && ! variation ) ) {
			whatsappButton.href = '#';
			whatsappButton.setAttribute( 'aria-disabled', 'true' );
			whatsappButton.classList.add( 'is-variation-pending' );
			return;
		}

		whatsappButton.href = `https://wa.me/${ encodeURIComponent( phone ) }?text=${ encodeURIComponent( message ) }`;
		whatsappButton.removeAttribute( 'aria-disabled' );
		whatsappButton.classList.remove( 'is-variation-pending' );
	};

	if ( whatsappButton ) {
		updateWhatsAppButton();
	}

	if ( variationForm && whatsappButton ) {
		variationForm.addEventListener( 'change', () => {
			updateWhatsAppButton( false );
		} );

		const resetButton = variationForm.querySelector( '.reset_variations' );

		if ( resetButton ) {
			resetButton.addEventListener( 'click', () => {
				updateWhatsAppButton( true );
			} );
		}
	}
} )();
