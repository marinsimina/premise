<?php
/**
 * Free product gateway class.
 *
 * @since 2.1.0
 */
class MemberAccess_FreeProduct_Gateway extends MemberAccess_Gateway {
	/**
	 * Class constructor.
	 *
	 * @since 2.1.0
	 */
	function __construct() {

		// always support the currency selected in the settings
		$currency = accesspress_get_option( 'currency' );
		$supported_currencies = empty( $currency ) ? false : array( $currency => $currency );
	 	$this->create( 'free', 'free', $supported_currencies );

	}
	/**
	 * Initialize the payment gateway.
	 *
	 * @since 2.1.0
	 */
	public function configure() {

		add_action( 'premise_checkout_form_before', array( $this, 'modify_checkout_form' ), 10, 2 );
		return true;

	}

	/**
	 * Handle the postback of the payment gateway form.
	 *
	 * @since 2.1.0
	 */
	public function _process_order( $args ) {

		return $args['order_details'];

	}

	/**
	 * Modify the checkout form & remove the payment fields.
	 *
	 * @since 2.1.0
	 */
	function modify_checkout_form( $args, $product_id ) {

		if ( ! $product_id )
			return;

		if ( ! get_post_meta( $product_id, '_acp_product_free_product', true ) ) {

			memberaccess_unregister_payment_gateway( __CLASS__ );
			return;

		}

		remove_all_actions( 'premise_checkout_form' );
		remove_all_actions( 'premise_checkout_form_after' );
		add_action( 'premise_checkout_form', 'accesspress_checkout_form_account' );
		add_action( 'premise_checkout_form', array( $this, 'checkout_payment_method' ) );
		add_filter( 'premise_checkout_button_text', array( $this, 'checkout_button_text' ), 5, 2 );

		do_action( 'premise_check_free_product_checkout', $product_id );

	}

	/**
	 * Modify the checkout form button text.
	 *
	 * @since 2.1.0
	 */
	function checkout_button_text( $text, $logged_in ) {

		return $logged_in ? __( 'Get Now', 'premise' ) : __( 'Create My Account', 'premise' );

	}

	/**
	 * Set the checkout payment gateway to this one.
	 *
	 * @since 2.1.0
	 */
	function checkout_payment_method() {

		echo '<input type="hidden" name="accesspress-checkout[payment-method]" value="free" />';

	}

}

add_action( 'memberaccess_setup', 'premise_register_free_product_gateway' );

function premise_register_free_product_gateway() {

	memberaccess_register_payment_gateway( 'MemberAccess_FreeProduct_Gateway' );

}

