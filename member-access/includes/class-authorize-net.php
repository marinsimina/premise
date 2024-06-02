<?php
/**
 * Authorize.net gateway class to configure and process payment gateways.
 *
 * This class uses the CIM api.
 *
 * @since 0.1.0
 */
class MemberAccess_AuthorizeNet_Gateway extends MemberAccess_Gateway {
	/**
	 * The Authorize.net merchant authentication block.
	 *
	 * @since 0.1.0
	 *
	 * @var string XML CIM merchant authentication block
	 */
	private $_merchant_login;

	/**
	 * The Authorize.net gateway URI.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_gateway_uri;

	/**
	 * The Authorize.net gateway mode.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	private $_gateway_mode;

	/**
	 * The Authorize.net gateway response.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public $response;

	/**
	 * The Authorize.net gateway response code.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	private $response_code;

	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	function __construct() {

	 	$this->create( 'cc', __( 'Credit Card', 'premise' ) );

	 	if ( ! $this->configured ) {

	 		remove_action( 'premise_checkout_form', 'accesspress_checkout_form_payment_cc', 12 );
	 		return;

	 	}

	 	add_action( 'edit_user_profile', array( $this, 'user_profile' ), 9 );
	 	add_action( 'edit_user_profile_update', array( $this, 'user_profile_update' ) );

	}
	/**
	 * Initialize the payment gateway.
	 *
	 * @since 0.1.0
	 */
	public function configure() {

		$api_login = accesspress_get_option( 'authorize_net_id' );
		$transaction_key = accesspress_get_option( 'authorize_net_key' );

		// we need both an id & key to use the gateway
		if ( empty( $api_login ) || empty( $transaction_key ) )
			return false;

		$this->_gateway_uri = 'https://' . ( '1' == self::mode( 'gateway_live_mode_authorize_net' ) ? 'api' : 'apitest' ) . '.authorize.net/xml/v1/request.api';
//		$this->_gateway_mode = '<validationMode>' . ( '1' == accesspress_get_option( 'gateway_live_mode' ) ? 'live' : 'test' ) . 'Mode</validationMode>';
		$this->_gateway_mode = '<validationMode>testMode</validationMode>';
		$this->_merchant_login = sprintf( '<merchantAuthentication><name>%s</name><transactionKey>%s</transactionKey></merchantAuthentication>', $api_login, $transaction_key );

		return true;

	}

