const fs = require( 'fs' );
const path = require( 'path' );

const repoRoot = path.resolve( __dirname, '..' );
const marketingRoot = path.join( repoRoot, 'assets', 'marketing' );
const screenshotRoot = path.join(
	repoRoot,
	'showcase-screenshots',
	'portfolio'
);

function ensureDir( dir ) {
	fs.mkdirSync( dir, { recursive: true } );
}

function write( relativePath, content ) {
	const target = path.join( marketingRoot, relativePath );
	ensureDir( path.dirname( target ) );
	fs.writeFileSync( target, content );
}

function imageHref( storefront, viewport, page ) {
	return path
		.relative(
			path.join( marketingRoot, 'composites' ),
			path.join( screenshotRoot, storefront, viewport, `${ page }.png` )
		)
		.replaceAll( path.sep, '/' );
}

function mockup( title, storefront, page, viewport = 'desktop' ) {
	const href = imageHref( storefront, viewport, page );
	const sizes = {
		desktop: { width: 1160, height: 820 },
		mobile: { width: 520, height: 980 },
		tablet: { width: 760, height: 940 },
	};
	const { width, height } = sizes[ viewport ] || sizes.desktop;
	return `<svg xmlns="http://www.w3.org/2000/svg" width="${ width }" height="${ height }" viewBox="0 0 ${ width } ${ height }" role="img" aria-labelledby="title desc">
<title id="title">${ title }</title>
<desc id="desc">Marketing mockup generated from Agency Commerce showcase screenshots.</desc>
<defs>
<linearGradient id="bg" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#111827"/><stop offset="1" stop-color="#F3EADF"/></linearGradient>
<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="28" stdDeviation="34" flood-color="#111827" flood-opacity=".28"/></filter>
</defs>
<rect width="100%" height="100%" rx="36" fill="url(#bg)"/>
<text x="48" y="76" fill="#fffaf3" font-family="Inter, Arial, sans-serif" font-size="34" font-weight="800">${ title }</text>
<rect x="48" y="120" width="${ width - 96 }" height="${
		height - 168
	}" rx="28" fill="#0f172a" filter="url(#shadow)"/>
<image href="${ href }" x="64" y="136" width="${ width - 128 }" height="${
		height - 200
	}" preserveAspectRatio="xMidYMin slice"/>
</svg>`;
}

function deviceComposite() {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="1800" height="1200" viewBox="0 0 1800 1200" role="img" aria-labelledby="title desc">
<title id="title">Agency Commerce responsive showcase composite</title>
<desc id="desc">Desktop, tablet, and mobile storefront screenshots presented as premium launch-page devices.</desc>
<defs>
<linearGradient id="bg" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#05070b"/><stop offset=".55" stop-color="#181614"/><stop offset="1" stop-color="#4a3528"/></linearGradient>
<filter id="shadow"><feDropShadow dx="0" dy="34" stdDeviation="34" flood-color="#000" flood-opacity=".38"/></filter>
</defs>
<rect width="1800" height="1200" fill="url(#bg)"/>
<circle cx="1440" cy="170" r="260" fill="#78ffdb" opacity=".12"/>
<circle cx="220" cy="980" r="320" fill="#c9824a" opacity=".18"/>
<text x="96" y="135" fill="#fffaf3" font-family="Inter, Arial, sans-serif" font-size="70" font-weight="900" letter-spacing="-3">Agency Commerce</text>
<text x="100" y="196" fill="#d8c7b4" font-family="Inter, Arial, sans-serif" font-size="28">Three premium storefront directions. One reusable WooCommerce framework.</text>
<g filter="url(#shadow)">
<rect x="96" y="280" width="1030" height="700" rx="34" fill="#0f172a"/>
<image href="${ imageHref(
		'fashion',
		'desktop',
		'homepage'
	) }" x="116" y="300" width="990" height="660" preserveAspectRatio="xMidYMin slice"/>
<rect x="1050" y="380" width="430" height="620" rx="34" fill="#0f172a"/>
<image href="${ imageHref(
		'home',
		'tablet',
		'homepage'
	) }" x="1066" y="396" width="398" height="588" preserveAspectRatio="xMidYMin slice"/>
