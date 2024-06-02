<?php
/**
 * Paypal gateway class to configure and process payment gateways.
 *
 * This class uses the Express Checkout api.
 *
 * @since 0.1.0
 */
class MemberAccess_Paypal_Gateway extends MemberAccess_Gateway {
	/**
	 * The Paypal merchant authentication block.
	 *
	 * @since 0.1.0
	 *
	 * @var string name-value pairs merchant authentication block
	 */
	private $_merchant_login;

	/**
	 * The Paypal gateway URI.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_gateway_uri;

	/**
	 * The Paypal gateway host. Required for HTTP 1.1.
	 *
	 * @since 2.5.4
	 *
	 * @var string
	 */
	private $_gateway_host;

	/**
	 * The Paypal validation URI.
	 *
	 * @since 2.0.3
	 *
	 * @var string
	 */
	private $_validation_uri;

	/**
	 * The Paypal customer URI.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_customer_uri;

	/**
	 * The Paypal gateway mode.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_gateway_mode;

	/**
	 * The Paypal api version.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_gateway_version;

	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	function __construct() {

		$supported_currencies = array(
			'AUD' => 'Australian Dollar',
			'BRL' => 'Brazilian Real',
			'CAD' => 'Canadian Dollar',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'EUR' => 'Euro',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungarian Forint',
			'ILS' => 'Israeli New Sheqel',
			'JPY' => 'Japanese Yen',
			'MYR' => 'Malaysian Ringgit',
			'MXN' => 'Mexican Peso',
			'NOK' => 'Norwegian Krone',
			'NZD' => 'New Zealand Dollar',
			'PHP' => 'Philippine Peso',
			'PLN' => 'Polish Zloty',
			'GBP' => 'Pound Sterling',
			'SGD' => 'Singapore Dollar',
			'SEK' => 'Swedish Krona',
			'CHF' => 'Swiss Franc',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira',
			'USD' => 'U.S. Dollar',
		);
	 	$this->create( 'paypal', '<!-- PayPal Logo --><a href="#" onclick="javascript:window.open(\'https://www.paypal.com/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside\',\'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350\');"><img  src="https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif" border="0" alt="Acceptance Mark"></a><!-- PayPal Logo -->', $supported_currencies );

	}
	/**
	 * Initialize the payment gateway.
	 *
	 * @since 0.1.0
	 */
	public function configure() {

		$api_username = urlencode( trim( accesspress_get_option( 'paypal_express_username' ) ) );
		$api_password = urlencode( trim( accesspress_get_option( 'paypal_express_password' ) ) );
		$api_signature = urlencode( trim( accesspress_get_option( 'paypal_express_signature' ) ) );

		// we need all three to use the gateway
		if ( empty( $api_username ) || empty( $api_password ) || empty( $api_signature ) )
			return false;

		$api_server = ( '1' == self::mode( 'gateway_live_mode_paypal' ) ? '' : '.sandbox' );
		$this->_gateway_uri = 'https://api-3t' . $api_server . '.paypal.com/nvp';
		$this->_gateway_host = 'api-3t' . $api_server . '.paypal.com';
		$this->_customer_uri = 'https://www' . $api_server . '.paypal.com/webscr&cmd=_express-checkout&token=';
		$this->_validation_uri = 'https://www' . $api_server . '.paypal.com/cgi-bin/webscr';
		$this->_merchant_login = sprintf( '&USER=%s&PWD=%s&SIGNATURE=%s', $api_username, $api_password, $api_signature );
		$this->_gateway_version = '&VERSION=' . urlencode( '65.1' );

		return true;

	}

