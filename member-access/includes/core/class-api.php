<?php
/**
 * AccessPress Payment Gateway API
 *
 * @package AccessPress
 */

/**
 * Abstract base class to configure and process payment gateways.
 *
 * This class is extended by subclasses that define specific payment gateways.
 *
 * @since 0.1.0
 */
abstract class MemberAccess_Gateway {

	/**
	 * Flag indicating whether the gateway is properly configures
	 *
	 * @since 0.1.0
	 *
	 * @var boolean configured
	 */
	public $configured = false;

	/**
	 * Name of the payment method for this gateway when the form is shown/processed.
	 *
	 * @since 0.1.0
	 *
	 * @var string Payment method
	 */
	public $payment_method;

	/**
	 * Opt in gateway name.
	 *
	 * @since 2.5
	 *
	 * @var string
	 */
	public $opt_in_gateway_name = '';

	/**
	 * Product & data posted from the checkout form.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $product = null;

	/**
	 * Checkout label
	 *
	 * @since 2.1
	 *
	 * @var string
	 */
	public $checkout_label = null;

	/**
	 * Supported Currencies
	 *
	 * @since 2.3
	 *
	 * @var array
	 */
	public $supported_currencies = array();

	/**
	 * Selected Currencies
	 *
	 * @since 2.3
	 *
	 * @var string
	 */
	public $selected_currency = null;

	/**
	 * Call this method in a subclass constructor to create the gateway.
	 *
	 * @since 0.1.0
	 *
	 * @param string $action_hook unique ID of this payment gateway
	 * @return null Returns early if action hook is not set
	 */
	public function create( $payment_method = '', $checkout_label = '', $supported_currencies = false ) {

		$this->payment_method = $this->payment_method ? $this->payment_method : $payment_method;
		$this->checkout_label = $this->checkout_label ? $this->checkout_label : $checkout_label;

		$this->configured = $this->configure();

		if ( ! $this->configured )
			return;

		// only support currencies if the gateway is configured
		$this->supported_currencies = $this->supported_currencies ? $this->supported_currencies : $supported_currencies;
		$this->supported_currencies = $this->supported_currencies ? $this->supported_currencies : array( 'USD' => 'U.S. Dollar' );

		$currency = accesspress_get_option( 'currency' );
		$this->selected_currency = $currency ? $currency : 'USD';

		if ( is_admin() )
			return;

		$this->configured = array_key_exists( $this->selected_currency, $this->supported_currencies );

	}
	/**
	 * Wrapper for processing order function _process_order
	 *
	 * @since 0.1.0
	 */
	function process_order( $args ) {

		if ( ! $this->configured )
			return new WP_Error( 'gateway', __( 'Gateway not configured!', 'premise' ) );

		return $this->_process_order( $args );

	}
	/**
	 * get the duration of a product subscription
	 *
	 * @since 2.0.3
	 */
	public function get_subscription_duration( $post_id ) {

		if ( ! accesspress_get_option( 'authorize_net_recurring' ) )
			return 0;

		$duration = (int) get_post_meta( $post_id, '_acp_product_duration', true );
		// pass original value to all filters
		$duration = apply_filters( 'premise_subscription_duration', $duration, $post_id, $duration );
		if ( ! $duration )
			return 0;

		if ( ! get_post_meta( $post_id, '_acp_product_subscription', true ) )
			return 0;

		return $duration;

	}
	/**
	 * get the trial duration of a product subscription
	 *
	 * @since 2.0.3
	 */
	function _get_trial_duration( $post_id ) {

		if ( ! accesspress_get_option( 'authorize_net_recurring' ) )
			return 0;

		$duration = (int) get_post_meta( $post_id, '_acp_product_trial_duration', true );
		// pass original value to all filters
		$duration = apply_filters( 'premise_trial_duration', $duration, $post_id, $duration );
		if ( ! $duration )
			return 0;

		// pass original value to all filters
		return $duration;

	}
	/**
	 * Validate report back from the payment gateway.
	 *
	 * Default is no report back
	 *
	 * @since 0.1.0
	 */
	public function validate_reportback() {

		return false;

	}
	/**
	 * Validate checkout form.
	 *
	 * Default is validated
	 *
	 * @return bool whether the checkout form has valid data
	 * @since 2.1
	 */
	public function validate_checkout_form( $args ) {

		return true;

	}
	/**
	 * Member can cancel flag.
	 *
	 * Default is true
	 * Override in subclass to disable cancel feature on gateway
	 *
	 * @return bool whether the gate supports cancel
	 * @since 2.1
	 */
	public function member_can_cancel() {

		return true;

	}
	/**
	 * Cancel a payment profile with the payment provider.
	 *
	 * By default nothing needs to be done on the payment processor end
	 * Override in subclass to call the payment processor api to cancel the payment profile
	 *
	 * @since 2.1
	 */
	public function cancel( $args ) {

		return true;

	}
	/**
	 * Refund a payment profile transaction with the payment provider.
	 *
	 * By default nothing needs to be done on the payment processor end
	 * Override in subclass to call the payment processor api to cancel the payment profile
	 *
	 * @since 2.5.0
	 */
	public function refund( $order_id ) {}
	/**
	 * Complete a sale for the payment gateway.
	 *
	 * Default is no completion step
	 *
	 * @since 0.1.0
	 */
	public function complete_sale( $args ) {

		return false;

	}
	/**
	 * Get the gateway mode.
	 *
	 * Default is Premise 2.0 mode setting
	 *
	 * @since 2.1.0
	 */
	static public function mode( $mode ) {

		$gateway_mode = accesspress_get_option( $mode );
		if ( ! strlen( $gateway_mode ) )
			$gateway_mode = accesspress_get_option( 'gateway_live_mode' );

		return $gateway_mode;

	}
	/**
	 * show the transaction meta for an order.
	 *
	 * Default is show nothing
	 *
	 * @since 2.2.0
	 */
	public function show_order_transaction_meta( $order_id ) {}
	/**
	 * has the transaction meta for an order.
	 *
	 * returns bool 
	 *
	 * @since 2.2.0
	 */
	 function has_transaction_meta( $order_id ) {

		return get_post_meta( $order_id, "_acp_order_{$this->payment_method}_transaction_id", true );

	}
	/**
	 * has the payment profile for an order.
	 *
	 * returns bool 
	 *
	 * @since 2.4.1
	 */
	 function has_payment_profile( $order_id ) {

		$user_id = get_post_meta( $order_id, '_acp_order_member_id', true );
		$product_id = get_post_meta( $order_id, '_acp_order_product_id', true );
		if ( ! $user_id || ! $product_id )
			return false;

		return get_user_option( "memberaccess_{$this->payment_method}_payment_{$product_id}", $user_id );

	}
	/**
	 * generate a PCI safe credit card number
	 *
	 * returns string 
	 *
	 * @since 2.2.2
	 */
	 function get_pci_number( $cc_number ) {

		// if it's a non-numeric string it's not a cc number
		if ( ! is_numeric( $cc_number ) )
			return $cc_number;

		$length = strlen( $cc_number );
		return str_pad( substr( $cc_number, -4 ), $length < 13 ? 16 : $length, '*', STR_PAD_LEFT );

	}
	/**
	 * get currency list supported by gateway
	 *
	 * returns string 
	 *
	 * @since 2.3
	 */
	 function get_supported_currencies() {

		return $this->supported_currencies;

	}
	/**
	 * get is opt in gateway name
	 *
	 * returns string 
	 *
	 * @since 2.5
	 */
	 function get_optin_name() {

		return $this->opt_in_gateway_name;

	}
	/**
	 * default opt in meta box fields
	 *
	 * @since 2.5
	 */
	public function optin_metabox( $meta ) {}
	/**
	 * default opt in extra fields for opt in as payment
	 *
	 * @since 2.5
	 */
	public function optin_extra_fields( $meta ) {}
	/**
	 * default opt in extra fields validation
	 *
	 * @since 2.5
	 */
	public function validate_optin_extra_fields( $errors, $args, $meta ) {

		return $errors;

	}

