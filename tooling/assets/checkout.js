( () => {
	const data = window.wc?.wcSettings?.getSetting?.(
		'agency-commerce-core-checkout_data',
		{}
	);
	if ( ! data || ( ! data.supportText && ! data.trustText ) ) {
		return;
	}

	const renderMessages = () => {
		const checkout = document.querySelector(
			'.wp-block-woocommerce-checkout'
		);
		if ( ! checkout || checkout.querySelector( '.ac-checkout-messages' ) ) {
			return;
		}

		const wrapper = document.createElement( 'div' );
		wrapper.className = 'ac-checkout-messages';

		if ( data.supportText ) {
			const support = document.createElement( 'p' );
			support.className = 'ac-checkout-messages__support';
			support.textContent = data.supportText;
			wrapper.appendChild( support );
		}

		if ( data.trustText ) {
			const trust = document.createElement( 'p' );
			trust.className = 'ac-checkout-messages__trust';
			trust.textContent = data.trustText;
			wrapper.appendChild( trust );
		}

		checkout.prepend( wrapper );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', renderMessages );
	} else {
		renderMessages();
	}
} )();