	/**
	 * Handle the postback of the payment gateway form.
	 *
	 * @since 0.1.0
	 */
	public function _process_order( $args ) {

		$sale_meta = $args['order_details'];
		// create local user
		$user_id = $sale_meta['_acp_order_member_id'];

		// we need a success & cancel url
		$base_url = add_query_arg( array( 'id' => $user_id, 'product_id' => $args['product_id'] ), get_permalink() );
		$success = add_query_arg( array( 'action' => 'complete' ), $base_url );
		$cancel = add_query_arg( array( 'action' => 'cancel' ), $base_url );

		$product_post = get_post( $args['product_id'] );
		$duration = $this->get_subscription_duration( $args['product_id'] );
		$sale_meta['_acp_order_coupon_id'] = MemberAccess_Coupons::get_product_coupon( $args['product_id'] );
		$initial_amount = $sale_meta['_acp_order_price'] = AccessPress_Products::get_product_price( $args['product_id'], $sale_meta['_acp_order_coupon_id'] );

		if ( $duration ) {

			$trial_duration = $this->_get_trial_duration( $args['product_id'] );
			if ( $trial_duration ) {

				$sale_meta['_acp_order_trial_price'] = AccessPress_Products::get_product_trial_price( $args['product_id'], $sale_meta['_acp_order_coupon_id'] );
				if ( $sale_meta['_acp_order_trial_price'] )
					$initial_amount = $sale_meta['_acp_order_trial_price'];

			}
		}

		// create authorization token
		$auth_request = sprintf( '&PAYMENTREQUEST_0_NAME=%1$s&PAYMENTREQUEST_0_QTY0=1&PAYMENTREQUEST_0_AMT=%2$s&RETURNURL=%3$s&CANCELURL=%4$s&PAYMENTREQUEST_0_CURRENCYCODE=%5$s',
			urlencode( $product_post->post_name ),
			urlencode( sprintf( '%.2f', $initial_amount ) ),
			urlencode( $success ),
			urlencode( $cancel ),
			urlencode( $this->selected_currency )
		);
		$auth_request .= '&NOSHIPPING=1&PAYMENTREQUEST_0_PAYMENTACTION=Authorization';

		$profile_date = '';
		if ( $duration ) {

			$sale_meta['_acp_order_renewal_time'] = $sale_meta['_acp_order_time'] + ( ( $trial_duration ? $trial_duration : $duration ) * 86400 );
			$profile_date = date( 'Y-m-d H:i:s', $sale_meta['_acp_order_renewal_time'] ) . 'Z';

			$auth_request .= sprintf( '&L_BILLINGTYPE0=RecurringPayments&L_PROFILESTARTDATE0=%s&L_BILLINGAGREEMENTDESCRIPTION0=%s&L_BILLINGPERIOD0=Day&L_BILLINGFREQUENCY0=%d&L_TOTALBILLINGCYCLES0=1&PAYMENTREQUEST_0_AMT1=%s',
				$profile_date,
				urlencode( $product_post->post_title ),
				$duration,
				urlencode( sprintf( '%.2f', $sale_meta['_acp_order_price'] ) )
			);

		}

		if ( !( $response = $this->_send_request( 'SetExpressCheckout', $auth_request ) ) )
			return $this->response;

		// we have a token - update the user meta with the transaction info
		$sale_meta['token'] = $response['TOKEN'];
		$sale_meta['profile_date'] = $profile_date;
		$accesspress_pp = array( $args['product_id'] => $sale_meta );
		update_user_option( $user_id, 'accesspress_pp', $accesspress_pp );

		// redirect the user to Paypal
		$url = $this->_customer_uri . urlencode( $response['TOKEN'] );

//@todo: translation support
?>
Redirecting to Paypal. Click this link if not redirected automatically:
<a href="<?php echo $url; ?>">Proceed to Paypal</a>
<script type="text/javascript">
//<!--
window.location = '<?php echo $url; ?>';
//-->
</script>
<?php
		return false;

	}

