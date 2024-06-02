<?php

/*
 * Plugin Name: vBulletin Bridge
 * Plugin URI: http://www.aaronforgue.com
 * Description: vBulletin Bridge Proof of Concept
 * Author: Aaron Forgue
 * Version: 1.1
 * Author URI: http://www.aaronforgue.com
 */

/*
 * modified by Copyblogger Media to
 * - convert to class
 * - initalize vBulletin in wp-config.php
 * - hook user creation into membership
 */

class Premise_vBulletin_Bridge {

	/**
	 * Flag indicating whether vBulletin 5 is installed
	 *
	 * @since 2.4
	 *
	 * @var boolean _is_vB5
	 */
	var $_is_vB5;

	/**
	 * initialize the class and hook into WordPress & Premise
	 *
	 * @since 2.0
	 *
	 * 
	 */
	function __construct() {

		$this->_is_vB5 = class_exists( 'vB_Datamanager_User' );

		// member status hooks
		add_action( 'premise_create_member', array( $this, 'member_create' ), 10, 3 );
		add_action( 'premise_membership_create_order', array( $this, 'add_member_to_group' ), 10, 3 );
		add_action( 'premise_cancel_subscription', array( $this, 'remove_member_from_group' ), 10, 4 );
		add_action( 'premise_membership_refund_order', array( $this, 'change_member_groups' ), 10, 3 );

		if ( $this->_is_vB5 ) {

			add_action( 'premise_checkout_account_after', array( $this, 'add_username_to_member_profile' ) );
			add_action( 'premise_member_profile_page_update', array( $this, 'update_username_from_member_profile' ) );
			add_action( 'wp_ajax_premise_forum_username_lookup', array( $this, 'ajax_username_lookup' ) );

		}

		// Auto-log in/out hook
		add_action( 'wp_login', array( $this, 'member_login' ) );
		add_action( 'wp_logout', array( $this, 'member_logout' ) );

		// product post type hooks
		add_filter( 'memberaccess_default_product_meta', array( $this, 'add_default_product_meta' ) );
		add_action( 'admin_menu', array( $this, 'add_metabox' ) );

	 	add_action( 'edit_user_profile', array( $this, 'user_profile' ), 9 );
	 	add_action( 'show_user_profile', array( $this, 'user_profile' ), 9 );
	 	add_action( 'edit_user_profile_update', array( $this, 'user_profile_update' ) );
	 	add_action( 'personal_options_update', array( $this, 'user_profile_update' ) );

	}

	/**
	 * Add member to vBulletin when either global or product vBulletin group is set
	 *
	 * @since 2.0
	 *
	 */
	function member_create( $user_id, $user_data, $member_group = false ) {

		global $product_post;

		if ( is_wp_error( $user_id ) || ! $user_id )
			return;

		if ( empty( $user_data['user_login'] ) || empty( $user_data['user_email'] ) || empty( $user_data['user_pass'] ) )
			return;

		if ( ! $member_group && ! empty( $product_post ) )
			$member_group = get_post_meta( $product_post, '_acp_product_vbulletin_group', true );

		if ( ! $member_group )
			$member_group = accesspress_get_option( 'vbulletin_group' );

		if ( ! $member_group )
			return;
			
		$vb_user_id = $this->add_member_to_forum( $user_data );
		if ( $vb_user_id )
			update_user_meta( $user_id, 'vbulletin_user_id', $vb_user_id );

	}

	/**
	 * Log member into vBulletin forum when the member logs into WordPress
	 *
	 * @since 2.0
	 *
	 */
	function member_login( $user_login, $member = null, $vb_user_id = null ) {

		if ( $vb_user_id === null ) {

			$wp_user_data = get_user_by( 'login', $user_login );
			if ( ! $wp_user_data )
				return;

			$vb_user_id = get_user_meta( $wp_user_data->ID, 'vbulletin_user_id', true );

		}

		if ( empty( $vb_user_id ) )
			return;

		if ( $this->_is_vB5 ) {

			$result = vB_User::processNewLogin( array( 'userid' => $vb_user_id ) );
			vB5_Auth::setLoginCookies( $result );
			return;

		}

		include( VBULLETIN_PATH . '/includes/functions_login.php' );
		$GLOBALS['vbulletin']->userinfo = verify_id( 'user', $vb_user_id, true, true, 0 );
		process_new_login( null, 0, null );
		$GLOBALS['vbulletin']->session->save();

	}

