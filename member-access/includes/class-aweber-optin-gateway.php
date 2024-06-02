<?php
/**
 * Optin gateway class to configure and process Optin gateways.
 *
 * This class allows payment via optin.
 *
 * @since 0.1.0
 */
class MemberAccess_AWeber_Optin_Gateway extends MemberAccess_Gateway {
	/**
	 * The user data submitted with the optin form.
	 *
	 * @since 0.1.0
	 *
	 * @var array of user data
	 */
	private $_user_data;

	/**
	 * The Premise meta for the current landing page.
	 *
	 * @since 0.1.0
	 *
	 * @var array of landing page meta
	 */
	private $_premise_meta;

	/**
	 * Class constructor.
	 *
	 * @since 0.1.0
	 */
	function __construct() {

	 	$this->create( 'aweber' );

	}
	/**
	 * Initialize the payment gateway.
	 *
	 * @since 0.1.0
	 */
	public function configure() {

		global $premise_base;

		add_action( 'premise_membership_create_order', array( $this, 'subscribe_customer' ), 10, 2 );

		// identify as opt in gateway ?
		$settings = $premise_base->get_settings();
		if ( isset( $settings['optin']['aweber-api'] ) && $settings['optin']['aweber-api'] ) {

			$this->opt_in_gateway_name = 'Aweber';

			// product post type hooks
			add_filter( 'memberaccess_default_product_meta', array( $this, 'add_default_product_meta' ) );
			add_action( 'admin_menu', array( $this, 'add_metabox' ) );

			if ( isset( $settings['optin']['aweber-enhanced'] ) && $settings['optin']['aweber-enhanced'] )
				add_action( 'premise_cancel_subscription', array( $this, 'unsubscribe_customer' ), 10, 3 );

		}
		// never show this gateway on the checkout form
		return false;

	}

	function add_metabox() {

		add_meta_box( 'accesspress-product-aweber-metabox', __( 'AWeber', 'premise' ), array( $this, 'product_metabox' ), 'acp-products', 'normal', 'low' );

	}

	function product_metabox() {

		global $Premise;
		$lists = $Premise->getAweberLists();

		if ( empty( $lists ) ) {
			_e( 'No AWeber lists found.', 'premise' );
			return;
		}
		?>
		<p>
			<label for="accesspress_product_meta[_acp_product_aweber_list]"><strong><?php _e( 'AWeber List', 'premise' ); ?></strong>:
			<select name="accesspress_product_meta[_acp_product_aweber_list]">
				<option value=""><?php _e( 'None', 'premise' ); ?></option>
		<?php

		foreach( $lists as $list )
			printf( '<option value="%s" %s>%s</option>', $list['id'], selected( accesspress_get_custom_field( '_acp_product_aweber_list' ), $list['id'], false ), $list['name'] );

		?>
			</select>
		</p>
		<?php
	}

	public function optin_extra_fields( $meta ) {

		$args = array(
			'heading_text' => false,
			'label_separator' => '*',
			'wrap_before' => '',
			'wrap_after' => '',
			'before_item' => '<li>',
			'after_item' => '</li>',
			'show_email_address' => false,
			'show_first_name' => true,
			'show_last_name' => true,
			'product_id' => $meta['member-product'],
		);

		accesspress_checkout_form_account( $args );

	}
	function validate_optin_extra_fields( $errors, $merge_vars, $meta ) {

		$this->_product_id = $meta['member-product'];
		$this->_premise_meta = $meta;

		$args = empty( $_POST['accesspress-checkout'] ) ? array() : $_POST['accesspress-checkout'];
		$args = wp_parse_args( $args, array(
				'username' => '',
				'first-name' => '',
				'last-name' => '',
				'password' => '',
				'password-repeat' => '',
			)
		);

		// add merge vars to args if substituted on the opt in form
		$args['email'] = ! empty( $args['email'] ) ? $args['email'] : $merge_vars['email'];
		if ( accesspress_get_option( 'email_as_username' ) )
			$args['username'] = $args['email'];

		if ( ! $args['first-name'] || ! $args['last-name'] || ! $args['username'] )
			$errors[] = __( 'The account information was not filled out.', 'premise' );

		if ( ! is_user_logged_in() ) {

			if ( ! $args['password'] || ! $args['password-repeat'] )
				$errors[] = __( 'The account information was not filled out.', 'premise' );

			/** If passwords do not match */
			if ( $args['password'] !== $args['password-repeat'] )
				$errors[] = __( 'The passwords do not match.', 'premise' );

		}

		if ( empty( $errors ) ) {

			$this->_member_args = $args;
			add_filter( 'premise_optin_subscribe_user', array( $this, 'register_user' ), 10, 2 );
			add_action( 'premise_optin_complete_order', array( $this, 'complete_order' ) );

		}

		return $errors;

	}