	/**
	 * Handle the postback of the payment gateway form.
	 *
	 * @since 0.1.0
	 */
	public function _process_order( $args ) {

		$trial_amount = $trial_duration = $duration = 0;
		$sale_meta = $args['order_details'];

		// create local user
		$user_id = $sale_meta['_acp_order_member_id'];
		$memberaccess_cc_profile_id = isset( $args['cc_profile_id'] ) ? $args['cc_profile_id'] : 0;
		$memberaccess_cc_payment_profile_id = $this->get_checkout_payment_profile( $args );

		if ( empty( $memberaccess_cc_profile_id ) && is_user_logged_in() )
			$memberaccess_cc_profile_id = get_user_option( 'memberaccess_cc_profile_id' );

		if ( is_user_logged_in() && empty( $args['first-name'] ) && empty( $args['last-name'] ) ) {

			$user = get_user_by( 'id', $user_id );
			$args['first-name'] = $user->first_name;
			$args['last-name'] = $user->last_name;
			$args['email'] = $user->user_email;

		}

		/** for initial payment attempts only */
		if ( ! $memberaccess_cc_profile_id ) {

			// create member profile
			$customer_info = sprintf( '<merchantCustomerId>%d</merchantCustomerId><description>%s</description><email>%s</email>',
				$user_id,
				trim( $args['first-name'] . ' ' . $args['last-name'] ),
				$args['email']
			);

			if ( !( $response = $this->_send_request( 'createCustomerProfileRequest', '<profile>' . $customer_info . '</profile>' ) ) )
				return $this->response;

			$this->customer_response = $response;
			$memberaccess_cc_profile_id = (string)$response->customerProfileId;

			update_user_option( $user_id, 'memberaccess_cc_profile_id', $memberaccess_cc_profile_id );

		}

		$customer = sprintf( '<customerProfileId>%d</customerProfileId>', $memberaccess_cc_profile_id );

		/** for new subscriptions only */
		if ( ! $memberaccess_cc_payment_profile_id ) {

			// profile created now send billing info
			$bill_to = sprintf( '<billTo><firstName>%s</firstName><lastName>%s</lastName><zip>%s</zip><country>%s</country></billTo>',
				esc_html( $args['first-name'] ),
				esc_html( $args['last-name'] ),
				$args['card-postal'],
				$args['card-country']
			);
			$payment = sprintf( '<payment><creditCard><cardNumber>%s</cardNumber><expirationDate>%04d-%02d</expirationDate><cardCode>%s</cardCode></creditCard></payment>',
				$args['card-number'],
				$args['card-year'],
				$args['card-month'],
				$args['card-security']
			);
			$profile = '<paymentProfile>' . $bill_to . $payment . '</paymentProfile>';

			if ( !( $response = $this->_send_request( 'createCustomerPaymentProfileRequest', $customer . $profile . $this->_gateway_mode ) ) )
				return $this->response;

			$this->profile_response = $response;
			$memberaccess_cc_payment_profile_id = (string)$response->customerPaymentProfileId;

			$payment_profiles = self::get_payment_profiles( $user_id );
			$payment_profiles[$memberaccess_cc_payment_profile_id] = $this->get_pci_number( $args['card-number'] );
			update_user_option( $user_id, 'memberaccess_cc_payment_profile_id', $payment_profiles );
			update_user_option( $user_id, 'memberaccess_cc_payment_' . $args['product_id'], $memberaccess_cc_payment_profile_id );

		}

		// payment profile created now charge the account
		$product_post = get_post( $args['product_id'] );
		$sale_meta['_acp_order_coupon_id'] = MemberAccess_Coupons::get_product_coupon( $args['product_id'] );
		$sale_meta['_acp_order_price'] = AccessPress_Products::get_product_price( $args['product_id'], $sale_meta['_acp_order_coupon_id'] );

		if ( empty( $sale_meta['_acp_order_renewal_time'] ) ) {

			$duration = $trial_duration = $this->_get_trial_duration( $args['product_id'] );
			if ( $trial_duration ) {

				$trial_amount = AccessPress_Products::get_product_trial_price( $args['product_id'], $sale_meta['_acp_order_coupon_id'] );
				if ( $trial_amount )
					$amount = sprintf( '<amount>%.2f</amount>', $trial_amount );

			}
		}

		if ( empty( $amount ) || empty( $duration ) ) {

			$amount = sprintf( '<amount>%.2f</amount>', $sale_meta['_acp_order_price'] );
			$duration = $this->get_subscription_duration( $args['product_id'] );

		}

		$recurring = $duration ? 'true' : 'false';
		$sale_meta['order_title'] = time() . '-' . $user_id;
		$product_description = $product_post->post_title . ' (' . $sale_meta['order_title'] . ')';
		$payment_profile = sprintf( '<customerPaymentProfileId>%d</customerPaymentProfileId><recurringBilling>%s</recurringBilling>', $memberaccess_cc_payment_profile_id, $recurring );
		$item = sprintf( '<lineItems><itemId>%s</itemId><name>%s</name><description>%s</description><quantity>1</quantity><unitPrice>%.2f</unitPrice><taxable>false</taxable></lineItems>',
			$args['product_id'] . '-' . time(),
			substr( $product_post->post_name, 0, 31 ),
			esc_html( $product_description ),
			! empty( $trial_amount ) ? $trial_amount : $sale_meta['_acp_order_price']
		);

		if ( ! $duration || ! $trial_duration || $trial_amount ) {

			$transaction = '<transaction><profileTransAuthCapture>' . $amount . $item . $customer . $payment_profile . '</profileTransAuthCapture></transaction>';
			if ( !( $response = $this->_send_request( 'createCustomerProfileTransactionRequest', $transaction ) ) )
				return $this->response;

			$direct_response = explode( ',', $response->directResponse );
			$sale_meta['_acp_order_anet_transaction_id'] = $direct_response[6];

		}

		if ( $duration ) {

			$sale_meta['_acp_order_renewal_time'] = ( ! empty( $sale_meta['_acp_order_renewal_time'] ) ? $sale_meta['_acp_order_renewal_time'] : $sale_meta['_acp_order_time'] ) + ( $duration * 86400 );
			$sale_meta['_acp_order_status'] = 'active';

			$number_payments = get_post_meta( $args['product_id'], '_acp_product_number_payments', true );
			if ( (int) $number_payments )
				$sale_meta['_acp_order_payments'] = $trial_amount ? array( $sale_meta['_acp_order_time'] => $sale_meta['_acp_order_price'] ) : array();

		}

		return $sale_meta;

	}

	private function _send_request( $tag, $content ) {

		$request_body = '<?xml version="1.0" encoding="utf-8"?><' . $tag . ' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">' . $this->_merchant_login . $content . '</' . $tag . '>';

		$response = wp_remote_post( $this->_gateway_uri, array( 'headers' => array( 'content-type' => 'text/xml' ), 'body' => $request_body, 'timeout' => 30 ) );

		if ( is_wp_error( $response ) ) {
			$this->response = $response;
			return false;
		}

		if ( empty( $response['body'] ) ) {
			$this->response = new WP_Error( 'cc-server', __( 'Invalid response from payment processor', 'premise' ) );
			return false;
		}

		$response = simplexml_load_string( $response['body'], 'SimpleXMLElement', LIBXML_NOWARNING );
		if ( $response->messages->resultCode == 'Error' ) {
			$this->response = new WP_Error( 'cc-error', (string) $response->messages->message->text );
			$this->response_code = (string) $response->messages->message->code;
			return false;
		}

		return $response;

	}