	/**
	 * Log member out of vBulletin forum when the member logs out of WordPress
	 *
	 * @since 2.5.0
	 *
	 */
	function member_logout() {

		if ( ! class_exists( 'vB5_Cookie' ) )
			return;

		$expire = time() - 3600;
		vB5_Cookie::set('sessionhash', '', $expire, true);
		vB5_Cookie::set('password', '', $expire);
		vB5_Cookie::set('userid', '', $expire);

	}
	/**
	 * Add member to vBulletin permission group
	 *
	 * @since 2.4
	 *
	 */
	function add_member_to_group( $member, $order_details, $renewal ) {

		if ( $renewal || is_wp_error( $member ) || ! $member )
			return;

		if ( ! empty( $order_details['_acp_order_product_id'] ) )
			$member_group = get_post_meta( $order_details['_acp_order_product_id'], '_acp_product_vbulletin_group', true );

		if ( empty( $member_group ) )
			$member_group = accesspress_get_option( 'vbulletin_group' );

		if ( ! $member_group )
			return;

		$vb_user_id = get_user_meta( $member, 'vbulletin_user_id', true );
		if ( empty( $vb_user_id ) ) {

			$user = get_user_by( 'id', $member );
			if ( ! $user )
				return;

			$user_data = array(
				'user_email' => $user->user_email,
				'user_login' => $user->user_login,
				'user_pass' => SHA1( serialize( $user ) . time() )
			);

			$vb_user_id = $this->add_member_to_forum( $user_data );
			if ( empty( $vb_user_id ) )
				return;

			update_user_meta( $member, 'vbulletin_user_id', $vb_user_id );

		}

		// get the vBulletin user
		$vb_user_data = $this->_get_user( $vb_user_id );

		// check for existing user
		$vb_primary_group = $vb_user_data->fetch_field( 'usergroupid' );
		if ( empty( $vb_primary_group ) || ! is_numeric( $vb_primary_group ) ) {

			$vb_user_data->set( 'usergroupid', $member_group );

		// user already has this primary group
		} elseif ( $vb_primary_group == $member_group ) {

			return;

		// add to secondary group
		} else {

			$secondary_groups = array( $member_group );
			$groups = $vb_user_data->fetch_field( 'membergroupids' );
			if ( ! empty( $groups ) ) {

				$secondary_groups = explode( ',', $groups );
				if ( in_array( $member_group, $secondary_groups ) )
					return;

				$secondary_groups[] = $member_group;
				sort( $secondary_groups );

			}

			$vb_user_data->set('membergroupids', implode( ',', $secondary_groups ) );
		}

		$vb_user_data->pre_save();
		if ( empty( $vb_user_data->errors ) )
			$vb_user_id = $vb_user_data->save();

	}
	/**
	 * Remove member from vBulletin permission group
	 *
	 * @since 2.5.0
	 *
	 */
	function remove_member_from_group( $order_id, $product_id, $member_id, $result ) {

		if ( is_wp_error( $result ) && $result->get_error_code() != 'no_gateway' )
			return;

		// is this a VB user?
		$vb_user_id = get_user_meta( $member_id, 'vbulletin_user_id', true );
		if ( empty( $vb_user_id ) )
			return;

		try {

			// get the vBulletin user
			$vb_user_data = $this->_get_user( $vb_user_id );
			if ( empty( $vb_user_id ) )
				return;

			$vb_primary_group = $vb_user_data->fetch_field( 'usergroupid' );
			$vb_secondary_groups = $vb_user_data->fetch_field( 'membergroupids' );

			$vb_groups = empty( $vb_secondary_groups ) ? array() : explode( ',', $vb_secondary_groups );
			$vb_groups[] = $vb_primary_group;
			$vb_groups = array_unique( $vb_groups );

			// does this product have a VB group?
			$product_group = get_post_meta( $product_id, '_acp_product_vbulletin_group', true );
			if ( ! empty( $product_group ) ) {

				$net_groups = array_diff( $vb_groups, array( $product_group ) );
	
				// remove if product group is the only group member has access to
				if ( empty( $net_groups ) )
					return self::remove_member_from_forum( $vb_user_data );

			}

			// is this the only product the member has
			$member_products = memberaccess_get_member_products( $member_id, 0, true );
			$net_products = array_diff( $member_products, array( $product_id ) );
			if ( empty( $net_products ) )
				return self::remove_member_from_forum( $vb_user_data );

			// this product does not have a group so nothing needs to be done
			if ( empty( $product_group ) )
				return;

			// need to change primary group?
			if ( $vb_primary_group == $product_group )
				$vb_primary_group = array_shift( $net_groups );
			else
				array_pop( $net_groups );

			if ( empty( $net_groups ) )
				$vb_secondary_groups = '';
			else
				$vb_secondary_groups = implode( ',', $net_groups );

			$vb_user_data->set( 'usergroupid', $vb_primary_group );
			$vb_user_data->set( 'membergroupids', $vb_secondary_groups );

			$vb_user_data->pre_save();
			if ( empty( $vb_user_data->errors ) )
				$vb_user_data->save();

		} catch( Exception $e ) {
			// prevent redirecting to VB
		}

	}
	/**
	 * Change vBulletin permission group on order refund
	 *
	 * @since 2.5.0
	 *
	 */
	 function change_member_groups( $order_id, $values, $refund_product ) {

		// check that order originally had a product
		if ( ! isset( $values['_acp_order_product_id'] ) )
			return;

		// is it the same product?
		if ( $values['_acp_order_product_id'] == $refund_product )
			return;

		// add to new group then remove from old group
		$remove_values = $values;
		$remove_values['_acp_order_product_id'] = $refund_product;
		$this->add_member_to_group( $values['_acp_order_member_id'], $values, false );
		$this->remove_member_from_group( $order_id, $values['_acp_order_product_id'], $values['_acp_order_member_id'], true );

	 }
	/**
	 * Remove member acces to vBulletin by adding to no access permission group
	 *
	 * @since 2.5.0
	 *
	 */
	function remove_member_from_forum( $vb_user_data ) {

		$cancel_group = accesspress_get_option( 'vbulletin_cancelled_group' );
		if ( empty( $cancel_group ) )
			return;

		$vb_user_data->set( 'usergroupid', $cancel_group );
		$vb_user_data->set( 'membergroupids', '' );

		$vb_user_data->pre_save();
		if ( empty( $vb_user_data->errors ) )
			$vb_user_data->save();

	}
	/**
	 * Add member user account to vBulletin
	 *
	 * @since 2.4
	 *
	 * @returns integer vBulletin user id
	 */
	function add_member_to_forum( $user_data ) {

		if ( $this->_is_vB5 ) {

			if ( accesspress_get_option( 'email_as_username' ) ) {

				$member = get_user_by( 'email', $user_data['user_email'] );
				if ( $member )
					$user_data['user_login'] = $member->first_name . $member->last_name;

				$search = $username = preg_replace( '|[^a-zA-Z0-9-\.]|', '', $user_data['user_login'] );

				// check for username is too short
				$vb_options =  vB_Api_Options::fetch( 'options' );
				$min_length = isset( $vb_options['options']['minuserlength'] ) ? $vb_options['options']['minuserlength'] : 3;
				while ( strlen( $username ) < $min_length )
					$search = $username .= rand( 1, 9 );

				// check for username already in use
				while ( $user = vB_Api::instanceInternal( "User" )->fetchByUsername( $search, array() ) )
					$search = $username . rand( 100, 999 );

			}

			$user_data['user_login'] = $search;
			$vb_user_data = new vB_Datamanager_User( $GLOBALS['vbulletin'], vB_DataManager_Constants::ERRTYPE_SILENT );

		} else {

			$vb_user_data = datamanager_init( 'User', $GLOBALS['vbulletin'], ERRTYPE_ARRAY );

		}

		$vb_user_data->set( 'email', $user_data['user_email'] );
		$vb_user_data->set( 'username', $user_data['user_login'] );
		$vb_user_data->set( 'password', $user_data['user_pass'] );

		$vb_user_data->pre_save();

		if ( empty( $vb_user_data->errors ) )
			return $vb_user_data->save();

		$to = get_option( 'admin_email' );
		$from = memberaccess_get_email_receipt_address();
		$from_description = accesspress_get_option( 'email_receipt_name' );
		$subject = __( 'Error create vBulletin account', 'premise' );
		$body = sprintf( __( "Username: %s\nEmail Address: %s\nError: %s\n\n%s", 'premise' ), $user_data['user_login'], $user_data['user_email'], current( $vb_user_data->errors ), get_option( 'blogname' ) );
		wp_mail( $to, $subject, $body, "From: \"{$from_description}\" <{$from}>" );

		return false;

	}