<rect x="1410" y="510" width="260" height="530" rx="36" fill="#0f172a"/>
<image href="${ imageHref(
		'electronics',
		'mobile',
		'homepage'
	) }" x="1422" y="528" width="236" height="494" preserveAspectRatio="xMidYMin slice"/>
</g>
</svg>`;
}

function beforeAfter() {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900" role="img" aria-labelledby="title desc">
<title id="title">Agency Commerce before and after comparison</title>
<desc id="desc">Comparison of an undifferentiated commerce layout against a premium Agency Commerce showcase storefront.</desc>
<rect width="1600" height="900" fill="#f7f2eb"/>
<text x="80" y="92" fill="#181614" font-family="Inter, Arial, sans-serif" font-size="44" font-weight="900">Before / After</text>
<g transform="translate(80 150)">
<rect width="680" height="640" rx="28" fill="#fff" stroke="#ded2c4"/>
<text x="40" y="70" fill="#736b62" font-family="Inter, Arial, sans-serif" font-size="26" font-weight="800">Generic theme</text>
<rect x="40" y="120" width="600" height="110" rx="12" fill="#e5e7eb"/>
<rect x="40" y="270" width="175" height="220" rx="12" fill="#e5e7eb"/>
<rect x="252" y="270" width="175" height="220" rx="12" fill="#e5e7eb"/>
<rect x="465" y="270" width="175" height="220" rx="12" fill="#e5e7eb"/>
<rect x="40" y="530" width="260" height="34" rx="17" fill="#d1d5db"/>
<rect x="40" y="582" width="430" height="22" rx="11" fill="#e5e7eb"/>
</g>
<g transform="translate(840 150)">
<rect width="680" height="640" rx="28" fill="#181614"/>
<image href="${ imageHref(
		'fashion',
		'desktop',
		'homepage'
	) }" x="18" y="18" width="644" height="604" preserveAspectRatio="xMidYMin slice"/>
<rect x="18" y="18" width="644" height="604" rx="18" fill="none" stroke="#fffaf3" opacity=".28"/>
</g>
</svg>`;
}

function featureGraphic( title, text, accent ) {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="900" height="540" viewBox="0 0 900 540" role="img" aria-labelledby="title desc">
<title id="title">${ title }</title>
<desc id="desc">${ text }</desc>
<rect width="900" height="540" rx="42" fill="#10151f"/>
<circle cx="720" cy="120" r="160" fill="${ accent }" opacity=".2"/>
<rect x="70" y="78" width="160" height="160" rx="34" fill="${ accent }"/>
<path d="M110 170 145 115 190 195" fill="none" stroke="#10151f" stroke-width="18" stroke-linecap="round" stroke-linejoin="round"/>
<text x="70" y="310" fill="#fffaf3" font-family="Inter, Arial, sans-serif" font-size="48" font-weight="900" letter-spacing="-2">${ title }</text>
<text x="72" y="366" fill="#cbd5e1" font-family="Inter, Arial, sans-serif" font-size="24">${ text }</text>
</svg>`;
}

function brandSvg() {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="720" height="720" viewBox="0 0 720 720" role="img" aria-labelledby="title">
<title id="title">Agency Commerce logo</title>
<rect width="720" height="720" rx="160" fill="#10151f"/>
<path d="M170 505 360 145 550 505H440l-80-162-80 162H170Z" fill="#fffaf3"/>
<path d="M250 505 360 295 470 505h-82l-28-64-28 64h-82Z" fill="#78ffdb"/>
<path d="M225 560h270" stroke="#c9824a" stroke-width="34" stroke-linecap="round"/>
</svg>`;
}