	/**
	 * Initialize the payment gateway.
	 *
	 * This method must be re-defined in the extended classes, to configure
	 * the payment gateway.
	 *
	 * @since 0.1.0
	 */
	abstract public function configure();

	/**
	 * Handle the postback of the payment gateway form.
	 *
	 * This method must be re-defined in the extended classes, to process
	 * the payment gateway post back.
	 *
	 * @since 0.1.0
	 */
	abstract public function _process_order( $args );

}

function memberaccess_register_payment_gateway( $classname ) {

	global $memberaccess_payment_gateways, $memberaccess_registered_gateways;

	if ( ! class_exists( $classname ) )
		return;

	if ( ! is_array( $memberaccess_payment_gateways ) )
		$memberaccess_payment_gateways = array();

	$gateway = new $classname;
	$memberaccess_registered_gateways[$gateway->payment_method] = $gateway;

	if ( $gateway->configured )
		$memberaccess_payment_gateways[$gateway->payment_method] = $gateway;

}

function memberaccess_unregister_payment_gateway( $classname ) {

	global $memberaccess_payment_gateways, $memberaccess_registered_gateways;

	foreach( $memberaccess_registered_gateways as $payment_method => $gateway ) {

		if ( is_a( $gateway, $classname ) ) {

			unset( $memberaccess_payment_gateways[$payment_method], $memberaccess_registered_gateways[$payment_method] );
			break;

		}

	}
}

function memberaccess_get_payment_gateway( $payment_method, $registered = false ) {

	global $memberaccess_payment_gateways, $memberaccess_registered_gateways;

	if ( isset( $memberaccess_payment_gateways[$payment_method] ) )
		return $memberaccess_payment_gateways[$payment_method];

	if ( $registered && isset( $memberaccess_registered_gateways[$payment_method] ) )
		return $memberaccess_registered_gateways[$payment_method];

	return null;
}