	function add_default_product_meta( $defaults ) {

		$defaults['_acp_product_mailchimp_email'] = '';
		$defaults['_acp_product_mailchimp_first-name'] = '';
		$defaults['_acp_product_mailchimp_last-name'] = '';
		$defaults['_acp_product_mailchimp_list'] = '';
		return $defaults;

	}

	public function register_user( $setting, $args ) {

		if ( empty( $this->_product_id ) || ! $this->_product_id )
			return $setting;

		$product = get_post( $this->_product_id );
		if ( ! $product || empty( $product->post_type ) || $product->post_type != 'acp-products' )
			return new WP_Error( 'product_missing', __( 'Product information missing', 'premise' ) );

		// eliminate case mismatches
		$userdata = array(
			'first_name' => $this->_member_args['first-name'],
			'last_name'  => $this->_member_args['last-name'],
			'user_email' => $this->_member_args['email'],
			'user_login' => $this->_member_args['username'],
			'user_pass'  => $this->_member_args['password'],
		);

		return accesspress_create_member( $userdata );

	}
	public function complete_order( $member ) {

		if ( empty( $this->_product_id ) || ! $member )
			return;

		$order_details = array(
			'_acp_order_time'       => time(),
			'_acp_order_status'     => 'complete',
			'_acp_order_product_id' => $this->_product_id,
			'_acp_order_member_id' => $member,
		);
		accesspress_create_order( $member, $order_details );

	}
	public function _process_order( $args ) {}
	/**
	 * Member can cancel flag.
	 *
	 * MailChimp isn't for subscriptions
	 *
	 * @return bool false
	 * @since 2.1
	 */
	public function member_can_cancel() {

		return false;

	}

	function subscribe_customer( $member, $order_details ) {

		$product_id = isset( $order_details['_acp_order_product_id'] ) ? $order_details['_acp_order_product_id'] : 0;
		$user = get_user_by( 'id', $member );
		if ( ! $product_id || ! $user )
			return;

		try {

			$list = $this->get_list_object( $product_id );
			if ( ! $list )
				return;

			# create a subscriber
			$params = array(
				'email' => $user->user_email,
				'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) && strlen( $_SERVER['REMOTE_ADDR'] ) > 6 ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
				'ad_tracking' => '',
				'last_followup_message_number_sent' => 0,
				'misc_notes' => '',
				'name' => $user->first_name . ' ' . $user->last_name,
			);

			$list->subscribers->create( $params );

		} catch( AWeberAPIException $exc ) {
//@todo: email the site admin on an exception
		}

	}

	function unsubscribe_customer( $order_id, $product_id, $member ) {

		$user = get_user_by( 'id', $member );
		if ( ! $product_id || ! $user )
			return;

		try {

			$list = $this->get_list_object( $product_id );
			if ( ! $list )
				return;

			# unsubscribe
			$subscribers = $list->subscribers->find( array( 'email' => $user->user_email ) );

			foreach( $subscribers as $subscriber ) {

				$subscriber->status = 'unsubscribed';
				$subscriber->save();

			}

		} catch( AWeberAPIException $exc ) {

			$to = get_option( 'admin_email' );
			$blogname = get_option( 'blogname' );
			$subject = __( 'Error unsubscribing', 'premise' );
			$message = sprintf( __( "An error occurred on the Aweber API unsubscribing %s.\n\nError Message: %s\n", 'premise' ), $user->user_email, $exc->message );

			wp_mail( $to, $subject, $message, "From: \"{$blogname}\" <{$to}>" );

		}
	}

	function get_list_object( $product_id ) {

		global $premise_base;

		$list_id = get_post_meta( $product_id, '_acp_product_aweber_list', true );
		if ( ! $list_id )
			return false;

		$the_list = $premise_base->get_aweber_list( $list_id );
		if ( ! $the_list )
			return false;

		$account = $premise_base->get_aweber_account();
		if ( ! $account )
			return false;

		try {

			return $account->loadFromUrl( "/accounts/{$account->id}/lists/{$the_list['id']}" );

		} catch( AWeberAPIException $exc ) {

			return false;

		}

	}
}

add_action( 'memberaccess_setup', 'premise_register_aweber_optin_gateway' );

function premise_register_aweber_optin_gateway() {

	memberaccess_register_payment_gateway( 'MemberAccess_AWeber_Optin_Gateway' );

}
