<?php
/**
 * Optin gateway class to configure and process Optin gateways.
 *
 * This class allows payment via optin.
 *
 * @since 0.1.0
 */
class MemberAccess_Optin_Gateway extends MemberAccess_Gateway {
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

	 	$this->create( 'mailchimp' );

	}
	/**
	 * Initialize the payment gateway.
	 *
	 * @since 0.1.0
	 */
	public function configure() {

		add_action( 'premise_membership_create_order', array( $this, 'subscribe_customer' ), 10, 2 );
		add_action( 'premise_cancel_subscription', array( $this, 'unsubscribe_customer' ), 10, 3 );

		// product post type hooks
		add_filter( 'memberaccess_default_product_meta', array( $this, 'add_default_product_meta' ) );
		add_action( 'admin_menu', array( $this, 'add_metabox' ) );

		// identify as opt in gateway
		$this->opt_in_gateway_name = 'MailChimp';

		// never show this gateway on the checkout form
		return false;

	}

	function add_metabox() {

		add_meta_box( 'accesspress-product-mailchimp-metabox', __( 'MailChimp', 'premise' ), array( $this, 'product_metabox' ), 'acp-products', 'normal', 'low' );

	}

	function product_metabox() {

		global $Premise;
		$lists = $Premise->getMailChimpLists();

		if ( empty( $lists ) ) {
			_e( 'No MailChimp lists found.', 'premise' );
			return;
		}
		?>
		<p>
			<label><strong><?php _e( 'MailChimp List', 'premise' ); ?></strong>:
			<select name="accesspress_product_meta[_acp_product_mailchimp_list]">
				<option value=""><?php _e( 'None', 'premise' ); ?></option>
		<?php

		foreach( $lists as $list )
			printf( '<option value="%s" %s>%s</option>', $list['id'], selected( accesspress_get_custom_field( '_acp_product_mailchimp_list' ), $list['id'], false ), $list['name'] );

		?>
			</select>
			</label>
		</p>
		<p>
			<label><strong><?php _e( 'Double Opt In', 'premise' ); ?></strong>:
			<select name="accesspress_product_meta[_acp_product_mailchimp_optin]">
				<option value=""><?php _e( 'Use Premise Main Setting', 'premise' ); ?></option>
				<option value="on" <?php selected( accesspress_get_custom_field( '_acp_product_mailchimp_optin' ), 'on' ); ?>><?php _e( 'Require', 'premise' ); ?></option>
				<option value="off" <?php selected( accesspress_get_custom_field( '_acp_product_mailchimp_optin' ), 'off' ); ?>><?php _e( 'Disable', 'premise' ); ?></option>
			</select>
			</label>
		</p>
		<?php

		$args = array(
			'title' => __( 'Add customers who purchase this product in MailChimp by identifying the fields.', 'premise' ),
			'field_format' => '%1$s : <input class="regular-text" type="text" name="accesspress_product_meta[_acp_product_mailchimp_%2$s]" id="accesspress_product_meta[_acp_product_mailchimp_%2$s]" value="%3$s" />',
			'data' => array(
				'email' => accesspress_get_custom_field( '_acp_product_mailchimp_email' ),
				'first-name' => accesspress_get_custom_field( '_acp_product_mailchimp_first-name' ),
				'last-name' => accesspress_get_custom_field( '_acp_product_mailchimp_last-name' ),
			    ),
		);
		$this->metabox_merge_fields( $args );
	}

	public function optin_metabox( $meta ) {

		$meta = wp_parse_args( $meta,
			array(
				'member-merge-email' => 'EMAIL',
				'member-merge-first-name' => '',
				'member-merge-last-name' => '',
			)
		);
?>
		<h4><?php _e('MailChimp', 'premise' ); ?></h4>
<?php
		$args = array(
			'title' => __( 'Create a list of these customers in MailChimp by identifying the fields.', 'premise' ),
			'field_format' => '%1$s : <input class="regular-text" type="text" name="premise[member-merge-%2$s]" id="premise-main-member-merge-%2$s" value="%3$s" />',
			'data' => array(
				'email' => $meta['member-merge-email'],
				'first-name' => $meta['member-merge-first-name'],
				'last-name' => $meta['member-merge-last-name'],
			    ),
		);
		$this->metabox_merge_fields( $args );

	}
	public function optin_extra_fields( $meta ) {

		$args = array(
			'heading_text' => false,
			'label_separator' => '*',
			'wrap_before' => '',
			'wrap_after' => '',
			'before_item' => '<li>',
			'after_item' => '</li>',
			'show_email_address' => empty( $meta['member-merge-email'] ),
			'show_first_name' => empty( $meta['member-merge-first-name'] ),
			'show_last_name' => empty( $meta['member-merge-last-name'] ),
			'product_id' => $meta['member-product'],
			'script_email_id' => '#mailchimp-EMAIL',
			'script_submit_control'	=> '#mailchimp-signup',
		);

		printf( '<input type="hidden" name="accesspress-checkout[product_id]" id="accesspress-checkout-product-id" value="%s" />', $meta['member-product'] );
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
		if ( ! empty( $meta['member-merge-first-name'] ) && isset( $merge_vars[$meta['member-merge-first-name']] ) )
			$args['first-name'] = $merge_vars[$meta['member-merge-first-name']];

		if ( ! empty( $meta['member-merge-last-name'] ) && isset( $merge_vars[$meta['member-merge-last-name']] ) )
			$args['last-name'] = $merge_vars[$meta['member-merge-last-name']];

		if ( accesspress_get_option( 'email_as_username' ) )
			$args['username'] = ! empty( $args['email'] ) ? $args['email'] : $merge_vars[$meta['member-merge-email']];

		if ( ! $args['first-name'] || ! $args['last-name'] || ! $args['username'])
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

		$optin_vars = array();
		// eliminate case mismatches
		foreach( (array) $args as $key => $value )
			$optin_vars[strtolower( $key )] = $value;

		$userdata = array(
			'first_name' => empty( $this->_premise_meta['member-merge-first-name'] ) ? $this->_member_args['first-name'] : $optin_vars[strtolower( $this->_premise_meta['member-merge-first-name'] )],
			'last_name'  => empty( $this->_premise_meta['member-merge-last-name'] ) ? $this->_member_args['last-name'] : $optin_vars[strtolower( $this->_premise_meta['member-merge-last-name'] )],
			'user_email' => empty( $this->_premise_meta['member-merge-email'] ) ? '' : $optin_vars[strtolower( $this->_premise_meta['member-merge-email'] )],
			'user_login' => $this->_member_args['username'],
			'user_pass'  => $this->_member_args['password'],
		);

		return accesspress_create_member( $userdata );

	}
	public function complete_order( $member ) {

		if ( ! $this->_product_id || ! $member )
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

	private function metabox_merge_fields( $args ) {

		$args = wp_parse_args( $args, array(
			'title' => '',
			'field_format' => '',
			'data' => array(
				'email' => '',
				'first-name' => '',
				'last-name' => '',
			    ),
		) );

		$data = (array)$args['data'];
		$field_labels = apply_filters( 'premise_mailchimp_metabox_merge_field_labels', array(
			'email' => __( 'Email Address', 'premise' ),
			'first-name' => __( 'First Name', 'premise' ),
			'last-name' => __( 'Last Name', 'premise' ),
		) );
?>
		<p><?php echo esc_html( $args['title'] ); ?></p>
		<p><?php _e( 'MailChimp List Merge Field(s):', 'premise' ); ?></p>
		<p>
			<ul>
			<?php
				foreach( $data as $name => $value ) {
			?>
				<li><?php printf( $args['field_format'], ! empty( $field_labels[$name] ) ? $field_labels[$name] : '', $name, esc_attr( $value ) ); ?></li>
			<?php
				}
			?>
			</ul>
		</p>
<?php
	}

	function subscribe_customer( $member, $order_details ) {

		$product_id = isset( $order_details['_acp_order_product_id'] ) ? $order_details['_acp_order_product_id'] : 0;
		$user = get_user_by( 'id', $member );
		if ( ! $product_id || ! $user )
			return;

		$list_id = get_post_meta( $product_id, '_acp_product_mailchimp_list', true );
		if ( ! $list_id )
			return;

		$merge_vars = array();
		foreach( array( 'email' => 'user_email', 'first-name' => 'first_name', 'last-name' => 'last_name' ) as $field => $user_field ) {

			$merge_field = get_post_meta( $product_id, '_acp_product_mailchimp_' . $field, true );
			if ( ! $merge_field )
				continue;

			if ( $field == 'email' )
				$merge_vars[$merge_field] = $user->$user_field;
			else
				$merge_vars[$merge_field] = get_user_meta( $user->ID, $user_field, true );

		}

		if ( empty( $merge_vars ) )
			return;

		// override main setting when setting present on product
		$double_opt_in = get_post_meta( $product_id, '_acp_product_mailchimp_optin', true );
		if ( ! empty( $double_opt_in ) )
			add_filter( 'premise_mailchimp_subscribe_double_opt_in', $double_opt_in == 'off' ? '__return_false' : '__return_true' );

		require_once( PREMISE_LIB_DIR . 'theme/class-theme.php' );
		Premise_Theme::signup_mailchimp_user( $merge_vars, $list_id );

	}

	function unsubscribe_customer( $order_id, $product_id, $member ) {

		$user = get_user_by( 'id', $member );
		if ( ! $product_id || ! $user )
			return;

		$list_id = get_post_meta( $product_id, '_acp_product_mailchimp_list', true );
		if ( ! $list_id )
			return;

		require_once( PREMISE_LIB_DIR . 'theme/class-theme.php' );
		$x = Premise_Theme::unsubscribe_mailchimp_user( $user->user_email, $list_id );

	}
}

add_action( 'memberaccess_setup', 'premise_register_mailchimp_optin_gateway' );

function premise_register_mailchimp_optin_gateway() {

	memberaccess_register_payment_gateway( 'MemberAccess_Optin_Gateway' );

}
