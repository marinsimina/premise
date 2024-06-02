<?php
/**
 * This file controls the member access control system in AccessPress.
 *
 * MemberAccess uses a custom user Role to segregate members from other users.
 * The custom role is assigned to members upon successful signup, and an access
 * level is assigned to their user meta, depending on what product they purchased.
 *
 * @package Premise
 */


/**
 * Add our master role, "Premise Member".
 *
 * @since 0.1.0
 */
function accesspress_create_role() {

	if ( get_role( 'premise_member' ) )
		return;

	add_role(
		'premise_member',
		__( 'Premise Member', 'premise' ),
		array(
			'access_membership' => true
		) 
	);
	
}

/**
 * Helper function to insert user into the Users table.
 *
 * Accepts same arguments as the WordPress function wp_insert_user()
 * @link http://xref.yoast.com/trunk/_functions/wp_insert_user.html
 *
 * @since 0.1.0
 *
 */
function accesspress_create_member( $userdata = array() ) {
	
	$userdata['role'] = 'premise_member';
	if ( ! isset( $userdata['show_admin_bar_front'] ) )
		$userdata['show_admin_bar_front'] = 'false';
	
	$user_id = wp_insert_user( $userdata );
/*
//@todo: initial code for ticket #360 - needs a redirect so cookie can be saved
	if ( ! is_wp_error( $user_id ) ) {

		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user_id );

	}
*/
	do_action( 'premise_create_member', $user_id, $userdata, null );
	
	return $user_id;
	
}

function memberaccess_checkout_css() {
?>
<style type="text/css">/*
.premise-checkout-wrap .accesspress-checkout-form-account,
.premise-checkout-wrap .accesspress-checkout-form-payment-method,
.premise-checkout-wrap .accesspress-checkout-form-cc {
	margin-bottom: 40px;
}
.premise-checkout-wrap .accesspress-checkout-form-row {
	clear: both;
}
.premise-checkout-wrap .checkout-text-label {
	display: block;
	float: left;
	padding: 6px 0;
	width: 135px;
}
.premise-checkout-wrap .accesspress-checkout-form-row {
	margin-bottom: 10px;
}
.premise-checkout-wrap .input-text {
	background: #f5f5f5;
	border: 1px solid #ddd;
	padding: 5px;
}
.premise-checkout-wrap .checkout-radio {
	margin-left: 140px;
}
.accesspress-checkout-form-payment-method input[type=radio] {
	vertical-align: top;
}
.premise-checkout-wrap .input-submit {
	background-color: #666;
	border: 0;
	color: #fff;
	cursor: pointer;
	padding: 8px 10px;
}
.premise-checkout-wrap .input-submit:hover {
	background-color: #333;
}
.premise-checkout-lookup {
	clear: left;
}*/
</style>
<?php
}

/** from wp-login.php */
function memberaccess_ssl_redirect() {

	if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) )
		wp_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
	else
		wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	exit();

}

function memberaccess_is_page( $page_id ) {

	global $premise_base;

	if ( is_page( $page_id ) )
		return true;

	if ( ! $premise_base->is_premise_post_type() )
		return false;

	return in_array( get_queried_object_id(), (array) $page_id );

}

add_action( 'wp', 'premise_process_checkout_form' );
/**
 * Process checkout & log new member in if checkout is complete
 */
function premise_process_checkout_form() {

	if ( is_admin() )
		return;

	$premise_checkout = Premise_Checkout::get_instance();
	$checkout_complete = $premise_checkout->process_checkout();

	if ( is_wp_error( $checkout_complete ) || ! isset( $checkout_complete['member_id'] ) )
		return;

	$checkout_args = $premise_checkout->get_checkout_args();
	if ( ! is_user_logged_in() && isset( $checkout_args['password'] ) ) {

		wp_clear_auth_cookie();
		wp_set_auth_cookie( $checkout_complete['member_id'] );
		wp_set_current_user( $checkout_complete['member_id'] );

		$member = get_user_by( 'id', $checkout_complete['member_id'] );
		if ( $member )
			do_action( 'wp_login', $member->user_login, $member );

	}

	if ( ! isset( $checkout_args['product_id'] ) )
		return;

	$thank_you_page = accesspress_get_custom_field( '_acp_product_thank_you', '', $checkout_args['product_id'] );
	$thank_you_url = $thank_you_page ? get_permalink( $thank_you_page ) : '';
	if ( ! $thank_you_url )
		return;

	do_action( 'premise_checkout_complete_thank_you', $checkout_args, $checkout_args['product_id'], $premise_checkout->get_args(), $checkout_complete );

	wp_redirect( add_query_arg( array( 'thank-you' => 'true' ), $thank_you_url ) );
	exit;

}