	private function _send_request( $method, $content ) {

		$request_body = 'METHOD=' . urlencode( $method ) . $this->_merchant_login . $content . $this->_gateway_version;

		$response = wp_remote_post( $this->_gateway_uri, array( 
			'body' => $request_body,
			'headers' => array(
					'Host' => $this->_gateway_host,
				),
			'httpversion' => '1.1', 
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			$this->response = $response;
			return false;
		}

		$response = wp_parse_args( $response['body'] );
		if ( strtolower( $response['ACK'] ) != 'success' ) {
			$this->response = new WP_Error( 'paypal-error', $response['L_LONGMESSAGE0'] );
			return false;
		}

		return $response;

	}
	/**
	 * Validate reportback from the payment gateway.
	 *
	 * @since 0.1.0
	 */
	public function validate_reportback() {

		// check for reportback
		if ( empty( $_REQUEST['id'] ) || empty( $_REQUEST['action'] ) )
			return false;

		// check for cancelled transaction
		if ( strtolower( $_REQUEST['action'] ) == 'cancel' || empty( $_REQUEST['token'] ) )
			return new WP_Error( 'cancelled', __( 'Transaction Cancelled.', 'premise' ) );

		// validate the transaction
		$user_id = (int) $_REQUEST['id'];
		$product_id = (int) $_REQUEST['product_id'];
		$meta = get_user_option( 'accesspress_pp', $user_id );
		if ( is_wp_error( $meta ) || empty( $meta ) || empty( $meta[$product_id] ) )
			return new WP_Error( 'invalid', __( 'Invalid Transaction.', 'premise' ) );

		$transaction = $meta[$product_id];
		if ( $transaction['token'] != $_REQUEST['token'] )
			return new WP_Error( 'invalid-key', __( 'Invalid Transaction Key.', 'premise' ) );

		// validated locally, now check Paypal & complete the transaction
		$get_details = sprintf( '&TOKEN=%s', urlencode( $transaction['token'] ) );
		if ( !( $response = $this->_send_request( 'GetExpressCheckoutDetails', $get_details ) ) )
			return $this->response;

		if ( $transaction['_acp_order_price'] != $response['AMT'] )
			return new WP_Error( 'invalid-amount', __( 'Amount does not match,', 'premise' ) );

		// validated, now send data back to the checkout
		$transaction['payer_id'] = $response['PAYERID'];
		$meta[$product_id] = $transaction;
		update_user_option( $user_id, 'accesspress_pp', $meta );
		return array(
			'member' => $user_id,
			'product_id' => $product_id,
			'order_details' => $transaction
		);

	}
	/**
	 * Refund a payment profile transaction.
	 *
	 * @since 2.5.0
	 */
	public function refund( $order_id ) {

		$order = get_post( $order_id );
		if ( ! $order )
			return;

		$product_id = get_post_meta( $order_id, '_acp_order_product_id', true );
		if ( ! $product_id )
			return;

		$product = get_post( $product_id );
		if ( ! $product )
			return;

		$product_description = $product->post_title . ' (' . $order->post_title . ')';

		// Paypal requires the transaction ID for a full refund of a transaction
		// cancel requires the member ID
		// check the amount so we know there is something to refund
		$meta = $this->get_transaction_meta( $order_id );
		foreach( array( 'transaction_id', 'amount', 'member_id' ) as $key ) {

			if ( empty( $meta[$key] ) )
				return;

		}

		$refund_request = sprintf( '&TRANSACTIONID=%s&REFUNDTYPE=Full', urlencode( $meta['transaction_id'] ) );

		try {
			
			$this->_send_request( 'RefundTransaction', $refund_request );

		} catch( Exception $exc ) {
			
			// this could have been refunded manually so ignore error

		}

		// now cancel the subscription
		$args = array(
			'product_id' => $product_id,
			'member_id' => $meta['member_id'],
		);
		$this->cancel( $args );
		
	}
	/**
	 * Show the confirmation form
	 *
	 * method is called by the checkout form when a sale is validated
	 *
	 * @since 0.1.0
	 */
	public function confirmation_form( $sale ) {

		// show the confirmation form
		echo '<form method="post" action="">';

			printf( '<input type="hidden" name="accesspress-checkout[product_id]" value="%s" />', $sale['product_id'] );
			printf( '<input type="hidden" name="accesspress-checkout[member]" value="%d" />', $sale['member'] );
			printf( '<input type="hidden" name="accesspress-checkout[payment-method]" value="%s" />', $this->payment_method );
			wp_nonce_field( $sale['order_details']['token'], 'accesspress-checkout[key]' );
			printf( '<input type="submit" value="%s" />', is_user_logged_in() ? __( 'Complete Order', 'premise' ) : __( 'Complete Order and Create My Account', 'premise' ) );

		echo '</form>';

		return false;
	}
	/**
	 * Complete a sale on the Paypal gateway
	 *
	 * method is called by the checkout form after a sale is validated
	 *
	 * @since 0.1.0
	 */
	public function complete_sale( $args ) {

		// validate based on the confirmation form
		if( empty( $args['product_id'] ) || empty( $args['member'] ) || empty( $args['key'] ) )
			return false;

		$meta = get_user_option( 'accesspress_pp', $args['member'] );
		if ( is_wp_error( $meta ) || empty( $meta ) || empty( $meta[$args['product_id']] ) )
			return new WP_Error( 'invalid', __( 'Invalid Transaction.', 'premise' ) );

		$transaction = $meta[$args['product_id']];
		if ( empty( $transaction['token'] ) || ! wp_verify_nonce( $args['key'], $transaction['token'] ) )
			return new WP_Error( 'invalid-key', __( 'Invalid Transaction Key.', 'premise' ) );

		$transaction['_acp_order_coupon_id'] = MemberAccess_Coupons::get_product_coupon( $args['product_id'] );
		$initial_amount = $transaction['_acp_order_price'];
		$trial_amount = isset( $transaction['_acp_order_trial_price'] ) ? $transaction['_acp_order_trial_price'] : 0;

		if ( $transaction['profile_date'] && $trial_amount )
			$initial_amount = $trial_amount;

		// complete the transaction
		$product_post = get_post( $args['product_id'] );
		$transaction['order_title'] = time() . '-' . $args['member'];
		$product_description = $product_post->post_title . ' (' . $transaction['order_title'] . ')';
		$trial_duration = $this->_get_trial_duration( $args['product_id'] );

		if ( ! $transaction['profile_date'] || ! $trial_duration || $trial_amount ) {

			$complete = sprintf( '&TOKEN=%s&PAYERID=%s&PAYMENTREQUEST_0_AMT=%s&PAYMENTREQUEST_0_CURRENCYCODE=%s&PAYMENTREQUEST_0_PAYMENTACTION=Sale&PAYMENTREQUEST_0_DESC=%s',
				urlencode( $transaction['token'] ),
				urlencode( $transaction['payer_id'] ),
				urlencode( sprintf( '%.2f', $initial_amount ) ),
				urlencode( $this->selected_currency ),
				urlencode( $product_description )
			);

			if ( !( $response = $this->_send_request( 'DoExpressCheckoutPayment', $complete ) ) )
				return $this->response;

		}

		$transaction['_acp_order_paypal_transaction_id'] = isset( $response['PAYMENTINFO_0_TRANSACTIONID'] ) ? $response['PAYMENTINFO_0_TRANSACTIONID'] : $transaction['token'];

		if ( $transaction['profile_date'] ) {

			$duration = $this->get_subscription_duration( $args['product_id'] );
			$number_payments = (int) get_post_meta( $args['product_id'], '_acp_product_number_payments', true );
			$trial_amount = (int) get_post_meta( $args['product_id'], '_acp_product_trial_price', true );

			$complete = sprintf( '&TOKEN=%s&PAYERID=%s&AMT=%s&CURRENCYCODE=%s&PAYMENTREQUEST_0_PAYMENTACTION=Sale&PROFILESTARTDATE=%s&DESC=%s&BILLINGPERIOD=Day&BILLINGFREQUENCY=%d&TOTALBILLINGCYCLES=%d&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital&L_PAYMENTREQUEST_0_NAME0=%6$s&L_PAYMENTREQUEST_0_AMT0=%3$s&L_PAYMENTREQUEST_0_QTY0=1',
				urlencode( $transaction['token'] ),
				urlencode( $transaction['payer_id'] ),
				urlencode( sprintf( '%.2f', $transaction['_acp_order_price'] ) ),
				urlencode( $this->selected_currency ),
				urlencode( $transaction['profile_date'] ),
				urlencode( $product_post->post_title ),
				$duration,
				$trial_amount ? $number_payments : ( $number_payments > 1 ? $number_payments - 1 : 0 )
			);

			if ( !( $response = $this->_send_request( 'CreateRecurringPaymentsProfile', $complete ) ) )
				return $this->response;

			if ( ! empty( $response['PROFILEID'] ) )
				update_user_option( $args['member'], 'memberaccess_paypal_profile_' . $args['product_id'], $response['PROFILEID'] );

		}
		// cleanup & return data to allow transaction to be completed by checkout
		unset( $transaction['token'] );
		delete_user_option( $args['member'], 'accesspress_pp' );

		return array(
			'member' => $args['member'],
			'order_details' => $transaction
		);

	}
	/**
	 * Test the Paypal gateway
	 *
	 * method is called by the member access settings page
	 *
	 * @since 0.1.0
	 */
	public function test() {

		$base_url = home_url( '/' );
		$success = add_query_arg( array( 'action' => 'complete' ), $base_url );
		$cancel = add_query_arg( array( 'action' => 'cancel' ), $base_url );

		// create authorization token
		$test_request = sprintf( '&L_NAME0=%1$s&L_AMT0=%2$s&L_QTY0=1&AMT=%2$s&ReturnUrl=%3$s&CANCELURL=%4$s&CURRENCYCODE=%5$s&PAYMENTACTION=DoAuthorization',
			urlencode( 'Test Product' ),
			urlencode( sprintf( '%.2f', 1 ) ),
			urlencode( $success ),
			urlencode( $cancel ),
			urlencode( $this->selected_currency )
		);

		return $this->_send_request( 'SetExpressCheckout', $test_request );

	}
	/**
	 * Validate a Paypal IPN reportback
	 *
	 * method is called by the recurring payments process
	 *
	 * @since 0.1.0
	 */
	public function validate_IPN() {

		if ( ! $this->configured || empty( $_POST ) )
			return false;

		$body = 'cmd=_notify-validate';
		foreach( $_POST as $key => $value )
			$body .= '&' . $key . '=' . urlencode( stripslashes( $value ) );

		$response = wp_remote_post( $this->_validation_uri, array( 
			'body' => $body, 
			'timeout' => 30, 
			'httpversion' => '1.1',
			'headers' => array(
				'Host' => 'www.paypal.com',
				'Connection' => 'close'
			)
		) );
		if ( empty( $response['body'] ) || strpos( $response['body'], 'VERIFIED' ) !== 0 )
			return false;

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

		if ( ! isset( $args['member_id'] ) || ! isset( $args['product_id'] ) )
			return false;

		$payment_profile = get_user_option( 'memberaccess_paypal_profile_' . $args['product_id'], $args['member_id'] );
		if( ! $payment_profile )
			return false;

		$cancel_request = sprintf( '&PROFILEID=%s&action=cancel', $payment_profile );
		if ( ! ( $response = $this->_send_request( 'ManageRecurringPaymentsProfileStatus', $cancel_request ) ) )
			return $this->response;

		return true;

	}
	/**
	 * show the transaction meta for an order.
	 *
	 * show transaction ID, customer profile ID and payment profile ID
	 *
	 * @since 2.2.0
	 */
	public function show_order_transaction_meta( $order_id ) {

			$member_id = get_post_meta( $order_id, '_acp_order_member_id', true );
			$product_id = get_post_meta( $order_id, '_acp_order_product_id', true );
			printf( '<p><b>%s</b>: %s</p>', __( 'PayPal Transaction ID', 'premise' ), get_post_meta( $order_id, '_acp_order_paypal_transaction_id', true ) );
			printf( '<p><b>%s</b>: %s</p>', __( 'PayPal Payment Profile ID', 'premise' ), get_user_option( 'memberaccess_paypal_profile_' . $product_id, $member_id ) );

	}
	/**
	 * get the transaction meta for an order.
	 *
	 * get member ID, transaction ID, customer profile ID, payment profile ID and amount
	 *
	 * @since 2.5.0
	 */
	public function get_transaction_meta( $order_id ) {

		$member_id = get_post_meta( $order_id, '_acp_order_member_id', true );
		$product_id = get_post_meta( $order_id, '_acp_order_product_id', true );

		$meta = array(
			'member_id' => $member_id,
			'transaction_id' => get_post_meta( $order_id, '_acp_order_paypal_transaction_id', true ),
			'payment_profile_id' => get_user_option( 'memberaccess_paypal_profile_' . $product_id, $member_id ),
			'amount' => get_post_meta( $order_id, '_acp_order_price', true ),
		);

		return $meta;

	}
}

add_action( 'memberaccess_setup', 'premise_register_paypal_express_gateway' );

function premise_register_paypal_express_gateway() {

	memberaccess_register_payment_gateway( 'MemberAccess_Paypal_Gateway' );

}