const assert = require( 'node:assert/strict' );
const { execFileSync } = require( 'node:child_process' );
const { randomUUID } = require( 'node:crypto' );
const { inspect } = require( 'node:util' );

function wpEval( code ) {
	const composeFile = process.env.WP_ENV_COMPOSE_FILE;
	const args = [ 'wp', 'eval', code ];
	const output = composeFile
		? execFileSync(
				'docker',
				[ 'compose', '-f', composeFile, 'exec', '-T', 'cli', ...args ],
				{ encoding: 'utf8' }
		  )
		: execFileSync( 'npm', [ 'run', 'env:cli', '--', ...args ], {
				encoding: 'utf8',
		  } );
	return output.trim().split( /\r?\n/ ).pop();
}

async function run() {
	const base = process.env.WP_BASE_URL;
	assert.ok( base, 'Run this fixture through npm run test:backend.' );
	assert.ok(
		[ 'localhost', '127.0.0.1' ].includes( new URL( base ).hostname )
	);

	const fixtures = JSON.parse(
		wpEval( `
			update_option( 'woocommerce_coming_soon', 'no' );
			update_option( 'woocommerce_default_country', 'US:CA' );
			update_option( 'woocommerce_currency', 'USD' );
			update_option( 'woocommerce_enable_guest_checkout', 'yes' );
			update_option( 'woocommerce_calc_taxes', 'no' );
			update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'enable_for_virtual' => 'yes' ) );
			$coupon = new WC_Coupon( 'agency-block-order' );
			$coupon->set_code( 'agency-block-order' );
			$coupon->set_discount_type( 'fixed_cart' );
			$coupon->set_amount( '5' );
			$coupon->save();
			$product = wc_get_product( get_page_by_path( 'cp3-variable-product', OBJECT, 'product' )->ID );
			echo wp_json_encode( $product->get_children() );
		` )
	);
	const [ variationId ] = fixtures;
	assert.ok( variationId > 0 );

	let token;
	async function request( path, body, status = 200 ) {
		const response = await fetch(
			`${ base }/wp-json/wc/store/v1/${ path }`,
			{
				method: body ? 'POST' : 'GET',
				redirect: 'error',
				headers: {
					...( token ? { 'Cart-Token': token } : {} ),
					...( body ? { 'Content-Type': 'application/json' } : {} ),
				},
				body: body ? JSON.stringify( body ) : undefined,
			}
		);
		const data = await response.json();
		assert.equal( response.status, status, JSON.stringify( data ) );
		token = response.headers.get( 'cart-token' ) || token;
		return data;
	}

	await request( 'cart' );
	assert.ok( token, 'Guest cart token missing.' );
	const added = await request(
		'cart/add-item',
		{ id: variationId, quantity: 1 },
		201
	);
	assert.equal( added.items[ 0 ].id, variationId );
	const key = added.items[ 0 ].key;
	const updated = await request( 'cart/update-item', { key, quantity: 2 } );
	assert.equal( updated.items[ 0 ].quantity, 2 );
	const removed = await request( 'cart/remove-item', { key } );
	assert.equal( removed.items.length, 0 );
	await request( 'cart/add-item', { id: variationId, quantity: 2 }, 201 );
	const discounted = await request( 'cart/apply-coupon', {
		code: 'agency-block-order',
	} );
	assert.equal( discounted.totals.total_discount, '500' );
	assert.equal( discounted.totals.total_price, '8500' );

	const email = `agency-${ randomUUID() }@example.test`;
	const billing = {
		first_name: 'Agency',
		last_name: 'Fixture',
		address_1: '1 Test Street',
		city: 'San Francisco',
		state: 'CA',
		postcode: '94103',
		country: 'US',
		email,
		phone: '4155550100',
	};
	const checkout = await request(
		'checkout',
		{
			billing_address: billing,
			payment_method: 'cod',
			payment_data: [],
		},
		200
	);
	assert.ok(
		Number.isSafeInteger( checkout.order_id ) && checkout.order_id > 0
	);
	const order = JSON.parse(
		wpEval( `
			$order = wc_get_order( ${ checkout.order_id } );
			$items = array_values( $order->get_items() );
			echo wp_json_encode( array(
				$order->get_customer_id(), $order->get_billing_email(),
				$order->get_payment_method(), (float) $order->get_total(),
				$order->get_coupon_codes(), $items[0]->get_variation_id(), $items[0]->get_quantity()
			) );
		` )
	);
	assert.deepEqual( order, [
		0,
		email,
		'cod',
		85,
		[ 'agency-block-order' ],
		variationId,
		2,
	] );
	process.stdout.write(
		'Verified Store API guest cart, variation, coupon and persisted COD order.\n'
	);
}

run().catch( ( error ) => {
	process.stderr.write( inspect( error ) + '\n' );
	process.exitCode = 1;
} );