	/**
	 * Add default meta value to product post save 
	 *
	 * @since 2.1
	 *
	 * @returns array default product meta values
	 */
	function add_default_product_meta( $defaults ) {

		$defaults['_acp_product_vbulletin_group'] = '';
		return $defaults;

	}

	/**
	 * Add the vBulletin group metabox to the edit product screen
	 *
	 * @since 2.0
	 *
	 */
	function add_metabox() {

		add_meta_box( 'accesspress-product-vbulletin-metabox', __( 'vBulletin', 'premise' ), array( $this, 'product_metabox' ), 'acp-products', 'normal', 'low' );

	}

	/**
	 * output the product metabox
	 *
	 * @since 2.0
	 *
	 */
	function product_metabox() {
		?>
		<p>
			<label for="accesspress_product_meta[_acp_product_vbulletin_group]"><strong><?php _e( 'vBulletin User Group', 'premise' ); ?></strong>:
			<br />
			</label><input type="text" name="accesspress_product_meta[_acp_product_vbulletin_group]" id="accesspress_product_meta[_acp_product_vbulletin_group]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_product_vbulletin_group' ) ); ?>" />
			<br />
			<span class="description"><?php _e( 'Choose the vBulletin user group that Premise Members will be added to for this product.', 'premise' ); ?></span>
		</p>

		<?php
	}
	/**
	 * show the vBulletin User ID on profile edit.
	 *
	 * @since 2.5.0
	 */
	function user_profile( $user ) {
	
		if ( empty( $user->ID ) || ! is_admin() )
			return;
	
		printf( '<h3>%s</h3><table class="form-table"><tr>', __( 'vBulletin', 'premise' ) );
		printf( '<th>%s</th>', __( 'vBulletin User ID', 'premise' ) );
		printf( '<td><input type="text" name="vbulletin_user_id" value="%s" /></td>', get_user_meta( $user->ID, 'vbulletin_user_id', true ) );
		echo '</tr></table>';
	
	
	}
	/**
	 * update the vBulletin User ID on profile edit.
	 *
	 * @since 2.5.0
	 */
	function user_profile_update( $user_id ) {
	
		if ( ! is_admin() || ! current_user_can( 'edit_users', $user_id ) )
			return;
	
		if ( ! empty( $_POST['vbulletin_user_id'] ) )
			update_user_meta( $user_id, 'vbulletin_user_id', (int)$_POST['vbulletin_user_id'] );

	}