function wordmarkSvg() {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="320" viewBox="0 0 1400 320" role="img" aria-labelledby="title">
<title id="title">Agency Commerce wordmark</title>
<rect width="1400" height="320" rx="40" fill="#fffaf3"/>
<g transform="translate(64 54) scale(.28)">${ brandSvg()
		.replace( /<svg[^>]*>/, '' )
		.replace( '</svg>', '' ) }</g>
<text x="310" y="142" fill="#10151f" font-family="Inter, Arial, sans-serif" font-size="76" font-weight="900" letter-spacing="-4">Agency Commerce</text>
<text x="315" y="204" fill="#4a5568" font-family="Inter, Arial, sans-serif" font-size="30">Reusable WooCommerce framework for agency storefronts</text>
</svg>`;
}

function ogSvg( title ) {
	return `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-labelledby="title">
<title id="title">${ title }</title>
<defs><linearGradient id="bg" x1="0" x2="1"><stop stop-color="#10151f"/><stop offset="1" stop-color="#4a3528"/></linearGradient></defs>
<rect width="1200" height="630" fill="url(#bg)"/>
<circle cx="1020" cy="110" r="220" fill="#78ffdb" opacity=".16"/>
<circle cx="120" cy="560" r="240" fill="#c9824a" opacity=".2"/>
<text x="80" y="168" fill="#fffaf3" font-family="Inter, Arial, sans-serif" font-size="76" font-weight="900" letter-spacing="-4">${ title }</text>
<text x="84" y="236" fill="#d8c7b4" font-family="Inter, Arial, sans-serif" font-size="32">Premium WooCommerce framework, showcase storefronts, and launch-ready agency assets.</text>
<image href="${ imageHref(
		'electronics',
		'desktop',
		'homepage'
	) }" x="86" y="300" width="470" height="250" preserveAspectRatio="xMidYMin slice"/>
<image href="${ imageHref(
		'fashion',
		'desktop',
		'shop'
	) }" x="590" y="300" width="270" height="250" preserveAspectRatio="xMidYMin slice"/>
<image href="${ imageHref(
		'home',
		'mobile',
		'homepage'
	) }" x="890" y="260" width="190" height="310" preserveAspectRatio="xMidYMin slice"/>
</svg>`;
}

ensureDir( marketingRoot );
write( 'brand/logo.svg', brandSvg() );
write( 'brand/wordmark.svg', wordmarkSvg() );
write( 'brand/admin-icon.svg', brandSvg() );
write(
	'brand/placeholder-product.svg',
	featureGraphic(
		'Placeholder Icon',
		'Original launch identity asset',
		'#78ffdb'
	)
);
write( 'og/open-graph.svg', ogSvg( 'Agency Commerce' ) );
write( 'og/github-social-preview.svg', ogSvg( 'Agency Commerce Framework' ) );
write( 'composites/hero-showcase.svg', deviceComposite() );
write( 'composites/before-after.svg', beforeAfter() );
write(
	'mockups/fashion-homepage-desktop.svg',
	mockup( 'Fashion Homepage', 'fashion', 'homepage' )
);
write(
	'mockups/electronics-shop-desktop.svg',
	mockup( 'Electronics Shop', 'electronics', 'shop' )
);
write(
	'mockups/home-product-desktop.svg',
	mockup( 'Home Product Page', 'home', 'product' )
);
write(
	'mockups/fashion-checkout-desktop.svg',
	mockup( 'Fashion Checkout', 'fashion', 'checkout' )
);
write(
	'mockups/electronics-mobile.svg',
	mockup( 'Electronics Mobile', 'electronics', 'homepage', 'mobile' )
);
write(
	'mockups/home-tablet.svg',
	mockup( 'Home Tablet', 'home', 'homepage', 'tablet' )
);
write(
	'features/architecture.svg',
	featureGraphic(
		'Reusable Architecture',
		'Parent theme, plugin, and child storefronts',
		'#78ffdb'
	)
);
write(
	'features/performance.svg',
	featureGraphic(
		'Performance First',
		'No page builder dependency or frontend framework',
		'#c9824a'
	)
);
write(
	'features/customization.svg',
	featureGraphic(
		'Client Customization',
		'Token-driven presets and child themes',
		'#a8855a'
	)
);
write(
	'features/checkout.svg',
	featureGraphic(
		'Woo Checkout',
		'Styled block checkout without replacing commerce logic',
		'#d6e2ff'
	)
);
// eslint-disable-next-line no-console
console.log( `Marketing assets written to ${ marketingRoot }` );