	function test() {

		return $this->_send_request( 'getCustomerProfileRequest', '<customerProfileId>1000</customerProfileId>' );

	}
	/**
	 * Validate checkout form.
	 *
	 * validate the credit card fields
	 *
	 * @since 2.1
	 */
	public function validate_checkout_form( $args ) {

		if ( ! $args['card-name'] || ! $args['card-number'] || ! $args['card-month'] || ! $args['card-year'] || ! $args['card-security'] || ! $args['card-country'] || ! $args['card-postal'] )
			return new WP_Error( 'credit_card_not_filled_out', 'The credit card info was not completed.' );

		return true;

	}
	/**
	 * Refund a payment profile transaction.
	 *
	 * @since 2.5.0
	 */
	public function refund( $order_id ) {

		// all three IDs are needed for refunding a transaction
		$meta = $this->get_transaction_meta( $order_id );
		foreach( array( 'profile_id', 'transaction_id', 'payment_profile_id', 'amount' ) as $key ) {

			if ( empty( $meta[$key] ) )
				return;

		}

		$amount = sprintf( '<amount>%.2f</amount>', $meta['amount'] );
		$customer = sprintf( '<customerProfileId>%d</customerProfileId>', $meta['profile_id'] );
		$payment_profile = sprintf( '<customerPaymentProfileId>%d</customerPaymentProfileId>', $meta['payment_profile_id'] );
		$transaction_id = sprintf( '<transId>%s</transId>', $meta['transaction_id'] );

		$transaction = sprintf( '<transaction><profileTransRefund>%s</profileTransRefund></transaction>', $amount . $customer . $payment_profile . $transaction_id );

		try {

			$result = $this->_send_request( 'createCustomerProfileTransactionRequest', $transaction );
			if ( $result !== false )
				return;
				
			$transaction = sprintf( '<transaction><profileTransVoid>%s</profileTransVoid></transaction>', $customer . $payment_profile . $transaction_id );
			$this->_send_request( 'createCustomerProfileTransactionRequest', $transaction );

		} catch( Exception $exc ) {
			
			// this could have been refunded manually in CIM so ignore error

		}

	}
	/**
	 * show the transaction meta for an order.
	 *
	 * show transaction ID, customer profile ID and payment profile ID
	 *
	 * @since 2.2.0
	 */
	public function show_order_transaction_meta( $order_id ) {

		$meta = self::get_transaction_meta( $order_id );

		printf( '<p><b>%s</b>: %s</p>', __( 'Authorize.net Transaction ID', 'premise' ), $meta[ 'transaction_id'] );
		printf( '<p><b>%s</b>: %s</p>', __( 'Authorize.net Profile ID', 'premise' ), $meta[ 'profile_id'] );

		if ( ! get_post_meta( $order_id, '_acp_order_renewal_time', true ) )
			return;

		$payment_profiles = self::get_payment_profiles( $meta['member_id'] );

		if ( isset( $payment_profiles[$meta[ 'payment_profile_id']] ) )
			printf( '<p><b>%s</b>: %s - %s</p>', __( 'Authorize.net Payment Profile ID', 'premise' ), $meta[ 'payment_profile_id'], $payment_profiles[$meta[ 'payment_profile_id']] );

	}
	/**
	 * has the transaction meta for an order.
	 *
	 * returns bool 
	 *
	 * @since 2.2.0
	 */
	 function has_transaction_meta( $order_id ) {

		return get_post_meta( $order_id, '_acp_order_anet_transaction_id', true );

	}
	/**
	 * show the CC profile ID on profile edit.
	 *
	 * @since 2.3.0
	 */
	function user_profile( $user ) {
	
		if ( empty( $user->ID ) || ! is_admin() )
			return;
	
		$memberaccess_cc_profile_id = get_user_option( 'memberaccess_cc_profile_id', $user->ID );
		$payment_profiles = self::get_payment_profiles( $user->ID );
	
		printf( '<h3>%s</h3><table class="form-table"><tr>', __( 'Authorize.net', 'premise' ) );
	
		printf( '<th>%s</th>', __( 'Authorize.net Profile ID', 'premise' ) );
		printf( '<td><input type="text" name="memberaccess_cc_profile_id" value="%s" /></td>', $memberaccess_cc_profile_id );
		echo '</tr><tr>';
		printf( '<th>%s</th>', __( 'Authorize.net Payment Profile ID', 'premise' ) );
		echo '<td><ul>';

		foreach( $payment_profiles as $id => $pci_number )
			printf( '<li>%1$s: <input type="text" name="memberaccess_cc_payment_profile_id[%2$s]" value="%2$s" /> %3$s: <input type="text" name="memberaccess_cc_payment_profile_number[%2$s]" value="%4$s" /></li>', __( 'ID', 'premise' ), $id, __( 'Card Number', 'premise' ), $pci_number );

		echo '</ul></td></tr></table>';
	
	
	}
	/**
	 * update the CC profile ID on profile edit.
	 *
	 * @since 2.3.0
	 */
	function user_profile_update( $user_id ) {
	
		if ( ! is_admin() || ! current_user_can( 'edit_users', $user_id ) )
			return;
	
		if ( isset( $_POST['memberaccess_cc_profile_id'] ) )
			update_user_option( $user_id, 'memberaccess_cc_profile_id', $_POST['memberaccess_cc_profile_id'] );
	
		if ( ! isset( $_POST['memberaccess_cc_payment_profile_id'] ) || ! isset( $_POST['memberaccess_cc_payment_profile_number'] ) || !  is_array( $_POST['memberaccess_cc_payment_profile_id'] ) || ! is_array( $_POST['memberaccess_cc_payment_profile_number'] ) )
			return;

		$new_ids = $_POST['memberaccess_cc_payment_profile_id'];
		$new_numbers = $_POST['memberaccess_cc_payment_profile_number'];
		$payment_profiles = self::get_payment_profiles( $user_id );
		
		foreach( $payment_profiles as $id => $pci_number ) {

			if ( ! isset( $new_ids[$id] ) )
				continue;

			$pci_cc = $this->get_pci_number( $new_numbers[$id] );

			// has the profile id changed
			if ( $new_ids[$id] == $id ) {

				$payment_profiles[$id] = $pci_cc;
				continue;

			}

			// dont' allow empty key
			$key = empty( $new_ids[$id] ) ? '0' : $new_ids[$id];
			$payment_profiles[$key] = $pci_cc;
			unset( $payment_profiles[$id] );

		}

		if ( count( $payment_profiles ) > 1 || ! empty( $key ) )
			update_user_option( $user_id, 'memberaccess_cc_payment_profile_id', $payment_profiles );

	}

