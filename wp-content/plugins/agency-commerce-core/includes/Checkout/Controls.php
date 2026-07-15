<?php
/**
 * Checkout controls.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Checkout;

use Exception;
use WC_Order;
use WP_REST_Request;

/**
 * Applies framework checkout controls through supported WooCommerce APIs.
 */
final class Controls {
	/**
	 * Registers hooks.
	 */
	public function register(): void {
		add_filter( 'woocommerce_default_address_fields', array( $this, 'filter_default_address_fields' ) );
		add_filter( 'woocommerce_billing_fields', array( $this, 'filter_billing_fields' ) );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'filter_shipping_fields' ) );
		add_filter( 'woocommerce_enable_order_notes_field', array( $this, 'filter_order_notes' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_available_gateways' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'render_classic_checkout_messages' ), 5 );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_classic_minimum_order' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'validate_block_checkout_controls' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( $this, 'persist_order_notes' ) );
	}

	/**
	 * Filters shared address fields.
	 *
	 * @param array<string, array<string, mixed>> $fields Address fields.
	 * @return array<string, array<string, mixed>>
	 */
	public function filter_default_address_fields( array $fields ): array {
		if ( ! empty( $fields['address_2'] ) ) {
			$fields['address_2']['required'] = ! (bool) agency_commerce_core_get_setting( 'checkout.optional_address_2', true );
		}

		if ( ! empty( $fields['postcode'] ) && (bool) agency_commerce_core_get_setting( 'checkout.optional_postcode', false ) ) {
			$fields['postcode']['required'] = false;
		}

		return $fields;
	}

	/**
	 * Filters billing fields.
	 *
	 * @param array<string, array<string, mixed>> $fields Billing fields.
	 * @return array<string, array<string, mixed>>
	 */
	public function filter_billing_fields( array $fields ): array {
		if ( ! empty( $fields['billing_company'] ) ) {
			$fields['billing_company']['required'] = ! (bool) agency_commerce_core_get_setting( 'checkout.optional_company', false );
		}

		return $fields;
	}

	/**
	 * Filters shipping fields.
	 *
	 * @param array<string, array<string, mixed>> $fields Shipping fields.
	 * @return array<string, array<string, mixed>>
	 */
	public function filter_shipping_fields( array $fields ): array {
		if ( ! empty( $fields['shipping_company'] ) ) {
			$fields['shipping_company']['required'] = ! (bool) agency_commerce_core_get_setting( 'checkout.optional_company', false );
		}

		return $fields;
	}

	/**
	 * Toggles order notes where WooCommerce supports the field.
	 *
	 * @param bool $enabled Current state.
	 */
	public function filter_order_notes( bool $enabled ): bool {
		return (bool) agency_commerce_core_get_setting( 'checkout.optional_order_notes', true ) ? $enabled : false;
	}

	/**
	 * Filters payment gateways.
	 *
	 * @param array<string, mixed> $gateways Payment gateways.
	 * @return array<string, mixed>
	 */
	public function filter_available_gateways( array $gateways ): array {
		if ( ! (bool) agency_commerce_core_get_setting( 'checkout.enable_cod_controls', false ) ) {
			return $gateways;
		}

		if ( ! (bool) agency_commerce_core_get_setting( 'checkout.cod_enabled', true ) ) {
			unset( $gateways['cod'] );
		}

		if ( isset( $gateways['cod'] ) ) {
			$instructions = trim( (string) agency_commerce_core_get_setting( 'checkout.cod_instructions', '' ) );

			if ( '' !== $instructions ) {
				$gateways['cod']->description  = wp_kses_post( $instructions );
				$gateways['cod']->instructions = wp_kses_post( $instructions );
			}
		}

		return $gateways;
	}

	/**
	 * Renders classic checkout messaging.
	 */
	public function render_classic_checkout_messages(): void {
		$support = trim( (string) agency_commerce_core_get_setting( 'checkout.support_text', '' ) );
		$trust   = trim( (string) agency_commerce_core_get_setting( 'checkout.trust_text', '' ) );

		if ( '' === $support && '' === $trust ) {
			return;
		}

		echo '<div class="ac-checkout-messages">';
		if ( '' !== $support ) {
			echo '<p class="ac-checkout-messages__support">' . esc_html( $support ) . '</p>';
		}
		if ( '' !== $trust ) {
			echo '<p class="ac-checkout-messages__trust">' . esc_html( $trust ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Validates classic checkout minimum order.
	 */
	public function validate_classic_minimum_order(): void {
		$message = $this->minimum_order_error_message();

		if ( '' !== $message ) {
			wc_add_notice( $message, 'error' );
		}
	}

	/**
	 * Validates block checkout controls.
	 *
	 * @param WC_Order        $order Order object.
	 * @param WP_REST_Request $request Request object.
	 * @throws Exception When minimum order is not met.
	 */
	public function validate_block_checkout_controls( WC_Order $order, WP_REST_Request $request ): void {
		unset( $order );

		$payment_method = sanitize_key( (string) $request->get_param( 'payment_method' ) );
		if (
			'cod' === $payment_method
			&& (bool) agency_commerce_core_get_setting( 'checkout.enable_cod_controls', false )
			&& ! (bool) agency_commerce_core_get_setting( 'checkout.cod_enabled', true )
		) {
			throw new Exception( esc_html__( 'Cash on Delivery is not available for this order.', 'agency-commerce-core' ) );
		}

		$message = $this->minimum_order_error_message();

		if ( '' !== $message ) {
			throw new Exception( esc_html( $message ) );
		}
	}

	/**
	 * Persists support/trust settings as private order context.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function persist_order_notes( WC_Order $order ): void {
		$support = trim( (string) agency_commerce_core_get_setting( 'checkout.support_text', '' ) );
		$trust   = trim( (string) agency_commerce_core_get_setting( 'checkout.trust_text', '' ) );

		if ( '' !== $support ) {
			$order->update_meta_data( '_agency_commerce_checkout_support_text', $support );
		}

		if ( '' !== $trust ) {
			$order->update_meta_data( '_agency_commerce_checkout_trust_text', $trust );
		}
	}

	/**
	 * Returns minimum order error message.
	 */
	private function minimum_order_error_message(): string {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return '';
		}

		$minimum = (float) agency_commerce_core_get_setting( 'checkout.minimum_order_amount', 0 );

		$minimum = (float) wc_format_decimal( $minimum, wc_get_price_decimals() );
		$total   = $this->current_cart_total();

		if ( $minimum <= 0 || $total >= $minimum ) {
			return '';
		}

		return sprintf(
			/* translators: %s: minimum order amount. */
			__( 'The minimum order total is %s before checkout can be completed.', 'agency-commerce-core' ),
			wp_strip_all_tags( wc_price( $minimum ) )
		);
	}

	/**
	 * Returns the current WooCommerce cart total after discounts, shipping, taxes, and fees.
	 */
	private function current_cart_total(): float {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return 0.0;
		}

		WC()->cart->calculate_totals();

		return (float) wc_format_decimal( WC()->cart->get_total( 'edit' ), wc_get_price_decimals() );
	}
}