add_action( 'init', 'premise_admin_redirect_member' );

function premise_admin_redirect_member() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;

	if ( ! is_admin() || ! is_user_logged_in() || current_user_can( 'read' ) )
		return;

	wp_redirect( home_url() );
	exit;

}

add_filter( 'manage_users_columns', 'memberaccess_manage_users_columns' );

function memberaccess_manage_users_columns( $columns ) {

	$columns['member-access'] = __( 'Access Levels', 'premise' );
	return $columns;

}

add_filter( 'manage_users_custom_column', 'memberaccess_manage_users_custom_column', 1, 3 );

function memberaccess_manage_users_custom_column( $content, $column_name, $user_id ) {

	if( $column_name != 'member-access' )
		return;

	$terms = get_terms( 'acp-access-level', array( 'hide_empty' => false ) );

	if ( ! $terms )
		return '';

	$output = '';

	foreach ( (array) $terms as $term ) {

		if ( ! member_has_access_level( $term->term_id, $user_id ) )
			continue;

		$output .= esc_html( $term->name ) . '<br />';

	}

	return $output;

}

add_action( 'template_redirect', 'memberaccess_location_check', 1 );

function memberaccess_location_check() {

	$checkout_page = accesspress_get_option( 'checkout_page' );
	/** check for ssl */
	if ( ! is_ssl() ) {

		if ( accesspress_get_option( 'ssl_everywhere' ) )
			memberaccess_ssl_redirect();

		if ( $checkout_page && accesspress_get_option( 'ssl_checkout' ) && memberaccess_is_page( $checkout_page ) )
			memberaccess_ssl_redirect();

		$login_page = accesspress_get_option( 'login_page' );
		if ( $login_page && force_ssl_admin() && memberaccess_is_page( $login_page ) )
			memberaccess_ssl_redirect();

	}

	$member_page = accesspress_get_option( 'member_page' );
	if ( ! $checkout_page && ! $member_page )
		return;

	if ( memberaccess_is_page( array( $checkout_page, $member_page ) ) ) {

		wp_enqueue_script( 'jquery' );
		add_action( 'wp_head', 'memberaccess_checkout_css', 99 );

	}
}

add_filter( 'user_row_actions', 'memberaccess_user_row_actions', 10, 2 );

function memberaccess_user_row_actions( $actions, $user ) {

	$comp_url = add_query_arg( array( 'post_type' => 'acp-orders', 'member' => $user->ID ), admin_url( 'post-new.php' ) );
	$actions['member_comp'] = sprintf( '<br /><a href="%s">%s</a>', wp_nonce_url( $comp_url, 'comp-product-' . $user->ID ), __( 'Complimentary Product', 'premise' ) );

	return $actions;

}
add_action( 'init', 'memberaccess_profile_update' );