	static function get_payment_profiles( $user_id = null ) {

		$payment_profiles = get_user_option( 'memberaccess_cc_payment_profile_id', $user_id );
		
		if ( ! is_array( $payment_profiles ) )
			$payment_profiles = array( is_numeric( $payment_profiles ) ? $payment_profiles : '0' => __( 'Card on file', 'premise' ) );

		return $payment_profiles;

	}

	function get_checkout_payment_profile( $args ) {

		return isset( $args['cc_payment_profile_id'] ) ? $args['cc_payment_profile_id'] : 0;

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
			'profile_id' => get_user_option( 'memberaccess_cc_profile_id', $member_id ),
			'transaction_id' => get_post_meta( $order_id, '_acp_order_anet_transaction_id', true ),
			'payment_profile_id' => get_user_option( 'memberaccess_cc_payment_' . $product_id, $member_id ),
			'amount' => get_post_meta( $order_id, '_acp_order_price', true ),
		);

		return $meta;

	}
}

class MemberAccess_AuthorizeNet_Existing_Gateway extends MemberAccess_AuthorizeNet_Gateway {
	/**
	 * The Authorize.net payment profile ID.
	 *
	 * @since 2.2.2
	 *
	 * @var integer
	 */
	private $_payment_profile_id;

	/**
	 * Class constructor.
	 *
	 * @since 2.2.2
	 */
	function __construct() {

		$payment_profiles = self::get_payment_profiles();
		foreach( $payment_profiles as $id => $pci_number ) {

			if ( ! $id )
				continue;

			$profile_hash = md5( $id );
			if ( memberaccess_get_payment_gateway( $profile_hash ) )
				continue;

			$this->_payment_profile_id = $id;
			$profile_description = $pci_number;
			break;

		}

		if ( ! $this->_payment_profile_id )
			return;

	 	$this->create( $profile_hash, $profile_description );

	}
	public function validate_checkout_form( $args ) {

		return true;

	}
	function get_checkout_payment_profile( $args ) {

		return $this->_payment_profile_id;

	}
}

add_action( 'memberaccess_setup', 'premise_register_authorize_net_gateway' );

function premise_register_authorize_net_gateway() {

	if ( is_user_logged_in() ) {

		$payment_profiles = MemberAccess_AuthorizeNet_Gateway::get_payment_profiles();
		for( $g = 0; $g < count( $payment_profiles ); $g++ )
			memberaccess_register_payment_gateway( 'MemberAccess_AuthorizeNet_Existing_Gateway' );

	}

	memberaccess_register_payment_gateway( 'MemberAccess_AuthorizeNet_Gateway' );

}