	/**
	 * show the vBulletin Username on member profile shortcode.
	 *
	 * @since 2.5.0
	 */
	function add_username_to_member_profile( $args ) {

		if ( ! is_user_logged_in() || ! accesspress_get_option( 'email_as_username' ) || ! accesspress_get_option( 'vbulletin_username' ) )
			return;

		if ( ! memberaccess_is_page( accesspress_get_option( 'member_page' ) ) )
			return;

		$vb_user_data = $this->_get_user();
		if ( ! $vb_user_data )
			return;

		$vb_username = $vb_user_data->fetch_field( 'username' );

		if ( ! $vb_username )
			return;

		printf( $args['before_item'], 'accesspress-checkout-forum-username' ); 
		?>
			<label for="accesspress-checkout-forum-username" class="checkout-text-label"><?php echo __( 'Forum Username', 'premise' ) . $args['label_separator']; ?></label>
			<input type="text" name="accesspress-checkout[forum-username]" id="accesspress-checkout-forum-username" class="input-text" value="<?php echo esc_attr( $vb_username ); ?>" />
		<?php 
		echo $args['after_item'];

		wp_nonce_field( 'forum-lookup-' . $vb_username, 'premise-forum-username-lookup' );

		$vb_options =  vB_Api_Options::fetch( 'options' );
		$min_length = isset( $vb_options['options']['minuserlength'] ) ? $vb_options['options']['minuserlength'] : 3;

		?>
		<span class="premise-forum-username-lookup accesspress-checkout-form-row premise-message" style="display: none;"><?php _e( 'Username not available.', 'premise' ); ?></span>
		<input type="hidden" id="premise-original-forum-username" value="<?php echo esc_attr( $vb_username ); ?>" />
		<input type="hidden" id="premise-forum-username-min-length" value="<?php echo esc_attr( $min_length ); ?>" />
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){

	// prevent the enter key submitting the form before the VB username is checked
	jQuery('#accesspress-checkout-forum-username').keydown(function(event) {
		jQuery('.input-submit').attr('disabled','disabled');
	});
	jQuery('#accesspress-checkout-forum-username').keyup(function(event) {
		jQuery('.input-submit').removeAttr('disabled');
		if (event.which != 13)
			return;

		jQuery('#accesspress-checkout-password').focus();
		event.stopImmediatePropagation();
	});

	jQuery('#accesspress-checkout-forum-username').blur(function(){
		ajaxurl='<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
		username = jQuery('#accesspress-checkout-forum-username').val();

		if (username == jQuery('#premise-original-forum-username').val()) {
			jQuery('.premise-forum-username-lookup').hide();
			jQuery('.input-submit').removeAttr('disabled');
			return;
		}

		if (!username || username.length < jQuery('#premise-forum-username-min-length').val())
			return;

		jQuery.post(
			ajaxurl,
			{
				action: 'premise_forum_username_lookup',
				username: username,
				old_username: jQuery('#premise-original-forum-username').val(),
				auth: jQuery('#premise-forum-username-lookup').val()
			},
			function(data, status) {
				if (data == '0') {
					jQuery('.premise-forum-username-lookup').hide();
					jQuery('.input-submit').removeAttr('disabled');
				} else {
					jQuery('.premise-forum-username-lookup').show();
					jQuery('.input-submit').attr('disabled','disabled');
				}
			},
			'text'
		);
	});
	// trigger an initial check after the browser has autofilled any fields
	setTimeout(function(){ jQuery('#accesspress-checkout-forum-username').blur() }, 250);
});
//-->
</script>
		<?php

	}

	/**
	 * update the vBulletin Username from member profile shortcode.
	 *
	 * @since 2.5.0
	 */
	function update_username_from_member_profile( $user_data ) {

		if ( ! accesspress_get_option( 'email_as_username' ) || ! accesspress_get_option( 'vbulletin_username' ) )
			return;

		if ( ! empty( $user_data['forum-username'] ) )
			$user_data['forum-username'] = trim( preg_replace( '|[^a-zA-Z0-9-\.\s]|', '', $user_data['forum-username'] ) );

		if ( empty( $user_data['forum-username'] ) )
			return;

		$vb_user_data = $this->_get_user();
		if ( ! $vb_user_data )
			return;

		$vb_username = $vb_user_data->fetch_field( 'username' );
		if ( $vb_username == $user_data['forum-username'] )
			return;

		$user = vB_Api::instanceInternal( "User" )->fetchByUsername( $user_data['forum-username'], array() );
		if ( $user )
			return;

		$vb_user_data->set( 'username', $user_data['forum-username'] );
		$vb_user_data->pre_save();

		if ( empty( $vb_user_data->errors ) )
			$vb_user_data->save();

	}

	/**
	 * check the changed vBulletin Username from member profile shortcode to see whether it is already in use.
	 *
	 * @since 2.5.0
	 */
	function ajax_username_lookup() {
	
		$args = wp_parse_args( $_POST, array(
			'username' => '',
			'old_username' =>  '',
			'auth' =>  ''
	
		) );

		if ( ! wp_verify_nonce( $args['auth'], 'forum-lookup-' . $args['old_username'] ) )
			echo '1';
		elseif ( vB_Api::instanceInternal( "User" )->fetchByUsername( $args['username'], array() ) )
			echo '1';
		else
			echo '0';

		exit;
	
	}


	/**
	 * get the vBulletin user info
	 *
	 * @since 2.5.0
	 */
	private function _get_user( $vb_user_id = null ) {
		
		if ( ! $vb_user_id ) {

			$member_id = get_current_user_id();
			$vb_user_id = get_user_meta( $member_id, 'vbulletin_user_id', true );

		}

		if ( ! $vb_user_id )
			return false;

		if ( $this->_is_vB5 ) {

			$user_info = vB_User::fetchUserInfo( $vb_user_id );
			$vb_user_data = new vB_Datamanager_User( $GLOBALS['vbulletin'], vB_DataManager_Constants::ERRTYPE_SILENT );

		} else {

			$vb_user_data = datamanager_init( 'User', $GLOBALS['vbulletin'], ERRTYPE_ARRAY );
			$user_info = fetch_userinfo( $vb_user_id );

		}

		$vb_user_data->set_existing( $user_info );

		return $vb_user_data;

	}

}