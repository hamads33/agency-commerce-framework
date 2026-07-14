/* eslint-disable react-hooks/rules-of-hooks */
const { createElement: el } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, RangeControl } = wp.components;

const textBlocks = [
	'promo-banner',
	'image-text',
	'testimonials',
	'brand-logos',
	'newsletter',
];

function editTextSection( name, title ) {
	return function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps( {
			className: `ac-home-section ac-${ name }`,
		} );
		return el(
			'section',
			blockProps,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Content' },
					el( TextControl, {
						label: 'Heading',
						value: attributes.heading || '',
						onChange: ( heading ) => setAttributes( { heading } ),
					} ),
					el( TextareaControl, {
						label: 'Text',
						value: attributes.text || '',
						onChange: ( text ) => setAttributes( { text } ),
					} ),
					el( TextControl, {
						label: 'Button text',
						value: attributes.buttonText || '',
						onChange: ( buttonText ) =>
							setAttributes( { buttonText } ),
					} ),
					el( TextControl, {
						label: 'Button URL',
						value: attributes.url || '',
						onChange: ( url ) => setAttributes( { url } ),
					} )
				)
			),
			el( 'h2', null, attributes.heading || title ),
			attributes.text ? el( 'p', null, attributes.text ) : null
		);
	};
}

wp.blocks.registerBlockType( 'agency-commerce/hero', {
	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps( { className: 'ac-home-hero' } );
		return el(
			'section',
			blockProps,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Hero content' },
					el( TextControl, {
						label: 'Eyebrow',
						value: attributes.eyebrow || '',
						onChange: ( eyebrow ) => setAttributes( { eyebrow } ),
					} ),
					el( TextControl, {
						label: 'Heading',
						value: attributes.heading || '',
						onChange: ( heading ) => setAttributes( { heading } ),
					} ),
					el( TextareaControl, {
						label: 'Description',
						value: attributes.description || '',
						onChange: ( description ) =>
							setAttributes( { description } ),
					} ),
					el( TextControl, {
						label: 'Primary button text',
						value: attributes.primaryText || '',
						onChange: ( primaryText ) =>
							setAttributes( { primaryText } ),
					} ),
					el( TextControl, {
						label: 'Primary URL',
						value: attributes.primaryUrl || '',
						onChange: ( primaryUrl ) =>
							setAttributes( { primaryUrl } ),
					} )
				)
			),
			attributes.eyebrow
				? el(
						'p',
						{ className: 'ac-home-hero__eyebrow' },
						attributes.eyebrow
				  )
				: null,
			el( 'h2', null, attributes.heading || 'Build a better store' ),
			attributes.description
				? el( 'p', null, attributes.description )
				: null
		);
	},
	save() {
		return null;
	},
} );

wp.blocks.registerBlockType( 'agency-commerce/category-grid', {
	edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps( { className: 'ac-category-grid' } );
		return el(
			'section',
			blockProps,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Category grid' },
					el( TextControl, {
						label: 'Heading',
						value: attributes.heading || '',
						onChange: ( heading ) => setAttributes( { heading } ),
					} ),
					el( RangeControl, {
						label: 'Category count',
						value: attributes.count || 4,
						min: 1,
						max: 12,
						onChange: ( count ) => setAttributes( { count } ),
					} )
				)
			),
			el( 'h2', null, attributes.heading || 'Shop by category' ),
			el(
				'p',
				null,
				'Product categories will render here on the storefront.'
			)
		);
	},
	save() {
		return null;
	},
} );

textBlocks.forEach( ( name ) => {
	const title = name
		.split( '-' )
		.map( ( part ) => part[ 0 ].toUpperCase() + part.slice( 1 ) )
		.join( ' ' );
	wp.blocks.registerBlockType( `agency-commerce/${ name }`, {
		edit: editTextSection( name, title ),
		save() {
			return null;
		},
	} );
} );

wp.blocks.registerBlockType( 'agency-commerce/trust-bar', {
	edit() {
		return el(
			'section',
			useBlockProps( { className: 'ac-trust-bar' } ),
			el( 'span', null, 'Secure checkout' ),
			el( 'span', null, 'Fast dispatch' ),
			el( 'span', null, 'Easy returns' )
		);
	},
	save() {
		return null;
	},
} );

wp.blocks.registerBlockType( 'agency-commerce/faq', {
	edit( { attributes, setAttributes } ) {
		return el(
			'section',
			useBlockProps( { className: 'ac-faq' } ),
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'FAQ' },
					el( TextControl, {
						label: 'Heading',
						value: attributes.heading || '',
						onChange: ( heading ) => setAttributes( { heading } ),
					} )
				)
			),
			el( 'h2', null, attributes.heading || 'Questions' ),
			el(
				'details',
				{ open: true },
				el( 'summary', null, 'Question' ),
				el( 'p', null, 'Answer' )
			)
		);
	},
	save() {
		return null;
	},
} );

wp.blocks.registerBlockType( 'agency-commerce/recently-viewed-products', {
	edit() {
		return el(
			'section',
			useBlockProps( { className: 'ac-recently-viewed' } ),
			el( 'h2', null, 'Recently viewed' ),
			el(
				'p',
				null,
				'Products appear here after visitors view product pages.'
			)
		);
	},
	save() {
		return null;
	},
} );