function memberaccess_profile_update() {

	global $memberaccess_profile_user_id;

	$user = wp_get_current_user();
	$nonce_key = 'premise-member-profile-' . $user->ID;
	$query_args = array();

	if ( empty( $_POST['accesspress-checkout']['member-key'] ) || ! wp_verify_nonce( $_POST['accesspress-checkout']['member-key'], $nonce_key ) )
		return;

	$user_data = stripslashes_deep( $_POST['accesspress-checkout'] );
	$user_changes = array( 'ID' => $user->ID );

	if ( $user_data['first-name'] != $user->first_name )
		$user_changes['first_name'] = $user_data['first-name'];

	if ( $user_data['last-name'] != $user->last_name )
		$user_changes['last_name'] = $user_data['last-name'];

	if ( ! empty( $user_changes['first_name'] ) || ! empty( $user_changes['last_name'] ) )
		$query_args['name-changed'] = 'true';

	if ( ! empty( $user_data['password'] ) && ! empty( $user_data['password-repeat'] ) && $user_data['password'] == $user_data['password-repeat'] )
		$user_changes['user_pass'] = $user_data['password'];

	if ( ! empty( $user_data['password'] ) && empty( $user_changes['user_pass'] ) )
		$query_args['password-changed'] = 'fail';

	if ( ! empty( $user_data['email'] ) && is_email( $user_data['email'] ) && $user_data['email'] != $user->user_email ) {

		// check for conflict
		if ( get_user_by( 'login', $user_data['email'] ) || get_user_by( 'email', $user_data['email'] ) )
			$query_args['email-changed'] = 'used';
		else
			$user_changes['user_email'] = $user_data['email'];

	}

	if ( is_email( $user->user_login ) && isset( $user_changes['user_email'] ) )
		$memberaccess_profile_user_id = $user->ID;

	if ( count( $user_changes ) > 1 )
		wp_update_user( $user_changes );

	// if we change the password the user needs to log in again
	if ( isset( $user_changes['user_pass'] ) || ( isset( $user_changes['user_email'] ) && accesspress_get_option( 'email_as_username' ) ) )
		$query_args['credentials-changed'] = 'true';

	// allow plugins to add & update on the member profile page - but don't pass them the password
	unset( $user_data['password'], $user_data['password-repeat'] );
	do_action( 'premise_member_profile_page_update', $user_data );

	wp_redirect( add_query_arg( $query_args, get_permalink( accesspress_get_option( 'member_page' ) ) ) );
	exit;

}

add_filter( 'upload_dir', 'memberaccess_ssl_upload_dir' );

function memberaccess_ssl_upload_dir( $uploads ) {

	$ssl_checkout = accesspress_get_option( 'ssl_checkout' ) && memberaccess_is_page( accesspress_get_option( 'checkout_page' ) );
	if ( ! $ssl_checkout && ! accesspress_get_option( 'ssl_everywhere' ) )
		return $uploads;

	foreach( array( 'url', 'baseurl' ) as $key )
		$uploads[$key] = preg_replace( '|^http://|', 'https://', $uploads[$key] );

	return $uploads;
}

add_action( 'wp_ajax_nopriv_premise_checkout_lookup', 'memberaccess_checkout_lookup' );

function memberaccess_checkout_lookup() {

	$args = wp_parse_args( $_POST, array(
		'username' => '',
		'email' =>  '',
		'product' =>  '',
		'auth' =>  ''

	) );

	if ( ! wp_verify_nonce( $args['auth'], 'checkout-lookup-' . $args['product'] ) )
		echo '1';
	elseif ( ! is_email( $args['email'] ) || get_user_by( 'email', $args['email'] ) )
		echo '1';
	elseif ( ! accesspress_get_option( 'email_as_username' ) && get_user_by( 'login', $args['username'] ) )
		echo '1';
	else
		echo '0';

	exit;

}

add_filter( 'pre_user_email', 'memberaccess_sync_user_login_email' );

function memberaccess_sync_user_login_email( $email ) {

	global $wpdb, $memberaccess_profile_user_id;

	if ( empty( $memberaccess_profile_user_id ) || ! accesspress_get_option( 'email_as_username' ) )
		return $email;

	$update_user = get_user_by( 'id', $memberaccess_profile_user_id );
	if ( ! $update_user )
		return $email;

	if ( ! is_email( $update_user->user_login ) || $update_user->user_login == $email )
		return $email;

	// check for existing username
	$existing_user = get_user_by( 'login', $email );
	if ( $existing_user )
		return $email;

	$wpdb->update( $wpdb->users, array( 'user_login' => $email ), array( 'ID' => $memberaccess_profile_user_id ) );

	return $email;

}

add_action( 'edit_user_profile_update', 'memberaccess_hook_user_email_change' );

function memberaccess_hook_user_email_change( $user_id ) {

	global $memberaccess_profile_user_id;

	$memberaccess_profile_user_id = $user_id;

}